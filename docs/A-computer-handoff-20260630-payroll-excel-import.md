# A电脑交接说明：工资表 Excel 导入

提交日期：2026-06-30

## 本次完成内容

企业后台「薪酬管理 > 工资表核算」增加工资表 Excel 导入能力：

- 支持下载工资表 Excel 模板。
- 支持选择工资月份后上传 Excel。
- 员工匹配规则：先按姓名匹配；企业内同名时再用手机号区分。
- 匹配失败、重名手机号异常、工资项目不一致、金额格式错误等数据进入异常列表，不会落入工资表。
- 企业未设置工资项目时，首次导入会按 Excel 表头识别工资项目，并先显示确认页；HR 确认后再生成工资项目并导入工资表。
- 导入成功后生成「已核算」状态工资表，可继续走提交审核、发工资条并归档流程。

## 重点文件

- `app/code/Salary/controllers/Frontend/SalaryController.php`
- `app/code/Salary/Model/SalaryPayrollImportModel.php`
- `app/design/frontend/default/Salary/salary/payroll.volt`
- `app/design/frontend/default/Salary/salary/importconfirm.volt`
- `app/design/frontend/default/Salary/salary/importresult.volt`

## 本地测试情况

- PHP 语法检查通过：
  - `SalaryController.php`
  - `SalaryPayrollImportModel.php`
- 本地浏览器测试通过：
  - 工资表核算页面可打开。
  - 模板下载返回 Excel 文件。
  - 正常 Excel 可导入为已核算工资表。
  - 不存在员工会显示异常列表，异常数据未落库。
- 2026-06-30 补充：工资表模板下载按钮已放到 Excel 导入按钮旁边，模板下载改为真实 `.xls` 文件，表头包含姓名、手机号和当前企业已启用工资项目。
- 2026-06-30 补充：下载按钮增加新窗口和 download 标记，模板下载响应头补充为文件传输格式，避免部分浏览器点击后没有明显下载提示。

## A电脑继续开发前注意

1. 先同步 GitHub 最新代码，不要手动复制覆盖文件夹。
2. 进入「薪酬管理 > 工资表核算」测试 Excel 导入。
3. 测试数据建议使用脱敏员工和脱敏工资金额。
4. 不要提交生产配置、数据库密码、服务器密码、客户工资表或客户上传文件。
5. 下一步如果继续开发，建议先确认功能说明，再开始写代码。
