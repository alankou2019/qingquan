# A 电脑交接：wecom 测试数据库隔离

## 完成时间

- 2026-07-21

## 测试环境

- 测试站点：`https://wecom-kpi.dacangcons.cn`
- 服务器：`43.139.16.63`
- 测试代码目录：`/www/wwwroot/dingding/wecom-staging`
- 测试配置：`/www/wwwroot/dingding/wecom-staging/app/etc/config.xml`

## 数据库隔离结果

- 已建立独立测试数据库：`wecom_kpi_staging`
- 字符集：`utf8mb4`
- 已从 `/root/wecom-staging-maintenance/wecom_kpi_staging_seed_20260721.sql` 导入测试种子。
- 导入后验证共有 33 张数据表，专用账号连接测试库正常。
- 服务器运行 MySQL 5.6，数据库用户名上限为 16 个字符。原计划名称 `wecom_staging_app` 超出限制，实际使用兼容名称：`wecom_staging_ap`。
- 专用账号仅授权访问 `wecom_kpi_staging`，没有全局数据库权限。
- 数据库密码只保存在服务器测试配置中，不写入本文件、不发送聊天、不提交 Git。

## 配置与回滚

- 已将 wecom 测试配置切换到独立测试库和专用账号。
- 切换前配置备份：`/www/wwwroot/dingding/wecom-staging/app/etc/config.xml.bak.db-isolation-20260721_220222`
- 宝塔保存的 MySQL root 凭据此前与实际密码不一致；经用户授权已同步修复，未在交接文件中保存密码。

## 验证结果

- MySQL 服务运行正常。
- 原有应用数据库账号连接正常。
- 测试专用账号可连接 `wecom_kpi_staging`。
- 测试配置 XML 格式和四项数据库参数已校验。
- 未修改生产目录 `/www/wwwroot/dingding/v1`。
- 未复制生产数据库数据。

## B 电脑后续要求

- 薪酬、提成等开发和测试只使用 wecom 测试站及独立测试库。
- 不覆盖服务器上的 `app/etc/config.xml`，不把真实配置或密码提交到 GitHub。
- 不连接、不导入、不清理生产数据库。
- 未经用户明确确认，不得把新模块部署到生产服务器。
- 如测试配置异常，先使用上述备份回滚，再排查，不要从生产配置复制覆盖。
