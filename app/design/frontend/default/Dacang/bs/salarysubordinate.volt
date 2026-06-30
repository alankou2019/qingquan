<div class="warp salary_mobile_page" id="evaluation">
	<style>
		.salary_mobile_page{display:block;background:#eef1f6;min-height:100vh;}
		.salary_header{height:2.25rem;line-height:2.25rem;background:#fff;text-align:center;font-size:0.72rem;color:#111827;border-bottom:1px solid #e5e7eb;position:relative;}
		.salary_header a{position:absolute;left:0.65rem;top:0;color:#64748b;font-size:0.58rem;}
		.salary_filter{display:flex;align-items:center;margin:0.6rem;background:#fff;border-radius:0.35rem;padding:0.55rem 0.7rem;box-shadow:0 0.08rem 0.24rem rgba(15,23,42,0.08);}
		.salary_filter input{flex:1;height:1.45rem;border:1px solid #d9e2ef;border-radius:0.18rem;padding:0 0.4rem;font-size:0.58rem;}
		.salary_filter button{height:1.45rem;border:0;background:#1f8fd8;color:#fff;border-radius:0.18rem;margin-left:0.35rem;padding:0 0.55rem;font-size:0.56rem;}
		.salary_summary{margin:0.6rem;background:#fff;border-radius:0.35rem;padding:0.72rem 0.7rem;box-shadow:0 0.08rem 0.24rem rgba(15,23,42,0.08);}
		.salary_summary .line{display:flex;align-items:center;font-size:0.56rem;color:#64748b;line-height:0.9rem;}
		.salary_summary .line span{flex:1;}
		.salary_summary .line strong{color:#111827;font-size:0.7rem;}
		.salary_list{margin:0.6rem;background:#fff;border-radius:0.35rem;overflow:hidden;box-shadow:0 0.08rem 0.24rem rgba(15,23,42,0.08);}
		.salary_item{display:flex;align-items:center;padding:0.72rem 0.7rem;border-bottom:1px solid #edf0f5;color:#111827;}
		.salary_item:last-child{border-bottom:none;}
		.salary_item .info{flex:1;}
		.salary_item .name{font-size:0.66rem;line-height:0.95rem;}
		.salary_item .meta{font-size:0.52rem;color:#94a3b8;line-height:0.78rem;margin-top:0.06rem;}
		.salary_item .money{font-size:0.72rem;color:#1f8fd8;font-weight:bold;}
		.empty{margin:0.6rem;background:#fff;border-radius:0.35rem;padding:1rem 0.7rem;color:#94a3b8;font-size:0.58rem;line-height:0.9rem;text-align:center;}
	</style>
	<div class="salary_header"><a href="{{helper.createUrl(['p':'bs/salary'])}}">返回</a>下属薪酬</div>
	<form class="salary_filter" method="get" action="{{helper.createUrl(['p':'bs/salarysubordinate'])}}">
		<input type="text" name="year" value="{{year}}" placeholder="年份，例如2026" />
		<button type="submit">查询</button>
	</form>
	{% if canViewSubordinateSalary %}
	<div class="salary_summary">
		<div class="line"><span>{{year}} 年记录</span><strong>{{summary['count']}}</strong></div>
		<div class="line"><span>应发合计</span><strong>￥{{summary['earning_total']}}</strong></div>
		<div class="line"><span>扣减合计</span><strong>￥{{summary['deduction_total']}}</strong></div>
		<div class="line"><span>实发合计</span><strong>￥{{summary['net_total']}}</strong></div>
	</div>
	{% if slips %}
	<div class="salary_list">
		{% for slip in slips %}
		<a class="salary_item" href="{{helper.createUrl(['p':'bs/salarysubordinatedetail','id':slip['id']])}}">
			<div class="info">
				<div class="name">{{slip['employee_name']}}　{{slip['payroll_month']}}</div>
				<div class="meta">{{slip['department_name']}}　{% if slip['confirmed_at'] > 0 %}已确认{% else %}待确认{% endif %}</div>
				<div class="meta">应发 {{slip['earning_total']}}　扣减 {{slip['deduction_total']}}</div>
			</div>
			<div class="money">￥{{slip['net_amount']}}</div>
		</a>
		{% endfor %}
	</div>
	{% else %}
	<div class="empty">当前授权范围内暂无已发放下属薪酬。</div>
	{% endif %}
	{% else %}
	<div class="empty">你暂未获得下属薪酬查看权限。如需查看，请由HR在企业后台“薪酬管理授权”中配置。</div>
	{% endif %}
</div>
