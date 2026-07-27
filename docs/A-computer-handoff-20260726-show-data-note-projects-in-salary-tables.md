# A 电脑交接：初始工资表显示数据类和说明类项目

日期：2026-07-26

## 问题原因

- 自定义工资项目旧逻辑允许保存为停用状态。
- 初始工资表只读取启用项目，因此已存在的数据类自定义项目被过滤，没有显示。

## 本次修复

- 自定义项目一律按启用状态处理，只有通用项目可以停用。
- 旧数据中已保存为停用的自定义项目，读取时自动按启用处理。
- 数据类和说明类项目继续排在实发总额之后，显示在初始工资表和工资核算表中。
- 编辑自定义项目时不再显示状态下拉框，避免再次设置为停用。

## 本地验证

- 初始工资表已经显示数据类项目：应出勤天数、出勤天数。
- 编辑数据类自定义项目时，项目类别显示为数据类，页面没有状态/停用选择。

## 影响文件

- `app/code/Salary/Model/SalaryProjectModel.php`
- `app/code/Salary/controllers/Frontend/SalaryController.php`
- `app/design/frontend/default/Salary/salary/project.volt`
- `docs/salary-module-functional-flow-for-mini-program-20260723.md`
