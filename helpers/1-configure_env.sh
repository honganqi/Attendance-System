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
read -p "Specify your timezone (currently: $TZ): " timezone
timezone=$(escape_string ${timezone:-$TZ})

export PUBLIC_BACKEND_URL
cleaned_backend_url=$(clean_url ${PUBLIC_BACKEND_URL})
read -p "Backend service URL (leave as default if using the default Docker containers) (currently: $cleaned_backend_url): " backend_url
backend_url=$(clean_url ${backend_url:-$PUBLIC_BACKEND_URL})
echo $backend_url

export FRONTEND_ORIGIN
cleaned_frontend_origin=$(clean_url ${FRONTEND_ORIGIN})
read -p "Hostname or IP address of this server (the hostname you set in Raspberry Pi Imager e.g. raspberrypi.local, attendance.local or an IP address like 192.168.1.6) (currently: $cleaned_frontend_origin): " frontend_url
frontend_url=$(clean_url ${frontend_url:-$FRONTEND_ORIGIN})

while true; do
    read -p "Do you want to modify the database credentials? (default: no) " modify
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
            break;;
        [Nn]* | "" ) break;;
        * ) echo "Please answer yes or no.";;
    esac
done


# Logo stuff for Admin
adminConfigFile="$base/admin/src/lib/config.js"
logoDashboard=""
logoDashboardDark=""
logoHeader=""
logoHeaderDark=""

# if the admin config.js file does not exist, create it from the config.js.example file
if [ ! -e $adminConfigFile ]
then
    # if no arguments are given, assume that the script is run as standalone
    # otherwise, run with the supplied username
    # -E is added to give user access to environment variables (PATH)
    if [ ! -z "$user" ]; then
        sudo -E -u ${user} cp -n "$base/admin/src/lib/config.js.example" $adminConfigFile
    else
        cp -n "$base/admin/src/lib/config.js.example" $adminConfigFile
    fi
fi

# Read the file line by line
while IFS= read -r line; do
    # Use regex to extract values from the lines
    if [[ $line =~ const\ ([a-zA-Z_][a-zA-Z_0-9]*)\ *=\ *\'([^\']*)\' ]] then
        var_name="${BASH_REMATCH[1]}"
        var_value=$(basename "${BASH_REMATCH[2]}")
        
        case $var_name in
            logoDashboard)
                logoDashboard="$var_value"
                ;;
            logoDashboardDark)
                logoDashboardDark="$var_value"
                ;;
            logoHeader)
                logoHeader="$var_value"
                ;;
            logoHeaderDark)
                logoHeaderDark="$var_value"
                ;;                
        esac
    fi
done < "$adminConfigFile"

while true; do
    read -p "Do you want to add your logo to the admin frontend? (default: no) " adminlogo
    adminlogo=${adminlogo:-no}
    case $adminlogo in
        [Yy]* )
            printf "The logos should already be present in the /admin/static/img/ directory before running this script. Type in the same filename if your logo is identical in normal and dark mode.\n"
            read -p "Logo for dashboard (currently $logoDashboard): " read_logoDashboard
            read -p "Logo for dashboard (dark mode) (currently $logoDashboardDark): " read_logoDashboardDark
            read -p "Logo for header nav (currently $logoHeader): " read_logoHeader
            read -p "Logo for header nav (dark mode) (currently $logoHeaderDark): " read_logoHeaderDark
            [ -n "$read_logoDashboard" ] && logoDashboard="/img/logo/$read_logoDashboard"
            [ -n "$read_logoDashboardDark" ] && logoDashboardDark="/img/logo/$read_logoDashboardDark"
            [ -n "$read_logoHeader" ] && logoHeader="/img/logo/$read_logoHeader"
            [ -n "$read_logoHeaderDark" ] && logoHeaderDark="/img/logo/$read_logoHeaderDark"
            break;;
        [Nn]* | "" ) break;;
        * ) echo "Please answer yes or no.";;
    esac
done

# if the .env file does not exist, create it from the .env.example file
if [ ! -e "$base/.env" ]
then
    # if no arguments are given, assume that the script is run as standalone
    # otherwise, run with the supplied username
    # -E is added to give user access to environment variables (PATH)
    if [ ! -z "$user" ]; then
        sudo -E -u ${user} cp -n "$base/.env.example" "$base/.env"
    else
        cp -n "$base/.env.example" "$base/.env"
    fi
fi

# if the terminal.ini file does not exist, create it from the .env.example file
if [ ! -e "$base/terminal/terminal.ini" ]
then
    # if no arguments are given, assume that the script is run as standalone
    # otherwise, run with the supplied username
    # -E is added to give user access to environment variables (PATH)
    if [ ! -z "$user" ]; then
        sudo -E -u ${user} cp -n "$base/terminal/terminal.ini.example" "$base/terminal/terminal.ini"
    else
        cp -n "$base/terminal/terminal.ini.example" "$base/terminal/terminal.ini"
    fi
fi

# finally set the environment variables
sed -i "s/TZ=.*/TZ=$timezone/" "$base/.env"
sed -i "s/PUBLIC_BACKEND_URL=.*/PUBLIC_BACKEND_URL=$backend_url/" "$base/.env"
sed -i "s/FRONTEND_ORIGIN=.*/FRONTEND_ORIGIN=http:\/\/$frontend_url:3000/" "$base/.env"
sed -i "s/^Host=.*/Host=http:\/\/$frontend_url:2024\//" "$base/terminal/terminal.ini"
sed -i "s/^const logoDashboard = .*/const logoDashboard = '${logoDashboard//\//\\/}';/" $adminConfigFile
sed -i "s/^const logoDashboardDark = .*/const logoDashboardDark = '${logoDashboardDark//\//\\/}';/" $adminConfigFile
sed -i "s/^const logoHeader = .*/const logoHeader = '${logoHeader//\//\\/}';/" $adminConfigFile
sed -i "s/^const logoHeaderDark = .*/const logoHeaderDark = '${logoHeaderDark//\//\\/}';/" $adminConfigFile