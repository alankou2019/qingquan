<style>
.salary_page{padding:18px;}
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
	{{ partial('salary_primary_navigation') }}
	<div class="salary_secondary_navigation"><a class="on" href="#">工资条发放</a></div>
	<div class="salary_page">
		<div style="margin-bottom:12px;">
			<a class="salary_link" href="{{helper.createUrl(['p':'salary/archive'])}}">查看归档记录</a>
		</div>
		<div class="salary_tip">已发放的工资条会出现在员工手机端的当月薪酬、当年薪酬、往年薪酬页面。</div>
		{% if periods is empty %}
		<div class="salary_empty">暂无工资条发放记录。</div>
		{% else %}
		<table class="salary_table">
			<thead>
				<tr>
					<th>工资月份</th>
					<th>来源</th>
					<th>状态</th>
					<th>工资表人数</th>
					<th>工资条进度</th>
					<th class="money">实发总额</th>
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
					<td>
						已发 {{period['published_count']}}<br />
						已查看 {{period['viewed_count']}}，已确认 {{period['confirmed_count']}}
						{% if period['unconfirmed_count'] > 0 %}<br />未确认 {{period['unconfirmed_count']}}{% endif %}
					</td>
					<td class="money">{{period['net_total']}}</td>
					<td>{{period['published_time']}}</td>
					<td class="operate">
						<a class="salary_link" href="{{helper.createUrl(['p':'salary/payslipdetail','id':period['id']])}}">查看确认</a>
						{% if period['can_publish'] %}
							<br />
							<a class="salary_link" href="{{helper.createUrl(['p':'salary/payslipconfirm','id':period['id'],'from':'payroll'])}}">发工资条</a>
						{% endif %}
					</td>
				</tr>
				{% endfor %}
			</tbody>
		</table>
		{% endif %}
	</div>
</div>
