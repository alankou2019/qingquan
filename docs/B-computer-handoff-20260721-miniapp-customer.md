# B电脑交接：旧运营后台接入小程序客户

## 交接范围

本次只供测试服务器验证，禁止部署生产环境。

- GitHub 仓库：alankou2019/qingquan
- 开发分支：codex/miniapp-customer
- 功能代码基线提交：cd2408d
- Salary 模块版本：1.0.0.14
- 旧运营后台继续作为企业主数据和客户开通入口。
- 原钉钉、企业微信业务入口和保存流程保持不变。

## 完成功能

1. 公司管理增加“添加小程序客户”和“小程序注册申请”。
2. 运营人员可以手动填写企业资料、总管理员、人数上限、到期时间和开放模块。
3. 企业可以通过公开接口提交注册申请，审核前不创建企业和账号。
4. 审核开通后自动生成：
   - 企业字母编码。
   - 总管理员工号“企业编码001”。
   - 初始密码 dc123456。
   - 总管理员对应的旧系统员工档案。
5. 小程序客户人数限制为 1 至 50。
6. 可设置薪酬管理、积分考核、KPI考核、提成管理、晋升管理、年度考核预留和培训管理预留。
7. 企业资料和模块授权会调用小程序后端同步接口。
8. 公司列表显示“已同步、待同步、失败”；重新编辑提交可以重试。

## 主要文件

- app/code/Dacang/controllers/Adminhtml/CompanyController.php
- app/code/Dacang/controllers/Frontend/ApiController.php
- app/code/Dacang/Helper/CompanyIdentity.php
- app/code/Dacang/Helper/MiniappProvisioningService.php
- app/code/Dacang/Helper/MiniappBackendSync.php
- app/code/Dacang/Model/MiniappRegistrationModel.php
- app/code/Salary/Model/CompanyModuleAuthModel.php
- app/code/Salary/sql/install-1.0.0.14.php
- app/design/adminhtml/default/Dacang/company/edit.volt
- app/design/adminhtml/default/Dacang/company/index.volt
- app/design/adminhtml/default/Dacang/company/miniappapplications.volt

## B电脑拉取方式

在测试服务器的 qingquan 测试代码目录执行：

    git fetch origin
    git switch --track origin/codex/miniapp-customer

如果本地已经有同名分支：

    git switch codex/miniapp-customer
    git pull --ff-only origin codex/miniapp-customer

不要在生产目录执行以上命令，不要合并到 master。

## 数据库升级

1. 先备份测试数据库。
2. 确认代码中的 Salary 版本为 1.0.0.14。
3. 访问测试站任一 Salary 页面，让旧系统模块安装器执行 install-1.0.0.14.php。
4. 验证 company 表新增：
   - company_code
   - miniapp_admin_name
   - miniapp_admin_mobile
   - miniapp_admin_user_id
   - miniapp_admin_employee_id
   - registration_source
   - miniapp_sync_status
   - miniapp_sync_error
   - miniapp_synced_at
5. 验证新表 miniapp_registration_application 已建立。
6. 只允许在测试库执行迁移。

迁移是增量且可重复检查字段，但首次执行前仍必须备份测试库。

## 小程序后端同步配置

旧系统 PHP 运行环境需要设置：

    MINIAPP_SYNC_URL=http://小程序后端测试地址/api/legacy/miniapp-companies
    MINIAPP_SYNC_TOKEN=测试环境随机密钥

小程序后端需要设置相同密钥：

    LEGACY_SYNC_TOKEN=与 MINIAPP_SYNC_TOKEN 相同的值

注意：

- 环境变量必须对实际运行旧系统的 PHP-FPM/Apache 进程生效，仅在 SSH Shell 中设置无效。
- 配置后需要按测试服务器现有方式平滑重载 PHP 服务。
- 不要把真实密钥写入 Git、交接文档或聊天截图。
- 未配置时企业仍会保存，但状态为“待同步”。
- 小程序后端必须已经提供 POST /api/legacy/miniapp-companies，并按 {code: 0} 返回成功结果。
- 薪酬管理在同步时映射为小程序后端模块编码 payroll。

## 企业自主注册接口

旧系统公开接口：

    POST /api/miniappregister
    Content-Type: application/json

字段：

- company_name
- industry
- contact_name
- admin_mobile
- address

成功后只生成 pending 申请。运营人员需要进入：

    管理中心 > 业务管理 > 公司管理 > 小程序注册申请

点击“审核并开通”，再补充人数、到期时间和开放模块。

## 人工验收清单

### 手动开通

1. 新增一个测试小程序企业。
2. 验证人数为 1 和 50 可以保存，0 和 51 被拒绝。
3. 验证企业编码由企业名称拼音首字母生成。
4. 验证总管理员工号为“企业编码001”。
5. 验证初始密码为 dc123456。
6. 验证旧系统 user 表和 company_user 表都有总管理员数据。
7. 验证公司列表平台标签显示“小程序”。
8. 验证使用期限、状态和开放模块再次编辑后可以保存。

### 自主注册

1. 用测试手机号调用注册接口。
2. 验证重复提交返回同一个待审核申请，不重复创建企业。
3. 验证审核前无法获得企业登录账号。
4. 从运营后台审核并开通。
5. 验证申请状态改为“已开通”并关联公司 ID。
6. 验证拒绝操作只能通过 POST 提交。

### 跨系统同步

1. 验证配置缺失时旧系统显示“待同步”。
2. 配置测试同步地址和密钥后重新提交企业。
3. 验证状态变为“已同步”。
4. 验证小程序后端生成相同企业、总管理员和员工档案。
5. 验证薪酬、积分、KPI、提成、晋升模块与运营后台勾选一致。
6. 修改总管理员姓名、人数、到期时间和模块后重新提交，验证同步更新。
7. 使用错误密钥测试一次，验证状态显示“失败”且不影响旧系统企业数据。

### 旧功能回归

1. 新增和编辑一个钉钉测试企业。
2. 新增和编辑一个企业微信测试企业。
3. 验证原公司列表、登录、通讯录和考核入口不受影响。
4. 不测试或修改生产客户数据。

## 已完成的本地自动检查

- 8 个变更 PHP 文件通过 PHP 8.2 语法检查，代码未使用 PHP 7+ 专属语法。
- 3 个 Volt 模板的 if/endif、for/endfor 数量匹配。
- 模板 UTF-8 校验通过。
- git diff --check 通过。
- 企业中文名称首字母和管理员工号测试通过。
- 本地模拟 HTTP 后端验证同步密钥、模块映射及待同步逻辑通过。
- 本机没有 Phalcon 扩展和旧系统测试数据库，因此真实页面渲染、迁移和登录必须由 B 电脑在测试服务器完成。

## 回滚边界

如果测试失败：

1. 测试代码目录切回测试前分支或提交，不要操作生产目录。
2. 不建议立即删除 1.0.0.14 新增字段和申请表；旧代码不会使用这些字段，保留更安全。
3. 删除或停用测试企业前，先记录旧系统公司 ID 和小程序后端公司 ID。
4. 清理测试数据时只删除明确创建的测试企业，不得批量删除或影响生产客户。
5. 将错误页面、请求响应、PHP 日志和数据库错误原文交回 A 电脑定位。

## 禁止事项

- 未经用户人工验收和明确批准，不得部署生产服务器。
- 不得把测试密钥、数据库密码、SSH 私钥或客户数据提交到仓库。
- 不得在生产数据库执行 1.0.0.14 迁移。
- 不得直接合并 codex/miniapp-customer 到 master。