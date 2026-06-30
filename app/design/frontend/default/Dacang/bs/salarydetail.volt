<div class="warp salary_mobile_page" id="evaluation">
	<style>
		.salary_mobile_page{display:block;background:#eef1f6;min-height:100vh;}
		.salary_header{height:2.25rem;line-height:2.25rem;background:#fff;text-align:center;font-size:0.72rem;color:#111827;border-bottom:1px solid #e5e7eb;position:relative;}
		.salary_header a{position:absolute;left:0.65rem;top:0;color:#64748b;font-size:0.58rem;}
		.salary_summary{margin:0.6rem;background:#fff;border-radius:0.35rem;padding:0.78rem 0.7rem;box-shadow:0 0.08rem 0.24rem rgba(15,23,42,0.08);}
		.salary_summary .month{font-size:0.6rem;color:#64748b;}
		.salary_summary .money{font-size:1.2rem;color:#111827;line-height:1.6rem;font-weight:bold;margin-top:0.12rem;}
		.salary_summary .meta{font-size:0.52rem;color:#94a3b8;line-height:0.78rem;}
		.salary_table{margin:0.6rem;background:#fff;border-radius:0.35rem;overflow:hidden;box-shadow:0 0.08rem 0.24rem rgba(15,23,42,0.08);}
		.salary_row{display:flex;align-items:center;padding:0.58rem 0.7rem;border-bottom:1px solid #edf0f5;font-size:0.58rem;}
		.salary_row:last-child{border-bottom:none;}
		.salary_row .name{flex:1;color:#111827;}
		.salary_row .amount{color:#1f8fd8;font-weight:bold;}
		.salary_tip{margin:0.6rem;color:#94a3b8;font-size:0.52rem;line-height:0.82rem;}
		.salary_confirm{margin:0.6rem;background:#fff;border-radius:0.35rem;padding:0.72rem 0.7rem;box-shadow:0 0.08rem 0.24rem rgba(15,23,42,0.08);}
		.salary_confirm .status{font-size:0.58rem;color:#64748b;line-height:0.9rem;margin-bottom:0.55rem;}
		.salary_confirm .confirm_btn{display:block;width:100%;height:1.75rem;line-height:1.75rem;text-align:center;background:#1f8fd8;color:#fff;border:0;border-radius:0.18rem;font-size:0.62rem;}
		.salary_confirm .confirmed_btn{display:block;height:1.75rem;line-height:1.75rem;text-align:center;background:#e8f7ef;color:#16803c;border-radius:0.18rem;font-size:0.62rem;}
	</style>
	<div class="salary_header"><a href="{{helper.createUrl(['p':'bs/salary'])}}">返回</a>薪酬详情</div>
	<div class="salary_summary">
		<div class="month">{{slip['payroll_month']}}</div>
		<div class="money">￥{{slip['net_amount']}}</div>
		<div class="meta">应发 {{slip['earning_total']}}　扣减 {{slip['deduction_total']}}</div>
		<div class="meta">发放 {{slip['published_time']}}　查看 {{slip['viewed_time']}}</div>
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
	<div class="salary_confirm">
		{% if slip['confirmed_at'] > 0 %}
			<div class="status">你已于 {{slip['confirmed_time']}} 确认本工资条。</div>
			<div class="confirmed_btn">已确认</div>
		{% else %}
			<div class="status">请确认本工资条金额和项目明细。确认后 HR 后台会记录确认时间。</div>
			<form method="post" action="{{helper.createUrl(['p':'bs/salaryconfirm','id':slip['id']])}}" onsubmit="return confirm('确认本工资条无误吗？');">
				<button class="confirm_btn" type="submit">确认无误</button>
			</form>
		{% endif %}
	</div>
	<div class="salary_tip">薪酬信息仅供本人查看。如对金额有疑问，请联系企业HR或薪酬管理员。</div>
</div>
