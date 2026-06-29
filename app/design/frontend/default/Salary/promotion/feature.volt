<style>
.module_feature{padding:18px;}
.module_notice{border:1px solid #d9e2ef;background:#fbfdff;padding:18px;line-height:26px;color:#475569;}
.module_notice h3{font-size:16px;color:#1f2937;margin:0 0 8px 0;}
.module_notice a{display:inline-block;background:#4560e6;color:#fff;padding:0 16px;line-height:30px;height:30px;text-decoration:none;margin-top:10px;}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li class="on"><a href="#">{{featureName}}</a></li>
		</ul>
	</div>
	<div class="module_feature">
		<div class="module_notice">
			<h3>{{featureName}}</h3>
			当前功能为预留入口，后续会按晋升管理流程逐步开发。
			<br />
			<a href="{{helper.createUrl(['p':'promotion/index'])}}">返回晋升首页</a>
		</div>
	</div>
</div>
