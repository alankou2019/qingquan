# A电脑交接：薪酬权限与菜单显隐细化

## 本次开发内容

- 企业后台薪酬首页入口显隐细化：
  - “工资表归档记录”和“薪酬统计报表”跟随“工资表核算”子功能开通状态。
  - “工资项目设置”和“薪酬操作日志”在薪酬模块开通后保留。
- 企业后台左侧菜单显隐细化：
  - 未开通“工资表核算”时，不显示“工资表核算、工资表归档记录、薪酬统计报表”。
  - 未开通“工资条发放”时，不显示“工资条发放”。
  - 提成核算、绩效工资核算继续按各自子功能开通状态显示。
- 手机端首页补齐 `hasSalaryMobile` 变量，由“薪酬管理 + 工资条发放”共同控制员工薪酬查询入口。
- 手机端薪酬未开通提示改为更清楚的员工提示。
- 手机端薪酬敏感动作补充日志：
  - `mobile_payslip_view`：手机端查看本人薪酬明细。
  - `mobile_payslip_confirm`：手机端确认本人工资条。

## 本地验证

- `php -l app/code/Dacang/controllers/Frontend/BsController.php`
- `php -l app/code/Salary/controllers/Frontend/SalaryController.php`
- `php -l app/code/Salary/Model/SalaryOperationLogModel.php`
- 本地验证页面：
  - `http://127.0.0.1:8101/salary/index`
  - `http://127.0.0.1:8101/index/left?bigClass=4`
  - `http://127.0.0.1:8101/bs/salary`
- 本地打开本人薪酬详情后，可在薪酬操作日志中查到“手机端查看本人薪酬”。

## 测试限制

- 本地访问 `http://127.0.0.1:8101/bs/newindex` 时，脱敏开发库缺少旧绩效表 `scsx_report_item`，会导致旧KPI首页统计报错。
- 该报错来自旧绩效首页本地测试数据缺失，不是本次薪酬菜单代码引起。
- 生产库和完整开发库有旧绩效表时，该页面会正常使用新增的 `hasSalaryMobile` 显隐变量。

## A电脑继续开发前注意

- 先拉取 GitHub 最新代码。
- 如A电脑也改过薪酬入口、手机端首页或日志模型，请先对比：
  - `app/code/Dacang/controllers/Frontend/BsController.php`
  - `app/code/Salary/controllers/Frontend/SalaryController.php`
  - `app/code/Salary/Model/SalaryOperationLogModel.php`
  - `app/design/frontend/default/Common/index/left.volt`
