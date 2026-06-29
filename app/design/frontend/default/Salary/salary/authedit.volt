<script src="/skin/adminhtml/default/js/form.js"></script>
<style>
.salary_auth_edit{padding:18px;}
.salary_auth_edit .section{border:1px solid #d9e2ef;background:#fff;margin-bottom:14px;padding:14px;}
.salary_auth_edit .section h3{font-size:15px;color:#1f2937;margin:0 0 10px 0;}
.salary_auth_edit .check_line{line-height:28px;color:#374151;}
.salary_auth_edit .employee_grid{display:grid;grid-template-columns:repeat(3,1fr);gap:6px 12px;}
.salary_auth_edit .tools{padding:12px 0;}
.salary_auth_edit .btn{display:inline-block;border:0;background:#4560e6;color:#fff;padding:0 18px;line-height:32px;height:32px;cursor:pointer;}
.salary_auth_edit .btn.gray{background:#94a3b8;text-decoration:none;margin-left:8px;}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li class="on"><a href="#">薪酬查询授权</a></li>
		</ul>
	</div>
	<div class="salary_auth_edit">
		<form action="{{helper.createUrl(['p':'salary/authsave'])}}" method="post" id="dataForm" name="dataForm">
			<input type="hidden" name="user_id" value="{{userInfo.id}}" />
			<div class="section">
				<h3>授权员工：{{userInfo.name}}</h3>
				<label class="check_line">
					<input type="checkbox" name="can_export" value="1" {% if canExport==1 %}checked="checked"{% endif %} />
					允许导出薪酬数据
				</label>
			</div>
			<div class="section">
				<h3>可查看部门薪酬</h3>
				{% for depart in departList %}
				<div class="check_line">
					{{depart.delimiter}}
					<input type="checkbox" name="role_department[]" level="{{depart.level}}" class="department_checkbox" value="{{depart.id}}" path="{{depart.path}}" {% if depart.isChecked==1 %}checked="checked"{% endif %} />
					{{depart.name}}
				</div>
				{% elsefor %}
				<div class="check_line">暂无部门</div>
				{% endfor %}
			</div>
			<div class="section">
				<h3>可查看指定员工薪酬</h3>
				<div class="employee_grid">
					{% for item in userItems %}
					<label class="check_line">
						<input type="checkbox" name="role_employee[]" value="{{item['id']}}" {% if item['isChecked']==1 %}checked="checked"{% endif %} />
						{{item['name']}}
					</label>
					{% elsefor %}
					<div class="check_line">暂无员工</div>
					{% endfor %}
				</div>
			</div>
			<div class="tools">
				<button type="submit" class="btn">保存</button>
				<a class="btn gray" href="{{helper.createUrl(['p':'salary/auth'])}}">返回</a>
			</div>
		</form>
	</div>
</div>
<script>
$('.department_checkbox').click(function(){
	var inputlen = $('.department_checkbox').length;
	var current = $(this).attr('path').split('_').pop();
	var isChecked = $(this).is(':checked');
	for (var i = 0; i < inputlen; i++) {
		var obj = $('.department_checkbox').eq(i);
		var path = obj.attr('path').split('_');
		if ($.inArray(current, path) >= 0) {
			obj.prop('checked', isChecked);
		}
	}
});
</script>
