# A电脑交接：员工手机端薪酬查询完善

## 本次开发内容

- 完善员工手机端薪酬查询体系，继续沿用旧 `bs` 手机端页面。
- 已有入口：`/bs/salary`。
- 本次完善页面：
  - 当月薪酬：继续在 `/bs/salary` 展示。
  - 当年薪酬：`/bs/salaryyear`，新增年度汇总。
  - 往年薪酬：`/bs/salaryhistory`，新增历史汇总。
  - 下属薪酬：`/bs/salarysubordinate`，由预留说明页改为授权范围内的下属薪酬列表。
  - 下属薪酬详情：新增 `/bs/salarysubordinatedetail?id=工资条ID`。

## 权限与安全

- 员工本人只能查看已发放给自己的工资条。
- 下属薪酬不按组织上下级自动放开，按企业后台“薪酬管理授权”控制：
  - 授权部门可查看该部门已发工资条。
  - 授权指定员工可查看指定员工已发工资条。
  - 未授权则不展示下属薪酬明细。
- 管理者查看下属薪酬详情时，不会替员工标记“已查看”。
- 管理者查看下属薪酬详情会写入薪酬操作日志：`mobile_subordinate_salary_view`。

## 本地验证

- `php -l app/code/Dacang/controllers/Frontend/BsController.php`
- `php -l app/code/Salary/Model/PayrollSlipModel.php`
- `php -l app/code/Salary/Model/SalaryOperationLogModel.php`
- 本地访问：
  - `http://127.0.0.1:8101/bs/salary`
  - `http://127.0.0.1:8101/bs/salaryyear`
  - `http://127.0.0.1:8101/bs/salaryhistory`
  - `http://127.0.0.1:8101/bs/salarysubordinate`
- 本地测试用户可打开本人薪酬详情；下属薪酬因未授权不展示明细，符合预期。

## A电脑继续开发前注意

- 先拉取 GitHub 最新代码。
- 如A电脑也改过手机端薪酬页面，请先对比：
  - `app/code/Dacang/controllers/Frontend/BsController.php`
  - `app/code/Salary/Model/PayrollSlipModel.php`
  - `app/design/frontend/default/Dacang/bs/salary.volt`
  - `app/design/frontend/default/Dacang/bs/salaryyear.volt`
  - `app/design/frontend/default/Dacang/bs/salaryhistory.volt`
  - `app/design/frontend/default/Dacang/bs/salarysubordinate.volt`
  - `app/design/frontend/default/Dacang/bs/salarysubordinatedetail.volt`
- 后续如果要做钉钉/企业微信/飞书专属样式，可继续复用这些 `bs` 页面，不需要另起平台。
