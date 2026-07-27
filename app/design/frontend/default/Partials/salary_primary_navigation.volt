<style>
.salary_primary_navigation{margin-bottom:0;}
.salary_primary_navigation li{width:auto !important;padding:0 18px;}
.salary_primary_navigation li.on a{color:#4560e6;font-weight:bold;}
.salary_primary_navigation li.salary_primary_return{float:right;width:140px !important;padding:0;border-left:1px solid #efefef;border-right:0;}
.salary_secondary_navigation{display:block;min-height:38px;padding:0 18px;border:1px solid #d9e2ef;border-top:0;background:#fbfdff;line-height:38px;}
.salary_secondary_navigation a{display:inline-block;margin-right:8px;padding:0 14px;color:#64748b;text-decoration:none;}
.salary_secondary_navigation a.on{background:#fff;color:#334155;font-weight:bold;border-left:1px solid #d9e2ef;border-right:1px solid #d9e2ef;}
</style>
<div class="head_tab clear salary_primary_navigation">
	<ul>
		<li><a href="{{helper.createUrl(['p':'salary/employeesync'])}}">员工同步</a></li>
		<li><a href="{{helper.createUrl(['p':'salary/project'])}}">工资项目设置</a></li>
		<li><a href="{{helper.createUrl(['p':'salary/payroll'])}}">工资表核算</a></li>
		<li><a href="{{helper.createUrl(['p':'salary/payslip'])}}">工资条发放</a></li>
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
	for(var i=0;i<links.length;i++){
		var href=links[i].getAttribute('href')||'';
		if(href==match||(match==''&&href!=''&&href!='/'&&(path==href||path.indexOf(href+'/')===0))){
			links[i].parentNode.className+=' on';
			break;
		}
	}
})();
</script>
