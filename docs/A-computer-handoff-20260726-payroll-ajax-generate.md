# Salary Payroll AJAX Generation Handoff

## Scope

- Changed only the salary payroll generation flow.
- No production server, production database, or production configuration was changed.

## Behavior

- Selecting a month and clicking `Generate` now submits in the background.
- The old system message page is no longer shown for this action.
- On success, only the payroll matrix area is replaced with the newly generated month data.
- The selected month remains on the current payroll page.
- If the visible payroll table has unsaved edits, generation is blocked until it is saved.
- Re-generating still overwrites the selected unarchived month using the current initial salary table.
- Archived or published payroll months remain protected by the existing backend rule.

## Files

- `app/code/Salary/controllers/Frontend/SalaryController.php`
- `app/design/frontend/default/Salary/salary/payroll.volt`

## Verification

- GitHub connector checked `master` before this change. The remote payroll template was still at pre-change SHA `b04fc550fa0c75f2f6afc3678ad00187bab816f3`.
- Local test session: POST `/salary/generatepayroll` with `salary_ajax=1` and month `2026-08` returned HTTP 200 JSON success.
- The returned reload URL rendered `#payroll_matrix_region` and the selected month.
- The added generation JavaScript was syntax-checked with Node.js.

## Git note

- Terminal SSH fetch failed because the local Git SSH credential was not accepted by GitHub.
- The GitHub connector was used for the remote read check. Push still requires restoring the local SSH credential or publishing through the configured GitHub workflow.
