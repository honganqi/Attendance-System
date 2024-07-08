#!/bin/sh -x

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
base="$(dirname "$SCRIPT_DIR")"

# build the Apache image
docker build -t attendance-backend "$base/backend"
