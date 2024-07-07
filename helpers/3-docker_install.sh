#!/bin/sh -x

# install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# install requirements for rootless mode
sudo apt-get install -y dbus-user-session fuse-overlayfs uidmap

# expose privileged ports
sudo setcap cap_net_bind_service=ep $(which rootlesskit)
systemctl --user restart docker

# install rootless mode
# if no arguments are given, assume that the script is run as standalone
# otherwise, run with the supplied username
# -E is added to give user access to environment variables (PATH)
if [ ! -z "$user" ]; then
    sudo -E -u ${user} dockerd-rootless-setuptool.sh install
else
    dockerd-rootless-setuptool.sh install
fi
