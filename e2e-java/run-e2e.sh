#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$ROOT"

MODE="${1:-test}"

usage() {
    cat <<'EOF'
Usage: ./e2e-java/run-e2e.sh [command]

Commands:
  test      Reset the e2e database, start the app, run the Selenium suite (default)
  serve     Reset the e2e database, start the app, and keep it running
  db        Start the local e2e database and exit
  db:stop   Stop the local e2e database
EOF
}

case "$MODE" in
    test | serve | db | db:stop) ;;
    help | -h | --help)
        usage
        exit 0
        ;;
    *)
        echo "Unknown command: $MODE" >&2
        usage >&2
        exit 1
        ;;
esac

PORT="${E2E_PORT:-8001}"
BASE_URL="${E2E_BASE_URL:-http://127.0.0.1:${PORT}}"
ENV_FILE="$ROOT/.env.e2e"
SERVER_LOG="$ROOT/storage/logs/e2e-server.log"

# Database defaults to a disposable Postgres cluster managed by this script.
# Set E2E_DB_MODE=external to use a database that is already running.
DB_MODE="${E2E_DB_MODE:-local}"
DB_HOST="${E2E_DB_HOST:-127.0.0.1}"
DB_PORT="${E2E_DB_PORT:-55432}"
DB_NAME="${E2E_DB_NAME:-db_pritani_e2e}"
DB_USER="${E2E_DB_USER:-$(whoami)}"
DB_PASSWORD="${E2E_DB_PASSWORD:-}"
PG_DIR="$ROOT/database/.pgsql-e2e"
PG_LOG="$ROOT/storage/logs/e2e-postgres.log"
PG_STARTED=0
SERVER_PID=""

ensure_env_file() {
    if [[ ! -f "$ENV_FILE" ]]; then
        cp "$SCRIPT_DIR/.env.e2e.example" "$ENV_FILE"
        echo "Created $ENV_FILE"
    fi

    sed -i "s|^APP_URL=.*|APP_URL=${BASE_URL}|" "$ENV_FILE"
    sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=pgsql|" "$ENV_FILE"
    sed -i "s|^DB_HOST=.*|DB_HOST=${DB_HOST}|" "$ENV_FILE"
    sed -i "s|^DB_PORT=.*|DB_PORT=${DB_PORT}|" "$ENV_FILE"
    sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|" "$ENV_FILE"
    sed -i "s|^DB_USERNAME=.*|DB_USERNAME=${DB_USER}|" "$ENV_FILE"
    sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD//&/\\&}|" "$ENV_FILE"

    if ! grep -q '^APP_KEY=base64:' "$ENV_FILE"; then
        KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
        sed -i "s|^APP_KEY=.*|APP_KEY=${KEY}|" "$ENV_FILE"
    fi
}

start_local_postgres() {
    if [[ ! -f "$PG_DIR/PG_VERSION" ]]; then
        echo "Initializing disposable Postgres cluster in $PG_DIR..."
        initdb -D "$PG_DIR" -U "$DB_USER" --auth=trust --encoding=UTF8 --locale=C >/dev/null
    fi

    if ! pg_ctl -D "$PG_DIR" status >/dev/null 2>&1; then
        if command -v ss >/dev/null 2>&1 && ss -ltn | grep -q ":${DB_PORT} "; then
            echo "Port ${DB_PORT} is already in use by another process." >&2
            echo "Stop it or set E2E_DB_PORT to a free port." >&2
            exit 1
        fi

        pg_ctl -D "$PG_DIR" -l "$PG_LOG" -o "-p ${DB_PORT} -k ${PG_DIR}" -w start >/dev/null
        PG_STARTED=1
    fi

    if [[ "$(psql -d postgres -tAc "SELECT 1 FROM pg_database WHERE datname='${DB_NAME}'")" != "1" ]]; then
        createdb "$DB_NAME"
    fi
}

stop_local_postgres() {
    if pg_ctl -D "$PG_DIR" status >/dev/null 2>&1; then
        pg_ctl -D "$PG_DIR" -m fast -w stop >/dev/null
        return 0
    fi

    return 1
}

start_database() {
    if [[ "$DB_MODE" == "local" ]]; then
        start_local_postgres
        echo "Database ready at ${DB_HOST}:${DB_PORT}/${DB_NAME}"
    else
        echo "Using external database ${DB_USER}@${DB_HOST}:${DB_PORT}/${DB_NAME}"
    fi
}

build_assets() {
    if [[ ! -f public/build/manifest.json ]]; then
        echo "Frontend build not found, running npm run build..."
        npm run build
    fi
}

migrate_database() {
    echo "Resetting e2e database..."
    APP_ENV=e2e php artisan config:clear >/dev/null
    APP_ENV=e2e php artisan migrate:fresh --seed --force
}

start_app() {
    echo "Starting application on ${BASE_URL}..."
    APP_ENV=e2e php artisan serve --host=127.0.0.1 --port="$PORT" >"$SERVER_LOG" 2>&1 &
    SERVER_PID=$!
}

wait_for_app() {
    for _ in $(seq 1 60); do
        if curl -fsS "${BASE_URL}/login" >/dev/null 2>&1; then
            return 0
        fi

        if ! kill -0 "$SERVER_PID" 2>/dev/null; then
            echo "Application exited before becoming ready. See ${SERVER_LOG}" >&2
            exit 1
        fi

        sleep 1
    done

    echo "Application did not become ready at ${BASE_URL}. See ${SERVER_LOG}" >&2
    exit 1
}

cleanup() {
    if [[ -n "$SERVER_PID" ]]; then
        kill "$SERVER_PID" >/dev/null 2>&1 || true
        wait "$SERVER_PID" 2>/dev/null || true
    fi

    if [[ "$PG_STARTED" == "1" ]]; then
        stop_local_postgres >/dev/null 2>&1 || true
    fi
}

ensure_env_file

export PGHOST="$DB_HOST"
export PGPORT="$DB_PORT"
export PGUSER="$DB_USER"
export PGPASSWORD="$DB_PASSWORD"

case "$MODE" in
    db)
        start_database
        ;;
    db:stop)
        if [[ "$DB_MODE" != "local" ]]; then
            echo "The database is external; there is nothing for this script to stop."
            exit 0
        fi

        if stop_local_postgres; then
            echo "Database stopped."
        else
            echo "Database is not running."
        fi
        ;;
    serve)
        start_database
        build_assets
        migrate_database
        trap cleanup EXIT INT TERM
        start_app
        wait_for_app
        echo "Application ready at ${BASE_URL}. Press Ctrl+C to stop."
        wait "$SERVER_PID" || true
        ;;
    test)
        start_database
        build_assets
        migrate_database
        trap cleanup EXIT INT TERM
        start_app
        wait_for_app

        echo "Running Selenium tests..."
        mvn -q -f "$SCRIPT_DIR/pom.xml" test -DbaseUrl="$BASE_URL"
        ;;
esac
