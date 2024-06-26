#!/bin/bash
# This script edits a line containing "Host=" in a file named "terminal.ini" with a value provided by the user

read -p "Enter the Server's IP address: " server_ip

sed -i "s/^Host=.*/Host=http:\/\/$server_ip:2024\//" ~/Attendance-System/terminal/terminal.ini
