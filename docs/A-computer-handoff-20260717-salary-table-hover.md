# 薪酬模块交接 - 表格行悬停（2026-07-17）

本次仅修改薪酬管理模块的公共界面样式，没有修改营运后台、生产配置或数据结构。

## 已完成

- 鼠标移入薪酬模块的数据行时，整行显示浅灰色背景，移开后恢复原来的颜色。
- 适用于工资项目、初始工资表、工资核算、工资条、归档、薪酬报表、提成项目、月收入测算、月提成核算及提成归档等使用 `salary_table` 或 `commission_table` 的表格。
- 保留平时的应发、应扣、提成项目等分类底色；仅在当前鼠标行悬停时统一高亮。
- 薪酬管理授权页的旧 `table_box` 已加入 `salary_table` 类，纳入相同效果。

## 涉及文件

- `skin/adminhtml/default/css/salary-table-hover.css`
- `app/design/frontend/default/Layouts/main.volt`
- `app/design/frontend/default/Salary/salary/auth.volt`

## 本地验证

- 本地 `/salary/project`、`/salary/commissionpayroll` 已确认加载公共样式文件。
- 未部署测试服务器或生产服务器；生产仍须用户人工测试和明确确认后再处理。
