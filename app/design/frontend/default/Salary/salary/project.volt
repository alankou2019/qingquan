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
.salary_scroll{overflow:auto;border:1px solid #d9e2ef;background:#fff;}
.salary_sheet{min-width:980px;}
.salary_sheet input[type=text]{width:88px;height:26px;line-height:26px;border:1px solid #cbd5e1;padding:0 6px;text-align:right;}
.salary_sheet input.salary_text_input{text-align:left;width:120px;}
.salary_sheet input[readonly]{background:#f1f5f9;color:#64748b;}
.salary_formula_area{display:inline-block;vertical-align:top;}
.salary_formula_tools{display:inline-block;vertical-align:top;width:520px;margin-left:8px;color:#64748b;}
.salary_formula_tools .formula_hint{line-height:22px;margin-bottom:6px;}
.salary_formula_refs{border:1px solid #e2e8f0;background:#fbfdff;padding:8px;max-height:92px;overflow:auto;}
.salary_formula_refs button{border:1px solid #cbd5e1;background:#fff;color:#334155;height:24px;line-height:22px;margin:0 6px 6px 0;padding:0 8px;cursor:pointer;}
.salary_formula_refs button:hover{border-color:#4560e6;color:#4560e6;}
.salary_formula_ops{margin-top:6px;}
.salary_formula_ops button{border:1px solid #cbd5e1;background:#fff;color:#334155;width:26px;height:24px;line-height:22px;margin-right:4px;cursor:pointer;}
.salary_default_field{display:inline-block;}
.salary_default_field input{width:180px;}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li class="on"><a href="#">工资项目设置</a></li>
			<li style="float:right;width:140px;border-left:1px solid #efefef;border-right:0;"><a href="{{helper.createUrl(['p':'salary/index'])}}">返回薪酬首页</a></li>
		</ul>
	</div>
	<div class="salary_page">
		<div class="salary_tip">工资项目分为通用项目和自定义项目。项目类别决定是否计入应发、应扣和实发；项目属性分为数字项、文本项和核算项，核算项按公式自动计算。</div>

		<div class="salary_block">
			<h3>固定项目</h3>
			<table class="salary_table">
				<tr>
					<th width="18%">项目名称</th>
					<th width="14%">项目类别</th>
					<th width="14%">项目属性</th>
					<th width="28%">核算方式</th>
					<th>操作</th>
				</tr>
				{% for item in fixedProjects %}
				<tr>
					<td>{{item['name']}}</td>
					<td>{{item['direction_label']}}</td>
					<td>{{item['source_type_label']}}</td>
					<td>{{item['calculation_mode_label']}}</td>
					<td>系统固定，不可编辑/停用/删除</td>
				</tr>
				{% endfor %}
			</table>
		</div>

		<div class="salary_block">
			<h3>通用项目</h3>
			<form method="post" action="{{helper.createUrl(['p':'salary/projectsavetemplates'])}}">
				<table class="salary_table">
					<tr>
						<th width="8%">启用</th>
						<th width="22%">项目名称</th>
						<th width="14%">项目类别</th>
						<th width="14%">项目属性</th>
						<th width="20%">说明</th>
						<th>操作</th>
					</tr>
					{% for item in templates %}
					<tr>
						<td><input type="checkbox" name="template_ids[]" value="{{item['id']}}" {% if item['is_selected'] %}checked="checked"{% endif %} /></td>
						<td>{{item['name']}}</td>
						<td>{{item['direction_label']}}</td>
						<td>{{item['source_type_label']}}</td>
						<td>{% if item['linked_module']!='none' %}关联 {{item['linked_module']}}{% else %}-{% endif %}</td>
						<td>
							<a class="salary_link_btn" href="{{helper.createUrl(['p':'salary/project','template_id':item['id']])}}">编辑</a>
							<button class="salary_link_btn" type="submit" name="template_id" value="{{item['id']}}" formaction="{{helper.createUrl(['p':'salary/projectdelete'])}}" formmethod="post" onclick="return confirm('确定删除当前企业的这个通用工资项目吗？删除不会影响平台模板、其他企业和历史归档记录。');">删除</button>
						</td>
					</tr>
					{% elsefor %}
					<tr><td colspan="6" class="salary_empty">暂无平台通用项目</td></tr>
					{% endfor %}
				</table>
				<div style="margin-top:10px;">
					<button class="salary_btn" type="submit">保存通用项目选择</button>
				</div>
			</form>
		</div>

		<div class="salary_block">
			<h3>{% if editItem %}编辑工资项目{% else %}新增自定义项目{% endif %}</h3>
			<form class="salary_form" method="post" action="{{helper.createUrl(['p':'salary/projectsave'])}}">
				<input type="hidden" name="id" value="{% if editItem %}{{editItem.id}}{% endif %}" />
				<input type="hidden" name="template_id" value="{% if editItem %}{{editItem.template_id}}{% endif %}" />
				<div>
					<label>项目名称</label>
					<input type="text" name="name" maxlength="80" value="{% if editItem %}{{editItem.name}}{% endif %}" />
					<label>项目类别</label>
					<select name="direction">
						{% for key,label in directions %}
						<option value="{{key}}" {% if editItem and editItem.direction==key %}selected="selected"{% endif %}>{{label}}</option>
						{% endfor %}
					</select>
					<label>项目属性</label>
					<select id="salary_project_source_type" name="source_type" onchange="toggleSalaryFormulaBox();">
						{% for key,label in sourceTypes %}
						<option value="{{key}}" {% if editItem and (editItem.source_type==key or (editItem.source_type=='fixed' and key=='number')) %}selected="selected"{% endif %}>{{label}}</option>
						{% endfor %}
					</select>
					<span id="salary_default_number_field" class="salary_default_field">
						<label>默认数字</label>
						<input type="text" name="default_number" maxlength="18" value="{% if editItem %}{{editItem.default_number}}{% else %}0.00{% endif %}" />
					</span>
					<span id="salary_default_text_field" class="salary_default_field">
						<label>默认文本</label>
						<input type="text" name="default_text" maxlength="500" value="{% if editItem %}{{editItem.default_text}}{% endif %}" />
					</span>
				</div>
				<div>
					<label>关联模块</label>
					<input type="text" name="linked_module" maxlength="30" value="{% if editItem %}{{editItem.linked_module}}{% else %}none{% endif %}" />
					<label>排序</label>
					<input type="text" name="sort_order" maxlength="10" style="width:70px;" value="{% if editItem %}{{editItem.sort_order}}{% else %}0{% endif %}" />
					<label>状态</label>
					<select name="status">
						{% for key,label in statusLabels %}
						<option value="{{key}}" {% if editItem and editItem.status==key %}selected="selected"{% endif %}>{{label}}</option>
						{% endfor %}
					</select>
				</div>
				<div id="salary_formula_row">
					<label>核算公式</label>
					<div class="salary_formula_area">
						<textarea id="salary_formula_text" name="formula_text">{% if editItem %}{{editItem.formula_text}}{% endif %}</textarea>
					</div>
					<div class="salary_formula_tools">
						<div class="formula_hint">点击工资项目名称可插入公式。支持 +、-、*、/、括号；被引用项目建议排在本项目前面。</div>
						<div class="salary_formula_refs">
							{% for item in formulaProjects %}
							<button type="button" data-project-name="{{item['name']}}" onclick="insertSalaryFormulaProject(this);">{{item['name']}}</button>
							{% elsefor %}
							<span>暂无可引用项目，请先启用数字项或核算项。</span>
							{% endfor %}
						</div>
						<div class="salary_formula_ops">
							<button type="button" onclick="insertSalaryFormulaText(' + ');">+</button>
							<button type="button" onclick="insertSalaryFormulaText(' - ');">-</button>
							<button type="button" onclick="insertSalaryFormulaText(' * ');">*</button>
							<button type="button" onclick="insertSalaryFormulaText(' / ');">/</button>
							<button type="button" onclick="insertSalaryFormulaText('(');">(</button>
							<button type="button" onclick="insertSalaryFormulaText(')');">)</button>
						</div>
					</div>
				</div>
				<div style="margin-top:10px;">
					<button class="salary_btn" type="submit">保存工资项目</button>
					{% if editItem %}
					<a class="salary_btn salary_btn_gray" href="{{helper.createUrl(['p':'salary/project'])}}">取消编辑</a>
					{% endif %}
				</div>
			</form>
		</div>

		<div class="salary_block">
			<h3>企业工资项目</h3>
			<table class="salary_table">
				<tr>
					<th width="12%">来源</th>
					<th width="18%">项目名称</th>
					<th width="12%">项目类别</th>
					<th width="12%">项目属性</th>
					<th width="12%">核算方式</th>
					<th width="8%">计入应发</th>
					<th width="8%">计入应扣</th>
					<th width="8%">计入实发</th>
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
						<a class="salary_link_btn" href="{{helper.createUrl(['p':'salary/project','id':item['id']])}}">编辑</a>
						<form class="inline_form" method="post" action="{{helper.createUrl(['p':'salary/projectdisable'])}}" onsubmit="return confirm('确定停用这个工资项目吗？');">
							<input type="hidden" name="id" value="{{item['id']}}" />
							<button class="salary_link_btn" type="submit">停用</button>
						</form>
						<form class="inline_form" method="post" action="{{helper.createUrl(['p':'salary/projectdelete'])}}" onsubmit="return confirm('确定删除这个工资项目吗？删除后不会影响历史工资表和归档记录。');">
							<input type="hidden" name="id" value="{{item['id']}}" />
							<button class="salary_link_btn" type="submit">删除</button>
						</form>
					</td>
				</tr>
				{% elsefor %}
				<tr><td colspan="9" class="salary_empty">暂无工资项目</td></tr>
				{% endfor %}
			</table>
		</div>
		<div class="salary_block">
			<h3>初始工资表</h3>
			<div class="salary_tip">初始工资表用于维护员工基础工资数据。人数少的企业可以直接在表格中录入；也可以先下载模板、Excel导入后再修改。公式项目会按公式自动计算，金额统一保留2位小数。</div>
			<div style="margin-bottom:10px;">
				<a class="salary_btn salary_btn_gray" href="{{helper.createUrl(['p':'salary/initialtemplate'])}}" target="_blank" download="salary_initial_template.xls">初始工资表模板下载</a>
				<form class="inline_form" method="post" action="{{helper.createUrl(['p':'salary/uploadinitialsalary'])}}" enctype="multipart/form-data" style="margin-left:8px;">
					<input type="file" name="initial_file" accept=".xls,.xlsx" />
					<button class="salary_btn" type="submit">Excel导入初始工资表</button>
				</form>
			</div>
			<form method="post" action="{{helper.createUrl(['p':'salary/saveinitialsalary'])}}">
				<div class="salary_scroll">
					<table class="salary_table salary_sheet">
						<tr>
							<th width="120">员工</th>
							<th width="120">手机号</th>
							<th width="140">部门</th>
							{% for project in initialProjects %}
								{% if project['status']=='active' and project['deleted_at']==0 %}
								<th>{{project['name']}}<br />{{project['calculation_mode_label']}}</th>
								{% endif %}
							{% endfor %}
						</tr>
						{% for employee in initialEmployees %}
						<tr>
							<td>{{employee['name']}}</td>
							<td>{{employee['mobile']}}</td>
							<td>{{employee['department_name']}}</td>
							{% for project in initialProjects %}
								{% if project['status']=='active' and project['deleted_at']==0 %}
								<td>
									{% if project['is_text_project'] %}
									<input type="text" class="salary_text_input" name="amount[{{employee['id']}}][{{project['id']}}]" value="{% if employee['values'][project['id']] is defined %}{{employee['values'][project['id']]}}{% endif %}" />
									{% else %}
									<input type="text" name="amount[{{employee['id']}}][{{project['id']}}]" value="{% if employee['values'][project['id']] is defined %}{{employee['values'][project['id']]}}{% else %}0.00{% endif %}" {% if project['is_formula_project'] %}readonly="readonly"{% endif %} />
									{% endif %}
								</td>
								{% endif %}
							{% endfor %}
						</tr>
						{% elsefor %}
						<tr><td colspan="20" class="salary_empty">暂无员工数据</td></tr>
						{% endfor %}
					</table>
				</div>
				<div style="margin-top:10px;">
					<button class="salary_btn" type="submit">保存初始工资表</button>
				</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
function insertSalaryFormulaProject(button) {
	if (!button) {
		return;
	}
	insertSalaryFormulaText(button.getAttribute('data-project-name'));
}
function insertSalaryFormulaText(text) {
	var textarea = document.getElementById('salary_formula_text');
	if (!textarea || text === null || typeof text == 'undefined') {
		return;
	}
	text = String(text);
	textarea.focus();
	if (typeof textarea.selectionStart == 'number') {
		var start = textarea.selectionStart;
		var end = textarea.selectionEnd;
		var value = textarea.value;
		textarea.value = value.substring(0, start) + text + value.substring(end);
		textarea.selectionStart = textarea.selectionEnd = start + text.length;
	} else if (document.selection) {
		var range = document.selection.createRange();
		range.text = text;
	} else {
		textarea.value += text;
	}
}
function toggleSalaryFormulaBox() {
	var sourceType = document.getElementById('salary_project_source_type');
	var formulaRow = document.getElementById('salary_formula_row');
	var numberField = document.getElementById('salary_default_number_field');
	var textField = document.getElementById('salary_default_text_field');
	if (!sourceType || !formulaRow || !numberField || !textField) {
		return;
	}
	formulaRow.style.display = sourceType.value == 'calculated' ? 'block' : 'none';
	numberField.style.display = sourceType.value == 'number' ? 'inline-block' : 'none';
	textField.style.display = sourceType.value == 'text' ? 'inline-block' : 'none';
}
toggleSalaryFormulaBox();
</script>
