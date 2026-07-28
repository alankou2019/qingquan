#!/usr/bin/env bash
set -euo pipefail

version="5.17.0"
commit="cc8b2ac68f51d93e7146d07da5ae8c41f4c5a23c"
build_root="/root/php84-upgrade"
source_dir="${build_root}/cphalcon-${version}"
php_prefix="/opt/php84"

mkdir -p "${build_root}" "${php_prefix}/etc/conf.d"

if [[ ! -d "${source_dir}/.git" ]]; then
    git clone --depth 1 --branch "v${version}" \
        https://github.com/phalcon/cphalcon.git "${source_dir}"
fi

cd "${source_dir}"
test "$(git rev-parse HEAD)" = "${commit}"

cd build/phalcon
"${php_prefix}/bin/phpize"
./configure --with-php-config="${php_prefix}/bin/php-config"
make -j1
make install

printf '%s\n' "extension=phalcon.so" \
    > "${php_prefix}/etc/conf.d/50-phalcon.ini"
"${php_prefix}/bin/php" --ri phalcon
