@echo on
setlocal EnableDelayedExpansion

rem Get the script directory
for %%i in ("%~dp0.") do set SCRIPT_DIR=%%~fi
set base=%SCRIPT_DIR:~0,-1%

rem Function to escape string
set "escape_string="
set "clean_url="

rem Function to escape special characters in a string
:escape_string
set input=%1
set input=!input:/=\/!
set input=!input:&=\&!
set input=!input:=\=!
set escaped_string=!input!
goto :eof

rem Function to clean URL
:clean_url
set input=%1
rem Remove "http://" or "https://"
set input=!input:http://=!
set input=!input:https://=!
rem Remove the port if present (e.g., ":3000")
for /f "tokens=1 delims=:" %%i in ("!input!") do set input=%%i
rem Remove trailing slashes
for /f "tokens=1 delims=/" %%i in ("!input!") do set input=%%i
set cleaned_url=!input!
goto :eof

rem Check if .env file exists
if exist "%base%\.env" (
    echo YES
    call :load_env "%base%\.env"
) else (
    echo NO
    call :load_env "%base%\.env.example"
)

rem Load environment variables from .env file
:load_env
for /f "delims=" %%i in (%1) do set %%i
goto :eof

set "TZ=%TZ%"
set /p "timezone=Specify your timezone (currently %TZ%): "
call :escape_string !timezone!
set timezone=!escaped_string!

set "BACKEND_URL=%BACKEND_URL%"
set /p "backend_url=Backend service URL (leave as default if using the default Docker containers) (currently %BACKEND_URL%): "
call :escape_string !backend_url!
set backend_url=!escaped_string!

set "FRONTEND_ORIGIN=%FRONTEND_ORIGIN%"
call :clean_url !FRONTEND_ORIGIN!
set cleaned_frontend_origin=!cleaned_url!
set /p "frontend_url=Hostname or IP address of this server (the hostname you set in Raspberry Pi Imager e.g. raspberrypi.local, attendance.local or an IP address like 192.168.1.6) (currently %cleaned_frontend_origin%): "
call :clean_url !frontend_url!
set frontend_url=!cleaned_url!

:modify_db
set "modify=no"
set /p "modify=Do you want to modify the database credentials? (no): "
if /i "%modify%"=="yes" (
    set "MYSQL_USER=%MYSQL_USER%"
    set "MYSQL_PASSWORD=%MYSQL_PASSWORD%"
    set "MYSQL_HOST=%MYSQL_HOST%"
    set "MYSQL_DATABASE=%MYSQL_DATABASE%"
    set "MARIADB_ROOT_PASSWORD=%MARIADB_ROOT_PASSWORD%"

    echo Specify the database settings you would like to use. (leave as default if using the default Docker containers)
    set /p "mysql_name=Database Name (%MYSQL_DATABASE%): "
    set /p "mysql_user=Username (%MYSQL_USER%): "
    set /p "mysql_pass=Password (%MYSQL_PASSWORD%) (Please note that what you type will be visible. This is by design.): "
    set /p "mysql_host=Host (%MYSQL_HOST%): "
    set /p "mysql_root=Root Password (%MARIADB_ROOT_PASSWORD%): "

    if not "!mysql_name!"=="" set MYSQL_DATABASE=!mysql_name!
    if not "!mysql_user!"=="" set MYSQL_USER=!mysql_user!
    if not "!mysql_pass!"=="" set MYSQL_PASSWORD=!mysql_pass!
    if not "!mysql_host!"=="" set MYSQL_HOST=!mysql_host!
    if not "!mysql_root!"=="" set MARIADB_ROOT_PASSWORD=!mysql_root!
) else (
    if not /i "%modify%"=="no" (
        echo Please answer yes or no.
        goto :modify_db
    )
)

rem Create .env file if it does not exist
if not exist "%base%\.env" (
    copy "%base%\.env.example" "%base%\.env"
)

rem Finally set the environment variables
powershell -Command "(gc '%base%\.env') -replace 'TZ=.*', 'TZ=%timezone%' | Out-File -encoding ASCII '%base%\.env'"
powershell -Command "(gc '%base%\.env') -replace 'BACKEND_URL=.*', 'BACKEND_URL=%backend_url%' | Out-File -encoding ASCII '%base%\.env'"
powershell -Command "(gc '%base%\.env') -replace 'FRONTEND_ORIGIN=.*', 'FRONTEND_ORIGIN=http://%frontend_url%:3000' | Out-File -encoding ASCII '%base%\.env'"
powershell -Command "(gc '%base%\terminal\terminal.ini') -replace '^Host=.*', 'Host=http://%frontend_url%:2024/' | Out-File -encoding ASCII '%base%\terminal\terminal.ini'"

endlocal
