<style>
.salary_page{padding:18px;}
.salary_toolbar{margin-bottom:12px;}
.salary_toolbar .btn{display:inline-block;background:#4560e6;color:#fff;padding:0 14px;line-height:30px;height:30px;text-decoration:none;margin-right:8px;}
.salary_toolbar .btn_gray{background:#64748b;}
.salary_table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #d9e2ef;}
.salary_table th{background:#f8fafc;color:#334155;font-weight:normal;text-align:left;padding:10px;border-bottom:1px solid #d9e2ef;}
.salary_table td{padding:10px;border-bottom:1px solid #edf2f7;color:#475569;}
.salary_table .money{text-align:right;color:#1f2937;}
.salary_table .operate{text-align:center;white-space:nowrap;}
.salary_status{display:inline-block;padding:0 8px;height:24px;line-height:24px;background:#eef2ff;color:#3949ab;}
.salary_status_done{background:#e8f7ef;color:#16803c;}
.salary_empty{border:1px solid #d9e2ef;background:#fbfdff;padding:18px;color:#64748b;}
.salary_tip{color:#64748b;line-height:24px;margin:0 0 12px 0;}
.salary_link{color:#4560e6;text-decoration:none;}
.salary_link_btn{border:0;background:none;color:#4560e6;cursor:pointer;padding:0;font-size:12px;}
.salary_disabled{color:#94a3b8;}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li class="on"><a href="#">工资核算</a></li>
			<li><a href="{{helper.createUrl(['p':'salary/payslip'])}}">工资条发放</a></li>
		</ul>
	</div>
	<div class="salary_page">
		<div class="salary_toolbar">
			<a class="btn" href="javascript:void(0);">Excel导入</a>
			<a class="btn btn_gray" href="{{helper.createUrl(['p':'salary/index'])}}">返回薪酬首页</a>
		</div>
		<div class="salary_tip">工资条来源于月工资表归档记录或Excel导入后的月工资表。每个已归档记录可发放工资条。</div>
		{% if periods is empty %}
		<div class="salary_empty">暂无月工资表归档记录。</div>
		{% else %}
		<table class="salary_table">
			<thead>
				<tr>
					<th>工资月份</th>
					<th>来源</th>
					<th>状态</th>
					<th>人数</th>
					<th class="money">应发合计</th>
					<th class="money">扣款合计</th>
					<th class="money">实发合计</th>
					<th>归档时间</th>
					<th>发放时间</th>
					<th class="operate">操作</th>
				</tr>
			</thead>
			<tbody>
				{% for period in periods %}
				<tr>
					<td>{{period['payroll_month']}}</td>
					<td>{{period['source_label']}}</td>
					<td>
						<span class="salary_status {% if period['status']=='published' %}salary_status_done{% endif %}">{{period['status_name']}}</span>
					</td>
					<td>{{period['row_count']}}</td>
					<td class="money">{{period['earning_total']}}</td>
					<td class="money">{{period['deduction_total']}}</td>
					<td class="money">{{period['net_total']}}</td>
					<td>{{period['archived_time']}}</td>
					<td>{{period['published_time']}}</td>
					<td class="operate">
						{% if period['status']=='published' %}
							<span class="salary_disabled">已发放 {{period['published_count']}} 人</span>
						{% elseif period['can_publish'] and canSendPayslip %}
							<form method="post" action="{{helper.createUrl(['p':'salary/sendpayslip','id':period['id']])}}" onsubmit="return confirm('确定给本月工资表的员工发工资条吗？');">
								<button class="salary_link_btn" type="submit">发工资条</button>
							</form>
						{% elseif !canSendPayslip %}
							<span class="salary_disabled">工资条未开通</span>
						{% else %}
							<span class="salary_disabled">归档后发放</span>
						{% endif %}
					</td>
				</tr>
				{% endfor %}
			</tbody>
		</table>
		{% endif %}
	</div>
</div>
