#!/usr/bin/env bash
set -euo pipefail

version="3.5.7"
build_root="/root/php84-upgrade"
prefix="/opt/php84-deps"
archive="openssl-${version}.tar.gz"
release_url="https://github.com/openssl/openssl/releases/download/openssl-${version}"

mkdir -p "${build_root}" "${prefix}"
cd "${build_root}"

wget -4 -c -O "${archive}" "${release_url}/${archive}"
wget -4 -O "${archive}.sha256" "${release_url}/${archive}.sha256"
sha256sum -c "${archive}.sha256"

if [[ ! -d "openssl-${version}" ]]; then
    tar -xf "${archive}"
fi

cd "openssl-${version}"
./Configure \
    --prefix="${prefix}" \
    --openssldir="${prefix}/ssl" \
    --libdir=lib \
    shared \
    zlib
make -j2
make install_sw

LD_LIBRARY_PATH="${prefix}/lib" "${prefix}/bin/openssl" version
