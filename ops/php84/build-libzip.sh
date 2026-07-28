#!/usr/bin/env bash
set -euo pipefail

version="1.11.4"
build_root="/root/php84-upgrade"
prefix="/opt/php84-deps"
archive="libzip-${version}.tar.xz"
checksum="8a247f57d1e3e6f6d11413b12a6f28a9d388de110adc0ec608d893180ed7097b"

if command -v cmake3 >/dev/null 2>&1; then
    cmake_bin="cmake3"
else
    cmake_bin="cmake"
fi

cmake_version="$("${cmake_bin}" --version | awk 'NR == 1 { print $3 }')"
test "$(printf '%s\n' "3.10.0" "${cmake_version}" | sort -V | head -n1)" = "3.10.0"

mkdir -p "${build_root}" "${prefix}"
cd "${build_root}"

wget -4 -c -O "${archive}" "https://libzip.org/download/${archive}"
echo "${checksum}  ${archive}" | sha256sum -c -

if [[ ! -d "libzip-${version}" ]]; then
    tar -xf "${archive}"
fi

cd "libzip-${version}"
mkdir -p build
cd build

export PKG_CONFIG_PATH="${prefix}/lib/pkgconfig"
"${cmake_bin}" .. \
    -DCMAKE_INSTALL_PREFIX="${prefix}" \
    -DCMAKE_INSTALL_LIBDIR=lib \
    -DCMAKE_PREFIX_PATH="${prefix}" \
    -DCMAKE_BUILD_TYPE=Release \
    -DBUILD_SHARED_LIBS=ON \
    -DENABLE_OPENSSL=ON \
    -DENABLE_BZIP2=OFF \
    -DENABLE_LZMA=OFF \
    -DENABLE_ZSTD=OFF \
    -DBUILD_DOC=OFF \
    -DBUILD_EXAMPLES=OFF \
    -DBUILD_REGRESS=OFF
"${cmake_bin}" --build . -- -j2
make install

PKG_CONFIG_PATH="${prefix}/lib/pkgconfig:${prefix}/lib64/pkgconfig" \
    pkg-config --modversion libzip
