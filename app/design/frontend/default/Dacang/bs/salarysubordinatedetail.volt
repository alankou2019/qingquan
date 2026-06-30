<div class="warp salary_mobile_page" id="evaluation">
	<style>
		.salary_mobile_page{display:block;background:#eef1f6;min-height:100vh;}
		.salary_header{height:2.25rem;line-height:2.25rem;background:#fff;text-align:center;font-size:0.72rem;color:#111827;border-bottom:1px solid #e5e7eb;position:relative;}
		.salary_header a{position:absolute;left:0.65rem;top:0;color:#64748b;font-size:0.58rem;}
		.salary_summary{margin:0.6rem;background:#fff;border-radius:0.35rem;padding:0.78rem 0.7rem;box-shadow:0 0.08rem 0.24rem rgba(15,23,42,0.08);}
		.salary_summary .month{font-size:0.6rem;color:#64748b;}
		.salary_summary .employee{font-size:0.72rem;color:#111827;line-height:1rem;font-weight:bold;margin-top:0.1rem;}
		.salary_summary .money{font-size:1.2rem;color:#111827;line-height:1.6rem;font-weight:bold;margin-top:0.12rem;}
		.salary_summary .meta{font-size:0.52rem;color:#94a3b8;line-height:0.78rem;}
		.salary_table{margin:0.6rem;background:#fff;border-radius:0.35rem;overflow:hidden;box-shadow:0 0.08rem 0.24rem rgba(15,23,42,0.08);}
		.salary_row{display:flex;align-items:center;padding:0.58rem 0.7rem;border-bottom:1px solid #edf0f5;font-size:0.58rem;}
		.salary_row:last-child{border-bottom:none;}
		.salary_row .name{flex:1;color:#111827;}
		.salary_row .amount{color:#1f8fd8;font-weight:bold;}
		.salary_tip{margin:0.6rem;color:#94a3b8;font-size:0.52rem;line-height:0.82rem;}
	</style>
	<div class="salary_header"><a href="{{helper.createUrl(['p':'bs/salarysubordinate'])}}">返回</a>下属薪酬详情</div>
	<div class="salary_summary">
		<div class="month">{{slip['payroll_month']}}</div>
		<div class="employee">{{slip['employee_name']}}　{{slip['department_name']}}</div>
		<div class="money">￥{{slip['net_amount']}}</div>
		<div class="meta">应发 {{slip['earning_total']}}　扣减 {{slip['deduction_total']}}</div>
		<div class="meta">发放 {{slip['published_time']}}　员工查看 {{slip['viewed_time']}}</div>
	</div>
	<div class="salary_table">
		{% if slip['values'] %}
			{% for item in slip['values'] %}
			<div class="salary_row">
				<div class="name">{{item['project_name']}}</div>
				<div class="amount">￥{{item['final_amount']}}</div>
			</div>
			{% endfor %}
		{% else %}
			<div class="salary_row">
				<div class="name">暂无薪酬项目明细</div>
				<div class="amount"></div>
			</div>
		{% endif %}
	</div>
	<div class="salary_tip">下属薪酬为高敏感数据，仅限企业授权范围内查看，系统会记录查看日志。</div>
</div>
