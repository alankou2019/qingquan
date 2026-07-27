<style>
.commission_payroll_page{padding:18px;}
.commission_toolbar{margin-bottom:12px;}
.commission_btn{display:inline-block;background:#4560e6;color:#fff;padding:0 14px;line-height:30px;height:30px;text-decoration:none;margin-right:8px;border:0;cursor:pointer;}
.commission_btn_gray{background:#64748b;}
.commission_filter{border:1px solid #d9e2ef;background:#fbfdff;padding:12px 14px;margin-bottom:12px;color:#475569;}
.commission_filter input[type=month]{height:28px;line-height:28px;border:1px solid #cbd5e1;padding:0 8px;width:130px;}
.commission_tip{color:#64748b;line-height:24px;margin:0 0 12px 0;}
.commission_status{display:inline-block;padding:0 8px;height:24px;line-height:24px;background:#eef2ff;color:#3949ab;}
.commission_scroll{overflow:auto;border:1px solid #d9e2ef;background:#fff;}
.commission_table{width:100%;border-collapse:collapse;background:#fff;min-width:1100px;}
.commission_table th{background:#f8fafc;color:#334155;font-weight:normal;text-align:left;padding:9px;border-bottom:1px solid #d9e2ef;white-space:nowrap;}
.commission_table td{padding:8px;border-bottom:1px solid #edf2f7;color:#475569;vertical-align:middle;white-space:nowrap;}
.commission_table input[type=text]{width:84px;height:26px;line-height:26px;border:1px solid #cbd5e1;padding:0 6px;text-align:right;}
.commission_table input.commission_remark{text-align:left;width:120px;}
.commission_project_col:nth-child(4n+1){background:#f0f7ff;}
.commission_project_col:nth-child(4n+2){background:#f1fbf5;}
.commission_project_col:nth-child(4n+3){background:#fff7ed;}
.commission_project_col:nth-child(4n){background:#f8f5ff;}
.commission_amount{font-weight:bold;color:#0f172a;}
.commission_total_col{background:#fbfdff;font-weight:bold;color:#1f2937;}
.commission_empty{border:1px solid #d9e2ef;background:#fbfdff;padding:18px;color:#64748b;}
.commission_unmatched{color:#e11d48;background:#fff1f2;padding:2px 6px;}
.commission_edit_box{border:1px solid #cbd5e1;background:#f8fafc;padding:14px;margin:0 0 12px 0;}
.commission_project_choices label{display:inline-block;min-width:180px;margin:6px 12px 6px 0;color:#334155;}
.commission_link{color:#3157d5;background:none;border:0;padding:0;cursor:pointer;text-decoration:none;font-size:14px;}
.inline_form{display:inline-block;margin:0 8px 4px 0;}
.commission_data_import_actions{display:inline-block;margin-left:12px;}
.commission_data_import_result{display:none;margin:0 0 12px 0;padding:9px 12px;border:1px solid #bbf7d0;background:#f0fdf4;color:#166534;line-height:20px;}
.commission_data_import_result.error{border-color:#fecaca;background:#fff1f2;color:#be123c;}
.commission_data_import_result table{margin-top:7px;border-collapse:collapse;background:#fff;font-size:12px;color:#475569;}
.commission_data_import_result th,.commission_data_import_result td{padding:4px 7px;border:1px solid #d9e2ef;text-align:left;}
</style>
<div class="full_box">
	{{ partial('salary_primary_navigation') }}
	<div class="salary_secondary_navigation"><a href="{{helper.createUrl(['p':'salary/commission'])}}">提成项目设置</a><a href="{{helper.createUrl(['p':'salary/commissionestimate'])}}">月收入测算</a><a class="on" href="#">月提成核算</a><a href="{{helper.createUrl(['p':'salary/commissionarchive'])}}">提成归档记录</a></div>
	<div class="commission_payroll_page">
		<div id="salary_inline_delete_message" style="display:none;margin:0 0 12px 0;padding:8px 12px;border:1px solid #bbf7d0;background:#f0fdf4;color:#166534;"></div>
		<div class="commission_toolbar">
			<a class="commission_btn commission_btn_gray" href="{{helper.createUrl(['p':'salary/commission'])}}">提成项目设置</a>
			<a class="commission_btn commission_btn_gray" href="{{helper.createUrl(['p':'salary/commissionestimate'])}}">月收入测算</a>
			<a class="commission_btn commission_btn_gray" href="{{helper.createUrl(['p':'salary/commissionarchive'])}}">提成归档记录</a>
		</div>
		<form class="commission_filter" method="post" action="{{helper.createUrl(['p':'salary/generatecommission'])}}">
			提成月份 <input type="month" name="commission_month" value="{{commissionMonth}}" />
			<button class="commission_btn" type="submit">生成此月提成核算表</button>
		</form>
		{% if !period %}
			<div class="commission_empty">
				{% if archivedPeriod %}
				该月份提成表已归档，请到提成归档记录查看，或恢复后再核算。
				<a class="commission_btn commission_btn_gray" href="{{helper.createUrl(['p':'salary/commissionarchive','commission_month':commissionMonth])}}" style="margin-left:10px;">查看归档记录</a>
				{% else %}
				当前月份还没有提成核算表。请先在提成项目设置中维护项目和规则，再点击上方按钮生成。
				{% endif %}
			</div>
		{% else %}
			<div class="commission_tip">
				当前提成表：{{period['commission_month']}}　
				状态：<span class="commission_status">{{period['status_name']}}</span>
				参与人数：<span id="commission_employee_count">{{period['employee_count']}}</span>　匹配人数：<span id="commission_matched_count">{{period['matched_count']}}</span>　提成合计：<span id="commission_total_amount">{{period['total_amount']}}</span>
				{% if period['can_edit'] %}<span class="commission_data_import_actions"><a class="commission_btn commission_btn_gray" href="{{helper.createUrl(['p':'salary/commissiondatatemplate','id':period['id']])}}">数据模板下载</a><button id="commission_data_import_button" class="commission_btn" type="button" data-upload-url="{{helper.createUrl(['p':'salary/uploadcommissiondata'])}}" onclick="openCommissionDataImport();">数据导入</button><input id="commission_data_file" type="file" accept=".xls,.xlsx" style="display:none;" onchange="uploadCommissionData(this);" /></span>{% endif %}
			</div>
			<div id="commission_data_import_result" class="commission_data_import_result"></div>
			{% if editRow and period['can_edit'] %}
			<div class="commission_edit_box">
				<strong>修改员工适配提成项目：{{editRow['employee_name']}}</strong>
				<form method="post" action="{{helper.createUrl(['p':'salary/savecommissionemployeeprojects'])}}" style="margin-top:8px;">
					<input type="hidden" name="id" value="{{period['id']}}" />
					<input type="hidden" name="employee_id" value="{{editRow['employee_id']}}" />
					<div class="commission_project_choices">
					{% for project in commissionProjects %}
						{% if project['status']=='active' and project['deleted_at']==0 %}
						<label><input type="checkbox" name="project_ids[]" value="{{project['id']}}" {% if selectedProjectMap[project['id']] is defined %}checked="checked"{% endif %} /> {{project['name']}}</label>
						{% endif %}
					{% endfor %}
					</div>
					<button class="commission_btn" type="submit">保存修改</button>
					<a class="commission_btn commission_btn_gray" href="{{helper.createUrl(['p':'salary/commissionpayroll','commission_month':commissionMonth])}}">取消</a>
				</form>
			</div>
			{% endif %}
			<form method="post" action="{{helper.createUrl(['p':'salary/savecommissionpayroll'])}}">
				<input type="hidden" name="id" value="{{period['id']}}" />
				<div class="commission_scroll">
					<table class="commission_table">
						<tr>
							<th>员工</th>
							<th>部门</th>
							<th>手机号</th>
							{% for project in commissionProjects %}
								{% if project['status']=='active' and project['deleted_at']==0 %}
								<th class="commission_project_col">{{project['name']}}<br />完成量</th>
								<th class="commission_project_col">{{project['name']}}<br />提成额</th>
								{% endif %}
							{% endfor %}
							<th class="commission_total_col">提成合计</th>
							<th>备注</th>
							<th>操作</th>
						</tr>
						{% for row in commissionRows %}
						<tr id="commission_employee_row_{{row['employee_id']}}">
							<td>{{row['employee_name']}}</td>
							<td>{{row['department_name']}}</td>
							<td>{{row['employee_no']}}</td>
							{% for project in commissionProjects %}
								{% if project['status']=='active' and project['deleted_at']==0 %}
								{% if row['item_map'][project['id']] is defined %}
								<td class="commission_project_col"><input type="text" name="input[{{row['employee_id']}}][{{project['id']}}]" value="{{row['item_map'][project['id']]['input_value']}}" {% if !period['can_edit'] %}readonly="readonly"{% endif %} /></td>
								<td class="commission_project_col commission_amount">{{row['item_map'][project['id']]['commission_amount']}}</td>
								{% else %}
								<td class="commission_project_col"><span class="commission_unmatched">未匹配</span></td>
								<td class="commission_project_col">0.00</td>
								{% endif %}
								{% endif %}
							{% endfor %}
							<td class="commission_total_col">{{row['total_amount']}}</td>
							<td><input type="text" class="commission_remark" name="remark[{{row['employee_id']}}]" value="{{row['remark']}}" {% if !period['can_edit'] %}readonly="readonly"{% endif %} /></td>
							<td>
								{% if period['can_edit'] %}
								<a class="commission_link" href="{{helper.createUrl(['p':'salary/commissionpayroll','commission_month':commissionMonth,'edit_employee_id':row['employee_id']])}}">编辑</a>　
								<button class="commission_link" type="button" data-delete-url="{{helper.createUrl(['p':'salary/deletecommissionemployee'])}}" data-delete-row-id="commission_employee_row_{{row['employee_id']}}" data-delete-confirm="只会从当前月提成核算表删除该员工，不影响人事档案。确认删除吗？" onclick="return salaryInlineDelete(this, {id:{{period['id']}},employee_id:{{row['employee_id']}}}, updateCommissionPayrollSummary);">删除</button>
								{% else %}-{% endif %}
							</td>
						</tr>
						{% elsefor %}
						<tr><td colspan="50" class="commission_empty">当前提成表暂无员工数据。</td></tr>
						{% endfor %}
					</table>
				</div>
				<div style="margin-top:10px;">
					{% if period['can_edit'] %}
					<button class="commission_btn" type="submit">保存核算表</button>
					{% endif %}
				</div>
			</form>
			{% if period['can_edit'] %}
			<form class="inline_form" method="post" action="{{helper.createUrl(['p':'salary/archivecommission'])}}" onsubmit="return confirm('归档后不可直接修改，需要恢复后才能重新核算。确认归档吗？');">
				<input type="hidden" name="id" value="{{period['id']}}" />
				<button class="commission_btn commission_btn_gray" type="submit">归档</button>
			</form>
			{% endif %}
		{% endif %}
	</div>
</div>
<script src="/skin/adminhtml/default/js/salary-inline-delete.js?v=20260717-2"></script>
<script type="text/javascript">
function updateCommissionPayrollSummary(data) {
	var employeeCount = document.getElementById('commission_employee_count');
	var matchedCount = document.getElementById('commission_matched_count');
	var totalAmount = document.getElementById('commission_total_amount');
	if (employeeCount && typeof data.employee_count != 'undefined') { employeeCount.innerHTML = data.employee_count; }
	if (matchedCount && typeof data.matched_count != 'undefined') { matchedCount.innerHTML = data.matched_count; }
	if (totalAmount && typeof data.total_amount != 'undefined') { totalAmount.innerHTML = data.total_amount; }
}
function commissionImportResultCell(row, value) {
	var cell = document.createElement('td');
	cell.appendChild(document.createTextNode(value === null || typeof value === 'undefined' ? '' : String(value)));
	row.appendChild(cell);
}
function renderCommissionDataImportResult(data, isError, message) {
	var box = document.getElementById('commission_data_import_result');
	if (!box) { return; }
	while (box.firstChild) { box.removeChild(box.firstChild); }
	box.className = 'commission_data_import_result' + (isError ? ' error' : '');
	box.style.display = 'block';
	if (!message && !isError && data) {
		message = '导入完成：更新' + (data.updated_cell_count || 0) + '个完成量，涉及' + (data.employee_count || 0) + '名员工；跳过空白单元格' + (data.skipped_blank_count || 0) + '个。';
	}
	var summary = document.createElement('div');
	summary.appendChild(document.createTextNode(message || '导入完成'));
	box.appendChild(summary);
	var errors = data && data.errors && data.errors.length ? data.errors : [];
	if (errors.length) {
		var note = document.createElement('div');
		note.style.marginTop = '6px';
		note.appendChild(document.createTextNode('以下' + errors.length + '条内容未导入：'));
		box.appendChild(note);
		var table = document.createElement('table'), head = document.createElement('tr');
		var titles = ['Excel行', '姓名', '手机号', '原因'];
		for (var i = 0; i < titles.length; i++) { var th = document.createElement('th'); th.appendChild(document.createTextNode(titles[i])); head.appendChild(th); }
		table.appendChild(head);
		for (var j = 0; j < errors.length; j++) {
			var row = document.createElement('tr');
			commissionImportResultCell(row, errors[j].row || '');
			commissionImportResultCell(row, errors[j].name || '');
			commissionImportResultCell(row, errors[j].mobile || '');
			commissionImportResultCell(row, errors[j].reason || '');
			table.appendChild(row);
		}
		box.appendChild(table);
	}
}
function openCommissionDataImport() {
	if (!confirm('请确认Excel中“姓名”和“手机号”均与当前提成表一致；仅非空完成量会写入，空白单元格不会覆盖原数据。')) { return; }
	var input = document.getElementById('commission_data_file');
	if (input) { input.click(); }
}
function uploadCommissionData(input) {
	if (!input || !input.files || !input.files.length) { return; }
	var button = document.getElementById('commission_data_import_button');
	var form = document.querySelector('form[action*="savecommissionpayroll"]');
	var periodId = form && form.elements['id'] ? parseInt(form.elements['id'].value, 10) || 0 : 0;
	var file = input.files[0], fileName = String(file.name || '').toLowerCase();
	if (!periodId) { alert('没有找到当前提成核算表'); input.value = ''; return; }
	if (!/\.(xls|xlsx)$/.test(fileName)) { alert('请选择xls或xlsx格式的Excel文件'); input.value = ''; return; }
	var data = new FormData();
	data.append('id', periodId);
	data.append('salary_ajax', '1');
	data.append('commission_data_file', file);
	if (button) { button.disabled = true; button.innerHTML = '导入中...'; }
	renderCommissionDataImportResult(null, false, '正在导入并按提成规则重新计算金额...');
	var request = new XMLHttpRequest();
	request.open('POST', button.getAttribute('data-upload-url'), true);
	request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
	request.onreadystatechange = function() {
		if (request.readyState !== 4) { return; }
		if (button) { button.disabled = false; button.innerHTML = '数据导入'; }
		input.value = '';
		var result = null;
		try { result = JSON.parse(request.responseText); } catch (error) { result = null; }
		if (request.status < 200 || request.status >= 300 || !result || result.status !== 'y') {
			renderCommissionDataImportResult(result && result.data ? result.data : null, true, result && result.error ? result.error : '导入响应异常，请重试');
			return;
		}
		var resultData = result.data || {};
		renderCommissionDataImportResult(resultData, false, '导入完成，已按提成规则重新计算金额。请点击“刷新查看结果”查看最新表格。');
		var refreshButton = document.createElement('button');
		refreshButton.type = 'button';
		refreshButton.className = 'commission_btn';
		refreshButton.style.marginTop = '7px';
		refreshButton.appendChild(document.createTextNode('刷新查看结果'));
		refreshButton.onclick = function() { window.location.href = resultData.reload_url || window.location.href; };
		document.getElementById('commission_data_import_result').appendChild(refreshButton);
	};
	request.send(data);
}
</script>
