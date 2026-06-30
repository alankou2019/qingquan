<style>
.salary_page{padding:18px;}
.salary_toolbar{margin-bottom:12px;}
.salary_toolbar .btn,.salary_btn{display:inline-block;background:#4560e6;color:#fff;padding:0 14px;line-height:30px;height:30px;text-decoration:none;margin-right:8px;border:0;cursor:pointer;}
.salary_toolbar .btn_gray,.salary_btn_gray{background:#64748b;}
.salary_filter{border:1px solid #d9e2ef;background:#fbfdff;padding:12px 14px;margin-bottom:12px;color:#475569;}
.salary_filter input[type=text]{height:28px;line-height:28px;border:1px solid #cbd5e1;padding:0 8px;width:90px;}
.salary_tip{color:#64748b;line-height:24px;margin:0 0 12px 0;}
.salary_status{display:inline-block;padding:0 8px;height:24px;line-height:24px;background:#eef2ff;color:#3949ab;}
.salary_status_done{background:#e8f7ef;color:#16803c;}
.salary_status_warn{background:#fff7e6;color:#a15c00;}
.salary_scroll{overflow:auto;border:1px solid #d9e2ef;background:#fff;}
.salary_table{width:100%;border-collapse:collapse;background:#fff;}
.salary_sheet{min-width:1100px;}
.salary_table th{background:#f8fafc;color:#334155;font-weight:normal;text-align:left;padding:9px;border-bottom:1px solid #d9e2ef;white-space:nowrap;}
.salary_table td{padding:8px;border-bottom:1px solid #edf2f7;color:#475569;vertical-align:middle;white-space:nowrap;}
.salary_sheet input[type=text]{width:88px;height:26px;line-height:26px;border:1px solid #cbd5e1;padding:0 6px;text-align:right;}
.salary_sheet input[readonly]{background:#f1f5f9;color:#64748b;}
.salary_empty{border:1px solid #d9e2ef;background:#fbfdff;padding:18px;color:#64748b;}
.salary_link_btn{border:0;background:none;color:#4560e6;cursor:pointer;padding:0;font-size:12px;}
.inline_form{display:inline-block;margin:0 8px 4px 0;}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li class="on"><a href="#">工资表核算</a></li>
		</ul>
	</div>
	<div class="salary_page">
		<div class="salary_toolbar">
			<a class="btn btn_gray" href="{{helper.createUrl(['p':'salary/archive'])}}">归档记录</a>
			<a class="btn btn_gray" href="{{helper.createUrl(['p':'salary/project'])}}">工资项目设置</a>
			<a class="btn btn_gray" href="{{helper.createUrl(['p':'salary/index'])}}">返回薪酬首页</a>
		</div>
		<form class="salary_filter" method="get" action="{{helper.createUrl(['p':'salary/payroll'])}}">
			<input type="hidden" name="p" value="salary/payroll" />
			工资月份 <input type="text" name="payroll_month" value="{{payrollMonth}}" placeholder="2026-06" />
			<button class="salary_btn" type="submit">查看</button>
		</form>
		{% if !period %}
			<div class="salary_empty">
				当前月份还没有工资表。请先在工资项目设置中维护初始工资表，然后从初始工资表生成本月工资表。
				<form class="inline_form" method="post" action="{{helper.createUrl(['p':'salary/generatepayroll'])}}" style="margin-left:10px;">
					<input type="hidden" name="payroll_month" value="{{payrollMonth}}" />
					<button class="salary_btn" type="submit">从初始工资表生成</button>
				</form>
			</div>
		{% else %}
			<div class="salary_tip">
				当前工资表：{{period['payroll_month']}}　
				状态：<span class="salary_status {% if period['status']=='approved' %}salary_status_done{% elseif period['status']=='submitted' %}salary_status_warn{% endif %}">{{period['status_name']}}</span>
				　应发合计：{{period['earning_total']}}　扣款合计：{{period['deduction_total']}}　实发合计：{{period['net_total']}}
			</div>
			<form method="post" action="{{helper.createUrl(['p':'salary/savepayroll'])}}">
				<input type="hidden" name="id" value="{{period['id']}}" />
				<div class="salary_scroll">
					<table class="salary_table salary_sheet">
						<tr>
							<th>员工</th>
							<th>手机号</th>
							<th>部门</th>
							{% for project in projects %}
								{% if project['status']=='active' and project['deleted_at']==0 %}
								<th>{{project['name']}}<br />{{project['calculation_mode_label']}}</th>
								{% endif %}
							{% endfor %}
							<th>应发</th>
							<th>扣款</th>
							<th>实发</th>
						</tr>
						{% for row in payrollRows %}
						<tr>
							<td>{{row['employee_name']}}</td>
							<td>{{row['employee_no']}}</td>
							<td>{{row['department_name']}}</td>
							{% for project in projects %}
								{% if project['status']=='active' and project['deleted_at']==0 %}
								<td>
									<input type="text" name="amount[{{row['employee_id']}}][{{project['id']}}]" value="{% if row['values'][project['id']] is defined %}{{row['values'][project['id']]}}{% else %}0.00{% endif %}" {% if !period['can_edit'] or project['calculation_mode']=='formula' %}readonly="readonly"{% endif %} />
								</td>
								{% endif %}
							{% endfor %}
							<td>{{row['earning_total']}}</td>
							<td>{{row['deduction_total']}}</td>
							<td>{{row['net_amount']}}</td>
						</tr>
						{% elsefor %}
						<tr><td colspan="50" class="salary_empty">当前工资表暂无员工数据</td></tr>
						{% endfor %}
					</table>
				</div>
				<div style="margin-top:10px;">
					{% if period['can_edit'] %}
					<button class="salary_btn" type="submit">保存核算表</button>
					{% endif %}
				</div>
			</form>
			<div style="margin-top:10px;">
				{% if period['can_submit_audit'] %}
				<form class="inline_form" method="post" action="{{helper.createUrl(['p':'salary/submitreview'])}}" onsubmit="return confirm('确定提交工资表审核吗？');">
					<input type="hidden" name="id" value="{{period['id']}}" />
					<button class="salary_link_btn" type="submit">提交审核</button>
				</form>
				{% endif %}
				{% if period['can_publish'] and canSendPayslip %}
				<form class="inline_form" method="post" action="{{helper.createUrl(['p':'salary/sendpayslip'])}}" onsubmit="return confirm('确定按当前工资表发工资条吗？');">
					<input type="hidden" name="id" value="{{period['id']}}" />
					<button class="salary_link_btn" type="submit">发工资条</button>
				</form>
				{% endif %}
				{% if period['can_archive'] %}
				<form class="inline_form" method="post" action="{{helper.createUrl(['p':'salary/archivepayroll'])}}" onsubmit="return confirm('归档后将转入归档记录，确定归档吗？');">
					<input type="hidden" name="id" value="{{period['id']}}" />
					<button class="salary_link_btn" type="submit">归档</button>
				</form>
				{% endif %}
			</div>
		{% endif %}
	</div>
</div>
