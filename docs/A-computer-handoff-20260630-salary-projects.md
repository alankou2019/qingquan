# A电脑同步说明：工资项目设置

最新提交内容：工资项目设置进入企业后台薪酬模块。

本次改动：
1. 企业后台新增 `工资项目设置` 页面。
2. 工资项目分成两类：
   - 通用项目：平台预置，企业勾选启用或取消。
   - 自定义项目：企业自行新增、编辑、停用。
3. 自定义项目支持维护名称、类别、项目类型、计算方式、关联模块、是否计入应发/扣款/实发、排序、状态、公式说明。
4. 薪酬首页增加 `工资项目设置` 入口。
5. 企业后台左侧薪酬菜单增加 `工资项目设置`。

涉及文件：
- app/code/Salary/controllers/Frontend/SalaryController.php
- app/code/Salary/Model/SalaryProjectModel.php
- app/code/Salary/Model/SalaryProjectTemplateModel.php
- app/design/frontend/default/Salary/salary/project.volt
- app/design/frontend/default/Common/index/left.volt

数据库：
- 本次没有新增数据表。
- 复用已有表：
  - salary_project_templates：平台通用工资项目模板。
  - salary_projects：企业已启用工资项目和自定义工资项目。

本地测试：
1. 工资项目设置页面可正常打开。
2. 通用项目勾选保存成功。
3. 通用项目取消后会停用并从已启用项目中隐藏。
4. 自定义项目新增成功。
5. 自定义项目停用成功。
6. PHP 语法检查通过。

A电脑继续开发前注意：
1. 先从 GitHub 同步最新版。
2. 不要手动复制覆盖项目文件夹。
3. 不要覆盖本地 `app/etc/config.xml`、数据库配置、上传文件、日志。
4. `prototypes/` 仍是本地原型目录，未提交。

下一步建议：
优先继续做 `Excel导入工资表`，导入时读取企业已启用的工资项目作为列匹配依据。
