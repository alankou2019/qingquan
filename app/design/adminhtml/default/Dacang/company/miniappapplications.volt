<style>
.registration-table { width: 100%; border-collapse: collapse; background: #fff; }
.registration-table th, .registration-table td { padding: 13px 12px; border: 1px solid #e2e8f0; text-align: left; vertical-align: middle; }
.registration-table th { background: #f4f7fb; color: #334155; }
.registration-status { display: inline-block; padding: 3px 9px; border: 1px solid #cbd5e1; color: #475569; }
.registration-status.pending { border-color: #f2c66d; color: #9a6700; background: #fff9e8; }
.registration-status.approved { border-color: #9dd9bd; color: #167253; background: #f0fbf6; }
.registration-status.rejected { border-color: #efb1b1; color: #b42318; background: #fff5f5; }
.registration-action { display: inline-block; margin-right: 8px; padding: 6px 12px; border: 1px solid #b8caf0; color: #2456a6; background: #f5f8ff; cursor: pointer; }
.registration-action.reject { border-color: #efc1c1; color: #b42318; background: #fff7f7; }
.registration-empty { padding: 45px; color: #64748b; text-align: center; background: #fff; }
</style>
<div class="full_box">
    <div class="full_title">
        <span class="name">小程序注册申请</span>
        <a class="go_back" onclick="window.location='{{helper.createUrl(['p':'company/index'])}}';">
            <i class="iconfont icon-fanhui"></i><span>返回公司列表</span>
        </a>
    </div>
    <div class="full_cont">
        {% if applications|length > 0 %}
        <table class="registration-table">
            <thead>
                <tr>
                    <th>企业名称</th>
                    <th>联系人 / 手机号</th>
                    <th>行业 / 地址</th>
                    <th>申请时间</th>
                    <th>状态</th>
                    <th>关联企业</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
            {% for application in applications %}
                <tr>
                    <td>{{application.company_name}}</td>
                    <td>{{application.contact_name}}<br>{{application.admin_mobile}}</td>
                    <td>{{application.industry}}<br>{{application.address}}</td>
                    <td>{{helper.formatDateTime(application.created_at)}}</td>
                    <td>
                        <span class="registration-status {{application.status}}">
                        {% if application.status == 'pending' %}待审核{% elseif application.status == 'approved' %}已开通{% else %}已拒绝{% endif %}
                        </span>
                    </td>
                    <td>{% if application.company_id > 0 %}企业ID：{{application.company_id}}{% else %}-{% endif %}</td>
                    <td>
                        {% if application.status == 'pending' %}
                        <a class="registration-action" href="{{helper.createUrl(['p':'company/new','platform':'miniapp','application_id':application.id])}}">审核并开通</a>
                        <form action="{{helper.createUrl(['p':'company/rejectminiappapplication'])}}" method="post" style="display:inline" onsubmit="return confirm('确认拒绝该申请？');">
                            <input type="hidden" name="id" value="{{application.id}}">
                            <button type="submit" class="registration-action reject">拒绝</button>
                        </form>
                        {% else %}-{% endif %}
                    </td>
                </tr>
            {% endfor %}
            </tbody>
        </table>
        {% else %}
        <div class="registration-empty">暂无小程序注册申请</div>
        {% endif %}
    </div>
</div>