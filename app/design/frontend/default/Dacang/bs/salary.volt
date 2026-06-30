<div class="warp salary_mobile_page" id="evaluation">
	<style>
		.salary_mobile_page{display:block;background:#eef1f6;min-height:100vh;}
		.salary_mobile_page .salary_hero{padding:1rem 0.75rem 0.8rem;background:linear-gradient(135deg,#1f8fd8,#18b59b);color:#fff;}
		.salary_mobile_page .salary_hero .label{font-size:0.58rem;opacity:0.9;}
		.salary_mobile_page .salary_hero .title{font-size:1rem;line-height:1.35rem;margin-top:0.22rem;font-weight:bold;}
		.salary_mobile_page .salary_hero .sub{font-size:0.56rem;line-height:0.8rem;margin-top:0.28rem;opacity:0.86;}
		.salary_mobile_page .salary_block{margin:0.6rem;background:#fff;border-radius:0.35rem;box-shadow:0 0.08rem 0.24rem rgba(15,23,42,0.08);overflow:hidden;}
		.salary_mobile_page .salary_block_title{height:1.75rem;line-height:1.75rem;padding:0 0.7rem;border-bottom:1px solid #edf0f5;color:#111827;font-size:0.68rem;font-weight:bold;}
		.salary_mobile_page .month_card{padding:0.75rem 0.7rem;}
		.salary_mobile_page .month_card .month{font-size:0.58rem;color:#64748b;}
		.salary_mobile_page .month_card .money{font-size:1.15rem;color:#111827;line-height:1.55rem;margin-top:0.15rem;font-weight:bold;}
		.salary_mobile_page .month_card .meta{font-size:0.55rem;color:#94a3b8;line-height:0.82rem;}
		.salary_mobile_page .salary_btn{display:block;margin-top:0.55rem;height:1.75rem;line-height:1.75rem;text-align:center;background:#1f8fd8;color:#fff;border-radius:0.18rem;font-size:0.62rem;}
		.salary_mobile_page .salary_menu{display:grid;grid-template-columns:1fr 1fr;border-top:1px solid #edf0f5;}
		.salary_mobile_page .salary_menu_item{padding:0.78rem 0.35rem;text-align:center;color:#111827;border-right:1px solid #edf0f5;border-bottom:1px solid #edf0f5;}
		.salary_mobile_page .salary_menu_item:nth-child(2n){border-right:none;}
		.salary_mobile_page .salary_menu_item:nth-last-child(1),.salary_mobile_page .salary_menu_item:nth-last-child(2){border-bottom:none;}
		.salary_mobile_page .salary_menu_item .num{font-size:0.86rem;font-weight:bold;color:#1f8fd8;line-height:1.05rem;}
		.salary_mobile_page .salary_menu_item .txt{font-size:0.55rem;color:#64748b;line-height:0.8rem;margin-top:0.12rem;}
		.salary_mobile_page .salary_menu_item.disabled .num,.salary_mobile_page .salary_menu_item.disabled .txt{color:#b8c0cc;}
		.salary_mobile_page .empty{padding:0.78rem 0.7rem;color:#94a3b8;font-size:0.58rem;line-height:0.9rem;}
		.salary_mobile_page .back_link{display:block;margin:0.75rem 0.6rem 1rem;text-align:center;color:#64748b;font-size:0.58rem;}
	</style>
	<div class="salary_hero">
		<div class="label">薪酬查询</div>
		<div class="title">我的薪酬</div>
		<div class="sub">仅展示已发放薪酬，未发放月份不会提前显示金额。</div>
	</div>
	<div class="salary_block">
		<div class="salary_block_title">当月薪酬</div>
		{% if monthSlip %}
		<div class="month_card">
			<div class="month">{{monthSlip['payroll_month']}}</div>
			<div class="money">￥{{monthSlip['net_amount']}}</div>
			<div class="meta">应发 {{monthSlip['earning_total']}}　扣减 {{monthSlip['deduction_total']}}</div>
			<div class="meta">{% if monthSlip['confirmed_at'] > 0 %}已确认{% else %}待确认{% endif %}</div>
			<a class="salary_btn" href="{{helper.createUrl(['p':'bs/salarydetail','id':monthSlip['id']])}}">查看当月薪酬</a>
		</div>
		{% else %}
		<div class="empty">{{currentMonth}} 暂无已发放薪酬</div>
		{% endif %}
		<div class="salary_menu">
			<a class="salary_menu_item" href="{{helper.createUrl(['p':'bs/salary'])}}">
				<div class="num">{% if monthSlip %}1{% else %}0{% endif %}</div>
				<div class="txt">当月薪酬</div>
			</a>
			<a class="salary_menu_item" href="{{helper.createUrl(['p':'bs/salaryyear'])}}">
				<div class="num">{{yearSlipCount}}</div>
				<div class="txt">当年薪酬</div>
			</a>
			<a class="salary_menu_item" href="{{helper.createUrl(['p':'bs/salaryhistory'])}}">
				<div class="num">{{historyCount}}</div>
				<div class="txt">往年薪酬</div>
			</a>
			<a class="salary_menu_item {% if !canViewSubordinateSalary %}disabled{% endif %}" href="{{helper.createUrl(['p':'bs/salarysubordinate'])}}">
				<div class="num">管</div>
				<div class="txt">下属薪酬</div>
			</a>
		</div>
	</div>
	<a class="back_link" href="{{helper.createUrl(['p':'bs/newindex'])}}">返回考核首页</a>
</div>
