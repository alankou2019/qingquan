<style>
.salary_page{padding:18px;}
.salary_tip{border:1px solid #d9e2ef;background:#fbfdff;padding:12px 14px;margin-bottom:14px;color:#475569;line-height:22px;}
.salary_success{border:1px solid #bfe3ce;background:#f5fff8;color:#166534;padding:12px 14px;margin-bottom:14px;line-height:24px;}
.salary_error{border:1px solid #f4c7c7;background:#fff8f8;color:#9f1d1d;padding:12px 14px;margin-bottom:14px;line-height:22px;}
.salary_table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #d9e2ef;margin-bottom:14px;}
.salary_table th{background:#f8fafc;color:#334155;font-weight:normal;text-align:left;padding:10px;border-bottom:1px solid #d9e2ef;}
.salary_table td{padding:10px;border-bottom:1px solid #edf2f7;color:#475569;}
.salary_btn{display:inline-block;background:#4560e6;color:#fff;border:0;padding:0 16px;line-height:32px;height:32px;text-decoration:none;cursor:pointer;margin-right:8px;}
.salary_btn_gray{background:#64748b;}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li class="on"><a href="#">工资表导入结果</a></li>
			<li style="float:right;width:140px;border-left:1px solid #efefef;border-right:0;"><a href="{{helper.createUrl(['p':'salary/index'])}}">返回薪酬首页</a></li>
		</ul>
	</div>
	<div class="salary_page">
		{% if result %}
		<div class="salary_success">
			工资表导入成功，共导入 {{result['employee_count']}} 人。应发总额 {{result['earning_total']}}，应扣总额 {{result['deduction_total']}}，实发总额 {{result['net_total']}}。下一步可在工资表核算页面提交审核。
		</div>
		<a class="salary_btn" href="{{helper.createUrl(['p':'salary/payroll'])}}">返回工资表核算</a>
		{% else %}
		<div class="salary_error">这份工资表没有导入成功。异常数据不会进入工资表，请HR修正Excel后重新上传。</div>
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
