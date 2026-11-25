#!/usr/bin/env python3
"""Activate a DNS-verified domain without regenerating application/database secrets."""
import argparse
import fcntl
import importlib.util
import ipaddress
import json
import os
from pathlib import Path
import re
import urllib.parse
import urllib.request

spec = importlib.util.spec_from_file_location("pull_cd", Path(__file__).with_name("poll-deploy.py"))
cd = importlib.util.module_from_spec(spec)
spec.loader.exec_module(cd)


def check_dns(domain, address):
    for host in [domain, "www." + domain]:
        for kind in ["A", "AAAA"]:
            url = "https://dns.google/resolve?" + urllib.parse.urlencode({"name": host, "type": kind})
            with urllib.request.urlopen(url, timeout=15) as response:
                answers = json.load(response).get("Answer", [])
            values = {entry["data"] for entry in answers if entry.get("type") == (1 if kind == "A" else 28)}
            if (kind == "A" and values != {address}) or (kind == "AAAA" and values):
                raise SystemExit("DNS not ready: " + host + " " + kind + " " + repr(sorted(values)))


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--domain", required=True)
    parser.add_argument("--ip", default="13.184.138.10")
    args = parser.parse_args()
    domain = args.domain.lower().rstrip(".")
    if (len(domain) > 253 or "." not in domain
            or any(not re.fullmatch(r"[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?", label)
                   for label in domain.split("."))):
        raise SystemExit("Invalid domain")
    address = ipaddress.ip_address(args.ip)
    if address.version != 4 or not address.is_global:
        raise SystemExit("Use the public IPv4 attached to this server")
    check_dns(domain, str(address))
    cd.STATE.mkdir(mode=0o700, parents=True, exist_ok=True)
    with (cd.STATE / "lock").open("a") as lock:
        try:
            fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
        except BlockingIOError:
            raise SystemExit("CD is running; retry domain activation after it completes")
        previous = (cd.ROOT / ".env").read_text()
        configured = cd.replace_env(previous, {
            "APP_DOMAIN": domain, "APP_URL": "https://" + domain,
            "APP_PUBLIC_IP": str(address), "CADDY_CONFIG_FILE": "./deploy/lightsail/Caddyfile.domain",
        })
        path = cd.STATE / "domain-activation.log"
        with os.fdopen(os.open(path, os.O_WRONLY | os.O_CREAT | os.O_APPEND, 0o600), "a") as log:
            launched = False
            try:
                cd.atomic_write(cd.ROOT / ".env", configured)
                cd.command(cd.COMPOSE + ["run", "--rm", "--no-deps", "-T", "caddy",
                                        "caddy", "validate", "--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"], log)
                launched = True
                cd.launch(log)
                cd.health(log)
                for host in [domain, "www." + domain]:
                    cd.command(["curl", "--fail", "--silent", "--show-error", "--location", "--retry", "12",
                                "--retry-all-errors", "--retry-delay", "3", "--max-time", "15", "--output", "/dev/null",
                                "https://" + host + "/login"], log)
                print("Domain active and HTTPS verified: https://" + domain)
            except Exception:
                cd.atomic_write(cd.ROOT / ".env", previous)
                if launched:
                    cd.launch(log)
                    cd.health(log)
                print("Domain activation failed; previous configuration restored. Private log:", path)
                raise


if __name__ == "__main__":
    main()
