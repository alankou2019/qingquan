# PHP 8.4 isolated test runtime

These scripts build a PHP 8.4 test runtime without replacing the server's
system libraries or existing PHP installations.

- Runtime prefix: `/opt/php84`
- Private dependency prefix: `/opt/php84-deps`
- Build workspace: `/root/php84-upgrade`
- Test application: `/www/wwwroot/dingding/wecom-php84-staging`
- Test database: `wecom_kpi_php84_staging`
- FPM endpoint: `127.0.0.1:9084`
- Test domain: `https://wecom-kpi.dacangcons.cn`

The scripts are intentionally version-pinned and verify upstream checksums
before compiling.

The production application, database, PHP installations, and Nginx virtual
host are not replaced by this runtime.

## Validation

Run the CLI compatibility suite:

```bash
/root/php84-upgrade/php84/run-smoke-tests.sh \
  /www/wwwroot/dingding/wecom-php84-staging
```

The test response must include:

```text
X-Wecom-Runtime: php84-staging
```

Always run `/www/server/nginx/sbin/nginx -t` before a graceful Nginx reload.

## Nginx rollback

Before changing the test virtual host, save a timestamped copy under
`/root/php84-upgrade/nginx-backups/`. To roll back, restore that exact file to
`/www/server/panel/vhost/nginx/wecom-kpi.dacangcons.cn.conf`, run `nginx -t`,
and only then perform a graceful reload. The production virtual host must not
be edited as part of this rollback.
