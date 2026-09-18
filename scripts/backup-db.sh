#!/bin/bash

# On-demand DB backup for Switch Scores
# Run on the server (codacity4), from anywhere:
#   ./scripts/backup-db.sh [OUTPUT_DIR]
#
# Reads DB_DATABASE / DB_USERNAME / DB_PASSWORD out of the site's own .env, so
# no password prompt and no root MySQL user needed - just the app's own DB user.
# Dumps to OUTPUT_DIR (default: ~/db-backups) as a dated, gzipped .sql.gz file.
#
# Not scheduled - run by hand. See side-projects/server-setup/context.md in
# claude-context for the agreed daily-cron + Spaces-upload version, not yet built.

set -e

SITE_DIR="/var/www/switchscores.com"
ENV_FILE="${SITE_DIR}/.env"
OUTPUT_DIR="${1:-$HOME/db-backups}"

if [ ! -f "$ENV_FILE" ]; then
  echo "=== No .env found at ${ENV_FILE} ==="
  exit 1
fi

# Pull DB_* values out of .env without sourcing it (avoids executing arbitrary
# lines), then strip surrounding quotes if present.
read_env_var() {
  grep -E "^$1=" "$ENV_FILE" | tail -n1 | cut -d '=' -f2- | sed -e 's/^"//' -e 's/"$//' -e "s/^'//" -e "s/'$//"
}

DB_DATABASE=$(read_env_var "DB_DATABASE")
DB_USERNAME=$(read_env_var "DB_USERNAME")
DB_PASSWORD=$(read_env_var "DB_PASSWORD")

if [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ]; then
  echo "=== Could not read DB_DATABASE / DB_USERNAME from ${ENV_FILE} ==="
  exit 1
fi

mkdir -p "$OUTPUT_DIR"

DATE_STAMP=$(date +%y%m%d)
DUMP_FILE="${OUTPUT_DIR}/switchscores_${DB_DATABASE}_${DATE_STAMP}.sql"

echo "=== Dumping ${DB_DATABASE} ==="
MYSQL_PWD="$DB_PASSWORD" mysqldump \
  --single-transaction \
  --no-tablespaces \
  -u "$DB_USERNAME" \
  "$DB_DATABASE" > "$DUMP_FILE"

gzip -f "$DUMP_FILE"

echo "=== Done: ${DUMP_FILE}.gz ==="
