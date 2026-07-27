<style>
.salary_page{padding:18px;}
.salary_toolbar{margin-bottom:12px;}
.salary_toolbar .btn,.salary_filter .btn{display:inline-block;background:#64748b;color:#fff;padding:0 14px;line-height:30px;height:30px;text-decoration:none;margin-right:8px;border:0;cursor:pointer;}
.salary_filter{border:1px solid #d9e2ef;background:#fbfdff;padding:12px 14px;margin-bottom:12px;color:#475569;}
.salary_filter label{margin-right:14px;}
.salary_filter input,.salary_filter select{height:28px;line-height:28px;border:1px solid #cbd5e1;padding:0 8px;background:#fff;}
.salary_filter .btn{background:#4560e6;}
.salary_table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #d9e2ef;}
.salary_table th{background:#f8fafc;color:#334155;font-weight:normal;text-align:left;padding:10px;border-bottom:1px solid #d9e2ef;white-space:nowrap;}
.salary_table td{padding:10px;border-bottom:1px solid #edf2f7;color:#475569;vertical-align:middle;}
.salary_table .nowrap{white-space:nowrap;}
.salary_empty{border:1px solid #d9e2ef;background:#fbfdff;padding:18px;color:#64748b;}
.salary_pagebar{margin-top:12px;color:#64748b;}
.salary_pagebar a{display:inline-block;border:1px solid #d9e2ef;background:#fff;color:#475569;text-decoration:none;padding:0 10px;height:28px;line-height:28px;margin-right:6px;}
</style>
<div class="full_box">
	{{ partial('salary_primary_navigation') }}
	<div class="salary_secondary_navigation"><a href="{{helper.createUrl(['p':'salary/archive'])}}">归档记录</a><a href="{{helper.createUrl(['p':'salary/report'])}}">报表统计</a><a class="on" href="#">操作日志</a></div>
	<div class="salary_page">
		<div class="salary_toolbar">
		</div>
		<div class="salary_filter">
			<form method="get" action="{{helper.createUrl(['p':'salary/log'])}}">
				<label>
					工资月份
					<input type="text" name="payroll_month" value="{{filter['payroll_month']}}" placeholder="例如 2026-06" />
				</label>
				<label>
					操作类型
					<select name="action_code">
						<option value="">全部</option>
						{% for code,label in actionLabels %}
						<option value="{{code}}" {% if filter['action_code']==code %}selected="selected"{% endif %}>{{label}}</option>
						{% endfor %}
					</select>
				</label>
				<button type="submit" class="btn">查询</button>
				<a class="btn" href="{{helper.createUrl(['p':'salary/log'])}}">重置</a>
			</form>
		</div>
		{% if logs %}
		<table class="salary_table">
			<thead>
				<tr>
					<th>时间</th>
					<th>操作类型</th>
					<th>工资月份</th>
					<th>对象</th>
					<th>说明</th>
					<th>操作人</th>
					<th>IP</th>
				</tr>
			</thead>
			<tbody>
				{% for log in logs %}
				<tr>
					<td class="nowrap">{{log['created_time']}}</td>
					<td class="nowrap">{{log['action_name']}}</td>
					<td class="nowrap">{% if log['payroll_month'] %}{{log['payroll_month']}}{% else %}-{% endif %}</td>
					<td class="nowrap">{% if log['object_type'] %}{{log['object_type']}} #{{log['object_id']}}{% else %}-{% endif %}</td>
					<td>{{log['summary']}}</td>
					<td class="nowrap">{% if log['operator_name'] %}{{log['operator_name']}}{% else %}ID {{log['operator_id']}}{% endif %}</td>
					<td class="nowrap">{{log['ip']}}</td>
				</tr>
				{% endfor %}
			</tbody>
		</table>
		<div class="salary_pagebar">
			共 {{total}} 条，第 {{page}} / {{pageCount}} 页
			{% if page > 1 %}
			<a href="{{helper.createUrl(['p':'salary/log','payroll_month':filter['payroll_month'],'action_code':filter['action_code'],'page':page-1])}}">上一页</a>
			{% endif %}
			{% if page < pageCount %}
			<a href="{{helper.createUrl(['p':'salary/log','payroll_month':filter['payroll_month'],'action_code':filter['action_code'],'page':page+1])}}">下一页</a>
			{% endif %}
		</div>
		{% else %}
		<div class="salary_empty">暂无薪酬操作日志。</div>
		{% endif %}
	</div>
</div>
