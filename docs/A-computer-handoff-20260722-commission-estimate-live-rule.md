# A电脑交接：提成测算即时计算与规则编辑

## 本次范围

本次只修改企业薪酬模块的“月收入测算”，不修改营运后台、数据库结构和生产服务器。

## 已完成内容

1. 低位、中位、高位业绩数据输入后，按当前提成规则自动请求服务端并即时更新三档提成、月收入和年收入。
2. 测算表中的每个提成项目新增“编辑”规则入口，可编辑简单提成的比例或固定单价，以及阶梯提成的阶梯区间。
3. 点击“保存并重算”后，同步更新该企业的提成项目规则，并立即使用新规则重新测算当前员工。
4. 规则保存前进行企业归属、项目状态、数值和阶梯连续性校验；服务端仍是唯一正式计算口径。
5. 保存规则会写入薪酬操作日志，便于后续追溯。

## 主要文件

- `app/code/Salary/Model/CommissionEstimateModel.php`
- `app/code/Salary/Model/CommissionProjectModel.php`
- `app/code/Salary/controllers/Frontend/SalaryController.php`
- `app/design/frontend/default/Salary/salary/commissionestimate.volt`
- `skin/adminhtml/default/js/commission-estimate-live.js`

## B电脑已验证

- PHP 7.3语法检查通过。
- JavaScript语法检查通过。
- `/salary/commissionestimate` 页面正常渲染，无 Fatal error 和页面脚本错误。
- 测试项目按 10% 规则输入业绩 `1000.00`，低位提成即时显示 `100.00`。
- 页面显示提成规则摘要、“编辑”和“保存并重算”操作。
- 本次验证只修改浏览器中的测算输入，没有保存测算记录，也没有修改生产数据。

## A电脑操作要求

1. 同步后打开 `/salary/commissionestimate`，使用 `Ctrl+F5` 刷新前端脚本。
2. 分别测试简单比例、固定单价、阶梯提成和超额阶梯提成。
3. 验证规则编辑保存后，提成项目设置页同步显示新规则，当前测算结果立即更新。
4. 验证非法区间、负数和空值会被拦截，且不同企业之间不能读取或修改对方规则。
5. 用户完成手工测试并明确批准前，不得部署或覆盖生产环境。
