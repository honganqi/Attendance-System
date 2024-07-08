#!/bin/sh -x

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
base="$(dirname "$SCRIPT_DIR")"

# install the Python requirements in the virtual environment
$base/terminal/src/.venv/bin/pip install -r "$base/terminal/src/requirements.txt"
