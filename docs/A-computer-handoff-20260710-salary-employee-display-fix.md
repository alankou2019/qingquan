# A电脑交接说明：薪酬模块员工显示修复

日期：2026-07-10

本次由 B 电脑完成，范围只涉及薪酬管理模块，不涉及营运后台，不涉及生产服务器。

## 问题

本地测试时，员工名单已经导入到 `company_user`，但薪酬相关页面存在员工不显示或部门显示不准确的情况。

原因是旧系统中员工 `department_id` 可能保存的是：

1. 部门表自增 `id`；
2. 钉钉部门 `dingding_id`。

薪酬模块原来只按 `dingding_id` 关联部门，遇到手工/Excel 导入或旧数据时容易显示异常。

## 本次修复

1. 薪酬模块员工读取兼容两种部门关联：
   - 优先按部门表 `id` 匹配；
   - 匹配不到时按 `dingding_id` 兜底。
2. 影响范围：
   - 员工同步/导入页；
   - 薪酬管理授权页；
   - 初始工资表；
   - 工资表导入员工匹配；
   - 工资表审核人显示。
3. 员工同步/导入页新增“当前员工”列表，导入后可直接看到员工是否进入薪酬模块。

## 已验证

1. PHP 语法检查通过：
   - `app/code/Salary/controllers/Frontend/SalaryController.php`
   - `app/code/Salary/Model/EmployeeSalaryStructureModel.php`
   - `app/code/Salary/Model/SalaryPayrollImportModel.php`
   - `app/code/Salary/Model/SalaryPayrollAuditModel.php`
2. 本地页面访问通过：
   - `http://127.0.0.1:8101/salary/employeesync`
   - `http://127.0.0.1:8101/salary/project`
3. 本地测试确认：
   - 员工同步/导入页能看到已导入员工；
   - 工资项目设置页的初始工资表能看到已导入员工。

## A电脑注意

1. 这次没有修改营运后台。
2. 如果 A 电脑后续要处理旧系统“人员管理”页面，可参考本次部门关联兼容逻辑。
3. 上生产前仍需用户人工测试确认，不要直接改生产服务器。
