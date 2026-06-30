<style>
.salary_page{padding:18px;}
.salary_tip{border:1px solid #d9e2ef;background:#fbfdff;padding:12px 14px;margin-bottom:14px;color:#475569;line-height:22px;}
.salary_block{margin-bottom:18px;}
.salary_block h3{font-size:15px;color:#1f2937;margin:0 0 10px 0;}
.salary_table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #d9e2ef;}
.salary_table th{background:#f8fafc;color:#334155;font-weight:normal;text-align:left;padding:9px;border-bottom:1px solid #d9e2ef;}
.salary_table td{padding:9px;border-bottom:1px solid #edf2f7;color:#475569;vertical-align:middle;}
.salary_form{border:1px solid #d9e2ef;background:#fff;padding:12px;}
.salary_form label{display:inline-block;width:80px;color:#475569;}
.salary_form input[type=text],.salary_form select{height:28px;line-height:28px;border:1px solid #cbd5e1;padding:0 8px;margin:0 10px 8px 0;}
.salary_form textarea{border:1px solid #cbd5e1;padding:6px;width:360px;height:54px;vertical-align:top;}
.salary_btn{display:inline-block;background:#4560e6;color:#fff;border:0;padding:0 14px;line-height:30px;height:30px;text-decoration:none;cursor:pointer;}
.salary_btn_gray{background:#64748b;}
.salary_link_btn{border:0;background:none;color:#4560e6;cursor:pointer;padding:0;font-size:12px;}
.salary_badge{display:inline-block;padding:0 8px;height:22px;line-height:22px;background:#eef2ff;color:#3949ab;}
.salary_empty{padding:18px;color:#94a3b8;text-align:center;}
.inline_form{display:inline-block;margin:0;}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li class="on"><a href="#">工资项目设置</a></li>
		</ul>
	</div>
	<div class="salary_page">
		<div class="salary_tip">工资项目分为两类：平台提供的通用项目由企业勾选启用；企业自定义项目可自行维护名称、类别、计算方式和是否计入合计。</div>

		<div class="salary_block">
			<h3>通用项目</h3>
			<form method="post" action="{{helper.createUrl(['p':'salary/projectsavetemplates'])}}">
				<table class="salary_table">
					<tr>
						<th width="8%">启用</th>
						<th width="22%">项目名称</th>
						<th width="14%">类别</th>
						<th width="14%">项目类型</th>
						<th>说明</th>
					</tr>
					{% for item in templates %}
					<tr>
						<td><input type="checkbox" name="template_ids[]" value="{{item['id']}}" {% if item['is_selected'] %}checked="checked"{% endif %} /></td>
						<td>{{item['name']}}</td>
						<td>{{item['direction_label']}}</td>
						<td>{{item['source_type_label']}}</td>
						<td>{% if item['linked_module']!='none' %}关联 {{item['linked_module']}}{% else %}-{% endif %}</td>
					</tr>
					{% elsefor %}
					<tr><td colspan="5" class="salary_empty">暂无平台通用项目</td></tr>
					{% endfor %}
				</table>
				<div style="margin-top:10px;">
					<button class="salary_btn" type="submit">保存通用项目选择</button>
				</div>
			</form>
		</div>

		<div class="salary_block">
			<h3>{% if editItem %}编辑自定义项目{% else %}新增自定义项目{% endif %}</h3>
			<form class="salary_form" method="post" action="{{helper.createUrl(['p':'salary/projectsave'])}}">
				<input type="hidden" name="id" value="{% if editItem %}{{editItem.id}}{% endif %}" />
				<div>
					<label>项目名称</label>
					<input type="text" name="name" maxlength="80" value="{% if editItem %}{{editItem.name}}{% endif %}" />
					<label>类别</label>
					<select name="direction">
						{% for key,label in directions %}
						<option value="{{key}}" {% if editItem and editItem.direction==key %}selected="selected"{% endif %}>{{label}}</option>
						{% endfor %}
					</select>
					<label>项目类型</label>
					<select name="source_type">
						{% for key,label in sourceTypes %}
						<option value="{{key}}" {% if editItem and editItem.source_type==key %}selected="selected"{% endif %}>{{label}}</option>
						{% endfor %}
					</select>
				</div>
				<div>
					<label>计算方式</label>
					<select name="calculation_mode">
						{% for key,label in calculationModes %}
						<option value="{{key}}" {% if editItem and editItem.calculation_mode==key %}selected="selected"{% endif %}>{{label}}</option>
						{% endfor %}
					</select>
					<label>关联模块</label>
					<input type="text" name="linked_module" maxlength="30" value="{% if editItem %}{{editItem.linked_module}}{% else %}none{% endif %}" />
					<label>排序</label>
					<input type="text" name="sort_order" maxlength="10" style="width:70px;" value="{% if editItem %}{{editItem.sort_order}}{% else %}0{% endif %}" />
				</div>
				<div>
					<label>计入应发</label>
					<input type="checkbox" name="include_earning" value="1" {% if editItem and editItem.include_earning %}checked="checked"{% endif %} />
					<label>计入扣款</label>
					<input type="checkbox" name="include_deduction" value="1" {% if editItem and editItem.include_deduction %}checked="checked"{% endif %} />
					<label>计入实发</label>
					<input type="checkbox" name="include_net" value="1" {% if !editItem or editItem.include_net %}checked="checked"{% endif %} />
					<label>状态</label>
					<select name="status">
						{% for key,label in statusLabels %}
						<option value="{{key}}" {% if editItem and editItem.status==key %}selected="selected"{% endif %}>{{label}}</option>
						{% endfor %}
					</select>
				</div>
				<div>
					<label>公式说明</label>
					<textarea name="formula_text">{% if editItem %}{{editItem.formula_text}}{% endif %}</textarea>
				</div>
				<div style="margin-top:10px;">
					<button class="salary_btn" type="submit">保存自定义项目</button>
					{% if editItem %}
					<a class="salary_btn salary_btn_gray" href="{{helper.createUrl(['p':'salary/project'])}}">取消编辑</a>
					{% endif %}
				</div>
			</form>
		</div>

		<div class="salary_block">
			<h3>已启用项目</h3>
			<table class="salary_table">
				<tr>
					<th width="12%">来源</th>
					<th width="18%">项目名称</th>
					<th width="12%">类别</th>
					<th width="12%">项目类型</th>
					<th width="12%">计算方式</th>
					<th width="8%">应发</th>
					<th width="8%">扣款</th>
					<th width="8%">实发</th>
					<th>操作</th>
				</tr>
				{% for item in projects %}
				<tr>
					<td><span class="salary_badge">{{item['project_kind_label']}}</span></td>
					<td>{{item['name']}}</td>
					<td>{{item['direction_label']}}</td>
					<td>{{item['source_type_label']}}</td>
					<td>{{item['calculation_mode_label']}}</td>
					<td>{% if item['include_earning'] %}是{% else %}否{% endif %}</td>
					<td>{% if item['include_deduction'] %}是{% else %}否{% endif %}</td>
					<td>{% if item['include_net'] %}是{% else %}否{% endif %}</td>
					<td>
						{% if item['project_kind']=='custom' %}
						<a class="salary_link_btn" href="{{helper.createUrl(['p':'salary/project','id':item['id']])}}">编辑</a>
						{% endif %}
						<form class="inline_form" method="post" action="{{helper.createUrl(['p':'salary/projectdelete'])}}" onsubmit="return confirm('确定停用这个工资项目吗？');">
							<input type="hidden" name="id" value="{{item['id']}}" />
							<button class="salary_link_btn" type="submit">停用</button>
						</form>
					</td>
				</tr>
				{% elsefor %}
				<tr><td colspan="9" class="salary_empty">暂无工资项目</td></tr>
				{% endfor %}
			</table>
		</div>
	</div>
</div>
