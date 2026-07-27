<style>
.commission_page{padding:18px;}
.commission_tip{border:1px solid #d9e2ef;background:#fbfdff;padding:12px 14px;margin-bottom:14px;color:#475569;line-height:22px;}
.commission_block{margin-bottom:18px;}
.commission_block h3{font-size:15px;color:#1f2937;margin:0 0 10px 0;}
.commission_table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #d9e2ef;}
.commission_table th{background:#f8fafc;color:#334155;font-weight:normal;text-align:left;padding:9px;border-bottom:1px solid #d9e2ef;white-space:nowrap;}
.commission_table td{padding:9px;border-bottom:1px solid #edf2f7;color:#475569;vertical-align:middle;}
.commission_form{border:1px solid #d9e2ef;background:#fff;padding:12px;}
.commission_form label{display:inline-block;width:74px;color:#475569;}
.commission_form input[type=text],.commission_form input[type=number],.commission_form select{height:28px;line-height:28px;border:1px solid #cbd5e1;padding:0 8px;margin:0 10px 8px 0;min-width:120px;}
.commission_form textarea{border:1px solid #cbd5e1;padding:6px;width:520px;height:58px;vertical-align:top;}
.commission_btn{display:inline-block;background:#4560e6;color:#fff;border:0;padding:0 14px;line-height:30px;height:30px;text-decoration:none;cursor:pointer;}
.commission_btn_gray{background:#64748b;}
.commission_link{color:#4560e6;text-decoration:none;cursor:pointer;background:none;border:0;padding:0;font-size:12px;}
.commission_badge{display:inline-block;padding:0 8px;height:22px;line-height:22px;background:#eef2ff;color:#3949ab;}
.commission_badge.off{background:#f1f5f9;color:#64748b;}
.commission_empty{padding:18px;color:#94a3b8;text-align:center;}
.commission_inline{display:inline-block;margin:0 8px 0 0;}
.commission_status_toggle{min-width:52px;height:24px;line-height:22px;padding:0 8px;border:1px solid #4560e6;background:#fff;color:#4560e6;cursor:pointer;font-size:12px;}
.commission_status_toggle.active{border-color:#b45309;color:#b45309;background:#fffbeb;}
.commission_status_toggle.inactive{border-color:#15803d;color:#15803d;background:#f0fdf4;}
.scope_selector{display:inline-block;position:relative;vertical-align:top;margin:0 12px 8px 0;}
.scope_menu_trigger{height:30px;min-width:210px;padding:0 10px;text-align:left;border:1px solid #cbd5e1;background:#fff;color:#334155;cursor:pointer;vertical-align:top;}
.scope_menu_trigger:after{content:'▼';float:right;color:#64748b;font-size:10px;}
.scope_menu{display:none;position:absolute;z-index:1200;top:31px;left:78px;width:470px;max-height:360px;overflow:auto;padding:12px;border:1px solid #cbd5e1;background:#fff;box-shadow:0 8px 20px rgba(15,23,42,.18);}
.scope_menu.on{display:block;}
.scope_menu_section{padding:8px 0;border-bottom:1px solid #edf2f7;}
.scope_menu_section strong{display:inline-block;width:54px;color:#334155;font-weight:normal;vertical-align:top;}
.scope_menu_options{display:inline-block;width:390px;vertical-align:top;}
.scope_menu_options label{display:inline-block;width:124px;margin:0 4px 7px 0;color:#475569;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;cursor:pointer;}
.scope_menu_options input{width:auto!important;min-width:0!important;height:auto!important;margin:0 4px 0 0!important;vertical-align:-2px;}
.scope_menu_hint{margin:4px 0 0 58px;color:#64748b;font-size:12px;line-height:18px;}
.tier_box{display:inline-block;vertical-align:top;border:1px solid #e2e8f0;background:#fbfdff;padding:8px 10px;margin-bottom:8px;}
.tier_box input{min-width:70px;width:82px;}
.tier_box span{color:#64748b;margin-right:4px;}
</style>
<div class="full_box">
	{{ partial('salary_primary_navigation') }}
	<div class="salary_secondary_navigation"><a class="on" href="#">提成项目设置</a><a href="{{helper.createUrl(['p':'salary/commissionestimate'])}}">月收入测算</a><a href="{{helper.createUrl(['p':'salary/commissionpayroll'])}}">月提成核算</a><a href="{{helper.createUrl(['p':'salary/commissionarchive'])}}">提成归档记录</a></div>
	<div class="commission_page">
		<div id="salary_inline_delete_message" style="{% if commissionStatusMessage %}display:block;{% else %}display:none;{% endif %}margin:0 0 12px 0;padding:8px 12px;border:1px solid #bbf7d0;background:#f0fdf4;color:#166534;">{% if commissionStatusMessage %}{{commissionStatusMessage}}{% endif %}</div>
		<div class="commission_tip">每个提成项目独立设置业绩口径、计算方式和适用范围。月提成核算会按员工命中的项目分别计算，再汇总到员工月提成合计。</div>

		<div class="commission_block">
			<h3>{% if editItem %}编辑提成项目{% else %}新增提成项目{% endif %}</h3>
			<form class="commission_form" method="post" action="{{helper.createUrl(['p':'salary/commissionsave'])}}" onsubmit="return prepareCommissionScope();">
				<input type="hidden" name="id" value="{% if editItem %}{{editItem.id}}{% endif %}" />
				<input type="hidden" name="scope_form_mode" value="multi" />
				<div>
					<label>项目名称</label><input type="text" name="name" maxlength="80" value="{% if editItem %}{{editItem.name}}{% endif %}" />
					<label>业绩口径</label>
					<select name="metric_type" id="commission_metric_type" onchange="toggleCustomMetric();">
						{% for key,label in metricLabels %}<option value="{{key}}" {% if editItem and editItem.metric_type==key %}selected="selected"{% endif %}>{{label}}</option>{% endfor %}
					</select>
					<input type="text" name="metric_name" id="commission_metric_name" placeholder="自定义口径" value="{% if editItem and editItem.metric_type=='custom' %}{{editItem.metric_name}}{% endif %}" />
				</div>
				<div>
					<label>提成方式</label><select name="commission_mode" id="commission_mode" onchange="toggleCommissionRule();">{% for key,label in modeLabels %}<option value="{{key}}" {% if editItem and editItem.commission_mode==key %}selected="selected"{% endif %}>{{label}}</option>{% endfor %}</select>
					<span id="simple_rule">
						<label>计算口径</label><select name="rate_type">{% for key,label in rateTypeLabels %}<option value="{{key}}" {% if editItem and editItem.rate_type==key %}selected="selected"{% endif %}>{{label}}</option>{% endfor %}</select>
						<label>提成值</label><input type="number" step="0.0001" min="0" name="rate_value" value="{% if editItem %}{{editItem.rate_value}}{% else %}0{% endif %}" />
					</span>
					<label>起提条件</label><input type="number" step="0.01" min="0" name="threshold_value" value="{% if editItem %}{{editItem.threshold_value}}{% else %}0{% endif %}" />
				</div>
				<div id="tier_rule">
					<label>阶梯规则</label>
					<div class="tier_box">
						<div><span>第1档</span><input type="number" step="0.01" name="tier_min[0]" placeholder="起始值" value="{% if editItem and editItem.tier_items[0] is defined %}{{editItem.tier_items[0]['min']}}{% endif %}" /> 到 <input type="number" step="0.01" name="tier_max[0]" placeholder="封顶值" value="{% if editItem and editItem.tier_items[0] is defined %}{{editItem.tier_items[0]['max']}}{% endif %}" /> 提成% <input type="number" step="0.0001" name="tier_rate[0]" value="{% if editItem and editItem.tier_items[0] is defined %}{{editItem.tier_items[0]['rate']}}{% endif %}" /></div>
						<div><span>第2档</span><input type="number" step="0.01" name="tier_min[1]" placeholder="起始值" value="{% if editItem and editItem.tier_items[1] is defined %}{{editItem.tier_items[1]['min']}}{% endif %}" /> 到 <input type="number" step="0.01" name="tier_max[1]" placeholder="封顶值" value="{% if editItem and editItem.tier_items[1] is defined %}{{editItem.tier_items[1]['max']}}{% endif %}" /> 提成% <input type="number" step="0.0001" name="tier_rate[1]" value="{% if editItem and editItem.tier_items[1] is defined %}{{editItem.tier_items[1]['rate']}}{% endif %}" /></div>
						<div><span>第3档</span><input type="number" step="0.01" name="tier_min[2]" placeholder="起始值" value="{% if editItem and editItem.tier_items[2] is defined %}{{editItem.tier_items[2]['min']}}{% endif %}" /> 到 <input type="number" step="0.01" name="tier_max[2]" placeholder="不填为以上" value="{% if editItem and editItem.tier_items[2] is defined %}{{editItem.tier_items[2]['max']}}{% endif %}" /> 提成% <input type="number" step="0.0001" name="tier_rate[2]" value="{% if editItem and editItem.tier_items[2] is defined %}{{editItem.tier_items[2]['rate']}}{% endif %}" /></div>
					</div>
				</div>
				<div><div class="scope_selector" id="commission_scope_selector"><label>适用范围</label><button id="scope_menu_trigger" class="scope_menu_trigger" type="button" onclick="toggleCommissionScopeMenu();">选择适用范围</button><div id="scope_menu" class="scope_menu"><div class="scope_menu_section"><strong>范围</strong><span class="scope_menu_options"><label><input type="checkbox" id="scope_type_all" name="scope_all" value="1" onchange="toggleCommissionScope(this);" {% if not editItem or editItem.scope_is_all %}checked="checked"{% endif %} />全部</label></span><div class="scope_menu_hint">“全部”与部门、岗位、人员不能同时选择。</div></div><div class="scope_menu_section"><strong>部门</strong><span class="scope_menu_options">{% for item in scopeOptions['departments'] %}<label title="{{item['name']}}"><input class="scope_type_check scope_department_check" type="checkbox" name="scope_departments[]" value="{{item['id']}}" onchange="toggleCommissionScope(this);" {% if item['selected'] %}checked="checked"{% endif %} />{{item['name']}}</label>{% elsefor %}<span>暂无部门</span>{% endfor %}</span></div><div class="scope_menu_section"><strong>岗位</strong><span class="scope_menu_options">{% for item in scopeOptions['positions'] %}<label title="{{item['name']}}"><input class="scope_type_check scope_position_check" type="checkbox" name="scope_positions[]" value="{{item['value']}}" onchange="toggleCommissionScope(this);" {% if item['selected'] %}checked="checked"{% endif %} />{{item['name']}}</label>{% elsefor %}<span>请先在员工名单维护岗位</span>{% endfor %}</span></div><div class="scope_menu_section"><strong>人员</strong><span class="scope_menu_options">{% for item in scopeOptions['employees'] %}<label title="{{item['name']}}"><input class="scope_type_check scope_employee_check" type="checkbox" name="scope_employees[]" value="{{item['id']}}" onchange="toggleCommissionScope(this);" {% if item['selected'] %}checked="checked"{% endif %} />{{item['name']}}</label>{% elsefor %}<span>暂无员工</span>{% endfor %}</span></div><div class="scope_menu_hint">可同时选择多个部门、岗位和人员；员工命中任一范围即可参与测算。</div></div></div><label>优先级</label><input type="number" name="priority" value="{% if editItem %}{{editItem.priority}}{% else %}0{% endif %}" /></div>
				<div>
					<label>规则说明</label><textarea name="rule_detail" maxlength="5000" placeholder="给HR和员工查看的文字说明">{% if editItem %}{{editItem.rule_detail}}{% endif %}</textarea>
				</div>
				<div><button class="commission_btn" type="submit">保存项目</button>{% if editItem %}<a class="commission_btn commission_btn_gray" href="{{helper.createUrl(['p':'salary/commission'])}}">取消编辑</a>{% endif %}</div>
			</form>
		</div>

		<div class="commission_block">
			<h3>已设置提成项目</h3>
			<table class="commission_table">
				<tr><th>项目名称</th><th>业绩口径</th><th>提成方式</th><th>计算规则</th><th>适用范围</th><th>优先级</th><th>状态</th><th>操作</th></tr>
				{% for item in projects %}
				<tr id="commission_project_row_{{item['id']}}">
					<td>{{item['name']}}</td>
					<td>{{item['metric_name']}}</td>
					<td>{{item['mode_label']}}</td>
					<td>{{item['rule_summary']}}</td>
					<td>{{item['scope_summary']}}</td>
					<td>{{item['priority']}}</td>
					<td><span class="commission_badge {% if item['status']!='active' %}off{% endif %}">{{item['status_label']}}</span></td>
					<td><a class="commission_link" href="{{helper.createUrl(['p':'salary/commission','id':item['id']])}}">编辑</a>　<form class="commission_inline" method="post" action="{{helper.createUrl(['p':'salary/commissionstatus'])}}"{% if item['status']=='active' %} onsubmit="return confirm('停用后，该项目不会参与后续提成测算；历史月提成和归档记录不受影响。确认停用吗？');"{% endif %}><input type="hidden" name="id" value="{{item['id']}}" /><input type="hidden" name="status" value="{% if item['status']=='active' %}inactive{% else %}active{% endif %}" /><button class="commission_status_toggle {% if item['status']=='active' %}active{% else %}inactive{% endif %}" type="submit" title="{% if item['status']=='active' %}点击停用该提成项目{% else %}点击启用该提成项目{% endif %}">{% if item['status']=='active' %}停用{% else %}启用{% endif %}</button></form>　<button class="commission_link" type="button" data-delete-url="{{helper.createUrl(['p':'salary/commissiondelete'])}}" data-delete-row-id="commission_project_row_{{item['id']}}" data-delete-confirm="确认删除该提成项目吗？" onclick="return salaryInlineDelete(this, {id:{{item['id']}}});">删除</button></td>
				</tr>
				{% elsefor %}<tr><td colspan="8" class="commission_empty">暂无提成项目，请先新增提成规则。</td></tr>{% endfor %}
			</table>
		</div>
	</div>
</div>
<script src="/skin/adminhtml/default/js/salary-inline-delete.js?v=20260717-2"></script>
<script>
function toggleCustomMetric(){
	var isCustom=document.getElementById('commission_metric_type').value==='custom';
	document.getElementById('commission_metric_name').style.display=isCustom ? 'inline-block' : 'none';
}
function toggleCommissionRule(){
	var mode=document.getElementById('commission_mode').value;
	document.getElementById('simple_rule').style.display=mode==='simple' ? 'inline' : 'none';
	document.getElementById('tier_rule').style.display=mode==='simple' ? 'none' : 'block';
}
function toggleCommissionScope(changed){var all=document.getElementById('scope_type_all'),checks=document.querySelectorAll('.scope_type_check');if(changed===all&&all.checked){for(var i=0;i<checks.length;i++){checks[i].checked=false;}}else if(changed&&changed!==all&&changed.checked){all.checked=false;}var hasSpecific=false;for(var j=0;j<checks.length;j++){if(checks[j].checked){hasSpecific=true;}}if(!hasSpecific&&!all.checked){all.checked=true;}updateCommissionScopeLabel();}
function updateCommissionScopeLabel(){var trigger=document.getElementById('scope_menu_trigger'),all=document.getElementById('scope_type_all');if(!trigger||!all){return;}if(all.checked){trigger.innerHTML='全部';return;}var groups=[['scope_department_check','部门'],['scope_position_check','岗位'],['scope_employee_check','人员']],parts=[];for(var i=0;i<groups.length;i++){var items=document.querySelectorAll('.'+groups[i][0]),count=0;for(var j=0;j<items.length;j++){if(items[j].checked){count++;}}if(count>0){parts.push(groups[i][1]+count+'项');}}trigger.innerHTML=parts.length?parts.join('、'):'选择适用范围';}
function toggleCommissionScopeMenu(){var menu=document.getElementById('scope_menu');if(menu){menu.className=menu.className.indexOf(' on')>=0?'scope_menu':'scope_menu on';}}
function prepareCommissionScope(){if(document.getElementById('scope_type_all').checked){return true;}var checks=document.querySelectorAll('.scope_type_check');for(var i=0;i<checks.length;i++){if(checks[i].checked){return true;}}alert('请至少选择一个部门、岗位或人员；如适用全公司请选择“全部”。');return false;}
document.onclick=function(event){var selector=document.getElementById('commission_scope_selector');if(selector&&!selector.contains(event.target)){document.getElementById('scope_menu').className='scope_menu';}};
toggleCustomMetric();
toggleCommissionRule();
toggleCommissionScope(null);
</script>
