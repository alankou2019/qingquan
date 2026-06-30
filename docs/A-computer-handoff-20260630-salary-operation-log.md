# A电脑交接：薪酬操作日志

## 本次开发内容

- 企业后台新增“薪酬操作日志”页面：`/salary/log`。
- 薪酬首页和左侧菜单增加“薪酬操作日志”入口。
- 新增低敏日志表 `salary_operation_log`，模块版本升级到 `1.0.0.7`。
- 已接入日志的关键动作：
  - 工资项目设置：保存通用项目、保存自定义项目、停用项目。
  - 初始工资表：保存、导入。
  - 工资表核算：生成、保存、Excel导入、提交审核、审核处理。
  - 工资条：发放、导出查看确认结果。
  - 归档记录：归档、恢复到工资表核算。
  - 薪酬管理授权：保存审核人、保存薪酬查询授权。

## 敏感信息处理

- 日志不记录工资金额、工资条明细、审核意见正文。
- 日志只记录操作类型、工资月份、对象ID、人数或简短说明、操作人、IP、时间。
- 不涉及生产配置、服务器密码、数据库密码、客户数据提交。

## 本地验证

- `php -l app/code/Salary/Model/SalaryOperationLogModel.php`
- `php -l app/code/Salary/controllers/Frontend/SalaryController.php`
- `php -l app/code/Salary/sql/install-1.0.0.7.php`
- 本地访问：`http://127.0.0.1:8101/salary/log`
- 本地触发一次工资条确认结果导出后，日志页面能查到“导出工资条确认结果”。

## A电脑继续开发前注意

- 先拉取 GitHub 最新代码。
- 如A电脑也改过薪酬模块，请先对比以下文件再继续：
  - `app/code/Salary/controllers/Frontend/SalaryController.php`
  - `app/code/Salary/Model/SalaryOperationLogModel.php`
  - `app/design/frontend/default/Salary/salary/log.volt`
  - `app/design/frontend/default/Common/index/left.volt`
- 后续新增薪酬敏感动作时，优先调用 `addSalaryLog()` 记录低敏操作日志。
