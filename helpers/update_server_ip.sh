#!/bin/bash
# This script edits a line containing "Host=" in a file named "terminal.ini" with a value provided by the user

# Function to remove occurrences of specified substrings
clean_input() {
    local input="$1"
    # Remove "http://" or "https://"
    input="${input#http://}"
    input="${input#https://}"

    # Remove the port if present (e.g., ":3000")
    input="${input%%:*}"

    # Remove trailing slashes
    input="${input%%/}"

    sed -i "s/FRONTEND_ORIGIN=.*/FRONTEND_ORIGIN=http:\/\/$input:3000\//" ../.env
    sed -i "s/^Host=.*/Host=http:\/\/$input\//" ../terminal/terminal.ini

    echo "$input"
}

printf "\nUsage:\n"
echo " - if used with an argument: \"$0 <IP address or hostname>\""
echo " - if used without an argument (will prompt for <IP address or hostname> after): \"$0\""
echo "Note: script will automatically strip all prefixes (e.g. http) and ports if included in <IP address or hostname>"
printf "\n"

# Check if an argument is provided
if [ -z "$1" ]
then
    read -p "Enter the server's IP address or hostname: " server_ip
else
    server_ip="$1"
fi

# Call the function and print the result
result=$(clean_input "$server_ip")
echo "Server address modified: $result"
