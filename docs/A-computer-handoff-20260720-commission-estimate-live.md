# A 电脑交接：提成测算即时计算

## 本次功能

- 月收入测算页面填写低位、中位、高位业绩值后，按现有提成规则自动计算，不再需要点击“测算”。
- 同步更新各提成项目金额、三档提成合计、三档月收入、年收入和横向柱状条。
- “保存测算”仍按服务端规则重新计算后保存，避免只信任浏览器显示值。

## 主要文件

- `app/code/Salary/controllers/Frontend/SalaryController.php`
- `app/code/Salary/Model/CommissionEstimateModel.php`
- `app/code/Salary/Model/SalaryEmployeeDepartmentModel.php`
- `app/design/frontend/default/Salary/salary/commissionestimate.volt`
- `skin/adminhtml/default/js/commission-estimate-live.js`

## 接口与安全

- 新增 POST 接口：`/salary/commissionestimatecalculate`。
- 企业 ID 继续从当前登录会话读取，不接受前端传入企业 ID。
- 员工和提成项目仍由 `CommissionEstimateModel::calculateEstimate()` 按当前企业、启用状态和适用范围加载。
- 即时计算不保存测算记录；只有用户点击“保存测算”才写入记录。

## 性能处理

- 本地 MariaDB 的 `SHOW COLUMNS`、`SHOW TABLES` 元数据查询明显偏慢，本次仅在 Salary 模块内增加一小时文件缓存，避免一次测算重复读取表结构。
- 旧框架公共模型仍会执行 `DESCRIBE scsx_module`、`DESCRIBE scsx_config`、`DESCRIBE scsx_company_module_auth`，本地首次页面打开仍可能较慢。B 电脑未修改 `Bootstrap.php` 或 Core 公共层，请 A 电脑后续统一评估公共元数据缓存。

## 已完成测试

- PHP 7.3 语法检查通过，JavaScript 语法检查通过，`git diff --check` 通过。
- 浏览器实测：将测试项目低位业绩填为 `123456`，页面无需刷新自动显示提成 `12345.60`；提成合计、月收入、年收入和柱状条同步更新。
- 本地测试页：`http://127.0.0.1:8110/salary/commissionestimate`。

## 同步与上线提醒

- 本次只改薪酬管理模块，没有修改营运后台。
- `prototypes/`、`tmp/`、本地数据库配置和测试凭证未提交。
- 上生产服务器前必须由用户完成完整人工测试并明确确认，不能直接部署生产。
