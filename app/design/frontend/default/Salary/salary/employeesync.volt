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
.salary_employee_edit{display:none;box-sizing:border-box;width:100%;height:28px;border:1px solid #cbd5e1;padding:0 6px;background:#fff;}
.salary_employee_row.is_editing .salary_employee_display{display:none;}
.salary_employee_row.is_editing .salary_employee_edit{display:block;}
.salary_employee_edit_actions{display:none;white-space:nowrap;}
.salary_employee_row.is_editing .salary_employee_default_actions{display:none;}
.salary_employee_row.is_editing .salary_employee_edit_actions{display:inline;}
.salary_employee_action{border:0;background:none;color:#4560e6;cursor:pointer;padding:0;font-size:12px;}
.salary_employee_action[disabled]{color:#94a3b8;cursor:not-allowed;}
.salary_employee_status{display:block;min-height:16px;margin-top:3px;color:#15803d;font-size:11px;white-space:normal;}
.salary_employee_status.error{color:#dc2626;}
.salary_table_foot{border:1px solid #d9e2ef;border-top:0;background:#fff;padding:10px 12px;}
.salary_table_foot label{color:#475569;}
.salary_empty{padding:18px;color:#94a3b8;text-align:center;}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li class="on"><a href="#">员工同步/导入</a></li>
			<li style="float:right;width:140px;border-left:1px solid #efefef;border-right:0;"><a href="{{helper.createUrl(['p':'index/index','bigClass':'4'])}}" target="_top">返回薪酬管理</a></li>
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
				<span class="salary_record_count">共 <span id="salary_employee_count">{{userItems|length}}</span> 条记录</span>
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
							<th style="width:130px;"><span>操作</span></th>
						</tr>
						{% for item in userItems %}
						<tr class="salary_employee_row" id="salary_employee_row_{{item['id']}}">
							<td class="check_box"><label class="radio_check"><input type="checkbox" name="salary_employee_check" value="{{item['id']}}" /></label></td>
							<td class="name"><span class="txt">{{item['id']}}</span></td>
							<td class="name">
								<span class="txt salary_employee_display" data-field-display="name">{{item['name']}}</span>
								<input class="salary_employee_edit" type="text" data-field="name" data-original="{{item['name']}}" value="{{item['name']}}" maxlength="80" />
							</td>
							<td class="name">
								<span class="txt salary_employee_display" data-field-display="mobile">{{item['mobile']}}</span>
								<input class="salary_employee_edit" type="text" data-field="mobile" data-original="{{item['mobile']}}" value="{{item['mobile']}}" maxlength="20" />
							</td>
							<td class="name">
								<span class="txt salary_employee_display" data-field-display="department_name">{% if item['departmentname'] %}{{item['departmentname']}}{% else %}-{% endif %}</span>
								<select class="salary_employee_edit" data-field="department_id" data-original="{{item['department_id']}}">
									<option value="0">未设置</option>
									{% for department in departmentItems %}
									<option value="{{department['value']}}" {% if item['department_id']==department['value'] %}selected="selected"{% endif %}>{{department['name']}}</option>
									{% endfor %}
								</select>
							</td>
							<td class="name">
								<span class="txt salary_employee_display" data-field-display="position_name">{% if item['position_name'] %}{{item['position_name']}}{% else %}-{% endif %}</span>
								<input class="salary_employee_edit" type="text" data-field="position_name" data-original="{{item['position_name']}}" value="{{item['position_name']}}" maxlength="100" />
							</td>
							<td class="name"><span class="txt">{% if item['is_admin'] %}是{% else %}否{% endif %}</span></td>
							<td class="name"><span class="txt">{% if item['is_leader'] %}是{% else %}否{% endif %}</span></td>
							<td class="name">
								<span class="salary_employee_default_actions">
									<button class="salary_employee_action" type="button" onclick="editSalaryEmployee({{item['id']}});">编辑</button>
									{% if item['is_admin'] %}
									　<button class="salary_employee_action" type="button" disabled="disabled" title="企业管理员不能删除">删除</button>
									{% else %}
									　<button class="salary_employee_action" type="button" onclick="deleteSalaryEmployee({{item['id']}});">删除</button>
									{% endif %}
								</span>
								<span class="salary_employee_edit_actions">
									<button class="salary_employee_action" type="button" onclick="saveSalaryEmployee({{item['id']}},this);">保存</button>
									　<button class="salary_employee_action" type="button" onclick="cancelSalaryEmployee({{item['id']}});">取消</button>
								</span>
								<span class="salary_employee_status" id="salary_employee_status_{{item['id']}}"></span>
							</td>
						</tr>
						{% elsefor %}
						<tr><td colspan="9" class="salary_empty">暂无员工数据</td></tr>
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

function getSalaryEmployeeRow(employeeId)
{
	return document.getElementById('salary_employee_row_' + employeeId);
}

function setSalaryEmployeeStatus(employeeId, message, isError)
{
	var status = document.getElementById('salary_employee_status_' + employeeId);
	if (!status) {
		return;
	}
	status.className = isError ? 'salary_employee_status error' : 'salary_employee_status';
	status.textContent = message || '';
}

function editSalaryEmployee(employeeId)
{
	var row = getSalaryEmployeeRow(employeeId);
	if (!row) {
		return;
	}
	row.className = 'salary_employee_row is_editing';
	setSalaryEmployeeStatus(employeeId, '', false);
}

function cancelSalaryEmployee(employeeId)
{
	var row = getSalaryEmployeeRow(employeeId);
	if (!row) {
		return;
	}
	var fields = row.querySelectorAll('[data-field]');
	for (var i = 0; i < fields.length; i++) {
		fields[i].value = fields[i].getAttribute('data-original');
	}
	row.className = 'salary_employee_row';
	setSalaryEmployeeStatus(employeeId, '', false);
}

function salaryEmployeePost(url, data, callback)
{
	var request = new XMLHttpRequest();
	var values = [];
	for (var key in data) {
		if (data.hasOwnProperty(key)) {
			values.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
		}
	}
	request.open('POST', url, true);
	request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
	request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
	request.onreadystatechange = function() {
		if (request.readyState !== 4) {
			return;
		}
		var result = null;
		try {
			result = JSON.parse(request.responseText);
		} catch (error) {
			callback(false, '服务器返回异常，请稍后重试');
			return;
		}
		if (request.status >= 200 && request.status < 300 && result.status === 'y') {
			callback(true, result.data || {});
			return;
		}
		callback(false, result.error || '操作失败，请稍后重试');
	};
	request.send(values.join('&'));
}

function saveSalaryEmployee(employeeId, button)
{
	var row = getSalaryEmployeeRow(employeeId);
	if (!row || !button) {
		return;
	}
	var fields = row.querySelectorAll('[data-field]');
	var data = {employee_id: employeeId};
	for (var i = 0; i < fields.length; i++) {
		data[fields[i].getAttribute('data-field')] = fields[i].value;
	}
	button.disabled = true;
	setSalaryEmployeeStatus(employeeId, '保存中...', false);
	salaryEmployeePost('{{helper.createUrl(['p':'salary/employeesave'])}}', data, function(success, result) {
		button.disabled = false;
		if (!success) {
			setSalaryEmployeeStatus(employeeId, result, true);
			return;
		}
		var employee = result.employee || {};
		var displayMap = {
			name: employee.name || '',
			mobile: employee.mobile || '',
			department_name: employee.department_name || '-',
			position_name: employee.position_name || '-'
		};
		for (var fieldName in displayMap) {
			if (!displayMap.hasOwnProperty(fieldName)) {
				continue;
			}
			var display = row.querySelector('[data-field-display="' + fieldName + '"]');
			if (display) {
				display.textContent = displayMap[fieldName];
			}
		}
		for (var i = 0; i < fields.length; i++) {
			fields[i].setAttribute('data-original', fields[i].value);
		}
		row.className = 'salary_employee_row';
		setSalaryEmployeeStatus(employeeId, result.message || '已保存', false);
	});
}

function deleteSalaryEmployee(employeeId)
{
	if (!confirm('删除后员工会从企业人员名单移除，历史薪酬记录仍然保留；来自钉钉或企业微信的员工下次同步可能重新出现。确认删除吗？')) {
		return;
	}
	setSalaryEmployeeStatus(employeeId, '删除中...', false);
	salaryEmployeePost('{{helper.createUrl(['p':'salary/employeedelete'])}}', {employee_id:employeeId}, function(success, result) {
		if (!success) {
			setSalaryEmployeeStatus(employeeId, result, true);
			return;
		}
		var row = getSalaryEmployeeRow(employeeId);
		if (row && row.parentNode) {
			row.parentNode.removeChild(row);
		}
		var count = document.getElementById('salary_employee_count');
		if (count) {
			var current = parseInt(count.textContent, 10);
			count.textContent = isNaN(current) || current <= 0 ? 0 : current - 1;
		}
	});
}
</script>
