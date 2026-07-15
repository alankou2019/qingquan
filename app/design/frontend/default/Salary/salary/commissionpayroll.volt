<style>
.commission_payroll_page{padding:18px;}
.commission_toolbar{margin-bottom:12px;}
.commission_btn{display:inline-block;background:#4560e6;color:#fff;padding:0 14px;line-height:30px;height:30px;text-decoration:none;margin-right:8px;border:0;cursor:pointer;}
.commission_btn_gray{background:#64748b;}
.commission_filter{border:1px solid #d9e2ef;background:#fbfdff;padding:12px 14px;margin-bottom:12px;color:#475569;}
.commission_filter input[type=text]{height:28px;line-height:28px;border:1px solid #cbd5e1;padding:0 8px;width:90px;}
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
.inline_form{display:inline-block;margin:0 8px 4px 0;}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li><a href="{{helper.createUrl(['p':'salary/commission'])}}">提成项目设置</a></li>
			<li><a href="{{helper.createUrl(['p':'salary/commissionestimate'])}}">月收入测算</a></li>
			<li class="on"><a href="{{helper.createUrl(['p':'salary/commissionpayroll'])}}">月提成核算</a></li>
			<li><a href="{{helper.createUrl(['p':'salary/commissionarchive'])}}">提成归档记录</a></li>
		</ul>
	</div>
	<div class="commission_payroll_page">
		<div class="commission_toolbar">
			<a class="commission_btn commission_btn_gray" href="{{helper.createUrl(['p':'salary/commission'])}}">提成项目设置</a>
			<a class="commission_btn commission_btn_gray" href="{{helper.createUrl(['p':'salary/commissionestimate'])}}">月收入测算</a>
			<a class="commission_btn commission_btn_gray" href="{{helper.createUrl(['p':'salary/commissionarchive'])}}">提成归档记录</a>
			<a class="commission_btn commission_btn_gray" href="{{helper.createUrl(['p':'salary/index'])}}">返回薪酬首页</a>
		</div>
		<form class="commission_filter" method="get" action="{{helper.createUrl(['p':'salary/commissionpayroll'])}}">
			<input type="hidden" name="p" value="salary/commissionpayroll" />
			提成月份 <input type="text" name="commission_month" value="{{commissionMonth}}" placeholder="2026-07" />
			<button class="commission_btn" type="submit">查看</button>
		</form>
		{% if !period %}
			<div class="commission_empty">
				当前月份还没有提成核算表。请先在提成项目设置中维护项目和规则，然后生成本月提成表。
				<form class="inline_form" method="post" action="{{helper.createUrl(['p':'salary/generatecommission'])}}" style="margin-left:10px;">
					<input type="hidden" name="commission_month" value="{{commissionMonth}}" />
					<button class="commission_btn" type="submit">生成本月提成表</button>
				</form>
			</div>
		{% else %}
			<div class="commission_tip">
				当前提成表：{{period['commission_month']}}　
				状态：<span class="commission_status">{{period['status_name']}}</span>
				　参与人数：{{period['employee_count']}}　匹配人数：{{period['matched_count']}}　提成合计：{{period['total_amount']}}
			</div>
			<form method="post" action="{{helper.createUrl(['p':'salary/savecommissionpayroll'])}}">
				<input type="hidden" name="id" value="{{period['id']}}" />
				<div class="commission_scroll">
					<table class="commission_table">
						<tr>
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
