<style>
.salary_page{padding:18px;}
.salary_toolbar{margin-bottom:12px;}
.salary_toolbar .btn,.salary_btn{display:inline-block;background:#4560e6;color:#fff;padding:0 14px;line-height:30px;height:30px;text-decoration:none;margin-right:8px;border:0;cursor:pointer;}
.salary_toolbar .btn_gray,.salary_btn_gray{background:#64748b;}
.salary_panel{border:1px solid #d9e2ef;background:#fff;margin-bottom:12px;}
.salary_panel_hd{background:#f8fafc;border-bottom:1px solid #d9e2ef;padding:10px 12px;color:#334155;font-weight:bold;}
.salary_panel_bd{padding:12px;color:#475569;}
.salary_summary{display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin-bottom:12px;}
.salary_summary_item{border:1px solid #e2e8f0;background:#fbfdff;padding:10px;}
.salary_summary_item label{display:block;color:#64748b;margin-bottom:5px;}
.salary_summary_item strong{font-size:16px;color:#1f2937;font-weight:normal;}
.salary_range{margin:0 0 12px 0;}
.salary_range label{margin-right:22px;cursor:pointer;}
.salary_scope{display:none;border:1px solid #e2e8f0;background:#fbfdff;padding:10px;margin-top:10px;}
.salary_scope_on{display:block;}
.salary_check_grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px 12px;}
.salary_check_grid label{white-space:nowrap;color:#334155;}
.salary_scroll{overflow:auto;border:1px solid #d9e2ef;background:#fff;margin-top:10px;}
.salary_table{width:100%;border-collapse:collapse;background:#fff;}
.salary_table th{background:#f8fafc;color:#334155;font-weight:normal;text-align:left;padding:9px;border-bottom:1px solid #d9e2ef;white-space:nowrap;}
.salary_table td{padding:8px;border-bottom:1px solid #edf2f7;color:#475569;vertical-align:middle;white-space:nowrap;}
.salary_table .money{text-align:right;color:#1f2937;}
.salary_status{display:inline-block;padding:0 8px;height:22px;line-height:22px;background:#eef2ff;color:#3949ab;}
.salary_status_done{background:#e8f7ef;color:#16803c;}
.salary_tip{color:#64748b;line-height:24px;margin:0 0 10px 0;}
.salary_warn{border:1px solid #fde68a;background:#fffbeb;color:#92400e;padding:8px 10px;margin-bottom:10px;}
@media (max-width:900px){.salary_summary{grid-template-columns:repeat(2,1fr);}.salary_check_grid{grid-template-columns:repeat(2,1fr);}}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li class="on"><a href="#">工资条发放确认</a></li>
		</ul>
	</div>
	<div class="salary_page">
		<div class="salary_toolbar">
			<a class="btn btn_gray" href="{{backUrl}}">返回</a>
		</div>
		<div class="salary_panel">
			<div class="salary_panel_hd">发放工资条前确认</div>
			<div class="salary_panel_bd">
				<div class="salary_summary">
					<div class="salary_summary_item"><label>工资月份</label><strong>{{period['payroll_month']}}</strong></div>
					<div class="salary_summary_item"><label>来源</label><strong>{% if sourcePage=='archive' %}归档记录{% else %}工资表核算{% endif %}</strong></div>
					<div class="salary_summary_item"><label>工资表状态</label><strong><span class="salary_status {% if period['status']=='approved' or period['status']=='archived' %}salary_status_done{% endif %}">{{period['status_name']}}</span></strong></div>
					<div class="salary_summary_item"><label>总人数</label><strong>{{period['employee_count']}}</strong></div>
					<div class="salary_summary_item"><label>已发工资条</label><strong>{{publishedCount}} 人</strong></div>
				</div>
				<div class="salary_summary">
					<div class="salary_summary_item"><label>应发总额</label><strong>{{period['earning_total']}}</strong></div>
					<div class="salary_summary_item"><label>应扣总额</label><strong>{{period['deduction_total']}}</strong></div>
					<div class="salary_summary_item"><label>实发总额</label><strong>{{period['net_total']}}</strong></div>
				</div>
				{% if sourcePage=='archive' %}
				<div class="salary_warn">当前从归档记录进入发放，请确认月份和人员范围后再发放。</div>
				{% endif %}
				<form method="post" action="{{helper.createUrl(['p':'salary/sendpayslip'])}}" onsubmit="return confirmPayslipRange();">
					<input type="hidden" name="id" value="{{period['id']}}" />
					<input type="hidden" name="from" value="{{sourcePage}}" />
					<input type="hidden" name="archive_id" value="{{archiveId}}" />
					<div class="salary_range">
						<label><input type="radio" name="range_type" value="all" checked="checked" onclick="togglePayslipScope();" /> 全部</label>
						<label><input type="radio" name="range_type" value="department" onclick="togglePayslipScope();" /> 部门</label>
						<label><input type="radio" name="range_type" value="employee" onclick="togglePayslipScope();" /> 指定员工</label>
					</div>
					<div id="department_scope" class="salary_scope">
						<div class="salary_tip">选择需要发放工资条的部门：</div>
						<div class="salary_check_grid">
							{% for department in departments %}
							<label><input type="checkbox" name="departments[]" value="{{department}}" /> {{department}}</label>
							{% endfor %}
						</div>
					</div>
					<div id="employee_scope" class="salary_scope">
						<div class="salary_tip">
							选择需要发放工资条的员工：
							<button type="button" class="salary_btn salary_btn_gray" onclick="checkAllEmployees(true);">全选</button>
							<button type="button" class="salary_btn salary_btn_gray" onclick="checkAllEmployees(false);">清空</button>
						</div>
						<div class="salary_scroll">
							<table class="salary_table">
								<tr>
									<th>选择</th>
									<th>员工</th>
									<th>手机号</th>
									<th>部门</th>
									<th class="money">应发总额</th>
									<th class="money">应扣总额</th>
									<th class="money">实发总额</th>
									<th>工资条</th>
								</tr>
								{% for row in rows %}
								<tr>
									<td><input type="checkbox" class="employee_check" name="employee_ids[]" value="{{row['employee_id']}}" /></td>
									<td>{{row['employee_name']}}</td>
									<td>{{row['employee_no']}}</td>
									<td>{{row['department_name']}}</td>
									<td class="money">{{row['earning_total']}}</td>
									<td class="money">{{row['deduction_total']}}</td>
									<td class="money">{{row['net_amount']}}</td>
									<td>{% if row['is_published'] %}已发{% else %}未发{% endif %}</td>
								</tr>
								{% endfor %}
							</table>
						</div>
					</div>
					<div style="margin-top:12px;">
						<button class="salary_btn" type="submit">确认发放工资条</button>
						<a class="salary_btn salary_btn_gray" href="{{backUrl}}">取消</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
function getPayslipRangeType() {
	var radios = document.getElementsByName('range_type');
	for (var i = 0; i < radios.length; i++) {
		if (radios[i].checked) {
			return radios[i].value;
		}
	}
	return 'all';
}
function togglePayslipScope() {
	var rangeType = getPayslipRangeType();
	document.getElementById('department_scope').className = rangeType == 'department' ? 'salary_scope salary_scope_on' : 'salary_scope';
	document.getElementById('employee_scope').className = rangeType == 'employee' ? 'salary_scope salary_scope_on' : 'salary_scope';
}
function checkAllEmployees(checked) {
	var boxes = document.getElementsByClassName('employee_check');
	for (var i = 0; i < boxes.length; i++) {
		boxes[i].checked = checked;
	}
}
function confirmPayslipRange() {
	var rangeType = getPayslipRangeType();
	if (rangeType == 'department') {
		var departments = document.getElementsByName('departments[]');
		var hasDepartment = false;
		for (var i = 0; i < departments.length; i++) {
			if (departments[i].checked) {
				hasDepartment = true;
				break;
			}
		}
		if (!hasDepartment) {
			alert('请先选择要发放的部门');
			return false;
		}
	}
	if (rangeType == 'employee') {
		var employees = document.getElementsByClassName('employee_check');
		var hasEmployee = false;
		for (var j = 0; j < employees.length; j++) {
			if (employees[j].checked) {
				hasEmployee = true;
				break;
			}
		}
		if (!hasEmployee) {
			alert('请先选择要发放的员工');
			return false;
		}
	}
	return confirm('确定按当前范围发放工资条吗？');
}
togglePayslipScope();
</script>
