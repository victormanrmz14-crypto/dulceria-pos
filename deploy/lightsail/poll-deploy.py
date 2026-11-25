#!/usr/bin/env python3
"""Pull CI-approved main commits from this public repository without credentials."""
import argparse
from datetime import datetime, timezone
import fcntl
import json
import os
from pathlib import Path
import re
import signal
import subprocess
import tempfile
import time
import urllib.request

REPO = "victormanrmz14-crypto/dulceria-pos"
ROOT = Path("/srv/dulceria-pos")
STATE = Path("/var/lib/dulceria-pos/cd")
COMPOSE = ["sudo", "-n", "docker", "compose", "-f", "docker-compose.yml", "-f", "docker-compose.lightsail.yml"]


def atomic_write(path, text, mode=0o600):
    fd, temporary = tempfile.mkstemp(prefix=".cd-", dir=path.parent)
    try:
        with os.fdopen(fd, "w") as stream:
            os.fchmod(stream.fileno(), mode)
            stream.write(text)
            stream.flush()
            os.fsync(stream.fileno())
        os.replace(temporary, path)
    finally:
        if os.path.exists(temporary):
            os.unlink(temporary)


def replace_env(text, values):
    for name, value in values.items():
        if not re.fullmatch(r"[A-Z_]+", name) or "\n" in value or "\r" in value:
            raise ValueError("Invalid environment update")
        pattern = r"^" + name + r"=.*$"
        if re.search(pattern, text, re.MULTILINE):
            text = re.sub(pattern, lambda _: name + "=" + value, text, flags=re.MULTILINE)
        else:
            text = text.rstrip("\n") + "\n" + name + "=" + value + "\n"
    return text


def approved_run(payload):
    runs = payload.get("workflow_runs", [])
    if not runs:
        return None
    run = runs[0]
    if (run.get("status") != "completed" or run.get("conclusion") != "success"
            or run.get("event") != "push" or run.get("head_branch") != "main"
            or (run.get("head_repository") or {}).get("full_name") != REPO
            or run.get("path") != ".github/workflows/ci.yml"
            or not isinstance(run.get("id"), int) or run["id"] <= 0
            or not re.fullmatch(r"[0-9a-f]{40}", run.get("head_sha", ""))):
        return None
    return run


def command(args, log, capture=False):
    result = subprocess.run(args, cwd=ROOT, text=True, check=True,
                            stdout=subprocess.PIPE if capture else log,
                            stderr=log, timeout=1500)
    return result.stdout.strip() if capture else None


def health(log):
    command(COMPOSE + ["exec", "-T", "app", "php", "docker/check-assets.php", "http://nginx"], log)
    for path in ["/up", "/login"]:
        command(["curl", "--fail", "--silent", "--show-error", "--retry", "12",
                 "--retry-all-errors", "--retry-delay", "2", "--max-time", "15",
                 "--output", "/dev/null", "https://13.184.138.10" + path], log)


def launch(log):
    command(COMPOSE + ["up", "-d", "--no-build", "--no-deps", "--force-recreate", "app", "nginx", "worker"], log)
    for cache in ["config:cache", "route:cache", "view:cache"]:
        command(COMPOSE + ["exec", "-T", "app", "php", "artisan", cache], log)
    # Refresh bind-mounted configuration even if git replaced its inode.
    # Certificate data remains in its persistent named volume.
    command(COMPOSE + ["up", "-d", "--no-build", "--no-deps", "--force-recreate", "caddy"], log)


def deploy(run, state):
    sha = run["head_sha"]
    log_path = STATE / ("deploy-" + sha + ".log")
    with os.fdopen(os.open(log_path, os.O_WRONLY | os.O_CREAT | os.O_APPEND, 0o600), "a") as log:
        command(["git", "fetch", "origin", "main"], log)
        if command(["git", "rev-parse", "origin/main"], log, capture=True) != sha:
            print("CI result superseded by another push; waiting for its CI.")
            return
        if command(["git", "status", "--porcelain", "--untracked-files=no"], log, capture=True):
            raise RuntimeError("Tracked server edits exist; refusing to overwrite them")
        previous_sha = state.get("commit") or command(["git", "rev-parse", "HEAD"], log, capture=True)
        previous_env = (ROOT / ".env").read_text()
        rollout_started = False
        try:
            command(["git", "checkout", "--detach", sha], log)
            atomic_write(ROOT / ".env", replace_env(previous_env, {"APP_IMAGE_TAG": sha}))
            (ROOT / ".deploy-status").mkdir(mode=0o755, exist_ok=True)
            command(COMPOSE + ["build", "app", "nginx"], log)
            # Migrate with the approved image before replacing live web containers.
            command(COMPOSE + ["run", "--rm", "--no-deps", "--no-build", "-T", "app",
                               "php", "artisan", "migrate", "--force"], log)
            rollout_started = True
            launch(log)
            health(log)
            # Refresh the stable poller from the CI-approved deployed source.
            command(["sudo", "-n", "install", "-m", "755", str(ROOT / "deploy/lightsail/poll-deploy.py"),
                     "/usr/local/lib/dulceria-pos/poll-deploy.py"], log)
            release = {"commit": sha, "ci_run_id": run["id"], "status": "healthy",
                       "deployed_at": datetime.now(timezone.utc).isoformat()}
            atomic_write(STATE / "state.json", json.dumps(release) + "\n")
            atomic_write(ROOT / ".deploy-status/release.json", json.dumps(release) + "\n", 0o644)
            print("Deployed healthy commit", sha, flush=True)
        except Exception:
            atomic_write(ROOT / ".env", previous_env)
            command(["git", "checkout", "--detach", previous_sha], log)
            if rollout_started:
                launch(log)
                health(log)
            failed = {**state, "failed_commit": sha, "retry_after": time.time() + 900}
            atomic_write(STATE / "state.json", json.dumps(failed) + "\n")
            print("Deployment failed; previous application restored. Private log:", log_path, flush=True)
            # Database migrations are not automatically undone.
            raise


def interrupted(signum, frame):
    raise RuntimeError("Deployment interrupted by signal " + str(signum))


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--check-only", action="store_true")
    args = parser.parse_args()
    signal.signal(signal.SIGTERM, interrupted)
    STATE.mkdir(mode=0o700, parents=True, exist_ok=True)
    with (STATE / "lock").open("a") as lock:
        try:
            fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
        except BlockingIOError:
            print("Another deployment is running.")
            return
        state_file = STATE / "state.json"
        state = json.loads(state_file.read_text()) if state_file.exists() else {}
        request = urllib.request.Request(
            f"https://api.github.com/repos/{REPO}/actions/workflows/ci.yml/runs?branch=main&event=push&per_page=1",
            headers={"Accept": "application/vnd.github+json", "User-Agent": "Dulceria-Pull-CD",
                     "X-GitHub-Api-Version": "2022-11-28"},
        )
        with urllib.request.urlopen(request, timeout=20) as response:
            run = approved_run(json.load(response))
        if not run:
            print("Latest main CI has not succeeded; no deployment.")
            return
        sha = run["head_sha"]
        if sha == state.get("commit"):
            print("Already deployed:", sha)
            return
        if state.get("failed_commit") == sha and time.time() < state.get("retry_after", 0):
            print("Waiting before retrying failed release:", sha)
            return
        print("CI-approved candidate:", sha, flush=True)
        if not args.check_only:
            deploy(run, state)


if __name__ == "__main__":
    main()
