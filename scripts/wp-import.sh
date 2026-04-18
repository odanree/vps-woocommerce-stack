#!/usr/bin/env bash
# wp-import.sh — run as root on the destination server (Ubuntu 24.04)
#
# Provisions a LEMP stack, imports the WordPress database and files
# exported by wp-export.sh, issues an SSL certificate via Certbot, and
# runs a WP-CLI search-replace to update any http:// URLs to https://.
#
# Usage:
#   Set env vars, then: ssh root@<DEST_IP> 'bash -s' < scripts/wp-import.sh
#
# Required env vars:
#   DOMAIN       — Domain the site will be served on (e.g. cms.example.com)
#   DB_NAME      — WordPress database name
#   DB_USER      — WordPress database user
#   DB_PASSWORD  — WordPress database password
#   ADMIN_EMAIL  — Email address for Certbot SSL registration
#
# Optional:
#   PHP_VER      — PHP version to install (default: 8.3)
#   SKIP_SSL     — Set to "1" to skip Certbot (if DNS isn't propagated yet)

set -euo pipefail

: "${DOMAIN:?Set DOMAIN to the site domain}"
: "${DB_NAME:?Set DB_NAME}"
: "${DB_USER:?Set DB_USER}"
: "${DB_PASSWORD:?Set DB_PASSWORD}"
: "${ADMIN_EMAIL:?Set ADMIN_EMAIL for Certbot}"

PHP_VER="${PHP_VER:-8.3}"
SKIP_SSL="${SKIP_SSL:-0}"
SITE_ROOT="/var/www/${DOMAIN}"
WP_ROOT="${SITE_ROOT}/htdocs"

# ── 1. System packages ────────────────────────────────────────────────────────
echo "==> [1/8] Installing Nginx, PHP ${PHP_VER}, MySQL, Certbot..."
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get install -y -qq \
    nginx \
    mysql-server \
    certbot python3-certbot-nginx \
    "php${PHP_VER}-fpm" \
    "php${PHP_VER}-mysql" \
    "php${PHP_VER}-curl" \
    "php${PHP_VER}-gd" \
    "php${PHP_VER}-mbstring" \
    "php${PHP_VER}-xml" \
    "php${PHP_VER}-zip" \
    "php${PHP_VER}-intl" \
    "php${PHP_VER}-bcmath"

# ── 2. WP-CLI ─────────────────────────────────────────────────────────────────
echo "==> [2/8] Installing WP-CLI..."
if ! command -v wp &>/dev/null; then
    curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
    chmod +x wp-cli.phar
    mv wp-cli.phar /usr/local/bin/wp
fi

# ── 3. Directory and database ─────────────────────────────────────────────────
echo "==> [3/8] Creating site directory and database..."
mkdir -p "${WP_ROOT}"

mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
mysql -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# ── 4. Extract WordPress files ────────────────────────────────────────────────
echo "==> [4/8] Extracting WordPress files..."
tar -xzf /tmp/wp-files.tar.gz -C "${SITE_ROOT}/"

# ── 5. Import database ────────────────────────────────────────────────────────
echo "==> [5/8] Importing database..."
mysql -u"${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" < /tmp/wp-db.sql

# ── 6. Nginx vhost ────────────────────────────────────────────────────────────
echo "==> [6/8] Configuring Nginx..."
cat > "/etc/nginx/sites-available/${DOMAIN}" <<NGINX
server {
    listen 80;
    server_name ${DOMAIN};
    root ${WP_ROOT};
    index index.php index.html;

    client_max_body_size 64M;

    location / {
        try_files \$uri \$uri/ /index.php?\$args;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php${PHP_VER}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_read_timeout 120;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff2?)\$ {
        expires max;
        log_not_found off;
    }

    location = /xmlrpc.php { deny all; }
    location ~ /\.ht { deny all; }
}
NGINX

ln -sf "/etc/nginx/sites-available/${DOMAIN}" "/etc/nginx/sites-enabled/${DOMAIN}"
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

# ── 7. File permissions ───────────────────────────────────────────────────────
echo "==> [7/8] Setting file permissions..."
chown -R www-data:www-data "${SITE_ROOT}"
find "${WP_ROOT}" -type d -exec chmod 755 {} \;
find "${WP_ROOT}" -type f -exec chmod 644 {} \;
chmod 600 "${WP_ROOT}/wp-config.php"

# ── 8. SSL certificate ────────────────────────────────────────────────────────
echo "==> [8/8] SSL certificate..."
if [[ "${SKIP_SSL}" == "1" ]]; then
    echo "    Skipped (SKIP_SSL=1). Run manually after DNS propagates:"
    echo "    certbot --nginx -d ${DOMAIN} --email ${ADMIN_EMAIL} --agree-tos --redirect"
else
    certbot --nginx \
        -d "${DOMAIN}" \
        --non-interactive \
        --agree-tos \
        --email "${ADMIN_EMAIL}" \
        --redirect
fi

# ── WP-CLI search-replace: http → https ──────────────────────────────────────
echo "==> Running WP-CLI search-replace (http → https)..."
wp search-replace \
    "http://${DOMAIN}" \
    "https://${DOMAIN}" \
    --all-tables \
    --allow-root \
    --path="${WP_ROOT}"

echo ""
echo "================================================================"
echo " Import complete."
echo "================================================================"
echo ""
echo " Next steps:"
echo "   1. Update DNS: ${DOMAIN} A record → this server's IP"
echo "      (Required before Certbot will work, if SKIP_SSL=1 was used)"
echo ""
echo "   2. Verify the REST API:"
echo "      curl -I https://${DOMAIN}/wp-json/wp/v2/posts"
echo ""
echo "   3. Check Vercel env var WORDPRESS_URL still points to https://${DOMAIN}"
echo ""
echo "   4. Decommission or repurpose the old server."
echo "================================================================"
