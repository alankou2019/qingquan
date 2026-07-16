<style>
.salary_page{padding:18px;}
.salary_toolbar{margin-bottom:12px;}
.salary_toolbar .btn,.salary_filter .btn{display:inline-block;background:#64748b;color:#fff;padding:0 14px;line-height:30px;height:30px;text-decoration:none;margin-right:8px;border:0;cursor:pointer;}
.salary_filter{border:1px solid #d9e2ef;background:#fbfdff;padding:12px 14px;margin-bottom:12px;color:#475569;line-height:32px;}
.salary_filter label{margin-right:14px;}
.salary_filter input,.salary_filter select{height:28px;line-height:28px;border:1px solid #cbd5e1;padding:0 8px;background:#fff;}
.salary_filter .btn{background:#4560e6;}
.salary_summary{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:12px;}
.salary_summary_item{border:1px solid #d9e2ef;background:#fff;padding:12px;color:#475569;}
.salary_summary_item strong{display:block;font-size:18px;color:#1f2937;margin-top:6px;}
.salary_table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #d9e2ef;}
.salary_table th{background:#f8fafc;color:#334155;font-weight:normal;text-align:left;padding:10px;border-bottom:1px solid #d9e2ef;white-space:nowrap;}
.salary_table td{padding:10px;border-bottom:1px solid #edf2f7;color:#475569;vertical-align:middle;white-space:nowrap;}
.salary_table .money{text-align:right;color:#1f2937;}
.salary_scroll{overflow:auto;background:#fff;}
.salary_empty{border:1px solid #d9e2ef;background:#fbfdff;padding:18px;color:#64748b;}
.salary_pagebar{margin-top:12px;color:#64748b;}
.salary_pagebar a{display:inline-block;border:1px solid #d9e2ef;background:#fff;color:#475569;text-decoration:none;padding:0 10px;height:28px;line-height:28px;margin-right:6px;}
.salary_tip{color:#64748b;line-height:24px;margin:0 0 12px 0;}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li class="on"><a href="#">薪酬统计报表</a></li>
			<li style="float:right;width:140px;border-left:1px solid #efefef;border-right:0;"><a href="{{helper.createUrl(['p':'salary/index'])}}">返回薪酬首页</a></li>
		</ul>
	</div>
	<div class="salary_page">
		<div class="salary_toolbar">
			<a class="btn" href="{{helper.createUrl(['p':'salary/auth'])}}">薪酬管理授权</a>
		</div>
		<form class="salary_filter" method="get" action="{{helper.createUrl(['p':'salary/report'])}}">
			<label>
				工资月份
				<select name="payroll_month">
					<option value="">全部月份</option>
					{% for month in months %}
					<option value="{{month}}" {% if filter['payroll_month']==month %}selected="selected"{% endif %}>{{month}}</option>
					{% endfor %}
				</select>
			</label>
			<label>
				部门
				<select name="department_name">
					<option value="">全部部门</option>
					{% for department in departments %}
					<option value="{{department}}" {% if filter['department_name']==department %}selected="selected"{% endif %}>{{department}}</option>
					{% endfor %}
				</select>
			</label>
			<label>
				员工
				<input type="text" name="keyword" value="{{filter['keyword']}}" placeholder="姓名/手机号" />
			</label>
			<button type="submit" class="btn">查询</button>
			<a class="btn" href="{{helper.createUrl(['p':'salary/report'])}}">重置</a>
			{% if scope['can_export'] %}
			<a class="btn" href="{{helper.createUrl(['p':'salary/reportexport','payroll_month':filter['payroll_month'],'department_name':filter['department_name'],'keyword':filter['keyword']])}}">导出</a>
			{% endif %}
		</form>
		{% if !scope['all'] and summary['row_count']==0 %}
		<div class="salary_tip">当前账号只能查看已授权范围内的薪酬数据，如需查看更多部门或员工，请在“薪酬管理授权”中配置。</div>
		{% endif %}
		<div class="salary_summary">
			<div class="salary_summary_item">记录数<strong>{{summary['row_count']}}</strong></div>
			<div class="salary_summary_item">应发总额<strong>{{summary['earning_total']}}</strong></div>
			<div class="salary_summary_item">应扣总额<strong>{{summary['deduction_total']}}</strong></div>
			<div class="salary_summary_item">实发总额<strong>{{summary['net_total']}}</strong></div>
		</div>
		{% if rows %}
		<div class="salary_scroll">
			<table class="salary_table">
				<thead>
					<tr>
						<th>工资月份</th>
						<th>员工姓名</th>
						<th>手机号</th>
						<th>部门</th>
						<th>状态</th>
						<th>来源</th>
						<th class="money">应发总额</th>
						<th class="money">应扣总额</th>
						<th class="money">实发总额</th>
					</tr>
				</thead>
				<tbody>
					{% for row in rows %}
					<tr>
						<td>{{row['payroll_month']}}</td>
						<td>{{row['employee_name']}}</td>
						<td>{{row['employee_no']}}</td>
						<td>{{row['department_name']}}</td>
						<td>{{row['status_name']}}</td>
						<td>{{row['source_label']}}</td>
						<td class="money">{{row['earning_total']}}</td>
						<td class="money">{{row['deduction_total']}}</td>
						<td class="money">{{row['net_amount']}}</td>
					</tr>
					{% endfor %}
				</tbody>
			</table>
		</div>
		<div class="salary_pagebar">
			第 {{page}} / {{pageCount}} 页
			{% if page > 1 %}
			<a href="{{helper.createUrl(['p':'salary/report','payroll_month':filter['payroll_month'],'department_name':filter['department_name'],'keyword':filter['keyword'],'page':page-1])}}">上一页</a>
			{% endif %}
			{% if page < pageCount %}
			<a href="{{helper.createUrl(['p':'salary/report','payroll_month':filter['payroll_month'],'department_name':filter['department_name'],'keyword':filter['keyword'],'page':page+1])}}">下一页</a>
			{% endif %}
		</div>
		{% else %}
		<div class="salary_empty">当前筛选条件下暂无薪酬数据。</div>
		{% endif %}
	</div>
</div>
