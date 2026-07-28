# PHP 8.4 isolated test runtime

These scripts build a PHP 8.4 test runtime without replacing the server's
system libraries or existing PHP installations.

- Runtime prefix: `/opt/php84`
- Private dependency prefix: `/opt/php84-deps`
- Build workspace: `/root/php84-upgrade`
- Test application: a separate directory and FPM pool (configured later)

The scripts are intentionally version-pinned and verify upstream checksums
before compiling.
