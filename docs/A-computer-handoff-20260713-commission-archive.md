# B 电脑交接：提成归档记录

提交前请先拉取远端最新 `master`，本次只改薪酬管理中的提成模块，未改营运后台、生产配置或生产服务器。

## 本次完成

1. 新增提成归档记录页面：`/salary/commissionarchive`
   - 可按归档月份、部门、员工查询。
   - 每条归档记录可执行“查看、恢复、删除”。
2. 月提成核算页面：`/salary/commissionpayroll`
   - 已保存的核算表可归档。
   - 归档后提成表变为只读，不能直接修改。
3. 恢复规则
   - 恢复后同一月份的原始提成数据回到“核算中”，可继续编辑。
   - 当前归档记录被移除，符合产品要求“恢复后删除归档记录”。
4. 删除规则
   - 页面删除后立即不再显示。
   - 归档快照及已删除的提成表在开发逻辑中保留 180 天，后续访问归档页时自动清理过期数据。
5. 操作日志
   - `commission_archive`
   - `commission_restore`
   - `commission_archive_delete`

## 数据库升级

- Salary 模块版本：`1.0.0.11`
- 新表：`salary_commission_archives`
- 表内保存提成归档的员工、项目完成量、提成额、规则快照和汇总金额。

## 涉及文件

- `app/code/Salary/Model/CommissionArchiveModel.php`
- `app/code/Salary/Model/CommissionPeriodModel.php`
- `app/code/Salary/controllers/Frontend/SalaryController.php`
- `app/code/Salary/Model/SalaryOperationLogModel.php`
- `app/code/Salary/sql/install-1.0.0.11.php`
- `app/design/frontend/default/Salary/salary/commissionpayroll.volt`
- `app/design/frontend/default/Salary/salary/commissionarchive.volt`
- `app/design/frontend/default/Salary/salary/commissionarchiveview.volt`

## 已完成本机验证

- PHP 7.3 语法检查通过。
- 开发数据库升级后已生成提成归档表。
- 临时测试数据完整走通：生成核算表 -> 归档 -> 恢复为核算中 -> 再归档 -> 删除并进入六个月保留状态。
- 提成核算、归档列表、归档详情页面均已测试，无 Fatal error 或 Parse error。

## 后续边界

- 本批不包含提成查阅通知发布、员工端当月/历史提成查询、业绩 Excel 导入。
- 营运后台仍由 A 电脑维护；本次继续使用既有 `commission` 功能授权编码。
