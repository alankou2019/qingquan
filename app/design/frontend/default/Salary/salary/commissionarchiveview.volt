<style>
.commission_archive_page{padding:18px;}
.commission_btn{display:inline-block;background:#64748b;color:#fff;padding:0 14px;line-height:30px;height:30px;text-decoration:none;margin-right:8px;border:0;cursor:pointer;}
.commission_summary{border:1px solid #d9e2ef;background:#fbfdff;padding:12px 14px;margin-bottom:12px;color:#475569;line-height:24px;}
.commission_table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #d9e2ef;}
.commission_table th{background:#f8fafc;color:#334155;font-weight:normal;text-align:left;padding:9px;border-bottom:1px solid #d9e2ef;white-space:nowrap;}
.commission_table td{padding:9px;border-bottom:1px solid #edf2f7;color:#475569;vertical-align:top;}
.commission_item{display:inline-block;background:#f1f5f9;padding:3px 6px;margin:0 5px 5px 0;line-height:20px;}
.commission_total{font-weight:bold;color:#0f172a;}
</style>
<div class="full_box">
	<div class="head_tab clear"><ul><li><a href="{{helper.createUrl(['p':'salary/commission'])}}">提成项目设置</a></li><li><a href="{{helper.createUrl(['p':'salary/commissionestimate'])}}">月收入测算</a></li><li><a href="{{helper.createUrl(['p':'salary/commissionpayroll'])}}">月提成核算</a></li><li class="on"><a href="{{helper.createUrl(['p':'salary/commissionarchive'])}}">提成归档记录</a></li><li style="float:right;width:140px;border-left:1px solid #efefef;border-right:0;"><a href="{{helper.createUrl(['p':'salary/index'])}}">返回薪酬首页</a></li></ul></div>
	<div class="commission_archive_page">
		<a class="commission_btn" href="{{helper.createUrl(['p':'salary/commissionarchive'])}}">返回归档记录</a>
		<div class="commission_summary">归档月份：{{archive['commission_month']}}　参与人数：{{archive['employee_count']}}　规则匹配人数：{{archive['matched_count']}}　提成合计：<strong>{{archive['total_amount']}}</strong></div>
		<table class="commission_table">
			<tr><th>员工</th><th>部门</th><th>手机号</th><th>提成项目完成量 / 提成额</th><th>提成合计</th><th>备注</th></tr>
			{% for row in rows %}
			<tr>
				<td>{{row['employee_name']}}</td><td>{{row['department_name']}}</td><td>{{row['employee_no']}}</td>
				<td>{% for item in row['items'] %}<span class="commission_item">{{item['project_name']}}：{{item['input_value']}} / {{item['commission_amount']}}</span>{% endfor %}</td>
				<td class="commission_total">{{row['total_amount']}}</td><td>{{row['remark']}}</td>
			</tr>
			{% elsefor %}<tr><td colspan="6">归档快照中没有员工数据。</td></tr>{% endfor %}
		</table>
	</div>
</div>
