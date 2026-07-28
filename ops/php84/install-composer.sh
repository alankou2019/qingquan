#!/usr/bin/env bash
set -euo pipefail

php_prefix="/opt/php84"
build_root="/root/php84-upgrade"
installer="${build_root}/composer-setup.php"

mkdir -p "${build_root}"
cd "${build_root}"

expected_signature="$(wget -4 -q -O - https://composer.github.io/installer.sig)"
wget -4 -O "${installer}" https://getcomposer.org/installer
actual_signature="$(sha384sum "${installer}" | awk '{ print $1 }')"
test "${actual_signature}" = "${expected_signature}"

"${php_prefix}/bin/php" "${installer}" \
    --install-dir="${php_prefix}/bin" \
    --filename=composer \
    --2
rm -f "${installer}"

"${php_prefix}/bin/composer" --version
