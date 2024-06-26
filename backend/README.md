# Attendance System Backend
## Dependencies
1. PHP 8.2
2. PDO for MySQL connections
3. MariaDB 10.6

## Requirements
1. Docker - You can install the Docker Engine (https://docs.docker.com/engine/install/) or Docker Desktop (https://www.docker.com/products/docker-desktop/) versions. Make sure to run it after installation.

## Installation
An install script named `install.sh` is available at the root of the main project to simplify the installation process. If you have other needs, please continue reading below.

## Deployment
You have 2 options. Both will run this backend image along with the database image.
1. Run the production container: In the root directory of the main project, run `docker compose up --build` to run the container. The `--build` tag builds the images before running the container.
    - This is if you don't have customizations for the admin frontend anymore and want to run it manually.
2. Run the development container: Run `docker compose --file docker-compose-dev.yml up --build`
    - This is if you have modifications to test on the Admin Frontend.
3. Modify the `MYSQL_*` variables in the `.env` file if you want/need to (kinda recommended).
