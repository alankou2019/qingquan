<style>
.salary_page{padding:18px;}
.salary_toolbar{margin-bottom:12px;}
.salary_toolbar .btn,.salary_btn{display:inline-block;background:#64748b;color:#fff;padding:0 14px;line-height:30px;height:30px;text-decoration:none;margin-right:8px;border:0;cursor:pointer;}
.salary_table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #d9e2ef;}
.salary_table th{background:#f8fafc;color:#334155;font-weight:normal;text-align:left;padding:10px;border-bottom:1px solid #d9e2ef;white-space:nowrap;}
.salary_table td{padding:10px;border-bottom:1px solid #edf2f7;color:#475569;vertical-align:middle;white-space:nowrap;}
.salary_table .money{text-align:right;color:#1f2937;}
.salary_empty{border:1px solid #d9e2ef;background:#fbfdff;padding:18px;color:#64748b;}
.salary_summary{border:1px solid #d9e2ef;background:#fbfdff;padding:12px 14px;margin-bottom:12px;color:#475569;line-height:24px;}
.salary_filter{margin-bottom:12px;}
.salary_filter a{display:inline-block;padding:0 10px;height:28px;line-height:28px;border:1px solid #d9e2ef;background:#fff;color:#475569;text-decoration:none;margin-right:6px;}
.salary_filter a.on{background:#4560e6;border-color:#4560e6;color:#fff;}
.salary_export{border:1px solid #d9e2ef;background:#fbfdff;padding:12px 14px;margin-bottom:12px;color:#475569;}
.salary_export_title{font-weight:bold;color:#334155;margin-bottom:8px;}
.salary_export label{margin-right:16px;line-height:28px;}
.salary_export_scope{display:none;border-top:1px solid #e2e8f0;margin-top:8px;padding-top:8px;}
.salary_export_scope_on{display:block;}
.salary_check_grid{display:grid;grid-template-columns:repeat(4,1fr);gap:6px 12px;}
.salary_export .salary_btn{background:#4560e6;margin-top:10px;}
.salary_status{display:inline-block;padding:0 8px;height:22px;line-height:22px;background:#eef2ff;color:#3949ab;}
.salary_status_done{background:#e8f7ef;color:#16803c;}
.salary_status_warn{background:#fff7e6;color:#a15c00;}
.salary_scroll{overflow:auto;background:#fff;}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li><a href="{{helper.createUrl(['p':'salary/payslip'])}}">工资条发放</a></li>
			<li class="on"><a href="#">查看确认</a></li>
		</ul>
	</div>
	<div class="salary_page">
		<div class="salary_toolbar">
			<a class="btn" href="{{backUrl}}">返回</a>
		</div>
		{% if period %}
		<div class="salary_summary">
			工资月份：{{period['payroll_month']}}　
			来源：{{period['source_label']}}　
			已发：{{period['published_count']}} 人　
			已查看：{{period['viewed_count']}} 人　
			已确认：{{period['confirmed_count']}} 人　
			未确认：{{period['unconfirmed_count']}} 人
		</div>
		<div class="salary_filter">
			<a class="{% if status=='all' %}on{% endif %}" href="{{helper.createUrl(['p':'salary/payslipdetail','id':period['id'],'from':sourcePage,'status':'all'])}}">全部</a>
			<a class="{% if status=='unviewed' %}on{% endif %}" href="{{helper.createUrl(['p':'salary/payslipdetail','id':period['id'],'from':sourcePage,'status':'unviewed'])}}">未查看</a>
			<a class="{% if status=='viewed_unconfirmed' %}on{% endif %}" href="{{helper.createUrl(['p':'salary/payslipdetail','id':period['id'],'from':sourcePage,'status':'viewed_unconfirmed'])}}">已查看未确认</a>
			<a class="{% if status=='confirmed' %}on{% endif %}" href="{{helper.createUrl(['p':'salary/payslipdetail','id':period['id'],'from':sourcePage,'status':'confirmed'])}}">已确认</a>
		</div>
		<div class="salary_export">
			<div class="salary_export_title">导出确认结果</div>
			<form method="get" action="{{helper.createUrl(['p':'salary/payslipexport'])}}" onsubmit="return checkPayslipExportScope();">
				<input type="hidden" name="id" value="{{period['id']}}" />
				<input type="hidden" name="status" value="{{status}}" />
				<input type="hidden" name="from" value="{{sourcePage}}" />
				<label><input type="radio" name="range_type" value="all" checked="checked" onclick="togglePayslipExportScope();" /> 全部</label>
				<label><input type="radio" name="range_type" value="department" onclick="togglePayslipExportScope();" /> 部门</label>
				<label><input type="radio" name="range_type" value="employee" onclick="togglePayslipExportScope();" /> 指定员工</label>
				<div id="export_department_scope" class="salary_export_scope">
					<div>选择导出部门：</div>
					<div class="salary_check_grid">
						{% for department in departments %}
						<label><input type="checkbox" name="departments[]" value="{{department}}" /> {{department}}</label>
						{% endfor %}
					</div>
				</div>
				<div id="export_employee_scope" class="salary_export_scope">
					<div>
						选择导出员工：
						<button type="button" class="salary_btn" onclick="checkAllExportEmployees(true);">全选</button>
						<button type="button" class="salary_btn" onclick="checkAllExportEmployees(false);">清空</button>
					</div>
					<div class="salary_check_grid">
						{% for employee in employees %}
						<label><input type="checkbox" class="export_employee_check" name="employee_ids[]" value="{{employee['id']}}" /> {{employee['name']}}{% if employee['department_name'] %}（{{employee['department_name']}}）{% endif %}</label>
						{% endfor %}
					</div>
				</div>
				<button class="salary_btn" type="submit">导出Excel</button>
			</form>
		</div>
		{% endif %}
		{% if items is empty %}
		<div class="salary_empty">当前筛选条件下暂无工资条明细。</div>
		{% else %}
		<div class="salary_scroll">
			<table class="salary_table">
				<tr>
					<th>员工</th>
					<th>手机号</th>
					<th>部门</th>
					<th class="money">应发</th>
					<th class="money">扣款</th>
					<th class="money">实发</th>
					<th>状态</th>
					<th>发放时间</th>
					<th>查看时间</th>
					<th>确认时间</th>
				</tr>
				{% for item in items %}
				<tr>
					<td>{{item['employee_name']}}</td>
					<td>{{item['employee_no']}}</td>
					<td>{{item['department_name']}}</td>
					<td class="money">{{item['earning_total']}}</td>
					<td class="money">{{item['deduction_total']}}</td>
					<td class="money">{{item['net_amount']}}</td>
					<td>
						<span class="salary_status {% if item['confirm_status']=='已确认' %}salary_status_done{% elseif item['confirm_status']=='已查看未确认' %}salary_status_warn{% endif %}">{{item['confirm_status']}}</span>
					</td>
					<td>{{item['published_time']}}</td>
					<td>{{item['viewed_time']}}</td>
					<td>{{item['confirmed_time']}}</td>
				</tr>
				{% endfor %}
			</table>
		</div>
		{% endif %}
	</div>
</div>
<script type="text/javascript">
function getPayslipExportRangeType() {
	var radios = document.getElementsByName('range_type');
	for (var i = 0; i < radios.length; i++) {
		if (radios[i].checked) {
			return radios[i].value;
		}
	}
	return 'all';
}
function togglePayslipExportScope() {
	var rangeType = getPayslipExportRangeType();
	document.getElementById('export_department_scope').className = rangeType == 'department' ? 'salary_export_scope salary_export_scope_on' : 'salary_export_scope';
	document.getElementById('export_employee_scope').className = rangeType == 'employee' ? 'salary_export_scope salary_export_scope_on' : 'salary_export_scope';
}
function checkAllExportEmployees(checked) {
	var boxes = document.getElementsByClassName('export_employee_check');
	for (var i = 0; i < boxes.length; i++) {
		boxes[i].checked = checked;
	}
}
function checkPayslipExportScope() {
	var rangeType = getPayslipExportRangeType();
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
			alert('请先选择要导出的部门');
			return false;
		}
	}
	if (rangeType == 'employee') {
		var employees = document.getElementsByClassName('export_employee_check');
		var hasEmployee = false;
		for (var j = 0; j < employees.length; j++) {
			if (employees[j].checked) {
				hasEmployee = true;
				break;
			}
		}
		if (!hasEmployee) {
			alert('请先选择要导出的员工');
			return false;
		}
	}
	return true;
}
togglePayslipExportScope();
</script>
