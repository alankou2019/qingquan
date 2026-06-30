<style>
.salary_page{padding:18px;}
.salary_toolbar{margin-bottom:12px;}
.salary_toolbar .btn{display:inline-block;background:#4560e6;color:#fff;padding:0 14px;line-height:30px;height:30px;text-decoration:none;margin-right:8px;border:0;cursor:pointer;}
.salary_toolbar .btn_gray{background:#64748b;}
.salary_import_box{border:1px solid #d9e2ef;background:#fbfdff;padding:12px 14px;margin-bottom:12px;color:#475569;}
.salary_import_box label{display:inline-block;margin-right:10px;}
.salary_import_box input[type=text]{height:28px;line-height:28px;border:1px solid #cbd5e1;padding:0 8px;width:90px;}
.salary_import_box input[type=file]{height:30px;line-height:30px;}
.salary_table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #d9e2ef;}
.salary_table th{background:#f8fafc;color:#334155;font-weight:normal;text-align:left;padding:10px;border-bottom:1px solid #d9e2ef;}
.salary_table td{padding:10px;border-bottom:1px solid #edf2f7;color:#475569;vertical-align:top;}
.salary_table .money{text-align:right;color:#1f2937;}
.salary_table .operate{text-align:left;white-space:nowrap;}
.salary_status{display:inline-block;padding:0 8px;height:24px;line-height:24px;background:#eef2ff;color:#3949ab;}
.salary_status_done{background:#e8f7ef;color:#16803c;}
.salary_status_warn{background:#fff7e6;color:#a15c00;}
.salary_empty{border:1px solid #d9e2ef;background:#fbfdff;padding:18px;color:#64748b;}
.salary_tip{color:#64748b;line-height:24px;margin:0 0 12px 0;}
.salary_link_btn{border:0;background:none;color:#4560e6;cursor:pointer;padding:0;font-size:12px;}
.salary_disabled{color:#94a3b8;}
.audit_list{margin-top:6px;color:#64748b;line-height:22px;}
.audit_list span{display:block;}
.inline_form{display:inline-block;margin:0 8px 4px 0;}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li class="on"><a href="#">工资表核算</a></li>
		</ul>
	</div>
	<div class="salary_page">
		<div class="salary_toolbar">
			<a class="btn btn_gray" href="{{helper.createUrl(['p':'salary/payrolltemplate'])}}">下载Excel模板</a>
			<a class="btn btn_gray" href="{{helper.createUrl(['p':'salary/index'])}}">返回薪酬首页</a>
		</div>
		<form class="salary_import_box" method="post" action="{{helper.createUrl(['p':'salary/uploadpayroll'])}}" enctype="multipart/form-data" onsubmit="return confirm('确定上传并校验这份工资表吗？');">
			<label>工资月份 <input type="text" name="payroll_month" value="{{defaultPayrollMonth}}" placeholder="2026-06" /></label>
			<label>工资表Excel <input type="file" name="payroll_file" accept=".xls,.xlsx" /></label>
			<button class="btn" type="submit">Excel导入</button>
		</form>
		<div class="salary_tip">工资表由HR核算后提交审核；薪酬管理授权中设置的审核人全部同意后，HR才能发工资条并归档。首次导入时，如企业还没有工资项目，系统会按Excel表头生成工资项目并请HR确认。</div>
		{% if periods is empty %}
		<div class="salary_empty">暂无工资表记录。</div>
		{% else %}
		<table class="salary_table">
			<thead>
				<tr>
					<th>工资月份</th>
					<th>来源</th>
					<th>状态</th>
					<th>审核进度</th>
					<th>人数</th>
					<th class="money">应发合计</th>
					<th class="money">扣款合计</th>
					<th class="money">实发合计</th>
					<th>发放/归档时间</th>
					<th class="operate">操作</th>
				</tr>
			</thead>
			<tbody>
				{% for period in periods %}
				<tr>
					<td>{{period['payroll_month']}}</td>
					<td>{{period['source_label']}}</td>
					<td>
						<span class="salary_status {% if period['status']=='published' %}salary_status_done{% elseif period['status']=='submitted' %}salary_status_warn{% endif %}">{{period['status_name']}}</span>
					</td>
					<td>
						{{period['audit_text']}}
						{% if period['audit']['items'] is not empty %}
						<div class="audit_list">
							{% for audit in period['audit']['items'] %}
							<span>{{audit['reviewer_name']}}：{% if audit['status']=='approved' %}已同意{% elseif audit['status']=='rejected' %}已驳回{% else %}待审核{% endif %}</span>
							{% endfor %}
						</div>
						{% endif %}
					</td>
					<td>{{period['row_count']}}</td>
					<td class="money">{{period['earning_total']}}</td>
					<td class="money">{{period['deduction_total']}}</td>
					<td class="money">{{period['net_total']}}</td>
					<td>{{period['published_time']}}</td>
					<td class="operate">
						{% if period['status']=='published' %}
							<span class="salary_disabled">已发工资条 {{period['published_count']}} 人</span>
						{% elseif period['can_publish'] and canSendPayslip %}
							<form class="inline_form" method="post" action="{{helper.createUrl(['p':'salary/sendpayslip'])}}" onsubmit="return confirm('审核已通过，确定发工资条并归档吗？');">
								<input type="hidden" name="id" value="{{period['id']}}" />
								<button class="salary_link_btn" type="submit">发工资条并归档</button>
							</form>
						{% elseif period['status']=='submitted' %}
							{% for audit in period['audit']['items'] %}
								{% if audit['status']=='pending' %}
								<form class="inline_form" method="post" action="{{helper.createUrl(['p':'salary/reviewperiod'])}}">
									<input type="hidden" name="id" value="{{period['id']}}" />
									<input type="hidden" name="reviewer_id" value="{{audit['reviewer_id']}}" />
									<input type="hidden" name="status" value="approved" />
									<button class="salary_link_btn" type="submit">{{audit['reviewer_name']}}同意</button>
								</form>
								<form class="inline_form" method="post" action="{{helper.createUrl(['p':'salary/reviewperiod'])}}" onsubmit="return confirm('确定驳回这张工资表吗？');">
									<input type="hidden" name="id" value="{{period['id']}}" />
									<input type="hidden" name="reviewer_id" value="{{audit['reviewer_id']}}" />
									<input type="hidden" name="status" value="rejected" />
									<button class="salary_link_btn" type="submit">驳回</button>
								</form>
								{% endif %}
							{% endfor %}
						{% elseif period['can_submit_audit'] %}
							<form class="inline_form" method="post" action="{{helper.createUrl(['p':'salary/submitreview'])}}" onsubmit="return confirm('确定提交工资表审核吗？');">
								<input type="hidden" name="id" value="{{period['id']}}" />
								<button class="salary_link_btn" type="submit">提交审核</button>
							</form>
						{% elseif !canSendPayslip %}
							<span class="salary_disabled">工资条功能未开通</span>
						{% else %}
							<span class="salary_disabled">等待审核完成</span>
						{% endif %}
					</td>
				</tr>
				{% endfor %}
			</tbody>
		</table>
		{% endif %}
	</div>
</div>
