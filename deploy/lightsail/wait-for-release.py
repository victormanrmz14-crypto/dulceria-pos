#!/usr/bin/env python3
"""GitHub-hosted verifier: require a matching commit and healthy public HTTPS."""
import json
import os
import re
import time
import urllib.error
import urllib.request


def main():
    expected = os.environ["EXPECTED_SHA"]
    if not re.fullmatch(r"[0-9a-f]{40}", expected):
        raise SystemExit("Invalid expected commit")
    base = os.environ["DEPLOY_URL"].rstrip("/")
    if not base.startswith("https://"):
        raise SystemExit("Deployment verification requires HTTPS")
    deadline = time.monotonic() + 25 * 60
    while time.monotonic() < deadline:
        try:
            request = urllib.request.Request(
                base + "/.well-known/dulceria/release.json?check=" + str(time.time_ns()),
                headers={"Cache-Control": "no-cache", "User-Agent": "Dulceria-CD-verifier"},
            )
            with urllib.request.urlopen(request, timeout=15) as response:
                release = json.load(response)
            if release.get("commit") == expected and release.get("status") == "healthy":
                for path in ["/up", "/login"]:
                    with urllib.request.urlopen(base + path, timeout=15) as response:
                        if response.status != 200:
                            raise ValueError("Health check did not return 200")
                print("Healthy deployment confirmed:", expected)
                return
            print("Waiting for CI-approved commit", expected[:12], flush=True)
        except (OSError, ValueError, urllib.error.URLError) as error:
            print("Waiting for public deployment endpoint:", type(error).__name__, flush=True)
        time.sleep(15)
    raise SystemExit("AWS did not publish the expected healthy commit within 25 minutes")


if __name__ == "__main__":
    main()
