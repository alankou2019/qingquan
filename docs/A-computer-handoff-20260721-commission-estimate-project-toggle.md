# A computer handoff: commission estimate project toggle

## Scope

Salary module commission estimate page only. Operations-admin code and production were not changed.

## Behavior

- The employee income estimate table displays each commission project's rule summary.
- Each applicable commission project can be temporarily disabled or enabled for the current estimate.
- A disabled project remains visible, but its low, middle, and high commission amounts are calculated as zero.
- The total commission, monthly income, annual income, and chart update immediately after a project is toggled.
- Saved estimate records retain the enabled state and show it as read-only text.
- Repeated unchanged requests are skipped and an unfinished calculation request is cancelled before the next one is sent.

## Verification completed on B computer

- PHP 7.3 syntax check passed.
- JavaScript syntax check passed.
- Local page `/salary/commissionestimate` loaded without a fatal error.
- Local calculation API test: project values 1000/2000/3000 returned commission 100/200/300; a disabled project returned zero for all three levels.
- No estimate record was saved by the calculation-only test.

## A computer actions

1. Pull and review this commit before adding related commission estimate changes.
2. Test changing three estimate values and toggling one project in isolated staging.
3. Confirm saved estimate records retain the selected project state.
4. Do not deploy to production until the user completes manual testing and explicitly approves it.
