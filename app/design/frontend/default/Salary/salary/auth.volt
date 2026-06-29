<style>
.salary_auth{padding:18px;}
.salary_auth_tip{border:1px solid #d9e2ef;background:#fbfdff;padding:12px 14px;margin-bottom:12px;color:#475569;line-height:22px;}
.salary_auth_block{margin-bottom:18px;}
.salary_auth_block h3{font-size:15px;color:#1f2937;margin:0 0 10px 0;}
.salary_auth .btn_link,.salary_auth .btn_save{display:inline-block;background:#4560e6;color:#fff;padding:0 12px;line-height:28px;height:28px;text-decoration:none;border:0;cursor:pointer;}
.salary_auth .empty{padding:20px;color:#94a3b8;text-align:center;}
.salary_auth label{display:inline-block;margin-right:12px;color:#475569;line-height:28px;}
.salary_auth input{vertical-align:middle;margin-right:4px;}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li class="on"><a href="#">薪酬管理授权</a></li>
		</ul>
	</div>
	<div class="salary_auth">
		<div class="salary_auth_tip">薪酬管理授权分为两类：工资表审核授权用于设置工资表审核人；薪酬查询授权用于设置谁可以查看部门或指定员工薪酬。</div>

		<div class="salary_auth_block">
			<h3>工资表审核授权</h3>
			<form method="post" action="{{helper.createUrl(['p':'salary/saveauditreviewers'])}}">
				<table class="table_box">
					<tr class="table_head">
						<th width="10%"><span>选择</span></th>
						<th width="18%"><span>ID</span></th>
						<th width="28%"><span>姓名</span></th>
						<th><span>部门</span></th>
					</tr>
					{% for item in userItems %}
					<tr>
						<td class="name"><input type="checkbox" name="reviewer_ids[]" value="{{item['id']}}" {% if item['is_audit_reviewer'] %}checked="checked"{% endif %} /></td>
						<td class="name"><span class="txt">{{item['id']}}</span></td>
						<td class="name"><span class="txt">{{item['name']}}</span></td>
						<td class="name"><span class="txt">{{item['departmentname']}}</span></td>
					</tr>
					{% elsefor %}
					<tr>
						<td colspan="4" class="empty">暂无员工</td>
					</tr>
					{% endfor %}
				</table>
				<div style="margin-top:10px;">
					<button class="btn_save" type="submit">保存审核人</button>
					<span style="color:#64748b;margin-left:10px;">当前已设置 {{reviewerCount}} 人。工资表需全部审核人同意后才能发工资条并归档。</span>
				</div>
			</form>
		</div>

		<div class="salary_auth_block">
			<h3>薪酬查询授权</h3>
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
						<a class="btn_link" href="{{helper.createUrl(['p':'salary/authedit','user_id':item['id']])}}">配置查询权限</a>
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
</div>
