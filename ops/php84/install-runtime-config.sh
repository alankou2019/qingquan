#!/usr/bin/env bash
set -euo pipefail

project_root="${1:?usage: install-runtime-config.sh PROJECT_ROOT}"
php_prefix="/opt/php84"

test -x "${php_prefix}/sbin/php-fpm"
test -d "${project_root}/ops/php84/config"
id www >/dev/null 2>&1

install -d -m 0755 \
    "${php_prefix}/etc/conf.d" \
    "${php_prefix}/etc/php-fpm.d" \
    "${php_prefix}/var/log" \
    "${php_prefix}/var/run"
install -m 0644 \
    "${project_root}/ops/php84/config/php-fpm.conf" \
    "${php_prefix}/etc/php-fpm.conf"
install -m 0644 \
    "${project_root}/ops/php84/config/wecom-php84.conf" \
    "${php_prefix}/etc/php-fpm.d/wecom-php84.conf"
install -m 0644 \
    "${project_root}/ops/php84/config/99-wecom.ini" \
    "${php_prefix}/etc/conf.d/99-wecom.ini"

chown -R www:www "${php_prefix}/var/log" "${php_prefix}/var/run"

"${php_prefix}/sbin/php-fpm" \
    --test \
    --fpm-config "${php_prefix}/etc/php-fpm.conf"
