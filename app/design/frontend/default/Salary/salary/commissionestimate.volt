<style>
.commission_estimate_page{padding:18px;color:#334155;}
.commission_tabs{border-bottom:1px solid #d9e2ef;margin-bottom:14px;}
.commission_tabs a{display:inline-block;padding:10px 16px;color:#475569;text-decoration:none;border:1px solid transparent;border-bottom:0;}
.commission_tabs a.on{background:#4560e6;color:#fff;}
.estimate_toolbar{background:#fbfdff;border:1px solid #d9e2ef;padding:12px 14px;margin-bottom:14px;}
.estimate_toolbar select{height:30px;border:1px solid #cbd5e1;padding:0 8px;min-width:320px;}
.estimate_btn{display:inline-block;height:30px;line-height:30px;background:#4560e6;color:#fff;border:0;padding:0 14px;text-decoration:none;cursor:pointer;margin-left:8px;}
.estimate_btn.gray{background:#64748b;}
.estimate_section{margin:0 0 18px 0;}
.estimate_section h3{font-size:15px;margin:0 0 10px 0;color:#1f2937;}
.estimate_tip{color:#64748b;margin:0 0 10px 0;line-height:22px;}
.estimate_scroll{overflow:auto;border:1px solid #d9e2ef;}
.estimate_table{width:100%;border-collapse:collapse;background:#fff;min-width:1240px;}
.estimate_table th{background:#f8fafc;font-weight:normal;text-align:center;padding:9px;border-bottom:1px solid #d9e2ef;white-space:nowrap;}
.estimate_table td{padding:8px;border-bottom:1px solid #edf2f7;white-space:nowrap;text-align:center;}
.estimate_table input[type=text]{width:82px;height:26px;border:1px solid #cbd5e1;padding:0 6px;text-align:center;}
.estimate_rule{min-width:150px;max-width:260px;white-space:normal!important;line-height:20px;color:#475569;}
.estimate_rule_view{display:flex;align-items:center;justify-content:center;gap:8px;}
.estimate_rule_summary{display:inline-block;}
.estimate_rule_edit{border:1px solid #4560e6;background:#fff;color:#4560e6;height:24px;line-height:22px;padding:0 8px;cursor:pointer;white-space:nowrap;}
.estimate_rule_editor{display:none;margin-top:8px;padding:10px;border:1px solid #cbd5e1;background:#f8fafc;color:#334155;min-width:360px;text-align:center;}
.estimate_rule_editor.is_open{display:block;}
.estimate_rule_editor select,.estimate_rule_editor input{height:26px;border:1px solid #cbd5e1;background:#fff;text-align:center;}
.estimate_rule_editor select{padding:0 5px;}
.estimate_rule_editor input{width:76px!important;padding:0 4px!important;}
.estimate_rule_tier{margin:5px 0;white-space:nowrap;}
.estimate_rule_note{margin:6px 0;color:#b45309;white-space:normal;}
.estimate_rule_actions{margin-top:7px;}
.estimate_rule_save,.estimate_rule_cancel{border:0;height:26px;line-height:26px;color:#fff;padding:0 10px;cursor:pointer;}
.estimate_rule_save{background:#4560e6;}.estimate_rule_cancel{background:#64748b;margin-left:6px;}
.estimate_toggle{border:0;padding:0 10px;height:26px;line-height:26px;background:#64748b;color:#fff;cursor:pointer;white-space:nowrap;}
.estimate_toggle.is_disabled{background:#16a34a;}
.estimate_table tr.commission_project_disabled td{color:#94a3b8;}
.estimate_table tr.commission_project_disabled input[type=text]{background:#f8fafc;color:#64748b;}
.estimate_status_text{white-space:nowrap;color:#64748b;}
.estimate_low{background:#eef6ff;color:#185fa7;}
.estimate_mid{background:#ecfdf3;color:#08783e;}
.estimate_high{background:#fff4e5;color:#a34d00;}
.estimate_total td{font-weight:bold;}
.income_formula{margin:12px 0 8px 0;color:#334155;}
.income_grid{border:1px solid #d9e2ef;background:#fbfdff;padding:12px;max-width:880px;}
.income_row{display:grid;grid-template-columns:120px minmax(180px,1fr) 110px 100px;align-items:center;gap:8px;margin:7px 0;}
.income_track{height:18px;background:#e8eef5;}
.income_bar{height:18px;}
.income_bar.low{background:#60a5fa;}.income_bar.mid{background:#22c55e;}.income_bar.high{background:#f59e0b;}
.income_value,.annual_value{padding:4px 7px;font-weight:bold;}
.estimate_empty{padding:18px;color:#94a3b8;border:1px solid #d9e2ef;background:#fbfdff;}
.estimate_record_table{width:100%;border-collapse:collapse;border:1px solid #d9e2ef;}
.estimate_record_table th,.estimate_record_table td{padding:9px;border-bottom:1px solid #edf2f7;text-align:left;}
.estimate_record_table th{background:#f8fafc;font-weight:normal;}
.estimate_link{color:#4560e6;text-decoration:none;background:none;border:0;padding:0;cursor:pointer;font-size:12px;}
.estimate_inline{display:inline-block;margin-left:10px;}
.estimate_live_status{display:inline-block;margin-left:8px;color:#64748b;font-size:12px;}
@media(max-width:760px){.estimate_toolbar select{min-width:180px;width:100%;margin-bottom:8px}.income_row{grid-template-columns:110px 1fr 90px}.annual_value{grid-column:3}.estimate_btn{margin-left:0;margin-right:6px}}
</style>

<div class="full_box">
	{{ partial('salary_primary_navigation') }}
	<div class="salary_secondary_navigation"><a href="{{helper.createUrl(['p':'salary/commission'])}}">提成项目设置</a><a class="on" href="#">月收入测算</a><a href="{{helper.createUrl(['p':'salary/commissionpayroll'])}}">月提成核算</a><a href="{{helper.createUrl(['p':'salary/commissionarchive'])}}">提成归档记录</a></div>
	<div class="commission_estimate_page">
	<div id="salary_inline_delete_message" style="display:none;margin:0 0 12px 0;padding:8px 12px;border:1px solid #bbf7d0;background:#f0fdf4;color:#166534;"></div>

	<form class="estimate_toolbar" method="get" action="{{helper.createUrl(['p':'salary/commissionestimate'])}}">
		<input type="hidden" name="p" value="salary/commissionestimate" />
		<label>测算对象　</label>
		<select name="employee_id">
			{% for employee in employees %}
			<option value="{{employee['id']}}" {% if employeeId==employee['id'] %}selected="selected"{% endif %}>{{employee['name']}} / {% if employee['position_name'] %}{{employee['position_name']}}{% else %}未设置岗位{% endif %} / {% if employee['department_name'] %}{{employee['department_name']}}{% else %}未设置部门{% endif %}</option>
			{% elsefor %}<option value="0">当前企业暂无员工</option>{% endfor %}
		</select>
		<button class="estimate_btn" type="submit">重新测算</button>
		<a class="estimate_btn gray" href="{{helper.createUrl(['p':'salary/commission'])}}">项目设置</a>
	</form>

	{% if estimate %}
	<form id="commission_estimate_form" method="post" action="{{helper.createUrl(['p':'salary/commissionestimate'])}}" data-calculate-url="{{helper.createUrl(['p':'salary/commissionestimatecalculate'])}}" data-save-rule-url="{{helper.createUrl(['p':'salary/commissionestimatesaverule'])}}">
		<input type="hidden" name="employee_id" value="{{employeeId}}" />
		<div class="estimate_section">
			<h3>提成测算</h3>
			<p class="estimate_tip">系统已加载该员工适用的全部启用提成项目。填写各项目低位、中位、高位业绩值后自动测算。<span id="commission_estimate_status" class="estimate_live_status"></span></p>
			{% if estimate['rows'] %}
			<div class="estimate_scroll">
				<table class="estimate_table">
					<tr><th>提成岗位</th><th>提成项目</th><th>提成规则</th><th>方式</th><th>业绩低位值</th><th>业绩中位值</th><th>业绩高位值</th><th class="estimate_low">低位提成</th><th class="estimate_mid">中位提成</th><th class="estimate_high">高位提成</th><th>操作</th></tr>
					{% for row in estimate['rows'] %}
					<tr id="commission_estimate_project_{{row['project_id']}}" class="{% if row['enabled'] is defined and !row['enabled'] %}commission_project_disabled{% endif %}">
						<td>{{row['position_name']}}</td><td>{{row['project_name']}}</td>
						<td class="estimate_rule">
							<div class="estimate_rule_view"><span id="commission_estimate_rule_summary_{{row['project_id']}}" class="estimate_rule_summary">{{row['rule_summary']}}</span>
							{% if not estimateRecord %}<button class="estimate_rule_edit commission_rule_edit" type="button" data-project-id="{{row['project_id']}}">编辑</button>{% endif %}</div>
							{% if not estimateRecord %}
							<div id="commission_rule_editor_{{row['project_id']}}" class="estimate_rule_editor" data-project-id="{{row['project_id']}}">
								{% if row['commission_mode']=='simple' %}
								<select data-rule-field="rate_type"><option value="percent" {% if row['rate_type']=='percent' %}selected="selected"{% endif %}>按比例（%）</option><option value="fixed" {% if row['rate_type']=='fixed' %}selected="selected"{% endif %}>按固定金额（元/单位）</option></select>
								<input type="text" data-rule-field="rate_value" value="{{row['rate_value']}}" />
								{% else %}
								{% for tier in row['tier_editor_items'] %}
								<div class="estimate_rule_tier">业绩 <input type="text" data-rule-field="tier_min[]" placeholder="起始值" value="{{tier['min']}}" /> 至 <input type="text" data-rule-field="tier_max[]" placeholder="不填为以上" value="{{tier['max']}}" />，提成 <input type="text" data-rule-field="tier_rate[]" placeholder="比例" value="{{tier['rate']}}" />%</div>
								{% endfor %}
								{% endif %}
								<div class="estimate_rule_note">保存后将同步更新“提成项目设置”，并影响以后使用该项目的提成测算。</div>
								<div class="estimate_rule_actions"><button class="estimate_rule_save commission_rule_save" type="button">保存并重算</button><button class="estimate_rule_cancel commission_rule_cancel" type="button">取消</button></div>
							</div>
							{% endif %}
						</td><td>{{row['mode_label']}}</td>
						<td><input class="commission_estimate_input" type="text" name="estimate[{{row['project_id']}}][low]" value="{{row['low_value']}}" {% if estimateRecord %}readonly="readonly"{% endif %} /></td>
						<td><input class="commission_estimate_input" type="text" name="estimate[{{row['project_id']}}][mid]" value="{{row['mid_value']}}" {% if estimateRecord %}readonly="readonly"{% endif %} /></td>
						<td><input class="commission_estimate_input" type="text" name="estimate[{{row['project_id']}}][high]" value="{{row['high_value']}}" {% if estimateRecord %}readonly="readonly"{% endif %} /></td>
						<td id="commission_estimate_row_{{row['project_id']}}_low" class="estimate_low">{{row['low_amount']}}</td><td id="commission_estimate_row_{{row['project_id']}}_mid" class="estimate_mid">{{row['mid_amount']}}</td><td id="commission_estimate_row_{{row['project_id']}}_high" class="estimate_high">{{row['high_amount']}}</td>
						<td>
							<input id="commission_estimate_enabled_{{row['project_id']}}" type="hidden" name="estimate[{{row['project_id']}}][enabled]" value="{% if row['enabled'] is defined and !row['enabled'] %}0{% else %}1{% endif %}" />
							{% if estimateRecord %}
							<span class="estimate_status_text">{% if row['enabled'] is defined and !row['enabled'] %}已停用{% else %}已启用{% endif %}</span>
							{% else %}
							<button class="estimate_toggle commission_project_toggle {% if row['enabled'] is defined and !row['enabled'] %}is_disabled{% endif %}" type="button" data-project-id="{{row['project_id']}}">{% if row['enabled'] is defined and !row['enabled'] %}启用{% else %}停用{% endif %}</button>
							{% endif %}
						</td>
					</tr>
					{% endfor %}
					<tr class="estimate_total"><td colspan="7">提成测算</td><td id="commission_estimate_total_low" class="estimate_low">{{estimate['commission']['low']}}</td><td id="commission_estimate_total_mid" class="estimate_mid">{{estimate['commission']['mid']}}</td><td id="commission_estimate_total_high" class="estimate_high">{{estimate['commission']['high']}}</td><td></td></tr>
				</table>
			</div>
			<div class="income_formula">月收入 = 月薪 <strong>{{estimate['base_salary']}}</strong> + 月提成　<span class="estimate_tip">月薪来源：{{estimate['base_salary_source']}}</span></div>
			<div class="income_grid">
				<div class="income_row"><span></span><span></span><span></span><span>年收入（万）</span></div>
				<div class="income_row"><span>月收入（低位）</span><div class="income_track"><div id="commission_estimate_bar_low" class="income_bar low" style="width:{{estimate['bar_width']['low']}}%"></div></div><span id="commission_estimate_income_low" class="income_value estimate_low">{{estimate['income']['low']}}</span><span id="commission_estimate_annual_low" class="annual_value estimate_low">{{estimate['annual']['low']}}</span></div>
				<div class="income_row"><span>月收入（中位）</span><div class="income_track"><div id="commission_estimate_bar_mid" class="income_bar mid" style="width:{{estimate['bar_width']['mid']}}%"></div></div><span id="commission_estimate_income_mid" class="income_value estimate_mid">{{estimate['income']['mid']}}</span><span id="commission_estimate_annual_mid" class="annual_value estimate_mid">{{estimate['annual']['mid']}}</span></div>
				<div class="income_row"><span>月收入（高位）</span><div class="income_track"><div id="commission_estimate_bar_high" class="income_bar high" style="width:{{estimate['bar_width']['high']}}%"></div></div><span id="commission_estimate_income_high" class="income_value estimate_high">{{estimate['income']['high']}}</span><span id="commission_estimate_annual_high" class="annual_value estimate_high">{{estimate['annual']['high']}}</span></div>
			</div>
			{% if estimateRecord %}
			<a class="estimate_btn" style="margin:12px 0 0 0" href="{{helper.createUrl(['p':'salary/commissionestimate','employee_id':employeeId])}}">重新测算</a>
			{% else %}
			<button class="estimate_btn gray" style="margin:12px 0 0 0" type="submit" name="estimate_action" value="save">保存测算</button>
			{% endif %}
			{% else %}<div class="estimate_empty">该员工尚未匹配任何启用的提成项目，请先检查提成项目的适用岗位、部门或人员设置。</div>{% endif %}
		</div>
	</form>
	{% else %}<div class="estimate_empty">当前企业暂无可测算员工。</div>{% endif %}

	<div class="estimate_section">
		<h3>测算记录</h3>
		<table class="estimate_record_table">
			<tr><th>测算对象</th><th>保存时间</th><th class="estimate_low">月收入（低位）</th><th class="estimate_mid">月收入（中位）</th><th class="estimate_high">月收入（高位）</th><th>操作</th></tr>
			{% for item in estimateRecords %}
			<tr id="commission_estimate_row_{{item['id']}}"><td>{{item['employee_name']}} / {% if item['position_name'] %}{{item['position_name']}}{% else %}未设置岗位{% endif %}</td><td>{{item['created_time']}}</td><td class="estimate_low">{{item['low_income']}}</td><td class="estimate_mid">{{item['mid_income']}}</td><td class="estimate_high">{{item['high_income']}}</td><td><a class="estimate_link" href="{{helper.createUrl(['p':'salary/commissionestimate','record_id':item['id']])}}">查看</a><button class="estimate_link" type="button" data-delete-url="{{helper.createUrl(['p':'salary/deletecommissionestimate'])}}" data-delete-row-id="commission_estimate_row_{{item['id']}}" data-delete-confirm="确认删除这条提成测算记录吗？" onclick="return salaryInlineDelete(this, {id:{{item['id']}}});">删除</button></td></tr>
			{% elsefor %}<tr><td colspan="6" class="estimate_empty">暂无已保存的测算记录。</td></tr>{% endfor %}
		</table>
	</div>
</div>
</div>
<script src="/skin/adminhtml/default/js/salary-inline-delete.js?v=20260717-2"></script>
{% if not estimateRecord %}<script src="/skin/adminhtml/default/js/commission-estimate-live.js?v=20260722-1"></script>{% endif %}
