#!/bin/sh -x

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
base="$(dirname "$SCRIPT_DIR")"

# create a virtual environment
# if no arguments are given, assume that the script is run as standalone
# otherwise, run with the supplied username
# -E is added to give user access to environment variables (PATH)
if [ ! -z "$user" ]; then
    sudo -E -u ${user} python -m venv "$base/terminal/src/.venv"
else
    python -m venv "$base/terminal/src/.venv"
fi


