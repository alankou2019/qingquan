<style>
.module_feature{padding:18px;}
.module_notice{border:1px solid #d9e2ef;background:#fbfdff;padding:18px;line-height:26px;color:#475569;}
.module_notice h3{font-size:16px;color:#1f2937;margin:0 0 8px 0;}
.module_notice a{display:inline-block;background:#4560e6;color:#fff;padding:0 16px;line-height:30px;height:30px;text-decoration:none;margin-top:10px;}
</style>
<div class="full_box">
	{{ partial('salary_primary_navigation') }}
	<div class="salary_secondary_navigation"><a class="on" href="#">{{featureName}}</a></div>
	<div class="module_feature">
		<div class="module_notice">
			<h3>{{featureName}}</h3>
			当前功能入口已预留，后续会在旧系统基础上分步接入真实业务数据、权限、日志和报表。
		</div>
	</div>
</div>
