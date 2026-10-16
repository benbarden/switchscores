#!/bin/bash

# Deploy Switch Scores on infrared-switch: pull the latest code and apply it.
#
# Usage (as benbarden, from anywhere):
#   sudo /var/www/switchscores.com/scripts/deploy.sh
#
# Runs as root so git can use root's deploy-key alias and ownership can be reset; every
# Composer and artisan step runs as www-data, the owner of the tree. Running them as anyone
# else leaves files www-data can't write (500s later) or hits Composer's
# "touch(): Utime failed: Operation not permitted" - setting an mtime needs ownership, not
# group write (see side-projects/switch-scores/context.md, 2026-08-07).
#
# Replaces scripts/post-deploy.sh, which was for the codacity4 SFTP flow.
#
# The body is one function called on the last line: bash reads the whole function before
# running it, so a `git pull` that changes this file can't affect the run in progress.

set -e

SITE_DIR="/var/www/switchscores.com"

as_www() {
  sudo -u www-data "$@"
}

main() {
  if [ "$(id -u)" -ne 0 ]; then
    echo "Run with sudo: sudo ${SITE_DIR}/scripts/deploy.sh"
    exit 1
  fi

  cd "$SITE_DIR"

  echo "=== Pulling latest code ==="
  git pull
  git log -1 --format='    now at %h %s'

  echo "=== Resetting ownership ==="
  chown -R www-data:www-data .
  chmod -R g+rw .

  echo "=== Installing Composer dependencies ==="
  as_www composer install --no-dev --optimize-autoloader --no-interaction

  echo "=== Clearing Laravel caches ==="
  as_www php artisan cache:clear
  as_www php artisan config:clear
  as_www php artisan route:clear
  as_www php artisan view:clear

  # Not strictly needed (twigbridge auto_reload recompiles changed templates), but cheap.
  echo "=== Clearing Twig cache ==="
  rm -rf storage/framework/views/twig/*

  echo "=== Running migrations ==="
  as_www php artisan migrate --force

  echo "=== Restarting queue worker ==="
  supervisorctl restart all

  echo "=== Deploy complete ==="
}

main "$@"; exit
