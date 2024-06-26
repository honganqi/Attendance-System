# Attendance System Admin Frontend
## Dependencies
1. SvelteKit (https://kit.svelte.dev/): main framework
2. Skeleton UI (https://www.skeleton.dev/)
3. TailwindCSS (https://tailwindcss.com/) used by Skeleton UI
4. Font Awesome (https://fontawesome.com/) for the icons
5. Floating UI (https://floating-ui.com/) used by Skeleton UI
6. @svelte-plugins/datepicker (https://github.com/svelte-plugins/datepicker)

## Requirements
1. Docker - You can install the Docker Engine (https://docs.docker.com/engine/install/) or Docker Desktop (https://www.docker.com/products/docker-desktop/) versions. Make sure to run it after installation.
2. Node.js - Only if you want to preview or run the development version of this Admin Frontend. (https://nodejs.org/en/download/package-manager/current)

## Installation
An install script named `install.sh` is available at the root of the main project to simplify the installation process. If you have other needs, please continue reading below.

## Production
You may run the production Docker container (`docker-compose.yml`) but the build process of the Admin Frontend image is listed below.
1. Set up your environment variables. To simplify the process of setting up the environment variables, a script is available in `/helpers/configure_env.sh` in Linux and OS X or `/helpers/configure_env_WINDOWS.bat` in Windows. This will create an `.env` file from `.env.example`. The actual process is listed below. You may leave a lot of these variables in their default values except for:
    1. `TZ` - Set your timezone accordingly. See "TZ identifier" in https://en.wikipedia.org/wiki/List_of_tz_database_time_zones.
    2. `FRONTEND_ORIGIN` - Hostname or IP address of your server (the hostname you set in Raspberry Pi Imager e.g. raspberrypi.local, attendance.local or an IP address like 192.168.1.6)
2. In the root directory of the main project, run `docker compose up --build` to run the container. The `--build` tag builds the images before running the container.

## For Development
*This requires the Docker container to be running the backend and database images.*
If you need to customize the Admin Frontend, you can install and manage this SvelteKit project manually:
1. Install the package dependencies by running `npm install` on your Terminal, PowerShell, or Command Prompt.
2. In the `/admin/.env` file, modify the `BACKEND_URL` variable and set it to the hostname or IP address of your Docker backend image using port 2024.
    - If you are running the Docker container and the Admin Frontend in the same system, you may use `http://localhost:2024`.
    - If you are running the Docker container on a separate system (like the Raspberry Pi), you may use the hostname you set or the IP address it has. e.g. `http://raspberrypi.local:2024` or `http://192.168.1.7:2024`.
3. Run the development Docker container (`docker-compose-dev.yml`) to run the backend and database images only.
4. Run `npm run dev` from inside the `/admin` directory.
5. Go to `http://localhost:5173` to access the Admin Frontend. You'll be able to see your changes live as you work.
