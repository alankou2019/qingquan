# A 电脑交接：移除工资项目“统计类”

日期：2026-07-26

## 本次范围

- 工资项目类别仅保留：应发类、应扣类、数据类、说明类。
- 新增和编辑工资项目时不再显示“统计类”。
- 应发总额、应扣总额、实发总额继续由系统计算，并显示为“系统汇总”。
- Excel 首次导入时，天数、工时、基数、余额等辅助数据归入“数据类”。

## 旧数据兼容

- 不执行破坏性数据库迁移。
- 过去保存为 `statistic` 的项目在读取和编辑时按 `data`（数据类）处理。
- 旧项目再次保存后会写入 `data`。
- 已归档工资表的项目快照和金额不修改。

## 影响文件

- `app/code/Salary/Model/SalaryProjectModel.php`
- `app/code/Salary/Model/SalaryPayrollImportModel.php`
- `app/code/Salary/Model/PayrollEmployeeRowModel.php`
- `app/code/Salary/Model/EmployeeSalaryStructureModel.php`
- `app/code/Salary/controllers/Frontend/SalaryController.php`
- `app/code/Salary/controllers/Adminhtml/SalaryprojectController.php`
- `app/design/frontend/default/Salary/salary/project.volt`
- `docs/salary-module-functional-flow-for-mini-program-20260723.md`

## 建议同步后检查

1. 新增工资项目的“项目类别”中没有“统计类”。
2. 编辑历史统计类项目时显示为“数据类”并可以正常保存。
3. 三个固定总额仍正常计算和显示。
4. 打开历史归档工资表，确认金额和项目快照保持不变。
