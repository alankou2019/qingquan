# A computer handoff: payroll snapshot isolation

## Scope

Salary module only. No operations-admin code, production configuration, customer data, or production server files were changed.

## Behavior

- A newly generated monthly payroll copies the latest initial salary table employees, salary projects, formulas, and values.
- After generation, that monthly payroll uses its own employee and project snapshot. Later changes to employee records, salary projects, formulas, or initial salary values do not alter it.
- Saving an existing monthly payroll only updates employees and projects already stored in that payroll snapshot.
- A payroll archive stores a versioned snapshot containing projects, employees, values, and totals.
- Restoring an archive uses the archive snapshot and keeps the original archive record.
- Older payroll periods and older row-only archive snapshots remain readable through a compatibility fallback.
- Display-only initial-table summary columns are no longer written as salary projects with ID 0.

## Database upgrade

- Salary module version: `1.0.0.14`
- Migration: `app/code/Salary/sql/install-1.0.0.14.php`
- Added nullable `project_snapshot MEDIUMTEXT` to `payroll_periods`.
- Existing payroll data is not rewritten or deleted.

## Performance fix

- Payroll save no longer checks the table schema once per employee/project cell.
- Employee rows and salary item values are inserted in batches, preventing the previous 60-second timeout growth as employee and project counts increase.

## Verification completed on B computer

- PHP 7.3 syntax checks passed for all changed PHP files.
- Module migration created `project_snapshot` successfully in the local development database.
- Generated local test payroll `2099-01`; verified valid project snapshot, employee rows, item values, and snapshot isolation; removed all test data afterward.
- Generated, archived, and restored local test payroll `2099-02`; verified archive snapshot version 2, archive retention after restore, and restored payroll project snapshot; removed all test data afterward.
- Production server and production database were not touched.

## A computer actions

1. Pull the commit and review this handoff before adding any Salary changes.
2. Run only in the isolated staging environment first and confirm module migration `1.0.0.14` executes.
3. Test one new month, edit current salary project/initial values, and confirm the generated month remains unchanged.
4. Archive and restore that month, confirming the archive stays available and restored values match the archive.
5. Do not deploy to production until the user completes manual testing and explicitly approves production deployment.
