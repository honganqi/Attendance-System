#!/bin/sh -x

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
base="$(dirname "$SCRIPT_DIR")"

if [ ! -z "$user" ]; then
    this_user=$user
else
    this_user=$USER
fi

# copy and enable Docker systemd files from project directory
sed -i "s/User=.*/User=$this_user/" "$base/backend/config/docker.d/docker.attendancesystem.service"
sudo cp "$base/backend/config/docker.d/docker.attendancesystem.service" /etc/systemd/system
sudo systemctl enable docker.attendancesystem.service
sudo systemctl start docker.attendancesystem.service

# set up terminal startup
mkdir -p ~/.config/autostart
\cp "$base/terminal/autostart.d/terminal.desktop" ~/.config/autostart
chmod +x ~/.config/autostart/terminal.desktop