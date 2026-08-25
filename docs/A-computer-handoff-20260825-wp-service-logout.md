# A电脑交接：服务号入口增加退出登录

日期：2026-08-25

## 本次更新

Excel 导入的员工通过微信公众号/服务号网页登录后，手机端首页蓝色个人信息区域右上角新增“退出登录”按钮。

- 点击退出时先要求用户确认。
- 确认后清除用户、企业及服务号登录会话，并使旧会话 Cookie 失效。
- 退出后返回 `/wp/loginpage` 登录页。
- 按钮仅在普通服务号网页登录场景显示，不影响钉钉免登和企业微信免登入口。
- 旧会话没有 `login_platform` 标记时，非钉钉浏览器仍会显示退出按钮，现有服务号用户无需重新登录即可看到。

## 相关文件

- `app/code/Dacang/controllers/Frontend/WpController.php`
- `app/code/Dacang/controllers/Frontend/BsController.php`
- `app/design/frontend/default/Dacang/bs/newindex.volt`

## 部署与验证

- 测试环境备份：`/www/backup/wp-logout-20260825_184330/staging`
- 生产环境备份：`/www/backup/wp-logout-20260825_184454/production`
- 测试与生产 PHP 语法检查均通过。
- 生产服务号登录页 `https://kpi.dacangcons.cn/wp/loginpage` 返回 HTTP 200。
- 本次没有数据库变更。

## 后续维护注意

生产旧系统与 GitHub 最新完整代码存在版本差异，本次生产发布采用三个文件内的精确小补丁。后续不要用 GitHub 整个目录直接覆盖生产；如需继续发布，应先对比生产文件并保留现有业务修复。
