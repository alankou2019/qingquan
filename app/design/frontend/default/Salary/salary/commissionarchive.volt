<style>
.commission_archive_page{padding:18px;}
.commission_btn{display:inline-block;background:#4560e6;color:#fff;padding:0 14px;line-height:30px;height:30px;text-decoration:none;margin-right:8px;border:0;cursor:pointer;}
.commission_btn_gray{background:#64748b;}
.commission_filter{border:1px solid #d9e2ef;background:#fbfdff;padding:12px 14px;margin-bottom:12px;color:#475569;}
.commission_filter input[type=text]{height:28px;line-height:28px;border:1px solid #cbd5e1;padding:0 8px;width:98px;margin-right:8px;}
.commission_table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #d9e2ef;}
.commission_table th{background:#f8fafc;color:#334155;font-weight:normal;text-align:left;padding:9px;border-bottom:1px solid #d9e2ef;white-space:nowrap;}
.commission_table td{padding:9px;border-bottom:1px solid #edf2f7;color:#475569;vertical-align:middle;}
.commission_link{color:#4560e6;text-decoration:none;cursor:pointer;background:none;border:0;padding:0;font-size:12px;margin-right:8px;}
.commission_empty{padding:18px;color:#94a3b8;text-align:center;}
.inline_form{display:inline-block;margin:0;}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li><a href="{{helper.createUrl(['p':'salary/commission'])}}">提成项目设置</a></li>
			<li><a href="{{helper.createUrl(['p':'salary/commissionestimate'])}}">月收入测算</a></li>
			<li><a href="{{helper.createUrl(['p':'salary/commissionpayroll'])}}">月提成核算</a></li>
			<li class="on"><a href="{{helper.createUrl(['p':'salary/commissionarchive'])}}">提成归档记录</a></li>
			<li style="float:right;width:140px;border-left:1px solid #efefef;border-right:0;"><a href="{{helper.createUrl(['p':'salary/index'])}}">返回薪酬首页</a></li>
		</ul>
	</div>
	<div class="commission_archive_page">
		<form class="commission_filter" method="get" action="{{helper.createUrl(['p':'salary/commissionarchive'])}}">
			<input type="hidden" name="p" value="salary/commissionarchive" />
			归档月份 <input type="text" name="commission_month" value="{{filter['commission_month']}}" placeholder="2026-07" />
			部门 <input type="text" name="department_name" value="{{filter['department_name']}}" placeholder="部门名称" />
			员工 <input type="text" name="employee_name" value="{{filter['employee_name']}}" placeholder="员工姓名" />
			<button class="commission_btn" type="submit">查询</button>
			<a class="commission_btn commission_btn_gray" href="{{helper.createUrl(['p':'salary/commissionpayroll'])}}">月提成核算</a>
		</form>
		<table class="commission_table">
			<tr><th>归档月份</th><th>归档时间</th><th>参与人数</th><th>规则匹配人数</th><th>提成合计</th><th>操作</th></tr>
			{% for item in archives %}
			<tr>
				<td>{{item['commission_month']}}</td>
				<td>{{item['archived_time']}}</td>
				<td>{{item['employee_count']}}</td>
				<td>{{item['matched_count']}}</td>
				<td>{{item['total_amount']}}</td>
				<td>
					<a class="commission_link" href="{{helper.createUrl(['p':'salary/commissionarchiveview','id':item['id']])}}">查看</a>
					<form class="inline_form" method="post" action="{{helper.createUrl(['p':'salary/restorecommissionarchive'])}}" onsubmit="return confirm('恢复后将回到月提成核算，当前归档记录会移除。确认恢复吗？');"><input type="hidden" name="id" value="{{item['id']}}" /><button class="commission_link" type="submit">恢复</button></form>
					<form class="inline_form" method="post" action="{{helper.createUrl(['p':'salary/deletecommissionarchive'])}}" onsubmit="return confirm('删除后归档记录不再显示，服务器保留备份六个月。确认删除吗？');"><input type="hidden" name="id" value="{{item['id']}}" /><button class="commission_link" type="submit">删除</button></form>
				</td>
			</tr>
			{% elsefor %}
			<tr><td colspan="6" class="commission_empty">暂无提成归档记录。</td></tr>
			{% endfor %}
		</table>
	</div>
</div>
