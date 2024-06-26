# Attendance System Terminal
This readme assumes you are working in the `/terminal` directory.

## Installation
An install script named `install.sh` is available at the root of the main project to simplify the installation process. If you have other needs, please continue reading below.

## Deployment
1. Create a virtual environment
```bash
python -m venv ./src/.venv
source ./src/venv/bin/activate
```
2. Install required modules
	- `pip install -r requirements.txt`
		- requests
		- python-dotenv
		- tk
		- Pillow
		- nfcpy
		- opencv-python
		- keyboard
3. Update the `terminal.ini` file in the root directory with the following variables under `[SERVER]`. Script is available with `update_server_ip.sh`.
	- `Host = <host address with http:// protocol, backend port number, and trailing slash>`
		- e.g. `http://192.168.1.6:2024/`
	- `AttendanceApiRoute = <route to API without starting slash e.g. >`
		- e.g. `api/attendance/entry/`
4. If you want to preview the terminal, you will need to run the containers in the root directory of the main project:
	- production `docker compose up --build` (use this if you don't have anything to modify in the admin frontend)
	- development `docker compose --file docker-compose-dev.yml up --build`
4. To preview the terminal, run `python src/terminal.py` while in the virtual environment.
5. Exit out of virtual environment to continue

## Install to run at startup
Make sure to exit out of the virtual environment before you do this.
1. Create an `autostart` directory inside `/home/<username>/.config`:
```bash
mkdir ~/.config/autostart
```
2. Make a <preferred_name>.desktop file, copy the following contents and make the necessary customizations. A startup script is already available in `/terminal/autostart.d/terminal.desktop`.
```bash
[Desktop Entry]
Name=<whatever>
Type=Application
Comment=<something>
Exec=bash -c "~/Attendance-System/terminal/src/.venv/bin/python ~/Attendance-System/terminal/src/terminal.py"
```
3. Copy your <preferred_name>.desktop file to the autostart directory you just made. For example:
```bash
cp <preferred_name>.desktop ~/.config/autostart
```