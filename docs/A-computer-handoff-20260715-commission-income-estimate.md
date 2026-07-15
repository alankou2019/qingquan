# A电脑交接：提成月收入测算

## 本次范围

本次仅开发企业薪酬管理中的提成测算功能，没有修改营运后台。

1. 新增页面：`/salary/commissionestimate`
2. 选择员工后，按员工、岗位、部门、全公司默认规则匹配全部启用提成项目。
3. 每个项目支持填写低位、中位、高位业绩数据，并按现有简单提成、阶梯提成、超额阶梯提成规则计算三档提成。
4. 显示三档月收入及年收入：`月收入 = 月薪 + 月提成`。
5. 月薪优先读取员工档案中的月薪字段；旧库没有该字段时，使用初始工资表中启用的应发类项目合计，并排除提成模块项目。
6. 支持保存、查看、删除测算记录，所有查询和操作均校验 `company_id`。
7. 新增操作日志编码：`commission_estimate_save`、`commission_estimate_delete`。

## 数据库

- Salary 模块版本：`1.0.0.12`
- 新表：`salary_commission_estimates`
- 安装脚本：`app/code/Salary/sql/install-1.0.0.12.php`
- 测算明细和当时规则保存为快照，后续修改提成规则不会改变历史测算记录。

## 主要文件

- `app/code/Salary/Model/CommissionEstimateModel.php`
- `app/code/Salary/Model/CommissionPeriodModel.php`
- `app/code/Salary/controllers/Frontend/SalaryController.php`
- `app/design/frontend/default/Salary/salary/commissionestimate.volt`
- `app/code/Salary/Model/SalaryOperationLogModel.php`

## 已完成测试

1. PHP 7.3 语法检查通过。
2. 页面和 Volt 模板实际渲染通过，无 Fatal error。
3. 自动创建临时提成项目并验证员工规则匹配。
4. 验证低、中、高三档业绩为 100、200、300 时，10% 简单提成结果为 10.00、20.00、30.00。
5. 验证测算记录保存、查看和删除。
6. 临时测试项目及测算记录已从可见业务数据中删除。

## A电脑注意

1. 拉取代码后访问任一 Salary 页面，旧系统模块安装器会执行 `1.0.0.12` 数据库升级。
2. 营运后台仍由 A电脑维护，本次没有修改其企业开通逻辑。
3. 不要把生产配置、数据库密码、客户数据或服务器密钥提交到仓库。
