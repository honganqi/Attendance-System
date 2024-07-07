#!/bin/sh -x

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
base="$(dirname "$SCRIPT_DIR")"

# compose
# if no arguments are given, assume that the script is run as standalone
# otherwise, run with the supplied username
# -E is added to give user access to environment variables (PATH)
if [ ! -z "$user" ]; then
    sudo -u ${user} docker compose --project-directory "$base" down
    sudo -u ${user} docker compose --project-directory "$base" up --detach --build
else
    docker compose --project-directory "$base" down
    docker compose --project-directory "$base" up --detach --build
fi
