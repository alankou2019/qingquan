# A电脑交接：薪酬员工导入与统一人员列表

日期：2026-07-24

## 本次功能

- 薪酬管理的“员工同步/导入”页面不再跳转到部门管理页面。
- “手工/Excel”区域直接提供“导入Excel”和“Excel模板下载”两个按钮。
- 钉钉同步完成后返回薪酬员工同步页面。
- 钉钉、企业微信、Excel等来源导入的员工统一显示在页面下方。
- 人员表格沿用旧部门管理页面的表格样式，显示ID、姓名、手机号、部门、岗位、管理员和负责人。
- 飞书入口继续保留为“暂未开放”。

## 兼容处理

- 未删除旧的 `department/index` 路由和页面，避免影响正在使用的绩效考核模块。
- `department/async` 和 `department/uploadexcel` 仅在携带 `from=salary` 时返回薪酬员工同步页面，旧入口保持原流程。
- 未修改营运后台、生产配置和生产数据库。

## 涉及文件

- `app/code/Salary/controllers/Frontend/SalaryController.php`
- `app/code/Dacang/controllers/Frontend/DepartmentController.php`
- `app/design/frontend/default/Salary/salary/employeesync.volt`

## 本地验证

- PHP 语法检查通过。
- 页面请求状态为 HTTP 200，无 Fatal error 或 Parse error。
- 浏览器验证地址：`http://127.0.0.1:8111/salary/employeesync`
- 已验证导入按钮、模板下载按钮、统一人员列表及人员字段正常显示。

## A电脑后续注意

- 拉取代码后保留旧部门管理页面，不要把本次改动理解为删除旧绩效模块的部门管理能力。
- 若继续开发企业微信或飞书同步，只需将同步结果写入企业员工表，薪酬页面会统一读取显示。
- 上生产前仍需用户人工测试并明确确认，不要直接部署到生产目录。
