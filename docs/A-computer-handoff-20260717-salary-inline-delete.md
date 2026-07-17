# 薪酬模块交接 - 表格内删除（2026-07-17）

本次仅修改薪酬管理模块，没有修改营运后台、企业资料或生产配置。

## 已完成

- 提成项目设置、月收入测算记录、月提成核算员工行、提成归档记录支持页面内删除。
- 工资项目设置中的通用项目、企业工资项目、初始工资表员工行支持页面内删除。
- 删除前仍要求人工确认；后端继续校验企业隔离、记录薪酬操作日志。
- 删除成功后仅移除当前行，保留当前月份、筛选条件和页面位置；月提成核算同时更新参与人数、匹配人数和提成合计。
- 普通表单请求仍保留原来的提示页行为，兼容未加载脚本的旧环境。

## 涉及文件

- `app/code/Salary/controllers/Frontend/SalaryController.php`
- `app/design/frontend/default/Salary/salary/commission.volt`
- `app/design/frontend/default/Salary/salary/commissionestimate.volt`
- `app/design/frontend/default/Salary/salary/commissionpayroll.volt`
- `app/design/frontend/default/Salary/salary/commissionarchive.volt`
- `app/design/frontend/default/Salary/salary/project.volt`
- `skin/adminhtml/default/js/salary-inline-delete.js`

## 本地验证

- PHP 7.3 语法检查通过。
- 临时月份自动测试通过：提成核算生成、员工项目保存、AJAX 删除、汇总回写、归档锁定、初始工资表员工 AJAX 移出和恢复。
- 本地页面验证：`/salary/commissionpayroll`、`/salary/project` 正常渲染，未发现浏览器脚本错误。

## 给 A 电脑的注意事项

- 此变更只依赖新增的静态脚本 `salary-inline-delete.js`；合并时请一并保留。
- 本地的 `tmp/` 和 `prototypes/` 为测试/原型目录，不提交。
- 尚未部署测试服务器或生产服务器，生产仍需用户人工测试和明确确认后再进行。
