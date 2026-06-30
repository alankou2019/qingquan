# A电脑交接说明：初始工资表、工资表核算改版、归档记录

提交日期：2026-06-30

## 本次完成内容

1. 工资项目设置增加「初始工资表」
   - 支持少人企业直接在页面逐个员工录入工资数据。
   - 支持下载初始工资表模板。
   - 支持 Excel 导入初始工资表。
   - 企业没有工资项目时，Excel 导入会按表头自动生成工资项目。
   - 导入后仍可在页面继续修改员工工资数据。

2. 工资项目增加「数字/公式」能力
   - 数字项目可手工录入或 Excel 导入。
   - 公式项目按公式计算，页面中只读。
   - 金额保存和计算统一保留小数点后 2 位，四舍五入。

3. 工资表核算改版
   - 不再以月份列表作为主界面。
   - 按工资月份查看一张类似 Excel 的核算表。
   - 可从初始工资表生成本月工资表。
   - 可编辑本月工资表数字项目。
   - 可保存核算表、提交审核。

4. 审核后拆分操作
   - 发工资条不再自动归档。
   - 归档单独处理。

5. 工资表归档记录
   - 新增归档记录页面。
   - 归档时保存一份独立归档快照。
   - 恢复到工资表核算后，原归档记录仍保留。
   - 归档记录中保留发工资条和恢复入口。

## 重点文件

- `app/code/Salary/controllers/Frontend/SalaryController.php`
- `app/code/Salary/Model/EmployeeSalaryStructureModel.php`
- `app/code/Salary/Model/PayrollPeriodModel.php`
- `app/code/Salary/Model/PayrollArchiveModel.php`
- `app/code/Salary/Model/PayrollEmployeeRowModel.php`
- `app/code/Salary/Model/SalaryPayrollImportModel.php`
- `app/design/frontend/default/Salary/salary/project.volt`
- `app/design/frontend/default/Salary/salary/payroll.volt`
- `app/design/frontend/default/Salary/salary/archive.volt`
- `app/code/Salary/sql/install-1.0.0.6.php`
- `app/code/Salary/etc/config.xml`

## 本地测试情况

- PHP 语法检查通过。
- 工资项目设置页、工资表核算页、归档记录页均可打开。
- 初始工资表保存测试通过：`6100.126` 保存为 `6100.13`。
- 可从初始工资表生成 `2026-08` 月工资表。
- 归档后会进入归档记录。
- 恢复后工资表回到核算页，归档记录仍保留。

## A电脑继续开发前注意

1. 先同步 GitHub 最新代码。
2. 不要提交生产配置、密码、客户工资表或客户上传文件。
3. 本版归档记录已保留快照；后续如要做到「从归档快照完全独立发工资条并查看详情」，建议继续增强工资条与归档快照的关联。
