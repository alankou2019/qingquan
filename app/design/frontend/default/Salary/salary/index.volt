<style>
.module_home{padding:18px;}
.module_home .summary{border:1px solid #d9e2ef;background:#fbfdff;padding:14px 16px;margin-bottom:14px;line-height:24px;color:#475569;}
.module_grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;}
.module_card{border:1px solid #d9e2ef;background:#fff;padding:14px;min-height:118px;}
.module_card h3{font-size:15px;color:#1f2937;margin:0 0 8px 0;}
.module_card p{color:#64748b;line-height:22px;margin:0 0 12px 0;}
.module_card .status{color:#94a3b8;}
.module_card .btn{display:inline-block;background:#4560e6;color:#fff;padding:0 16px;line-height:30px;height:30px;text-decoration:none;}
.module_card.disabled{background:#f8fafc;}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li class="on"><a href="#">薪酬管理</a></li>
		</ul>
	</div>
	<div class="module_home">
		<div class="summary">薪酬管理模块已开通。当前页面先提供功能入口和授权控制，工资核算、工资条、提成、绩效工资的具体业务流程后续分步上线。</div>
		<div class="module_grid">
			{% for feature in features %}
			<div class="module_card {% if !feature['enabled'] %}disabled{% endif %}">
				<h3>{{feature['name']}}</h3>
				<p>{{feature['desc']}}</p>
				{% if feature['enabled'] %}
				<a class="btn" href="{{helper.createUrl(['p':feature['url']])}}">进入</a>
				{% else %}
				<span class="status">未开通</span>
				{% endif %}
			</div>
			{% endfor %}
		</div>
	</div>
</div>
