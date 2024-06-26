#!/bin/bash

RESET="\e[0m"
BORDER="\e[32m";
TEXT_COLOR="\e[1;37m";
PADDING=4

steps=(
	"Environment Variables"
	"Update & Upgrade APT"
	"Python: Create Virtual Environment"
	"Python: Upgrade pip"
	"Python: Install Requirements in virtualenv"
	"Docker: Install Docker Engine"
	#"Docker: Build Backend Image"
	#"Docker: Build Admin Frontend Image"
	"Docker: Docker Compose (run container)"
	"Install Startup Scripts"
	)

longest_string=10

# get longest string in array of titles
for string in "${steps[@]}"
do
	current_length=${#string}
	if [ $current_length -gt $longest_string ]
	then
		longest_string=${#string}
	fi
done

box_length=$((longest_string + ((PADDING * 2))))

function PrintBoxLine() {
	char=$1

	printf "${BORDER}##"

	# check if 2nd argument exists: string to print (title, etc)
	if [ "$2" ]
	then
		inner_str=$2
		str_len=${#inner_str}
		padding=$(((($box_length - $str_len)) / 2))
		for (( i=0; i < $padding; ++i ))
		do
			printf "$1"
		done
		printf "${TEXT_COLOR}$inner_str"
		for (( i=0; i < $padding; ++i ))
		do
			printf "$1"
		done

		# extra character if string length is odd number
		if (( str_len % 2 ))
		then
			printf "$1"
		fi
	else
		for (( i=0; i < $box_length; ++i ))
		do
			printf "$1"
		done
	fi

	printf "${RESET}${BORDER}##${RESET}\n"
}

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

# installation functions
function CreateEnvFile {
	source ./helpers/configure_env.sh
}

function UpgradeApt {
	sudo apt update && sudo apt upgrade -y
}

function PythonMakeVenv {
	source ./helpers/1-terminal_create_python_venv.sh
}

function PythonUpgradePip {
	./terminal/src/.venv/bin/python -m pip install --upgrade pip
}

function PythonInstallReqs {
	source ./helpers/2-terminal_install_reqs_in_venv.sh
}

function DockerInstallEngine {
	if [ -x "$(command -v docker)" ]; then
		echo "Skipping: Docker is already installed"
	else
		source ./helpers/3-docker_install.sh
	fi
}

function DockerBuildBackend {
	source ./helpers/4-docker_build_backend.sh
}

function DockerBuildAdmin {
	source ./helpers/6-docker_build_admin_when_final.sh
}

function DockerRunContainers {
	source ./helpers/7-docker_compose_prod.sh
}

function InstallStartupScripts {
	source ./helpers/8-install_startup_when_ready.sh
}

step_num=1
start_time=$(date +%s)

# installation loop
for step in "${steps[@]}"
do
	printf "\n"
	PrintBoxLine "#"
	PrintBoxLine " "
	PrintBoxLine " " "STEP $step_num"
	PrintBoxLine " " "${step}"
	PrintBoxLine " "
	PrintBoxLine "#"

	case "$step_num" in
		"1") CreateEnvFile
		;;
		"2") UpgradeApt
		;;
		"3") PythonMakeVenv
		;;
		"4") PythonUpgradePip
		;;
		"5") PythonInstallReqs
		;;
		"6") DockerInstallEngine
		;;
		"7") DockerRunContainers
		;;
		"8") InstallStartupScripts
		;;
	esac

	# print time of completion
	now="$(date +"%T")"
	current_time=$(date +%s)
	echo "STEP $step_num finished: $now ($(($current_time - $start_time)) seconds)"

	((step_num++))
done