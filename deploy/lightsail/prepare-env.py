#!/usr/bin/env python3
"""Create a NEW server environment without printing secrets or replacing an existing key."""

import argparse
import base64
import ipaddress
import os
from pathlib import Path
import re
import secrets

parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument("--host", required=True, help="Public IPv4 or DNS name, without a scheme")
parser.add_argument("--output", type=Path, default=Path(".env"))
parser.add_argument("--image-tag", default="local")
parser.add_argument("--tls-email", default="")
args = parser.parse_args()

try:
    address = ipaddress.ip_address(args.host)
    if address.version != 4 or not address.is_global:
        parser.error("Use a public IPv4 address for this initial deployment")
    is_ip = True
except ValueError:
    if not re.fullmatch(r"[a-zA-Z0-9](?:[a-zA-Z0-9.-]*[a-zA-Z0-9])?", args.host) or "." not in args.host:
        parser.error("Invalid DNS name")
    is_ip = False
if (not is_ip or args.tls_email) and not re.fullmatch(r"[^\s@]+@[^\s@]+\.[^\s@]+", args.tls_email):
    parser.error("Provide --tls-email for DNS mode")
if not re.fullmatch(r"[a-zA-Z0-9_.-]+", args.image_tag):
    parser.error("Invalid image tag")

template = Path(__file__).with_name("production.env.example").read_text()
values = {
    "APP_KEY": "base64:" + base64.b64encode(secrets.token_bytes(32)).decode(),
    "DB_PASSWORD": secrets.token_hex(24),
    "DB_ROOT_PASSWORD": secrets.token_hex(24),
    "APP_URL": "https://" + args.host,
    "APP_DOMAIN": args.host,
    "TLS_EMAIL": args.tls_email,
    "APP_IMAGE_TAG": args.image_tag,
    "CADDY_CONFIG_FILE": "./deploy/lightsail/Caddyfile.ip" if is_ip else "./deploy/lightsail/Caddyfile",
}
for name, value in values.items():
    template = re.sub(r"^" + name + r"=.*$", lambda _: name + "=" + value, template, flags=re.MULTILINE)
if "CHANGE_ME" in "\n".join(line for line in template.splitlines() if not line.startswith("#")):
    raise SystemExit("Unresolved secret placeholder")

try:
    fd = os.open(args.output, os.O_WRONLY | os.O_CREAT | os.O_EXCL, 0o600)
except FileExistsError:
    raise SystemExit("Environment already exists; refusing to overwrite keys or database passwords")
with os.fdopen(fd, "w") as output:
    output.write(template)
print("New server environment created with mode 600; secrets were not printed.")
