# A电脑交接说明：薪酬员工部门按来源关联

日期：2026-07-10

本次由 B 电脑完成，范围只涉及薪酬管理模块，不涉及营运后台，不涉及生产服务器。

## 背景

薪酬模块上一版为了让导入员工能显示，临时兼容了部门表 `id` 和 `dingding_id` 两种关联方式。用户指出这样不严谨，部门关联应该按员工信息导入来源判断：

1. 钉钉同步导入：使用钉钉部门 `dingding_id`。
2. 人工录入 / Excel 导入：使用部门表自增 `id`。
3. 企业微信同步导入：使用平台部门映射表。
4. 飞书同步导入：后续同企业微信，使用平台部门映射表。

## 本次修复

1. 新增薪酬模块统一员工部门解析类：
   - `app/code/Salary/Model/SalaryEmployeeDepartmentModel.php`
2. 薪酬模块员工部门读取统一走该解析类，不再在每个页面单独写部门关联 SQL。
3. 当前规则：
   - 企业平台为 `dingding`：按 `company_user.department_id = company_department.dingding_id`。
   - 企业平台为 `wecom` / `feishu` 且平台映射表存在：通过 `platform_user_identity`、`platform_department_identity` 映射到系统部门。
   - 人工/Excel或平台映射表不存在：按 `company_user.department_id = company_department.id`。
4. 已替换以下薪酬模块位置：
   - 员工同步/导入页员工列表；
   - 薪酬管理授权页员工列表；
   - 初始工资表员工列表；
   - 工资表导入员工匹配；
   - 工资表审核人显示。

## 本地测试

1. PHP 语法检查通过：
   - `SalaryEmployeeDepartmentModel.php`
   - `EmployeeSalaryStructureModel.php`
   - `SalaryPayrollImportModel.php`
   - `SalaryPayrollAuditModel.php`
   - `SalaryController.php`
2. 本地页面验证通过：
   - `http://127.0.0.1:8101/salary/employeesync`
   - `http://127.0.0.1:8101/salary/project`
3. 本地测试数据已按“人工/Excel=系统部门 id”规则修正，T2/T3 员工部门显示正常。

## 后续建议

后续如果继续完善员工导入，建议给员工或导入批次增加明确来源字段，例如：

- `dingding`
- `wecom`
- `feishu`
- `excel`
- `manual`

这样同一家企业同时存在平台同步员工和人工补录员工时，可以按员工级来源精确判断，避免只按企业平台判断。
