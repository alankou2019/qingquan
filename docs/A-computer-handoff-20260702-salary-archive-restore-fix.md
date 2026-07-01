# A电脑交接：薪酬归档恢复链路修复

## 本次处理

- 修复工资表归档时，归档记录已生成但工资表主表没有同步变成已归档的问题。
- 修复归档记录恢复时，工资表应恢复到“核算中/已核算”状态，而不是审核驳回状态。
- 恢复归档记录时，会重新把归档快照数据写回工资核算表，并清空该工资表已发工资条，避免旧工资条继续误用。
- 补全员工手机端确认工资条日志的工资月份，方便后续按月追溯。

## 本地验证

- PHP 语法检查通过：
  - `app/code/Salary/controllers/Frontend/SalaryController.php`
  - `app/code/Salary/Model/PayrollArchiveModel.php`
  - `app/code/Salary/Model/PayrollPeriodModel.php`
  - `app/code/Dacang/controllers/Frontend/BsController.php`
- 本地开发环境测试地址：`http://127.0.0.1:8101`
- 已复测：
  - 工资表审核通过后归档，页面提示“工资表已归档”。
  - 工资表主表状态同步为 `archived`。
  - 操作日志新增 `payroll_archive`，并带工资月份。
  - 从归档记录恢复后，工资表状态回到 `calculated`，归档记录保留。
  - 恢复后对应工资条记录清空，避免使用旧发放记录。
  - 员工手机端确认工资条后，`mobile_payslip_confirm` 日志带工资月份。

## A电脑注意

1. 开发前先同步 GitHub 最新代码。
2. 不要提交生产配置、服务器密码、数据库密码、客户工资表、客户上传文件或日志。
3. 如果继续开发薪酬模块，建议优先从本地页面复测：
   - `/salary/payroll`
   - `/salary/archive`
   - `/salary/payslip`
   - `/salary/log`
   - `/bs/salary`
4. 上生产服务器前，必须先经过用户人工测试确认。

## 建议下一步

下一步建议做薪酬模块第二轮人工测试清单：按企业后台 HR 真实操作顺序，把“工资项目设置、初始工资表、工资核算、审核、发工资条、归档、员工查看确认、查询导出、操作日志”完整走一遍。
