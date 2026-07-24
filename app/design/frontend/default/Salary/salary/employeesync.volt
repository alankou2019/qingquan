<style>
.salary_panel{padding:18px;}
.salary_notice{border:1px solid #d9e2ef;background:#fbfdff;padding:14px 16px;margin-bottom:14px;line-height:24px;color:#475569;}
.salary_grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;}
.salary_sync_item{border:1px solid #d9e2ef;background:#fff;padding:14px;min-height:118px;}
.salary_sync_item.active{border-color:#4560e6;background:#f8fbff;}
.salary_sync_item h3{font-size:15px;color:#1f2937;margin:0 0 8px 0;}
.salary_sync_item p{color:#64748b;line-height:22px;margin:0 0 12px 0;}
.salary_tag{display:inline-block;background:#eef2ff;color:#3949ab;padding:0 8px;line-height:22px;margin-bottom:8px;}
.salary_btn{display:inline-block;background:#4560e6;color:#fff;padding:0 16px;line-height:30px;height:30px;text-decoration:none;}
.salary_btn_gray{background:#64748b;}
.salary_sync_actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.salary_file_input{display:none;}
.salary_block{margin-top:18px;}
.salary_block_header{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;}
.salary_block_header h3{font-size:15px;color:#1f2937;margin:0;}
.salary_record_count{color:#64748b;}
.salary_table_body{border:1px solid #d9e2ef;overflow-x:auto;}
.salary_table_body .table_box{min-width:860px;}
.salary_table_body .table_box tbody tr:hover td{background:#f1f3f5;}
.salary_table_body .table_box td{transition:background-color .12s ease;}
.salary_table_foot{border:1px solid #d9e2ef;border-top:0;background:#fff;padding:10px 12px;}
.salary_table_foot label{color:#475569;}
.salary_empty{padding:18px;color:#94a3b8;text-align:center;}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li class="on"><a href="#">员工同步/导入</a></li>
			<li style="float:right;width:140px;border-left:1px solid #efefef;border-right:0;"><a href="{{helper.createUrl(['p':'salary/index'])}}">返回薪酬首页</a></li>
		</ul>
	</div>
	<div class="salary_panel">
		<div class="salary_notice">
			当前企业通讯平台：{{platformName}}
		</div>
		<div class="salary_grid">
			{% for code,item in syncItems %}
			<div class="salary_sync_item {% if code==platform %}active{% endif %}">
				<h3>{{item['name']}}</h3>
				<span class="salary_tag">{{item['status']}}</span>
				<p>{{item['desc']}}</p>
				{% if code=='manual' %}
				<div class="salary_sync_actions">
					<button class="salary_btn" type="button" onclick="selectEmployeeExcel();">导入Excel</button>
					<a class="salary_btn salary_btn_gray" href="{{item['template_url']}}">Excel模板下载</a>
				</div>
				<form method="post" action="{{item['upload_url']}}" enctype="multipart/form-data" id="salary_employee_excel_form">
					<input class="salary_file_input" type="file" name="exceltpl" id="salary_employee_excel_input" accept=".xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" onchange="submitEmployeeExcel(this);" />
					<input type="hidden" name="from" value="salary" />
				</form>
				{% elseif item['url'] %}
				<a class="salary_btn" href="{{item['url']}}">{{item['action']}}</a>
				{% else %}
				<span class="salary_tag">暂未开放</span>
				{% endif %}
			</div>
			{% endfor %}
		</div>
		<div class="salary_block">
			<div class="salary_block_header">
				<h3>当前员工</h3>
				<span class="salary_record_count">共 {{userItems|length}} 条记录</span>
			</div>
			<div class="salary_table_body">
				<table class="table_box">
					<tbody>
						<tr class="table_head">
							<th class="check_box"><label class="radio_check ck_all"><input type="checkbox" id="salary_employee_check_all" onclick="toggleSalaryEmployees(this);" /></label></th>
							<th style="width:70px;"><span>ID</span></th>
							<th style="width:150px;"><span>姓名</span></th>
							<th style="width:160px;"><span>手机号</span></th>
							<th style="width:220px;"><span>部门</span></th>
							<th style="width:160px;"><span>岗位</span></th>
							<th style="width:90px;"><span>管理员</span></th>
							<th style="width:90px;"><span>负责人</span></th>
						</tr>
						{% for item in userItems %}
						<tr>
							<td class="check_box"><label class="radio_check"><input type="checkbox" name="salary_employee_check" value="{{item['id']}}" /></label></td>
							<td class="name"><span class="txt">{{item['id']}}</span></td>
							<td class="name"><span class="txt">{{item['name']}}</span></td>
							<td class="name"><span class="txt">{{item['mobile']}}</span></td>
							<td class="name"><span class="txt">{% if item['departmentname'] %}{{item['departmentname']}}{% else %}-{% endif %}</span></td>
							<td class="name"><span class="txt">{% if item['position_name'] %}{{item['position_name']}}{% else %}-{% endif %}</span></td>
							<td class="name"><span class="txt">{% if item['is_admin'] %}是{% else %}否{% endif %}</span></td>
							<td class="name"><span class="txt">{% if item['is_leader'] %}是{% else %}否{% endif %}</span></td>
						</tr>
						{% elsefor %}
						<tr><td colspan="8" class="salary_empty">暂无员工数据</td></tr>
						{% endfor %}
					</tbody>
				</table>
			</div>
			<div class="salary_table_foot">
				<label><input type="checkbox" onclick="toggleSalaryEmployees(this);" /> 全选</label>
			</div>
		</div>
	</div>
</div>
<script>
function selectEmployeeExcel()
{
	document.getElementById('salary_employee_excel_input').click();
}

function submitEmployeeExcel(input)
{
	if (!input || !input.value) {
		return;
	}
	document.getElementById('salary_employee_excel_form').submit();
}

function toggleSalaryEmployees(source)
{
	var items = document.getElementsByName('salary_employee_check');
	for (var i = 0; i < items.length; i++) {
		items[i].checked = source.checked;
	}
	var topCheck = document.getElementById('salary_employee_check_all');
	if (topCheck && topCheck !== source) {
		topCheck.checked = source.checked;
	}
}
</script>
