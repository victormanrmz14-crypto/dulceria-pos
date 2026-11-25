#!/usr/bin/env bash
# Lightsail user data. Runs as root only on the NEW Ubuntu server, after approval.
# Lightsail prepends a /bin/sh initialization wrapper, so enter Bash explicitly.
bash <<'DULCERIA_BOOTSTRAP'
set -euo pipefail

if [[ $EUID -ne 0 ]]; then
    printf 'Ejecutar como root en la instancia Ubuntu nueva.\n' >&2
    exit 1
fi
source /etc/os-release
if [[ ${ID:-} != ubuntu || ${VERSION_ID:-} != 24.04 ]]; then
    printf 'Este bootstrap requiere Ubuntu 24.04.\n' >&2
    exit 1
fi

export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get install -y ca-certificates curl git
install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
chmod a+r /etc/apt/keyrings/docker.asc
cat > /etc/apt/sources.list.d/docker.sources <<EOF
Types: deb
URIs: https://download.docker.com/linux/ubuntu
Suites: ${UBUNTU_CODENAME:-$VERSION_CODENAME}
Components: stable
Architectures: $(dpkg --print-architecture)
Signed-By: /etc/apt/keyrings/docker.asc
EOF
# Bound local container logs so they cannot consume the instance disk indefinitely.
if [[ ! -e /etc/docker/daemon.json ]]; then
    install -m 0755 -d /etc/docker
    cat > /etc/docker/daemon.json <<'JSON'
{"log-driver":"local","log-opts":{"max-size":"10m","max-file":"3"}}
JSON
fi
apt-get update
apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
systemctl enable --now docker
install -d -o ubuntu -g ubuntu -m 0750 /srv/dulceria-pos
install -d -m 0755 /var/lib/dulceria-pos
docker version
docker compose version
touch /var/lib/dulceria-pos/bootstrap-ready
DULCERIA_BOOTSTRAP
