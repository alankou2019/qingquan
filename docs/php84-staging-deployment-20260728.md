# PHP 8.4 staging deployment record

Deployment date: 2026-07-28

## Isolation

- Application: `/www/wwwroot/dingding/wecom-php84-staging`
- Database: `wecom_kpi_php84_staging`
- Runtime: PHP 8.4.22 at `/opt/php84`
- Dependencies: `/opt/php84-deps`
- Phalcon: 5.17.0
- FPM: `127.0.0.1:9084`
- Staging domain: `https://wecom-kpi.dacangcons.cn`
- Git branch: `codex/php84-upgrade`

The production application, database, PHP runtime, and Nginx virtual host were
not modified.

## Rollback points

- Database source backup:
  `/root/php84-upgrade/db-backups/wecom_kpi_staging-20260728_191300.sql.gz`
- Previous staging Nginx virtual host:
  `/root/php84-upgrade/nginx-backups/wecom-kpi.dacangcons.cn.before-php84-20260728-202155.conf`

## Verification completed

- PHP syntax scan for the application and PHP 8.4 tests
- Crypto compatibility checks
- Phalcon cache, session, loader, and dispatcher checks
- MySQL 5.6 dialect compatibility
- PhpSpreadsheet write/read round trip
- Admin captcha, login, and authenticated dashboard
- Enterprise-user captcha and login
- Twenty GET routes covering organization, employees, performance, salary,
  payroll, payroll archive, payslips, reports, commission, and
  performance-linked salary
- Nginx rewrite, static assets, protected-path 404, TLS staging response, and
  PHP 8.4 runtime response header
- Production login redirect remained healthy after each Nginx reload

The staging database contains a copied test dataset plus empty legacy
performance table structures. No production business rows were copied into
the staging database during this deployment.
