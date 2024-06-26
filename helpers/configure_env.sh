#!/bin/bash

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
base="$(dirname "$SCRIPT_DIR")"

escape_string() {
	echo "$1" | sed 's/[\/&]/\\&/g'
}

clean_url() {
	local input="$1"
	# Remove "http://" or "https://"
	input="${input#http://}"
	input="${input#https://}"

	# Remove the port if present (e.g., ":3000")
	input="${input%%:*}"

	# Remove trailing slashes
	input="${input%%/}"

	echo "$input"
}

if [ -e "$base/.env" ]
then
    source "$base/.env"
else
    source "$base/.env.example"
fi

export TZ
read -p "Specify your timezone (currently $TZ): " timezone
timezone=$(escape_string ${timezone:-$TZ})

export BACKEND_URL
cleaned_backend_url=$(clean_url ${BACKEND_URL})
read -p "Backend service URL (leave as default if using the default Docker containers) (currently $cleaned_backend_url): " backend_url
backend_url=$(clean_url ${backend_url:-$BACKEND_URL})
echo $backend_url

export FRONTEND_ORIGIN
cleaned_frontend_origin=$(clean_url ${FRONTEND_ORIGIN})
read -p "Hostname or IP address of this server (the hostname you set in Raspberry Pi Imager e.g. raspberrypi.local, attendance.local or an IP address like 192.168.1.6) (currently $cleaned_frontend_origin): " frontend_url
frontend_url=$(clean_url ${frontend_url:-$FRONTEND_ORIGIN})

while true; do
    read -p "Do you want to modify the database credentials? (no) " modify
    modify=${modify:-no}
    case $modify in
        [Yy]* )
            export MYSQL_USER
            export MYSQL_PASSWORD
            export MYSQL_HOST
            export MYSQL_DATABASE
            export MARIADB_ROOT_PASSWORD
            
            printf "Specify the database settings you would like to use. (leave as default if using the default Docker containers)\n"
            read -p "Database Name ($MYSQL_DATABASE): " mysql_name
            read -p "Username ($MYSQL_USER): " mysql_user
            read -p "Password ($MYSQL_PASSWORD) (Please note that what you type will be visible. This is by design.): " mysql_pass
            read -p "Host ($MYSQL_HOST): " mysql_host
            read -p "Root Password ($MARIADB_ROOT_PASSWORD): " mysql_root
            mysql_name=${mysql_name:-$MYSQL_DATABASE}
            mysql_user=${mysql_user:-$MYSQL_USER}
            mysql_pass=${mysql_pass:-$MYSQL_PASSWORD}
            mysql_host=${mysql_host:-$MYSQL_HOST}
            mysql_root=${mysql_root:-$MARIADB_ROOT_PASSWORD}
        ;;
        [Nn]* | "" ) break;;
        * ) echo "Please answer yes or no.";;
    esac
done

# if the .env file does not exist, create it from the .env.example file
if [ ! -e "$base/.env" ]
then
    cp -n "$base/.env.example" "$base/.env"
fi

# finally set the environment variables
sed -i "s/TZ=.*/TZ=$timezone/" "$base/.env"
sed -i "s/BACKEND_URL=.*/BACKEND_URL=$backend_url/" "$base/.env"
sed -i "s/FRONTEND_ORIGIN=.*/FRONTEND_ORIGIN=http:\/\/$frontend_url:3000/" "$base/.env"
sed -i "s/^Host=.*/Host=http:\/\/$frontend_url:2024\//" "$base/terminal/terminal.ini"