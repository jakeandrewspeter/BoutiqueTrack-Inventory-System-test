#!/usr/bin/env bash
set -Eeuo pipefail

APP_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DATA_DIR="$APP_ROOT/var/dev-mysql"
SOCKET="$DATA_DIR/mysql.sock"
PID_FILE="$DATA_DIR/mysql.pid"
LOG_FILE="$DATA_DIR/mysql.log"
DB_PROCESS=""
PHP_PROCESS=""

mkdir -p "$DATA_DIR"
if [[ ! -d "$DATA_DIR/mysql" ]]; then
  mysql_install_db --datadir="$DATA_DIR" --auth-root-authentication-method=normal --skip-test-db >/dev/null
fi

if ! mariadb-admin --socket="$SOCKET" --user=root ping --silent >/dev/null 2>&1; then
  rm -f "$SOCKET" "$PID_FILE"
  mariadbd \
    --user="$(id -un)" \
    --datadir="$DATA_DIR" \
    --socket="$SOCKET" \
    --pid-file="$PID_FILE" \
    --bind-address=127.0.0.1 \
    --port="${DB_PORT:-3306}" \
    --skip-name-resolve \
    --log-error="$LOG_FILE" &
  DB_PROCESS=$!

  ready=0
  for _ in $(seq 1 45); do
    if mariadb-admin --socket="$SOCKET" --user=root ping --silent >/dev/null 2>&1; then
      ready=1
      break
    fi
    if ! kill -0 "$DB_PROCESS" 2>/dev/null; then
      break
    fi
    sleep 1
  done
  if [[ "$ready" != "1" ]]; then
    echo "MariaDB did not start. Details:" >&2
    tail -n 50 "$LOG_FILE" >&2 || true
    exit 1
  fi
fi

mariadb --socket="$SOCKET" --user=root -e \
  "CREATE DATABASE IF NOT EXISTS boutique_track CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mariadb --socket="$SOCKET" --user=root boutique_track < "$APP_ROOT/database/install.sql"
mariadb --socket="$SOCKET" --user=root boutique_track < "$APP_ROOT/database/demo-data.sql"

cleanup() {
  if [[ -n "$PHP_PROCESS" ]]; then
    kill "$PHP_PROCESS" 2>/dev/null || true
  fi
  if [[ -n "$DB_PROCESS" ]]; then
    mariadb-admin --socket="$SOCKET" --user=root shutdown >/dev/null 2>&1 || kill "$DB_PROCESS" 2>/dev/null || true
  fi
}
trap cleanup EXIT INT TERM

php -S "0.0.0.0:${PORT:-3000}" -t "$APP_ROOT/public" &
PHP_PROCESS=$!
wait "$PHP_PROCESS"