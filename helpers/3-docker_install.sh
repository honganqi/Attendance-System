#!/bin/sh -x

# install Docker
# sudo apt update && sudo apt upgrade -y
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# install requirements for rootless mode
sudo apt-get install -y dbus-user-session
sudo apt-get install -y fuse-overlayfs
sudo apt-get install -y uidmap

# expose privileged ports
sudo setcap cap_net_bind_service=ep $(which rootlesskit)
systemctl --user restart docker

# install rootless mode
dockerd-rootless-setuptool.sh install