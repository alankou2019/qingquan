# A 电脑交接：薪酬模块表格标题居中

## 本次调整

- 薪酬管理模块所有表格的标题栏文字统一水平居中。
- 覆盖工资表、工资项目、归档、工资条、报表、员工同步、提成核算和提成测算等页面。
- 只调整表头 `th`，表格数据单元格继续使用原有对齐方式。
- 使用薪酬专用公共样式统一控制，没有逐页重复增加样式。

## 修改文件

- `skin/adminhtml/default/css/salary-table-hover.css`
- `app/design/frontend/default/Layouts/main.volt`

## 缓存处理

- 公共样式版本由 `v=20260717` 更新为 `v=20260725`，确保浏览器刷新后加载新样式。

## 已验证页面

- `/salary/project`
- `/salary/payroll?payroll_month=2026-07`
- `/salary/employeesync`
- `/salary/commission`
- `/salary/commissionestimate`
- `/salary/archive`

上述页面共检查 92 个表头，全部为居中显示，无 Fatal、Parse 或模板错误。

## 注意事项

- 本次只修改开发代码和本地测试环境。
- 未修改生产服务器、生产数据库或 Nginx。
