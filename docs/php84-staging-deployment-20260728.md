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
- Clean pre-write staging database backup:
  `/root/php84-upgrade/db-backups/wecom_kpi_php84_staging-pre-write-clean-20260728_203722.sql.gz`
  (SHA-256:
  `daa9f79d6085a2dfab17dfad07c0eb2bd8990b984a0b302b4c35808dd31aadc5`)
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
- Synthetic write regression for KPI reports, points-based performance,
  custom salary projects, and commission projects: create, update, read,
  delete, and database verification
- Performance deletion scope regression: records belonging to the logged-in
  company were deleted completely, while a synthetic foreign-company ID was
  rejected
- Every synthetic write used a unique `PHP84REG-*` or `PHP84DEL-*` marker and
  was removed after verification; post-test marker counts were zero
- PHP 5.6, PHP 7.3, and PHP 8.4 syntax checks for all controllers changed by
  the write regression fixes
- No new PHP 8.4 error-log entries during the final write and deletion-scope
  regression runs
- Full future-month payroll lifecycle regression for `2099-12`: generated and
  saved a 9-employee payroll with 108 item values, submitted it for review,
  verified that pre-approval archiving was blocked, approved it as the logged-in
  reviewer, archived it, published 9 payslips, and opened the archive, payslip
  confirmation, list, and detail pages
- The payroll lifecycle test temporarily replaced the staging fixture reviewer
  with the regression account and restored the original reviewer afterward;
  payroll periods, rows, values, audit records, archives, payslips, operation
  logs, and the temporary session were removed, leaving zero `2099-12` residue
- Payslip publication only wrote the isolated staging payroll tables; the tested
  controller/model path did not invoke WeCom, Feishu, SMS, or other external
  notification services
- Nginx rewrite, static assets, protected-path 404, TLS staging response, and
  PHP 8.4 runtime response header
- Production login redirect remained healthy after each Nginx reload

## Issues fixed by write regression

- Replaced legacy empty-string numeric accumulators in KPI and points-based
  performance validation with numeric zero for PHP 8.4 compatibility.
- Recovered the newly-created salary project ID before building the AJAX
  response, so creation now returns the saved project instead of a false
  read-back error.
- Completed KPI and points-based performance deletion by removing the report
  master row after its child rows, and restricted deletion to the logged-in
  company.

- Bound payroll approval to the reviewer identity in the authenticated session
  instead of trusting a posted `reviewer_id`, preventing approval impersonation.
- Added the missing submit-review and reviewer approve/reject controls to the
  payroll page, with audit progress shown for the current payroll.
- Restricted payroll archiving to the `approved` state so draft, calculated,
  rejected, and submitted payrolls cannot bypass the review workflow.

The staging database contains a copied test dataset plus empty legacy
performance table structures. No production business rows were copied into
the staging database during this deployment.
