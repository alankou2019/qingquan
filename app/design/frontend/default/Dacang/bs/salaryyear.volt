<div class="warp salary_mobile_page" id="evaluation">
	<style>
		.salary_mobile_page{display:block;background:#eef1f6;min-height:100vh;}
		.salary_header{height:2.25rem;line-height:2.25rem;background:#fff;text-align:center;font-size:0.72rem;color:#111827;border-bottom:1px solid #e5e7eb;position:relative;}
		.salary_header a{position:absolute;left:0.65rem;top:0;color:#64748b;font-size:0.58rem;}
		.salary_list{margin:0.6rem;background:#fff;border-radius:0.35rem;overflow:hidden;box-shadow:0 0.08rem 0.24rem rgba(15,23,42,0.08);}
		.salary_item{display:flex;align-items:center;padding:0.72rem 0.7rem;border-bottom:1px solid #edf0f5;color:#111827;}
		.salary_item:last-child{border-bottom:none;}
		.salary_item .info{flex:1;}
		.salary_item .month{font-size:0.66rem;line-height:0.95rem;}
		.salary_item .meta{font-size:0.52rem;color:#94a3b8;line-height:0.78rem;margin-top:0.06rem;}
		.salary_item .money{font-size:0.72rem;color:#1f8fd8;font-weight:bold;}
		.empty{margin:0.6rem;background:#fff;border-radius:0.35rem;padding:1rem 0.7rem;color:#94a3b8;font-size:0.58rem;line-height:0.9rem;text-align:center;}
	</style>
	<div class="salary_header"><a href="{{helper.createUrl(['p':'bs/salary'])}}">返回</a>{{year}} 年薪酬</div>
	{% if slips %}
	<div class="salary_list">
		{% for slip in slips %}
		<a class="salary_item" href="{{helper.createUrl(['p':'bs/salarydetail','id':slip['id']])}}">
			<div class="info">
				<div class="month">{{slip['payroll_month']}}</div>
				<div class="meta">应发 {{slip['earning_total']}}　扣减 {{slip['deduction_total']}}</div>
			</div>
			<div class="money">￥{{slip['net_amount']}}</div>
		</a>
		{% endfor %}
	</div>
	{% else %}
	<div class="empty">暂无已发放薪酬</div>
	{% endif %}
</div>
