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
.commission_form input[type=text],.commission_form input[type=number],.commission_form select{height:28px;line-height:28px;border:1px solid #cbd5e1;padding:0 8px;margin:0 10px 8px 0;min-width:130px;}
.commission_form textarea{border:1px solid #cbd5e1;padding:6px;width:430px;height:72px;vertical-align:top;}
.commission_btn{display:inline-block;background:#4560e6;color:#fff;border:0;padding:0 14px;line-height:30px;height:30px;text-decoration:none;cursor:pointer;}
.commission_btn_gray{background:#64748b;}
.commission_link{color:#4560e6;text-decoration:none;cursor:pointer;}
.commission_badge{display:inline-block;padding:0 8px;height:22px;line-height:22px;background:#eef2ff;color:#3949ab;}
.commission_badge.off{background:#f1f5f9;color:#64748b;}
.commission_empty{padding:18px;color:#94a3b8;text-align:center;}
.commission_inline{display:inline-block;margin:0 8px 0 0;}
.scope_control{display:none;}
.scope_control.on{display:inline-block;}
</style>
<div class="full_box">
	<div class="head_tab clear"><ul><li class="on"><a href="#">提成项目设置</a></li></ul></div>
	<div class="commission_page">
		<div class="commission_tip">每个提成项目独立设置业绩口径、提成方式和适用范围。员工同时命中多个启用项目时，后续月提成核算会分别计算并汇总。</div>

		<div class="commission_block">
			<h3>{% if editItem %}编辑提成项目{% else %}新增提成项目{% endif %}</h3>
			<form class="commission_form" method="post" action="{{helper.createUrl(['p':'salary/commissionsave'])}}" onsubmit="return prepareCommissionScope();">
				<input type="hidden" name="id" value="{% if editItem %}{{editItem.id}}{% endif %}" />
				<input type="hidden" name="scope_label" id="commission_scope_label" value="{% if editItem %}{{editItem.scope_label}}{% endif %}" />
				<div>
					<label>项目名称</label><input type="text" name="name" maxlength="80" value="{% if editItem %}{{editItem.name}}{% endif %}" />
					<label>业绩口径</label>
					<select name="metric_type" id="commission_metric_type" onchange="toggleCustomMetric();">
						{% for key,label in metricLabels %}<option value="{{key}}" {% if editItem and editItem.metric_type==key %}selected="selected"{% endif %}>{{label}}</option>{% endfor %}
					</select>
					<input type="text" name="metric_name" id="commission_metric_name" placeholder="自定义口径" value="{% if editItem and editItem.metric_type=='custom' %}{{editItem.metric_name}}{% endif %}" />
				</div>
				<div>
					<label>提成方式</label><select name="commission_mode">{% for key,label in modeLabels %}<option value="{{key}}" {% if editItem and editItem.commission_mode==key %}selected="selected"{% endif %}>{{label}}</option>{% endfor %}</select>
					<label>起提条件</label><input type="number" step="0.01" min="0" name="threshold_value" value="{% if editItem %}{{editItem.threshold_value}}{% else %}0{% endif %}" />
					<label>优先级</label><input type="number" name="priority" value="{% if editItem %}{{editItem.priority}}{% else %}0{% endif %}" />
				</div>
				<div>
					<label>适用范围</label>
					<select name="scope_type" id="commission_scope_type" onchange="toggleCommissionScope();">
						{% for key,label in scopeLabels %}<option value="{{key}}" {% if editItem and editItem.scope_type==key %}selected="selected"{% endif %}>{{label}}</option>{% endfor %}
					</select>
					<span id="scope_employee" class="scope_control"><select id="scope_employee_select">{% for item in scopeOptions['employees'] %}<option value="{{item['id']}}" data-label="{{item['name']}}" {% if editItem and editItem.scope_type=='employee' and editItem.scope_value==item['id'] %}selected="selected"{% endif %}>{{item['name']}}</option>{% endfor %}</select></span>
					<span id="scope_department" class="scope_control"><select id="scope_department_select">{% for item in scopeOptions['departments'] %}<option value="{{item['id']}}" data-label="{{item['name']}}" {% if editItem and editItem.scope_type=='department' and editItem.scope_value==item['id'] %}selected="selected"{% endif %}>{{item['name']}}</option>{% endfor %}</select></span>
					<span id="scope_position" class="scope_control"><input type="text" id="scope_position_input" value="{% if editItem and editItem.scope_type=='position' %}{{editItem.scope_value}}{% endif %}" placeholder="填写岗位名称" /></span>
					<input type="hidden" name="scope_value" id="commission_scope_value" value="{% if editItem %}{{editItem.scope_value}}{% endif %}" />
					<label>状态</label><select name="status">{% for key,label in statusLabels %}<option value="{{key}}" {% if editItem and editItem.status==key %}selected="selected"{% endif %}>{{label}}</option>{% endfor %}</select>
				</div>
				<div>
					<label>规则明细</label><textarea name="rule_detail" maxlength="5000" placeholder="例如：0-5万不提成；5-10万超额3%；10万以上超额5%">{% if editItem %}{{editItem.rule_detail}}{% endif %}</textarea>
				</div>
				<div><button class="commission_btn" type="submit">保存项目</button>{% if editItem %}<a class="commission_btn commission_btn_gray" href="{{helper.createUrl(['p':'salary/commission'])}}">取消编辑</a>{% endif %}</div>
			</form>
		</div>

		<div class="commission_block">
			<h3>已设置提成项目</h3>
			<table class="commission_table">
				<tr><th>项目名称</th><th>业绩口径</th><th>提成方式</th><th>起提条件</th><th>适用范围</th><th>规则明细</th><th>优先级</th><th>状态</th><th>操作</th></tr>
				{% for item in projects %}
				<tr>
					<td>{{item['name']}}</td><td>{{item['metric_name']}}</td><td>{{item['mode_label']}}</td><td>{{item['threshold_value']}}</td><td>{{item['scope_type_label']}}{% if item['scope_label']!='全公司默认' %}：{{item['scope_label']}}{% endif %}</td><td>{{item['rule_detail']}}</td><td>{{item['priority']}}</td><td><span class="commission_badge {% if item['status']!='active' %}off{% endif %}">{{item['status_label']}}</span></td>
					<td><a class="commission_link" href="{{helper.createUrl(['p':'salary/commission','id':item['id']])}}">编辑</a>　<form class="commission_inline" method="post" action="{{helper.createUrl(['p':'salary/commissiondelete'])}}" onsubmit="return confirm('确认删除该提成项目吗？');"><input type="hidden" name="id" value="{{item['id']}}" /><button class="commission_link" type="submit">删除</button></form></td>
				</tr>
				{% elsefor %}<tr><td colspan="9" class="commission_empty">暂无提成项目，请先新增提成规则。</td></tr>{% endfor %}
			</table>
		</div>
	</div>
</div>
<script>
function toggleCustomMetric(){
	var isCustom=document.getElementById('commission_metric_type').value==='custom';
	document.getElementById('commission_metric_name').style.display=isCustom ? 'inline-block' : 'none';
}
function toggleCommissionScope(){
	var type=document.getElementById('commission_scope_type').value;
	var ids=['scope_employee','scope_department','scope_position'];
	for(var i=0;i<ids.length;i++){document.getElementById(ids[i]).className='scope_control';}
	if(type==='employee'){document.getElementById('scope_employee').className='scope_control on';}
	if(type==='department'){document.getElementById('scope_department').className='scope_control on';}
	if(type==='position'){document.getElementById('scope_position').className='scope_control on';}
}
function prepareCommissionScope(){
	var type=document.getElementById('commission_scope_type').value;
	var value=''; var label='全公司默认'; var select;
	if(type==='employee'){
		select=document.getElementById('scope_employee_select');
		if(!select.options.length){alert('当前企业暂无员工，请先维护员工信息。');return false;}
		value=select.value;label=select.options[select.selectedIndex].getAttribute('data-label');
	}
	if(type==='department'){
		select=document.getElementById('scope_department_select');
		if(!select.options.length){alert('当前企业暂无部门，请先维护部门信息。');return false;}
		value=select.value;label=select.options[select.selectedIndex].getAttribute('data-label');
	}
	if(type==='position'){
		value=document.getElementById('scope_position_input').value;
		if(!value){alert('请填写岗位名称。');return false;}
		label=value;
	}
	document.getElementById('commission_scope_value').value=value;
	document.getElementById('commission_scope_label').value=label;
	return true;
}
toggleCustomMetric();
toggleCommissionScope();
</script>
