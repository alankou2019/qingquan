# A电脑交接说明：月提成核算基础

## 本次提交范围

本次由 B 电脑在薪酬管理模块内继续开发提成模块，不涉及营运后台，也没有修改生产服务器配置。

已完成：

1. 提成项目设置支持可计算规则：
   - 简单提成：按比例、按固定金额。
   - 阶梯提成：按命中档位整体计算。
   - 超额阶梯提成：按每档超额部分累计计算。
2. 新增月提成核算基础页：
   - 路径：`/salary/commissionpayroll`
   - 可按月份生成本月提成核算表。
   - 员工来源复用薪酬模块的人事档案和部门解析逻辑。
   - 按员工/部门/岗位/全公司范围匹配启用的提成项目。
   - HR 可录入每个命中项目的完成量，保存后自动计算项目提成和员工月提成合计。
3. 新增提成核算数据表：
   - `salary_commission_periods`
   - `salary_commission_rows`
   - `salary_commission_item_values`
4. 操作日志新增：
   - `commission_generate`
   - `commission_save`

## 主要文件

- `app/code/Salary/Model/CommissionProjectModel.php`
- `app/code/Salary/Model/CommissionPeriodModel.php`
- `app/code/Salary/Model/SalaryEmployeeDepartmentModel.php`
- `app/code/Salary/Model/SalaryOperationLogModel.php`
- `app/code/Salary/controllers/Frontend/SalaryController.php`
- `app/code/Salary/etc/config.xml`
- `app/code/Salary/sql/install-1.0.0.10.php`
- `app/design/frontend/default/Salary/salary/commission.volt`
- `app/design/frontend/default/Salary/salary/commissionpayroll.volt`

## 本地验证

已在 B 电脑本地开发环境验证：

1. PHP 7.3 语法检查通过：
   - `CommissionProjectModel.php`
   - `CommissionPeriodModel.php`
   - `SalaryController.php`
   - `install-1.0.0.10.php`
2. 临时本地服务访问通过：
   - `/salary/commission` 返回 200，无 Fatal error。
   - `/salary/commissionpayroll` 返回 200，无 Fatal error。
3. 开发库表结构确认已生成：
   - `scsx_salary_commission_projects`
   - `scsx_salary_commission_periods`
   - `scsx_salary_commission_rows`
   - `scsx_salary_commission_item_values`

## A电脑注意

1. 请先 `git pull` 同步最新代码，再继续开发。
2. 营运后台仍由 A 电脑统一开发；B 电脑本次只改薪酬/提成模块。
3. 本次提成核算还是第一阶段基础链路，暂未做：
   - 业绩数据 Excel 导入。
   - 提成归档记录。
   - 发布提成查阅给员工。
   - 员工端历史提成查询。
4. 岗位匹配目前优先读取员工表中已存在的岗位字段；如果旧库没有岗位字段，岗位范围项目暂时不会自动命中，需要后续结合员工档案岗位字段统一处理。
5. 上生产前必须先经过人工测试和用户明确确认，不要直接覆盖生产。
