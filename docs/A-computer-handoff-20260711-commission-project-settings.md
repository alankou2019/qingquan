# A电脑交接说明：提成项目设置第一批

日期：2026-07-11

本次由 B 电脑完成，只涉及既有 `Salary` 薪酬模块，不涉及营运后台，不涉及生产服务器。

## 本次完成范围

提成模块第一批正式功能已落入旧版系统，入口为：

- 企业薪酬后台：`/salary/commission`
- 前置条件：企业已开通薪酬管理并启用 `commission` 功能项。

已实现“提成项目设置”，每个项目可维护：

1. 项目名称；
2. 业绩口径：销售额、产品数、毛利、自定义；
3. 提成方式：简单提成、阶梯提成、超额阶梯提成；
4. 起提条件、优先级、规则明细；
5. 适用范围：全公司默认、指定员工、指定部门、指定岗位；
6. 启用/停用、编辑、软删除；
7. 同一企业内项目名称去重。

## 数据与安全

1. 新增升级脚本：`app/code/Salary/sql/install-1.0.0.9.php`。
2. 新表：`salary_commission_projects`，实际表名会按现有数据库前缀生成。
3. 所有读取、编辑、删除均按 `company_id` 过滤；删除为软删除。
4. 已写入薪酬操作日志：保存提成项目规则、删除提成项目规则。
5. 适用范围下拉没有员工或部门时，页面会提示先维护对应资料，避免保存空规则。

## 关键文件

- `app/code/Salary/Model/CommissionProjectModel.php`
- `app/code/Salary/controllers/Frontend/SalaryController.php`
- `app/design/frontend/default/Salary/salary/commission.volt`
- `app/code/Salary/Model/SalaryOperationLogModel.php`
- `app/code/Salary/etc/config.xml`
- `app/code/Salary/sql/install-1.0.0.9.php`

## 本地验证

1. PHP 7.3 语法检查通过。
2. 本机开发数据库升级后，`salary_commission_projects` 表已生成，Salary 模块版本为 `1.0.0.9`。
3. 已使用本地测试登录态访问 `/salary/commission`，页面正常打开，无 Fatal error。

## 暂未实现

本次不包含以下后续批次，避免与既有薪酬核算流程耦合过早：

1. 月提成核算表及业绩数据导入；
2. 提成项目按人员/岗位自动匹配和提成金额计算；
3. 提成归档、恢复、发布查阅；
4. 员工手机端当月提成和历史提成查看；
5. 按员工进行低/中/高三档收入测算。

## 协作提示

营运后台仍由 A 电脑统一维护。本次仅消费既有企业功能开通项 `commission`，未修改营运后台开通逻辑；A 电脑如后续调整该开通项名称或编码，请同步确认 `SalaryController::getSalaryFeatures()` 中的功能编码仍为 `commission`。
