<style>
.salary_primary_navigation{margin-bottom:0;background:#f3f6fb;border-top-color:#d8e0ec;border-bottom-color:#cfd8e6;}
.salary_primary_navigation li{width:auto !important;padding:0 18px;background:#f3f6fb;border-right-color:#dce3ed;}
.salary_primary_navigation li a{color:#425466;}
.salary_primary_navigation li:hover{background:#e8eef7;}
.salary_primary_navigation li:hover a{color:#2f49b9;}
.salary_primary_navigation li.on{background:#4560e6;}
.salary_primary_navigation li.on a,.salary_primary_navigation li.on:hover a{color:#fff;font-weight:bold;}
.salary_primary_navigation li.on:before,.salary_primary_navigation li.on:after{display:none;}
.salary_primary_navigation li.salary_primary_return{float:right;width:140px !important;padding:0;border-left:1px solid #dce3ed;border-right:0;background:#fff;}
.salary_primary_navigation li.salary_primary_return a{color:#526477;}
.salary_secondary_navigation{display:block;min-height:40px;padding:0 18px;border:1px solid #d9e2ef;border-top:0;background:#fff;line-height:40px;}
.salary_secondary_navigation a{display:inline-block;box-sizing:border-box;height:40px;margin-right:4px;padding:0 14px;color:#64748b;text-decoration:none;border-bottom:2px solid transparent;}
.salary_secondary_navigation a:hover{color:#3454c5;background:#f3f6ff;}
.salary_secondary_navigation a.on{background:#eaf0ff;color:#3454c5;font-weight:bold;border-bottom-color:#4560e6;}
</style>
<div class="head_tab clear salary_primary_navigation">
	<ul>
		<li><a href="{{helper.createUrl(['p':'salary/project'])}}">工资项目设置</a></li>
		<li><a href="{{helper.createUrl(['p':'salary/payroll'])}}">工资表核算</a></li>
		<li><a href="{{helper.createUrl(['p':'salary/archive'])}}">工资表归档</a></li>
		<li><a href="{{helper.createUrl(['p':'salary/commission'])}}">提成核算</a></li>
		<li><a href="{{helper.createUrl(['p':'salary/performance'])}}">绩效工资核算</a></li>
		<li><a href="{{helper.createUrl(['p':'salary/auth'])}}">薪酬管理授权</a></li>
		<li class="salary_primary_return"><a href="{{helper.createUrl(['p':'salary/index'])}}">返回薪酬管理</a></li>
	</ul>
</div>
<script>
(function(){
	var path=window.location.pathname||'',links=document.querySelectorAll('.salary_primary_navigation a'),match='';
	if(path.indexOf('/salary/import')===0)match='/salary/payroll';
	if(path.indexOf('/salary/payslip')===0)match='/salary/archive';
	for(var i=0;i<links.length;i++){
		var href=links[i].getAttribute('href')||'';
		if(href==match||(match==''&&href!=''&&href!='/'&&(path==href||path.indexOf(href+'/')===0))){
			links[i].parentNode.className+=' on';
			break;
		}
	}
})();
</script>
