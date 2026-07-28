#!/usr/bin/env bash
set -euo pipefail

version="2.12.10"
build_root="/root/php84-upgrade"
prefix="/opt/php84-deps"
archive="libxml2-${version}.tar.xz"
source_url="https://download.gnome.org/sources/libxml2/2.12/${archive}"
checksum_url="${source_url%.tar.xz}.sha256sum"

mkdir -p "${build_root}" "${prefix}"
cd "${build_root}"

wget -4 -c -O "${archive}" "${source_url}"
wget -4 -O "libxml2-${version}.sha256sum" "${checksum_url}"
grep "${archive}" "libxml2-${version}.sha256sum" | sha256sum -c -

if [[ ! -d "libxml2-${version}" ]]; then
    tar -xf "${archive}"
fi

cd "libxml2-${version}"
./configure \
    --prefix="${prefix}" \
    --without-python \
    --without-lzma
make -j2
make install

"${prefix}/bin/xml2-config" --version
