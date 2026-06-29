<style>
.salary_auth{padding:18px;}
.salary_auth_tip{border:1px solid #d9e2ef;background:#fbfdff;padding:12px 14px;margin-bottom:12px;color:#475569;line-height:22px;}
.salary_auth .btn_link{display:inline-block;background:#4560e6;color:#fff;padding:0 12px;line-height:28px;height:28px;text-decoration:none;}
.salary_auth .empty{padding:20px;color:#94a3b8;text-align:center;}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li class="on"><a href="#">薪酬查看授权</a></li>
		</ul>
	</div>
	<div class="salary_auth">
		<div class="salary_auth_tip">薪酬数据按企业隔离。授权后，指定员工可以查看被授权部门或指定员工的薪酬数据。</div>
		<table class="table_box">
			<tr class="table_head">
				<th width="12%"><span>ID</span></th>
				<th width="24%"><span>姓名</span></th>
				<th width="28%"><span>部门</span></th>
				<th width="18%"><span>授权数量</span></th>
				<th><span>操作</span></th>
			</tr>
			{% for item in userItems %}
			<tr>
				<td class="name"><span class="txt">{{item['id']}}</span></td>
				<td class="name"><span class="txt">{{item['name']}}</span></td>
				<td class="name"><span class="txt">{{item['departmentname']}}</span></td>
				<td class="name"><span class="txt">{{item['role_count']}}</span></td>
				<td class="operate">
					<a class="btn_link" href="{{helper.createUrl(['p':'salary/authedit','user_id':item['id']])}}">配置权限</a>
				</td>
			</tr>
			{% elsefor %}
			<tr>
				<td colspan="5" class="empty">暂无员工</td>
			</tr>
			{% endfor %}
		</table>
	</div>
</div>
