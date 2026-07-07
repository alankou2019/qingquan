# A电脑交接：薪酬工资项目类别、属性和公式引用优化

## 本次处理

- 工资项目设置中，“类别”改为“项目类别”：
  - 应发类
  - 应扣类
  - 统计类
  - 数据类
  - 说明类
- 原“项目类型”改为“项目属性”：
  - 数字项
  - 文本项
  - 核算项
- 旧的 `fixed` 固定项目兼容为“数字项”，新保存项目使用 `number`。
- 新增 `text_value` 字段，用于保存文本项、说明类数据：
  - `employee_salary_structure_values.text_value`
  - `payroll_item_values.text_value`
- 初始工资表、工资表核算、Excel 导入、工资条详情已适配文本项。
- 核算公式旁边新增“可引用项目”按钮：
  - 只展示已启用的数字项/核算项。
  - 不展示文本项。
  - 编辑某个项目时，不展示当前项目本身。
  - 点击项目名称会插入到核算公式输入框。

## 验证结果

- PHP 语法检查通过：
  - `SalaryController.php`
  - `SalaryProjectModel.php`
  - `EmployeeSalaryStructureModel.php`
  - `PayrollPeriodModel.php`
  - `SalaryPayrollImportModel.php`
  - `PayrollSlipModel.php`
- 本地访问 `/salary/project` 验证通过：
  - 页面显示“项目类别 / 项目属性”。
  - 页面显示“数字项 / 文本项 / 核算项”。
  - 核算公式区域显示可引用项目按钮。
  - 点击项目按钮可插入项目名称。
- 本地访问 `/salary/payroll` 验证通过：
  - 工资表核算页面无 Volt 模板错误。

## 注意事项

1. 本次只改薪酬管理模块，不涉及营运后台。
2. 公式当前按“工资项目名称”引用，例如：`基本工资 + 岗位工资 - 缺勤扣款`。
3. 被引用项目建议排序在核算项之前，否则后续需要继续优化公式依赖顺序。
4. 文本项、说明类不参与应发、应扣、实发合计。
5. 上生产前需先执行薪酬模块 SQL 升级脚本 `install-1.0.0.8.php`，并经过人工测试确认。
6. 不要提交生产配置、服务器密码、数据库密码、客户工资数据或客户上传文件。
