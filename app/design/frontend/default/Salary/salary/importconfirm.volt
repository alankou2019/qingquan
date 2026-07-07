<style>
.salary_page{padding:18px;}
.salary_tip{border:1px solid #d9e2ef;background:#fbfdff;padding:12px 14px;margin-bottom:14px;color:#475569;line-height:22px;}
.salary_table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #d9e2ef;margin-bottom:14px;}
.salary_table th{background:#f8fafc;color:#334155;font-weight:normal;text-align:left;padding:10px;border-bottom:1px solid #d9e2ef;}
.salary_table td{padding:10px;border-bottom:1px solid #edf2f7;color:#475569;}
.salary_btn{display:inline-block;background:#4560e6;color:#fff;border:0;padding:0 16px;line-height:32px;height:32px;text-decoration:none;cursor:pointer;margin-right:8px;}
.salary_btn_gray{background:#64748b;}
.salary_error{border:1px solid #f4c7c7;background:#fff8f8;color:#9f1d1d;padding:12px 14px;margin-bottom:14px;line-height:22px;}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li class="on"><a href="#">首次导入确认</a></li>
		</ul>
	</div>
	<div class="salary_page">
		{% if previewOk %}
		<div class="salary_tip">
			当前企业还没有工资项目设置。系统将按这份Excel的表头自动生成工资项目，确认后再导入 {{payrollMonth}} 的工资表。
		</div>
		<table class="salary_table">
			<thead>
				<tr>
					<th>工资项目</th>
					<th>系统初步识别项目类别</th>
				</tr>
			</thead>
			<tbody>
				{% for item in previewProjects %}
				<tr>
					<td>{{item['name']}}</td>
					<td>{{item['direction_name']}}</td>
				</tr>
				{% endfor %}
			</tbody>
		</table>
		<form method="post" action="{{helper.createUrl(['p':'salary/confirmpayrollimport'])}}" onsubmit="return confirm('确认生成工资项目并导入工资表吗？');">
			<input type="hidden" name="payroll_month" value="{{payrollMonth}}" />
			<input type="hidden" name="uploaded_file" value="{{uploadedFile}}" />
			<input type="hidden" name="source_name" value="{{sourceName}}" />
			<button class="salary_btn" type="submit">确认生成并导入</button>
			<a class="salary_btn salary_btn_gray" href="{{helper.createUrl(['p':'salary/payroll'])}}">返回重新上传</a>
		</form>
		{% else %}
		<div class="salary_error">这份Excel暂时不能导入，请按下面异常提示修正后重新上传。</div>
		<table class="salary_table">
			<thead>
				<tr>
					<th>行号</th>
					<th>姓名</th>
					<th>手机号</th>
					<th>异常原因</th>
				</tr>
			</thead>
			<tbody>
				{% for error in errors %}
				<tr>
					<td>{{error['row']}}</td>
					<td>{{error['name']}}</td>
					<td>{{error['mobile']}}</td>
					<td>{{error['reason']}}</td>
				</tr>
				{% endfor %}
			</tbody>
		</table>
		<a class="salary_btn salary_btn_gray" href="{{helper.createUrl(['p':'salary/payroll'])}}">返回重新上传</a>
		{% endif %}
	</div>
</div>
