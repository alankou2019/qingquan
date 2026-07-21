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
.salary_sheet .initial_name_col{width:110px;min-width:110px;max-width:110px;}
.salary_sheet .initial_mobile_col{width:120px;min-width:120px;max-width:120px;}
.salary_sheet .initial_department_col{width:160px;min-width:160px;max-width:160px;}
.salary_sheet .initial_project_col{width:116px;min-width:116px;max-width:116px;}
.salary_sheet .initial_project_col .salary_bulk_copy{display:block;margin-top:4px;white-space:nowrap;}
.salary_two_line{display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden;max-height:36px;line-height:18px;white-space:normal;word-break:break-all;}
.initial_mobile_col .salary_two_line{display:block;white-space:nowrap;text-overflow:ellipsis;}
.salary_status_enabled{color:#15803d;}
.salary_status_disabled{color:#94a3b8;}
.salary_sheet .initial_summary{background:#eaf3ff;}
.salary_sheet .initial_earning{background:#edf9f1;}
.salary_sheet .initial_deduction{background:#fff4e8;}
.salary_sheet .initial_statistic{background:#f3f0ff;}
.salary_sheet .initial_data{background:#edf7f8;}
.salary_sheet .initial_note{background:#fffbe8;}
.salary_sheet .initial_other{background:#f8fafc;}
.salary_row_actions{min-width:105px;white-space:nowrap;}
.salary_row_edit_actions{display:none;}
.salary_removed_box{margin-top:10px;padding:10px 12px;border:1px solid #d9e2ef;background:#fbfdff;color:#64748b;}
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
.salary_bulk_copy{border:0;background:none;color:#4560e6;cursor:pointer;padding:0;font-size:11px;}
.salary_bulk_dialog{display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(15,23,42,.28);}
.salary_bulk_dialog_box{width:320px;margin:16vh auto 0;background:#fff;border:1px solid #cbd5e1;padding:16px;box-shadow:0 8px 24px rgba(15,23,42,.18);}
.salary_bulk_dialog_title{color:#1f2937;font-size:15px;margin-bottom:10px;}
.salary_bulk_dialog input{box-sizing:border-box;width:100%;height:32px;border:1px solid #cbd5e1;padding:0 8px;}
.salary_bulk_dialog_actions{margin-top:14px;text-align:right;}
.salary_bulk_dialog_actions .salary_btn{margin-left:8px;}
.salary_initial_actions{margin-top:10px;text-align:right;}
.salary_initial_actions .salary_btn{margin-left:8px;}
.salary_initial_unsaved{display:none;margin-right:10px;color:#b45309;}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li class="on"><a href="#">工资项目设置</a></li>
			<li style="float:right;width:140px;border-left:1px solid #efefef;border-right:0;"><a href="{{helper.createUrl(['p':'salary/index'])}}">返回薪酬首页</a></li>
		</ul>
	</div>
	<div class="salary_page">
		<div id="salary_inline_delete_message" style="display:none;margin:0 0 12px 0;padding:8px 12px;border:1px solid #bbf7d0;background:#f0fdf4;color:#166534;"></div>
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
			<table class="salary_table">
					<tr>
						<th width="8%">状态</th>
						<th width="22%">项目名称</th>
						<th width="14%">项目类别</th>
						<th width="14%">项目属性</th>
						<th width="20%">说明</th>
						<th>操作</th>
					</tr>
					{% for item in templates %}
					<tr id="salary_template_row_{{item['id']}}" data-salary-template-id="{{item['id']}}">
						<td class="salary_template_status">{% if item['is_selected'] %}<span class="salary_status_enabled">已启用</span>{% else %}<span class="salary_status_disabled">未启用</span>{% endif %}</td>
						<td>{{item['name']}}</td>
						<td>{{item['direction_label']}}</td>
						<td>{{item['source_type_label']}}</td>
						<td>{% if item['linked_module']!='none' %}关联 {{item['linked_module']}}{% else %}-{% endif %}</td>
						<td class="salary_template_action">
							<a class="salary_link_btn" href="{{helper.createUrl(['p':'salary/project','template_id':item['id']])}}">编辑</a>
							{% if !item['is_selected'] %}
							<form class="inline_form" method="post" action="{{helper.createUrl(['p':'salary/projectenabletemplate'])}}" onsubmit="return enableSalaryTemplate(this);">
								<input type="hidden" name="template_id" value="{{item['id']}}" />
								<button class="salary_link_btn" type="submit" data-enable-url="{{helper.createUrl(['p':'salary/projectenabletemplate'])}}">启用</button>
							</form>
							{% endif %}
						</td>
					</tr>
					{% elsefor %}
					<tr><td colspan="6" class="salary_empty">暂无平台通用项目</td></tr>
					{% endfor %}
			</table>
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
			<table id="salary_company_projects_table" class="salary_table">
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
				<tr id="salary_project_row_{{item['id']}}" {% if item['template_id'] %}data-salary-template-id="{{item['template_id']}}"{% endif %}>
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
						{% if item['template_id'] %}
						<form class="inline_form" method="post" action="{{helper.createUrl(['p':'salary/projectdisable'])}}" onsubmit="return confirm('确定停用这个工资项目吗？');">
							<input type="hidden" name="id" value="{{item['id']}}" />
							<button class="salary_link_btn" type="submit">停用</button>
						</form>
						{% else %}
						<button class="salary_link_btn" type="button" data-delete-url="{{helper.createUrl(['p':'salary/projectdelete'])}}" data-delete-row-id="salary_project_row_{{item['id']}}" data-delete-confirm="确定删除这个工资项目吗？删除后不会影响历史工资表和归档记录。" onclick="return salaryInlineDelete(this, {id:{{item['id']}}});">删除</button>
						{% endif %}
					</td>
				</tr>
				{% elsefor %}
				<tr class="salary_project_empty"><td colspan="9" class="salary_empty">暂无工资项目</td></tr>
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
			<form id="initial_salary_form" method="post" action="{{helper.createUrl(['p':'salary/saveinitialsalary'])}}">
				<input type="hidden" id="initial_salary_employee_id" name="initial_salary_employee_id" value="0" />
				<div class="salary_scroll">
					<table class="salary_table salary_sheet">
						<tr>
							<th class="initial_name_col">员工</th>
							<th class="initial_mobile_col">手机号</th>
							<th class="initial_department_col">部门</th>
							{% for project in initialProjects %}
								{% if project['status']=='active' and project['deleted_at']==0 %}
								<th class="initial_{{project['initial_group']}} initial_project_col" title="{{project['name']}}"><span class="salary_two_line">{{project['name']}}</span>{% if !project['is_text_project'] and !project['is_formula_project'] and !project['is_summary_project'] %}<button class="salary_bulk_copy" type="button" data-project-id="{{project['id']}}" data-project-name="{{project['name']}}" onclick="openInitialSalaryBulkCopy(this);">全部复制</button>{% endif %}</th>
								{% endif %}
							{% endfor %}
							<th>操作</th>
						</tr>
						{% for employee in initialEmployees %}
						<tr id="initial_salary_row_{{employee['id']}}">
							<td class="initial_name_col" title="{{employee['name']}}"><span class="salary_two_line">{{employee['name']}}</span></td>
							<td class="initial_mobile_col" title="{{employee['mobile']}}"><span class="salary_two_line">{{employee['mobile']}}</span></td>
							<td class="initial_department_col" title="{{employee['department_name']}}"><span class="salary_two_line">{{employee['department_name']}}</span></td>
							{% for project in initialProjects %}
								{% if project['status']=='active' and project['deleted_at']==0 %}
								<td class="initial_{{project['initial_group']}}" data-project-id="{{project['id']}}" data-project-name="{{project['name']}}" data-project-kind="{% if project['is_summary_project'] %}summary{% elseif project['is_formula_project'] %}formula{% elseif project['is_text_project'] %}text{% else %}number{% endif %}" data-formula="{% if project['formula_text'] is defined %}{{project['formula_text']}}{% endif %}" data-include-earning="{% if project['include_earning'] is defined %}{{project['include_earning']}}{% else %}0{% endif %}" data-include-deduction="{% if project['include_deduction'] is defined %}{{project['include_deduction']}}{% else %}0{% endif %}" data-summary-key="{{project['value_key']}}">
									{% if project['is_text_project'] %}
									<input type="text" class="salary_text_input" name="amount[{{employee['id']}}][{{project['id']}}]" value="{% if employee['values'][project['value_key']] is defined %}{{employee['values'][project['value_key']]}}{% endif %}" readonly="readonly" />
									{% else %}
									<input type="text" {% if !project['is_summary_project'] %}name="amount[{{employee['id']}}][{{project['id']}}]"{% endif %} value="{% if employee['values'][project['value_key']] is defined %}{{employee['values'][project['value_key']]}}{% else %}0.00{% endif %}" readonly="readonly" {% if project['is_formula_project'] or project['is_summary_project'] %}data-always-readonly="1"{% else %}oninput="recalculateInitialSalaryRow(this);"{% endif %} />
									{% endif %}
								</td>
								{% endif %}
							{% endfor %}
							<td class="salary_row_actions">
								<span class="salary_row_default_actions">
									<button class="salary_link_btn" type="button" onclick="editInitialSalaryRow({{employee['id']}});">编辑</button>　
					<button class="salary_link_btn" type="button" data-delete-url="{{helper.createUrl(['p':'salary/deleteinitialsalaryemployee'])}}" data-delete-row-id="initial_salary_row_{{employee['id']}}" data-delete-confirm="只会从初始工资表移出该员工，不影响人事档案、部门和历史工资记录。确认删除吗？" onclick="return salaryInlineDelete(this, {initial_salary_employee_id:{{employee['id']}}});">删除</button>
								</span>
								<span class="salary_row_edit_actions">
									<button class="salary_link_btn" type="submit" onclick="return prepareInitialSalarySave({{employee['id']}});">保存</button>　
									<button class="salary_link_btn" type="button" onclick="cancelInitialSalaryRow({{employee['id']}});">取消</button>
								</span>
							</td>
						</tr>
						{% elsefor %}
						<tr><td colspan="20" class="salary_empty">暂无员工数据</td></tr>
						{% endfor %}
					</table>
				</div>
				<div class="salary_initial_actions">
					<span id="initial_salary_unsaved" class="salary_initial_unsaved">当前数据尚未保存</span>
					<button class="salary_btn salary_btn_gray" type="button" onclick="clearInitialSalaryTable();">清空当前数据</button>
					<button class="salary_btn" type="submit" onclick="return prepareInitialSalarySave(0);">保存整张工资表</button>
				</div>
			</form>
			<div id="initial_salary_bulk_dialog" class="salary_bulk_dialog" role="dialog" aria-modal="true" aria-labelledby="initial_salary_bulk_title">
				<div class="salary_bulk_dialog_box">
					<div id="initial_salary_bulk_title" class="salary_bulk_dialog_title">批量填写</div>
					<div>请输入要复制到所有员工的数字：</div>
					<input id="initial_salary_bulk_value" type="number" step="0.01" inputmode="decimal" value="0.00" />
					<div class="salary_bulk_dialog_actions">
						<button class="salary_btn salary_btn_gray" type="button" onclick="closeInitialSalaryBulkCopy();">取消</button>
						<button class="salary_btn" type="button" onclick="applyInitialSalaryBulkCopy();">确定</button>
					</div>
				</div>
			</div>
			{% if excludedInitialEmployees %}
			<div class="salary_removed_box">
				已移出初始工资表：
				{% for employee in excludedInitialEmployees %}
				<form class="inline_form" method="post" action="{{helper.createUrl(['p':'salary/restoreinitialsalaryemployee'])}}">
					<input type="hidden" name="employee_id" value="{{employee['id']}}" />
					{{employee['name']}} <button class="salary_link_btn" type="submit">恢复</button>
				</form>　
				{% endfor %}
			</div>
			{% endif %}
		</div>
	</div>
</div>
<script src="/skin/adminhtml/default/js/salary-inline-delete.js?v=20260717-2"></script>
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
function showSalaryTemplateMessage(message, isError) {
	var box = document.getElementById('salary_inline_delete_message');
	if (!box) {
		return;
	}
	box.innerHTML = message || '';
	box.style.display = 'block';
	box.style.borderColor = isError ? '#fecaca' : '#bbf7d0';
	box.style.background = isError ? '#fef2f2' : '#f0fdf4';
	box.style.color = isError ? '#b91c1c' : '#166534';
}
function getSalaryTemplateActionCell(row) {
	var cells = row ? row.getElementsByTagName('td') : [];
	return cells.length ? cells[cells.length - 1] : null;
}
function updateSalaryTemplateRow(templateId, project) {
	var row = document.getElementById('salary_template_row_' + templateId);
	if (!row) {
		return;
	}
	var status = row.getElementsByClassName('salary_template_status')[0];
	if (status) {
		status.innerHTML = '<span class="salary_status_enabled">已启用</span>';
	}
	var action = row.getElementsByClassName('salary_template_action')[0];
	if (action) {
		var forms = action.getElementsByTagName('form');
		for (var i = forms.length - 1; i >= 0; i--) {
			forms[i].parentNode.removeChild(forms[i]);
		}
	}
	appendSalaryProjectRow(project);
}
function appendSalaryProjectRow(project) {
	if (!project || !project.id) {
		return;
	}
	var table = document.getElementById('salary_company_projects_table');
	if (!table) {
		return;
	}
	var existing = document.getElementById('salary_project_row_' + project.id);
	if (existing) {
		return;
	}
	var empty = table.getElementsByClassName('salary_project_empty');
	for (var i = empty.length - 1; i >= 0; i--) {
		empty[i].parentNode.removeChild(empty[i]);
	}
	var row = table.insertRow(-1);
	row.id = 'salary_project_row_' + project.id;
	row.setAttribute('data-salary-template-id', project.template_id || '');
	var values = [project.project_kind_label, project.name, project.direction_label, project.source_type_label, project.calculation_mode_label];
	for (var j = 0; j < values.length; j++) {
		var cell = row.insertCell(-1);
		if (j === 0) {
			var badge = document.createElement('span');
			badge.className = 'salary_badge';
			badge.appendChild(document.createTextNode(values[j] || '通用项目'));
			cell.appendChild(badge);
		} else {
			cell.appendChild(document.createTextNode(values[j] || ''));
		}
	}
	var flags = ['include_earning', 'include_deduction', 'include_net'];
	for (var k = 0; k < flags.length; k++) {
		var flagCell = row.insertCell(-1);
		flagCell.appendChild(document.createTextNode(parseInt(project[flags[k]], 10) ? '是' : '否'));
	}
	var action = row.insertCell(-1);
	var edit = document.createElement('a');
	edit.className = 'salary_link_btn';
	edit.href = project.edit_url || '#';
	edit.appendChild(document.createTextNode('编辑'));
	action.appendChild(edit);
	action.appendChild(document.createTextNode(' '));
	var disableForm = document.createElement('form');
	disableForm.className = 'inline_form';
	disableForm.method = 'post';
	disableForm.action = project.disable_url || '';
	disableForm.onsubmit = function() { return confirm('确定停用这个工资项目吗？'); };
	var hidden = document.createElement('input');
	hidden.type = 'hidden';
	hidden.name = 'id';
	hidden.value = project.id;
	disableForm.appendChild(hidden);
	var disableButton = document.createElement('button');
	disableButton.className = 'salary_link_btn';
	disableButton.type = 'submit';
	disableButton.appendChild(document.createTextNode('停用'));
	disableForm.appendChild(disableButton);
	action.appendChild(disableForm);
}
function enableSalaryTemplate(form) {
	if (!form) {
		return false;
	}
	var button = form.getElementsByTagName('button')[0];
	var templateInput = form.getElementsByTagName('input')[0];
	if (!button || !templateInput) {
		return false;
	}
	button.disabled = true;
	var xhr = new XMLHttpRequest();
	xhr.open('POST', form.action, true);
	xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
	xhr.onreadystatechange = function() {
		if (xhr.readyState !== 4) {
			return;
		}
		button.disabled = false;
		var result = null;
		try {
			result = JSON.parse(xhr.responseText);
		} catch (e) {
			showSalaryTemplateMessage('启用失败，请稍后重试', true);
			return;
		}
		if (result && result.status == 'y' && result.data && result.data.project) {
			updateSalaryTemplateRow(templateInput.value, result.data.project);
			showSalaryTemplateMessage(result.data.message || '通用工资项目已启用', false);
		} else {
			showSalaryTemplateMessage(result && result.error ? result.error : '启用失败，请稍后重试', true);
		}
	};
	xhr.send('template_id=' + encodeURIComponent(templateInput.value) + '&salary_ajax=1');
	return false;
}
var initialSalaryDirty = false;
function salaryMoney(value) {
	value = parseFloat(String(value || '').replace(/,/g, ''));
	if (isNaN(value) || !isFinite(value)) {
		value = 0;
	}
	return Math.round(value * 100) / 100;
}
function formatSalaryMoney(value) {
	return salaryMoney(value).toFixed(2);
}
function markInitialSalaryUnsaved() {
	initialSalaryDirty = true;
	var notice = document.getElementById('initial_salary_unsaved');
	if (notice) {
		notice.style.display = 'inline';
	}
}
function getInitialSalaryCellInput(cell) {
	var inputs = cell ? cell.getElementsByTagName('input') : [];
	return inputs.length ? inputs[0] : null;
}
function calculateInitialSalaryFormula(formula, amountMap) {
	var expression = String(formula || '');
	var names = [];
	for (var name in amountMap) {
		if (amountMap.hasOwnProperty(name) && name) {
			names.push(name);
		}
	}
	names.sort(function(a, b) { return b.length - a.length; });
	for (var i = 0; i < names.length; i++) {
		expression = expression.split(names[i]).join('(' + formatSalaryMoney(amountMap[names[i]]) + ')');
	}
	expression = expression.replace(/\s+/g, '');
	if (!expression || !/^[0-9\.\+\-\*\/\(\)]+$/.test(expression)) {
		return 0;
	}
	var result = 0;
	try {
		result = Function('"use strict";return (' + expression + ');')();
	} catch (e) {
		result = 0;
	}
	return salaryMoney(result);
}
function recalculateInitialSalaryRow(source, markDirty) {
	var row = source;
	while (row && row.tagName && row.tagName.toLowerCase() != 'tr') {
		row = row.parentNode;
	}
	if (!row) {
		return;
	}
	var cells = row.getElementsByTagName('td');
	var amountMap = {};
	var formulaCells = [];
	var earningTotal = 0;
	var deductionTotal = 0;
	for (var i = 0; i < cells.length; i++) {
		var kind = cells[i].getAttribute('data-project-kind');
		var input = getInitialSalaryCellInput(cells[i]);
		if (!kind || !input) {
			continue;
		}
		if (kind == 'number') {
			var amount = salaryMoney(input.value);
			amountMap[cells[i].getAttribute('data-project-name') || ''] = amount;
			if (parseInt(cells[i].getAttribute('data-include-earning'), 10)) {
				earningTotal += amount;
			}
			if (parseInt(cells[i].getAttribute('data-include-deduction'), 10)) {
				deductionTotal += amount;
			}
		} else if (kind == 'formula') {
			formulaCells.push(cells[i]);
		}
	}
	for (var j = 0; j < formulaCells.length; j++) {
		var formulaInput = getInitialSalaryCellInput(formulaCells[j]);
		var formulaAmount = calculateInitialSalaryFormula(formulaCells[j].getAttribute('data-formula'), amountMap);
		formulaInput.value = formatSalaryMoney(formulaAmount);
		amountMap[formulaCells[j].getAttribute('data-project-name') || ''] = formulaAmount;
		if (parseInt(formulaCells[j].getAttribute('data-include-earning'), 10)) {
			earningTotal += formulaAmount;
		}
		if (parseInt(formulaCells[j].getAttribute('data-include-deduction'), 10)) {
			deductionTotal += formulaAmount;
		}
	}
	for (var k = 0; k < cells.length; k++) {
		if (cells[k].getAttribute('data-project-kind') != 'summary') {
			continue;
		}
		var summaryInput = getInitialSalaryCellInput(cells[k]);
		var summaryKey = cells[k].getAttribute('data-summary-key');
		if (summaryKey == 'summary_earning_total') {
			summaryInput.value = formatSalaryMoney(earningTotal);
		} else if (summaryKey == 'summary_deduction_total') {
			summaryInput.value = formatSalaryMoney(deductionTotal);
		} else if (summaryKey == 'summary_net_total') {
			summaryInput.value = formatSalaryMoney(earningTotal - deductionTotal);
		}
	}
	if (markDirty !== false) {
		markInitialSalaryUnsaved();
	}
}
function initializeInitialSalaryCalculations() {
	var form = document.getElementById('initial_salary_form');
	if (!form) {
		return;
	}
	var rows = form.getElementsByTagName('tr');
	for (var i = 0; i < rows.length; i++) {
		if (rows[i].id && rows[i].id.indexOf('initial_salary_row_') === 0) {
			recalculateInitialSalaryRow(rows[i], false);
		}
	}
}
function getInitialSalaryRow(employeeId) {
	return document.getElementById('initial_salary_row_' + employeeId);
}
function editInitialSalaryRow(employeeId) {
	var row = getInitialSalaryRow(employeeId);
	if (!row) {
		return;
	}
	var inputs = row.getElementsByTagName('input');
	for (var i = 0; i < inputs.length; i++) {
		inputs[i].setAttribute('data-original-value', inputs[i].value);
		if (inputs[i].getAttribute('data-always-readonly') != '1') {
			inputs[i].removeAttribute('readonly');
		}
	}
	row.getElementsByClassName('salary_row_default_actions')[0].style.display = 'none';
	row.getElementsByClassName('salary_row_edit_actions')[0].style.display = 'inline';
}
function cancelInitialSalaryRow(employeeId) {
	var row = getInitialSalaryRow(employeeId);
	if (!row) {
		return;
	}
	var inputs = row.getElementsByTagName('input');
	for (var i = 0; i < inputs.length; i++) {
		if (inputs[i].getAttribute('data-original-value') !== null) {
			inputs[i].value = inputs[i].getAttribute('data-original-value');
		}
		inputs[i].setAttribute('readonly', 'readonly');
	}
	recalculateInitialSalaryRow(row, false);
	row.getElementsByClassName('salary_row_default_actions')[0].style.display = 'inline';
	row.getElementsByClassName('salary_row_edit_actions')[0].style.display = 'none';
}
function prepareInitialSalarySave(employeeId) {
	document.getElementById('initial_salary_employee_id').value = employeeId;
	initialSalaryDirty = false;
	return true;
}
var initialSalaryBulkProjectId = 0;
var initialSalaryBulkProjectName = '';
function openInitialSalaryBulkCopy(button) {
	if (!button) {
		return;
	}
	initialSalaryBulkProjectId = parseInt(button.getAttribute('data-project-id'), 10) || 0;
	initialSalaryBulkProjectName = button.getAttribute('data-project-name') || '工资项目';
	if (!initialSalaryBulkProjectId) {
		return;
	}
	document.getElementById('initial_salary_bulk_title').textContent = '全部复制：' + initialSalaryBulkProjectName;
	document.getElementById('initial_salary_bulk_value').value = '0.00';
	document.getElementById('initial_salary_bulk_dialog').style.display = 'block';
	document.getElementById('initial_salary_bulk_value').focus();
}
function closeInitialSalaryBulkCopy() {
	document.getElementById('initial_salary_bulk_dialog').style.display = 'none';
}
function applyInitialSalaryBulkCopy() {
	var input = document.getElementById('initial_salary_bulk_value');
	var value = parseFloat(input.value);
	if (isNaN(value) || !isFinite(value)) {
		alert('请输入有效数字');
		input.focus();
		return;
	}
	value = (Math.round(value * 100) / 100).toFixed(2);
	var fields = document.getElementById('initial_salary_form').getElementsByTagName('input');
	var suffix = '][' + initialSalaryBulkProjectId + ']';
	var targets = [];
	for (var i = 0; i < fields.length; i++) {
		var name = fields[i].getAttribute('name') || '';
		if (name.indexOf('amount[') === 0 && name.slice(-suffix.length) === suffix) {
			targets.push(fields[i]);
		}
	}
	if (!targets.length) {
		alert('没有找到可填写的员工数据');
		return;
	}
	if (!confirm('确定将“' + initialSalaryBulkProjectName + '”统一设置为 ' + value + ' 吗？填写后请点击“保存整张工资表”。')) {
		return;
	}
	for (var targetIndex = 0; targetIndex < targets.length; targetIndex++) {
		targets[targetIndex].value = value;
	}
	var rows = document.getElementById('initial_salary_form').getElementsByTagName('tr');
	for (var j = 0; j < rows.length; j++) {
		if (rows[j].id && rows[j].id.indexOf('initial_salary_row_') === 0) {
			recalculateInitialSalaryRow(rows[j], false);
		}
	}
	markInitialSalaryUnsaved();
	closeInitialSalaryBulkCopy();
}
function clearInitialSalaryTable() {
	if (!confirm('确定清空当前初始工资数据吗？数字项将变为0.00，文本项将清空。点击“保存整张工资表”后才会写入数据库。')) {
		return;
	}
	var form = document.getElementById('initial_salary_form');
	var cells = form.getElementsByTagName('td');
	for (var i = 0; i < cells.length; i++) {
		var kind = cells[i].getAttribute('data-project-kind');
		var input = getInitialSalaryCellInput(cells[i]);
		if (!input) {
			continue;
		}
		if (kind == 'number') {
			input.value = '0.00';
		} else if (kind == 'text') {
			input.value = '';
		}
	}
	var rows = form.getElementsByTagName('tr');
	for (var j = 0; j < rows.length; j++) {
		if (rows[j].id && rows[j].id.indexOf('initial_salary_row_') === 0) {
			recalculateInitialSalaryRow(rows[j], false);
		}
	}
	markInitialSalaryUnsaved();
}
function prepareInitialSalaryDelete(employeeId) {
	document.getElementById('initial_salary_employee_id').value = employeeId;
	return confirm('只会从初始工资表移出该员工，不影响人事档案、部门和历史工资记录。确认删除吗？');
}
toggleSalaryFormulaBox();
initializeInitialSalaryCalculations();
window.onbeforeunload = function() {
	if (initialSalaryDirty) {
		return '初始工资表还有未保存的数据';
	}
};
</script>
