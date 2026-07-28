#!/usr/bin/env bash
set -euo pipefail

source_dir="/root/php84-upgrade/openssl-3.5.7-git"
prefix="/opt/php84-deps"

cd "${source_dir}"
test "$(git rev-parse HEAD)" = "8cf17aaeb4599f8af87fefd810b5b5fee90fe69e"
test "$(git tag --points-at HEAD)" = "openssl-3.5.7"

./Configure \
    --prefix="${prefix}" \
    --openssldir="${prefix}/ssl" \
    --libdir=lib \
    shared \
    zlib
make -j2
make install_sw

LD_LIBRARY_PATH="${prefix}/lib" "${prefix}/bin/openssl" version
