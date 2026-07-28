#!/usr/bin/env bash
set -euo pipefail

version="8.4.22"
build_root="/root/php84-upgrade"
prefix="/opt/php84"
dependency_prefix="/opt/php84-deps"
archive="php-${version}.tar.xz"
checksum="696c0f6ad92e94c59059c1eb6e300842b8d050934226efcdf00f2a413cb083cf"

mkdir -p "${build_root}" "${prefix}/etc/conf.d"
cd "${build_root}"

wget -4 -c -O "${archive}" "https://www.php.net/distributions/${archive}"
echo "${checksum}  ${archive}" | sha256sum -c -

if [[ ! -d "php-${version}" ]]; then
    tar -xf "${archive}"
fi

cd "php-${version}"
export PKG_CONFIG_PATH="${dependency_prefix}/lib/pkgconfig"
export CPPFLAGS="-I${dependency_prefix}/include"
export LDFLAGS="-L${dependency_prefix}/lib -Wl,-rpath,${dependency_prefix}/lib"

./configure \
    --prefix="${prefix}" \
    --with-config-file-path="${prefix}/etc" \
    --with-config-file-scan-dir="${prefix}/etc/conf.d" \
    --enable-fpm \
    --with-fpm-user=www \
    --with-fpm-group=www \
    --disable-cgi \
    --with-openssl \
    --with-zlib \
    --with-curl \
    --with-zip \
    --with-mysqli=mysqlnd \
    --with-pdo-mysql=mysqlnd \
    --enable-mbstring \
    --enable-bcmath \
    --enable-soap \
    --enable-sockets \
    --enable-opcache \
    --enable-exif \
    --enable-pcntl \
    --enable-ftp \
    --enable-calendar \
    --enable-gd \
    --with-jpeg \
    --with-freetype \
    --with-gettext
make -j2
make install

cp php.ini-production "${prefix}/etc/php.ini"
"${prefix}/bin/php" -v
"${prefix}/bin/php" -m
