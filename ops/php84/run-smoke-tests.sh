#!/usr/bin/env bash
set -euo pipefail

project_root="${1:-/www/wwwroot/dingding/wecom-php84-staging}"
php_bin="${PHP84_BIN:-/opt/php84/bin/php}"

test -x "${php_bin}"
test -d "${project_root}/app"

"${php_bin}" -v
"${php_bin}" --ri phalcon

while IFS= read -r -d '' php_file; do
    "${php_bin}" -l "${php_file}" >/dev/null
done < <(
    find \
        "${project_root}/app" \
        "${project_root}/tests/php84" \
        -type f -name '*.php' -print0
)
"${php_bin}" -l "${project_root}/index.php" >/dev/null

cd "${project_root}"
"${php_bin}" tests/php84/crypto_compat.php
"${php_bin}" tests/php84/phalcon_services.php
"${php_bin}" tests/php84/phpspreadsheet_roundtrip.php

printf '%s\n' "PASS: PHP 8.4 smoke test suite"
