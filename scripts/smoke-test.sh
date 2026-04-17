#!/usr/bin/env bash
# smoke-test.sh — verify the provisioned stack is healthy
# Usage: ./scripts/smoke-test.sh shop.example.com [server-ip]
set -euo pipefail

DOMAIN="${1:?Usage: smoke-test.sh <domain>}"
PASS=0
FAIL=0

green()  { echo -e "\033[32m✔  $*\033[0m"; }
red()    { echo -e "\033[31m✘  $*\033[0m"; }
yellow() { echo -e "\033[33m⚠  $*\033[0m"; }

check() {
    local label="$1"
    shift
    if "$@" &>/dev/null; then
        green "$label"
        ((PASS++))
    else
        red "$label"
        ((FAIL++))
    fi
}

echo ""
echo "── Smoke test: $DOMAIN ──────────────────────────────────"
echo ""

# TLS certificate validity
check "TLS cert valid" \
    bash -c "echo | openssl s_client -connect '$DOMAIN:443' -servername '$DOMAIN' 2>/dev/null | openssl x509 -noout -checkend 86400"

# HTTPS redirect from HTTP
check "HTTP → HTTPS redirect" \
    bash -c "curl -sI 'http://$DOMAIN/' | grep -q '301\|302'"

# HTTPS response
check "HTTPS 200 from homepage" \
    bash -c "curl -sf --max-time 10 'https://$DOMAIN/' > /dev/null"

# HSTS header
check "HSTS header present" \
    bash -c "curl -sI 'https://$DOMAIN/' | grep -qi 'strict-transport-security'"

# Security headers
check "X-Frame-Options header" \
    bash -c "curl -sI 'https://$DOMAIN/' | grep -qi 'x-frame-options'"
check "X-Content-Type-Options header" \
    bash -c "curl -sI 'https://$DOMAIN/' | grep -qi 'x-content-type-options'"

# WordPress admin reachable (200 or redirect to login — not 500)
check "wp-admin reachable (not 500)" \
    bash -c "status=\$(curl -so /dev/null -w '%{http_code}' 'https://$DOMAIN/wp-admin/'); [[ \$status != 500 ]]"

# xmlrpc.php blocked
check "xmlrpc.php blocked (403)" \
    bash -c "status=\$(curl -so /dev/null -w '%{http_code}' -X POST 'https://$DOMAIN/xmlrpc.php'); [[ \$status == 403 ]]"

# wp-config.php blocked
check "wp-config.php blocked (403/404)" \
    bash -c "status=\$(curl -so /dev/null -w '%{http_code}' 'https://$DOMAIN/wp-config.php'); [[ \$status == 403 || \$status == 404 ]]"

# TLS grade check (informational only)
if command -v nmap &>/dev/null; then
    weak_ciphers=$(nmap --script ssl-enum-ciphers -p 443 "$DOMAIN" 2>/dev/null | grep -c "TLSv1.0\|TLSv1.1\|NULL\|EXPORT\|DES\|RC4" || true)
    if [[ "$weak_ciphers" -eq 0 ]]; then
        green "No weak TLS ciphers detected"
        ((PASS++))
    else
        red "Weak TLS ciphers detected (count: $weak_ciphers)"
        ((FAIL++))
    fi
else
    yellow "nmap not installed — skipping cipher check"
fi

echo ""
echo "── Results: $PASS passed, $FAIL failed ─────────────────"
echo ""

[[ $FAIL -eq 0 ]]
