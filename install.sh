#!/bin/bash

# initial variables and constants
RESET="\e[0m"
BORDER="\e[32m"
TEXT_COLOR="\e[1;37m"
ERROR_COLOR="\e[1;31m"
PADDING=4
steps=(
	"CreateEnvFile"
	"UpgradeApt"
	"PythonMakeVenv"
	"PythonUpgradePip"
	"PythonInstallReqs"
	"DockerInstallEngine"
	"DockerRunContainers"
	"InstallStartupScripts"
	)

declare -A STEP_LIST
STEP_LIST["CreateEnvFile"]="Environment Variables"
STEP_LIST["UpgradeApt"]="Update & Upgrade APT"
STEP_LIST["PythonMakeVenv"]="Python: Create Virtual Environment"
STEP_LIST["PythonUpgradePip"]="Python: Upgrade pip"
STEP_LIST["PythonInstallReqs"]="Python: Install Requirements in virtualenv"
STEP_LIST["DockerInstallEngine"]="Docker: Install Docker Engine"
STEP_LIST["DockerRunContainers"]="Docker: Docker Compose (run container)"
STEP_LIST["InstallStartupScripts"]="Install Startup Scripts"

longest_string=10
user=""

# get longest string in array of titles
for string in "${steps[@]}"
do
	current_length=${#STEP_LIST[$string]}
	if [ $current_length -gt $longest_string ]
	then
		longest_string=${current_length}
	fi
done

box_length=$((longest_string + ((PADDING * 2))))

PrintBoxLine() {
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

# installation functions
CreateEnvFile() {
	source ./helpers/1-configure_env.sh
}

UpgradeApt() {
	while true; do
		printf "Do you want to get ${TEXT_COLOR}system updates${RESET}? "
		read -p "Answer \"no\" only if you have a specific reason to) (default: yes) " get_updates
		get_updates=${get_updates:-yes}
		case $get_updates in
			[Yy]* )
				sudo apt update && sudo apt upgrade -y
				break;;
			[Nn]* | "" ) break;;
			* ) echo "Please answer yes or no.";;
		esac
	done	
}

PythonMakeVenv() {
	source ./helpers/3-terminal_create_python_venv.sh
}

PythonUpgradePip() {
	./terminal/src/.venv/bin/python -m pip install --upgrade pip
}

PythonInstallReqs() {
	source ./helpers/5-terminal_install_reqs_in_venv.sh
}

DockerInstallEngine() {
	source ./helpers/6-docker_install.sh
}

DockerRunContainers() {
	source ./helpers/7-docker_compose_prod.sh
}

InstallStartupScripts() {
	source ./helpers/8-install_startup_when_ready.sh
}




# upon running, ask for username
printf "\n"
read -p "Please input the username of system's user: " user
if ! id "$user" >/dev/null 2>&1; then
	printf "${ERROR_COLOR}ERROR: ${TEXT_COLOR}User ${BORDER}$user ${TEXT_COLOR}not found\n"
	exit 1
fi




step_num=1
start_time=$(date +%s)

# installation loop
for step in "${!steps[@]}"
do
	printf "\n"
	PrintBoxLine "#"
	PrintBoxLine " "
	PrintBoxLine " " "STEP $step_num"
	PrintBoxLine " " "${STEP_LIST[${steps[$step]}]}"
	PrintBoxLine " "
	PrintBoxLine "#"

	case "${steps[$step]}" in
		"CreateEnvFile") CreateEnvFile
		;;
		"UpgradeApt") UpgradeApt
		;;
		"PythonMakeVenv") PythonMakeVenv
		;;
		"PythonUpgradePip") PythonUpgradePip
		;;
		"PythonInstallReqs") PythonInstallReqs
		;;
		"DockerInstallEngine") DockerInstallEngine
		;;
		"DockerRunContainers") DockerRunContainers
		;;
		"InstallStartupScripts") InstallStartupScripts
		;;
	esac

	# print time of completion
	now="$(date +"%T")"
	current_time=$(date +%s)
	echo "STEP $step_num finished: $now ($(($current_time - $start_time)) seconds)"

	((step_num++))
done