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
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li><a href="{{helper.createUrl(['p':'salary/commission'])}}">提成项目设置</a></li>
			<li><a href="{{helper.createUrl(['p':'salary/commissionestimate'])}}">月收入测算</a></li>
			<li class="on"><a href="{{helper.createUrl(['p':'salary/commissionpayroll'])}}">月提成核算</a></li>
			<li><a href="{{helper.createUrl(['p':'salary/commissionarchive'])}}">提成归档记录</a></li>
			<li style="float:right;width:140px;border-left:1px solid #efefef;border-right:0;"><a href="{{helper.createUrl(['p':'salary/index'])}}">返回薪酬首页</a></li>
		</ul>
	</div>
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
			</div>
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
						<tr id="commission_employee_row_{{row['employee_id']}}">
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
						<tr>
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
<script src="/skin/adminhtml/default/js/salary-inline-delete.js"></script>
<script type="text/javascript">
function updateCommissionPayrollSummary(data) {
	var employeeCount = document.getElementById('commission_employee_count');
	var matchedCount = document.getElementById('commission_matched_count');
	var totalAmount = document.getElementById('commission_total_amount');
	if (employeeCount && typeof data.employee_count != 'undefined') { employeeCount.innerHTML = data.employee_count; }
	if (matchedCount && typeof data.matched_count != 'undefined') { matchedCount.innerHTML = data.matched_count; }
	if (totalAmount && typeof data.total_amount != 'undefined') { totalAmount.innerHTML = data.total_amount; }
}
</script>
