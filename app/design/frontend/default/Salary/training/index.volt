<style>
.module_home{padding:18px;}
.module_home .summary{border:1px solid #d9e2ef;background:#fbfdff;padding:14px 16px;margin-bottom:14px;line-height:24px;color:#475569;}
.module_grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;}
.module_card{border:1px solid #d9e2ef;background:#fff;padding:14px;min-height:90px;}
.module_card h3{font-size:15px;color:#1f2937;margin:0 0 12px 0;}
.module_card .status{color:#94a3b8;}
.module_card .btn{display:inline-block;background:#4560e6;color:#fff;padding:0 16px;line-height:30px;height:30px;text-decoration:none;}
.module_card.disabled{background:#f8fafc;}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li class="on"><a href="#">培训管理</a></li>
		</ul>
	</div>
	<div class="module_home">
		<div class="summary">培训管理为预留模块，后续可扩展培训计划、培训记录、考试测评和培训档案。</div>
		<div class="module_grid">
			{% for feature in features %}
			<div class="module_card {% if !feature['enabled'] %}disabled{% endif %}">
				<h3>{{feature['name']}}</h3>
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
