#!/usr/bin/env bash
# wp-export.sh — run as root on the source WordPress server
#
# Dumps the WordPress database and archives the site files into /tmp,
# then prints the scp commands to transfer them to the destination server.
#
# Usage:
#   Set env vars, then: ssh root@<SOURCE_IP> 'bash -s' < scripts/wp-export.sh
#
# Required env vars (set before running):
#   SOURCE_IP   — IP of the server this script runs on (for the scp hint)
#   DEST_IP     — IP of the destination server
#   WP_ROOT     — Absolute path to the WordPress install (e.g. /var/www/example.com/htdocs)
#   DB_NAME     — WordPress database name
#   DB_USER     — WordPress database user
#   DB_PASSWORD — WordPress database password

set -euo pipefail

: "${SOURCE_IP:?Set SOURCE_IP to the source server IP}"
: "${DEST_IP:?Set DEST_IP to the destination server IP}"
: "${WP_ROOT:?Set WP_ROOT to the WordPress install path}"
: "${DB_NAME:?Set DB_NAME}"
: "${DB_USER:?Set DB_USER}"
: "${DB_PASSWORD:?Set DB_PASSWORD}"

echo "==> [1/2] Dumping database..."
mysqldump \
    -u"${DB_USER}" \
    -p"${DB_PASSWORD}" \
    --single-transaction \
    --routines \
    --triggers \
    "${DB_NAME}" > /tmp/wp-db.sql

echo "    Saved: /tmp/wp-db.sql ($(du -sh /tmp/wp-db.sql | cut -f1))"

echo "==> [2/2] Archiving WordPress files..."
tar -czf /tmp/wp-files.tar.gz \
    -C "$(dirname "${WP_ROOT}")" \
    "$(basename "${WP_ROOT}")"

echo "    Saved: /tmp/wp-files.tar.gz ($(du -sh /tmp/wp-files.tar.gz | cut -f1))"

echo ""
echo "Export complete. Transfer files to the destination by running from your local machine:"
echo ""
echo "  scp root@${SOURCE_IP}:/tmp/wp-db.sql root@${DEST_IP}:/tmp/"
echo "  scp root@${SOURCE_IP}:/tmp/wp-files.tar.gz root@${DEST_IP}:/tmp/"
echo ""
echo "Then SSH into the destination server and run wp-import.sh."
