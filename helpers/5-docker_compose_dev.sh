#!/bin/sh -x

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
base="$(dirname "$SCRIPT_DIR")"

# compose
docker compose --project-directory "$base" --file "$base/docker-compose-dev.yml" up --detach
