# A电脑交接：薪酬统计报表

## 本次开发内容

- 企业后台新增“薪酬统计报表”页面：`/salary/report`。
- 薪酬首页和左侧菜单增加“薪酬统计报表”入口。
- 支持按以下条件查询：
  - 工资月份
  - 部门
  - 员工姓名或手机号
- 页面展示汇总数据：
  - 记录数
  - 应发合计
  - 扣款合计
  - 实发合计
- 页面展示员工明细：
  - 工资月份、员工姓名、手机号、部门、工资表状态、来源、应发、扣款、实发。
- 支持导出当前筛选范围内的薪酬报表。
- 薪酬报表导出会写入薪酬操作日志，只记录导出条数，不记录金额明细到日志。

## 权限控制

- 企业超级管理员可查看和导出本企业全部薪酬报表。
- 非超级管理员按“薪酬管理授权”控制：
  - 可查看授权部门或指定员工的薪酬数据。
  - 只有授权时勾选“允许导出薪酬数据”的账号才能导出。
  - 没有授权范围时默认不展示薪酬明细。
- 为兼容旧系统，授权员工匹配逻辑先按当前登录ID匹配员工表，找不到再按手机号匹配。

## 本地验证

- `php -l app/code/Salary/Model/SalaryReportModel.php`
- `php -l app/code/Salary/controllers/Frontend/SalaryController.php`
- `php -l app/code/Salary/Model/SalaryOperationLogModel.php`
- 本地访问：`http://127.0.0.1:8101/salary/report`
- 本地导出：`http://127.0.0.1:8101/salary/reportexport`
- 导出后在 `http://127.0.0.1:8101/salary/log?action_code=salary_report_export` 可看到日志。

## A电脑继续开发前注意

- 先拉取 GitHub 最新代码。
- 如A电脑也改过薪酬模块，请先对比以下文件：
  - `app/code/Salary/controllers/Frontend/SalaryController.php`
  - `app/code/Salary/Model/SalaryReportModel.php`
  - `app/code/Salary/Model/SalaryOperationLogModel.php`
  - `app/design/frontend/default/Salary/salary/report.volt`
  - `app/design/frontend/default/Common/index/left.volt`
- 后续如果要增加更复杂的部门汇总、年度报表、图表分析，可继续复用 `SalaryReportModel`。
