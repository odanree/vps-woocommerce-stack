#!/usr/bin/env bash
# Usage: ./scripts/smoke-test.sh <domain>
# Checks that the WooCommerce stack is up and returning expected responses.

set -uo pipefail

DOMAIN="${1:?Usage: $0 <domain>}"
BASE="https://${DOMAIN}"
PASS=0
FAIL=0

check() {
  local label="$1"
  local url="$2"
  local expected_code="$3"
  local expected_body="${4:-}"

  response=$(curl -s -o /tmp/smoke_body -w "%{http_code}" --max-time 10 "$url")

  if [[ "$response" != "$expected_code" ]]; then
    echo "FAIL  [$label] expected HTTP $expected_code, got $response -- $url"
    FAIL=$((FAIL + 1))
    return
  fi

  if [[ -n "$expected_body" ]] && ! grep -q "$expected_body" /tmp/smoke_body; then
    echo "FAIL  [$label] body missing \"$expected_body\" -- $url"
    FAIL=$((FAIL + 1))
    return
  fi

  echo "OK    [$label] $url"
  PASS=$((PASS + 1))
}

echo "=== Smoke test: $BASE ==="
echo ""

check "Homepage loads"        "$BASE/"                    200  "wp-content"
check "WP login page"         "$BASE/wp-login.php"        200  "Log In"
check "WP admin redirects"    "$BASE/wp-admin/"           302  ""
check "WooCommerce REST API"  "$BASE/wp-json/wc/v3/"     200  "namespace"
check "WP REST API root"      "$BASE/wp-json/"            200  "authentication"
check "xmlrpc blocked"        "$BASE/xmlrpc.php"          403  ""

echo ""
echo "=== Results: $PASS passed, $FAIL failed ==="
[[ $FAIL -eq 0 ]]
