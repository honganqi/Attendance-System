#!/bin/sh -x

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
base="$(dirname "$SCRIPT_DIR")"

# build the SvelteKit admin frontend when modifications are final
docker build --tag attendance-admin --build-arg BACKEND_URL=attendance_backend "$base/admin"
