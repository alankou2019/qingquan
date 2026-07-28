#!/usr/bin/env bash
set -euo pipefail

version="8.21.0"
build_root="/root/php84-upgrade"
prefix="/opt/php84-deps"
archive="curl-${version}.tar.xz"
checksum="aa1b66a70eace83dc624508745646c08ae561de512ab403adffb93ac87fc72e6"

mkdir -p "${build_root}" "${prefix}"
cd "${build_root}"

wget -4 -c -O "${archive}" "https://curl.se/download/${archive}"
echo "${checksum}  ${archive}" | sha256sum -c -

if [[ ! -d "curl-${version}" ]]; then
    tar -xf "${archive}"
fi

cd "curl-${version}"
export PKG_CONFIG_PATH="${prefix}/lib/pkgconfig"
export CPPFLAGS="-I${prefix}/include"
export LDFLAGS="-L${prefix}/lib -Wl,-rpath,${prefix}/lib"

./configure \
    --prefix="${prefix}" \
    --with-openssl="${prefix}" \
    --with-zlib="${prefix}" \
    --disable-ldap \
    --disable-ldaps \
    --without-libpsl \
    --without-brotli \
    --without-zstd \
    --without-nghttp2 \
    --without-nghttp3 \
    --without-libssh2
make -j2
make install

LD_LIBRARY_PATH="${prefix}/lib" "${prefix}/bin/curl" --version
