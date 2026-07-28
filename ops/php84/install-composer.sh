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
    --filename=composer.phar \
    --2
rm -f "${installer}"

printf '%s\n' \
    '#!/usr/bin/env bash' \
    'exec /opt/php84/bin/php /opt/php84/bin/composer.phar "$@"' \
    > "${php_prefix}/bin/composer"
chmod 0755 "${php_prefix}/bin/composer"

"${php_prefix}/bin/composer" --version
