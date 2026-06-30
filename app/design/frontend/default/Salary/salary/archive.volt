<style>
.salary_page{padding:18px;}
.salary_toolbar{margin-bottom:12px;}
.salary_toolbar .btn{display:inline-block;background:#64748b;color:#fff;padding:0 14px;line-height:30px;height:30px;text-decoration:none;margin-right:8px;}
.salary_table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #d9e2ef;}
.salary_table th{background:#f8fafc;color:#334155;font-weight:normal;text-align:left;padding:10px;border-bottom:1px solid #d9e2ef;}
.salary_table td{padding:10px;border-bottom:1px solid #edf2f7;color:#475569;vertical-align:top;}
.salary_table .money{text-align:right;color:#1f2937;}
.salary_empty{border:1px solid #d9e2ef;background:#fbfdff;padding:18px;color:#64748b;}
.salary_link_btn{border:0;background:none;color:#4560e6;cursor:pointer;padding:0;font-size:12px;}
.inline_form{display:inline-block;margin:0 8px 4px 0;}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li class="on"><a href="#">工资表归档记录</a></li>
		</ul>
	</div>
	<div class="salary_page">
		<div class="salary_toolbar">
			<a class="btn" href="{{helper.createUrl(['p':'salary/payroll'])}}">返回工资表核算</a>
		</div>
		{% if periods is empty %}
		<div class="salary_empty">暂无归档工资表。</div>
		{% else %}
		<table class="salary_table">
			<tr>
				<th>工资月份</th>
				<th>来源</th>
				<th>人数</th>
				<th class="money">应发合计</th>
				<th class="money">扣款合计</th>
				<th class="money">实发合计</th>
				<th>归档时间</th>
				<th>工资条</th>
				<th>操作</th>
			</tr>
			{% for period in periods %}
			<tr>
				<td>{{period['payroll_month']}}</td>
				<td>{{period['source_label']}}</td>
				<td>{{period['row_count']}}</td>
				<td class="money">{{period['earning_total']}}</td>
				<td class="money">{{period['deduction_total']}}</td>
				<td class="money">{{period['net_total']}}</td>
				<td>{{period['archived_time']}}</td>
				<td>已发 {{period['published_count']}} 人</td>
				<td>
					{% if canSendPayslip %}
					<form class="inline_form" method="post" action="{{helper.createUrl(['p':'salary/sendpayslip'])}}" onsubmit="return confirm('确定按归档工资表发工资条吗？');">
						<input type="hidden" name="id" value="{{period['payroll_period_id']}}" />
						<button class="salary_link_btn" type="submit">发工资条</button>
					</form>
					{% endif %}
					<form class="inline_form" method="post" action="{{helper.createUrl(['p':'salary/restorearchive'])}}" onsubmit="return confirm('确定恢复到工资表核算重新核算吗？');">
						<input type="hidden" name="id" value="{{period['id']}}" />
						<button class="salary_link_btn" type="submit">恢复</button>
					</form>
				</td>
			</tr>
			{% endfor %}
		</table>
		{% endif %}
	</div>
</div>
