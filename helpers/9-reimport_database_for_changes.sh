#!/bin/sh -x

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
base="$(dirname "$SCRIPT_DIR")"

docker exec -i attendance_db mysql -uattendance_user -ppassword attendance < "$base/backend/config/initial_db/attendance_sync.sql"
docker exec -i attendance_db mysql -uattendance_user -ppassword attendance < "$base/backend/config/initial_db/attendance_sync_server.sql"