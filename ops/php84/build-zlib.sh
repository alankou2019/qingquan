#!/usr/bin/env bash
set -euo pipefail

version="1.3.2"
build_root="/root/php84-upgrade"
prefix="/opt/php84-deps"
archive="zlib-${version}.tar.gz"

mkdir -p "${build_root}" "${prefix}"
cd "${build_root}"

wget -4 -O "${archive}" "https://zlib.net/current/zlib.tar.gz"
echo "bb329a0a2cd0274d05519d61c667c062e06990d72e125ee2dfa8de64f0119d16  ${archive}" \
    | sha256sum -c -

if [[ ! -d "zlib-${version}" ]]; then
    tar -xf "${archive}"
fi

cd "zlib-${version}"
./configure --prefix="${prefix}"
make -j2
make install

PKG_CONFIG_PATH="${prefix}/lib/pkgconfig" pkg-config --modversion zlib
