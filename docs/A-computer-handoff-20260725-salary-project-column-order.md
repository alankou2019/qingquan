# A 电脑交接：工资项目列拖动排序

## 本次功能

- 初始工资表的非固定工资项目支持鼠标拖动调整列顺序。
- 月工资核算表的非固定工资项目支持鼠标拖动调整列顺序。
- 拖动时整列移动，员工、手机号、部门、操作列不参与排序。
- 应发总额、应扣总额、实发总额为系统固定列，不参与排序。
- 只允许在同一项目类别内调整，例如应发类只能在应发类内部移动。
- 排序通过 Ajax 自动保存，不刷新页面；保存失败会恢复原顺序并显示错误。

## 数据规则

- 初始工资表顺序写入当前企业 `salary_projects.sort_order`，作为以后生成工资核算表的默认顺序。
- 月工资核算表顺序只更新当前月份 `payroll_periods.project_snapshot`，不反向影响初始工资表。
- 已归档工资表继续使用归档时的项目快照，本次修改不会改变历史归档记录。
- 所有更新均带 `company_id` 条件，保持企业数据隔离。
- 新增操作日志类型：
  - `project_order_save`
  - `payroll_project_order_save`

## 新增接口

- `POST /salary/saveprojectorder`
  - 参数：`direction`、`project_ids`
- `POST /salary/savepayrollprojectorder`
  - 参数：`id`、`direction`、`project_ids`

## 主要文件

- `app/code/Salary/Model/SalaryProjectModel.php`
- `app/code/Salary/Model/PayrollEmployeeRowModel.php`
- `app/code/Salary/Model/SalaryOperationLogModel.php`
- `app/code/Salary/controllers/Frontend/SalaryController.php`
- `app/design/frontend/default/Salary/salary/project.volt`
- `app/design/frontend/default/Salary/salary/payroll.volt`

## 已验证

- PHP 语法检查通过。
- 初始工资表与月工资核算表页面均能正常渲染，无 Fatal、Parse 或模板错误。
- 初始工资项目交换后刷新保持，随后已恢复测试前顺序。
- 2026-07 月工资核算表项目交换后刷新保持，随后已恢复测试前顺序。
- 缺少项目的非法排序请求会被后端拒绝。
- 本次未修改生产服务器、生产数据库或 Nginx。

## A 电脑注意

- 合并前先拉取 `master` 最新代码。
- 若 A 电脑同期修改上述工资项目或工资核算文件，请保留本次两个排序接口、项目快照隔离和操作日志。
- 人工测试重点：同类别拖动、跨类别不能拖动、刷新后顺序保持、重新生成新月份继承初始表顺序、历史归档顺序不改变。
