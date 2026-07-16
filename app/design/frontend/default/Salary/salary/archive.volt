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
			<li style="float:right;width:140px;border-left:1px solid #efefef;border-right:0;"><a href="{{helper.createUrl(['p':'salary/index'])}}">返回薪酬首页</a></li>
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
				<th class="money">应发总额</th>
				<th class="money">应扣总额</th>
				<th class="money">实发总额</th>
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
				<td>
					已发 {{period['published_count']}} 人<br />
					已查看 {{period['viewed_count']}} 人，已确认 {{period['confirmed_count']}} 人
					{% if period['unconfirmed_count'] > 0 %}<br />未确认 {{period['unconfirmed_count']}} 人{% endif %}
				</td>
				<td>
					{% if canSendPayslip %}
					<a class="salary_link_btn" href="{{helper.createUrl(['p':'salary/payslipconfirm','id':period['payroll_period_id'],'archive_id':period['id'],'from':'archive'])}}">发工资条</a>
					<a class="salary_link_btn" href="{{helper.createUrl(['p':'salary/payslipdetail','id':period['payroll_period_id'],'from':'archive'])}}">查看确认</a>
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
