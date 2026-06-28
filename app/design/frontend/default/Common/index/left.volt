<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>左侧导航menu</title>
<link href="/skin/newadminhtml/css/css.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="/skin/newadminhtml/js/sdmenu.js"></script>
<script type="text/javascript">
	// <![CDATA[
	var myMenu;
	window.onload = function() {
		myMenu = new SDMenu("my_menu");
		myMenu.init();
	};
	// ]]>
</script>
<style type=text/css>
html{ SCROLLBAR-FACE-COLOR: #538ec6; SCROLLBAR-HIGHLIGHT-COLOR: #dce5f0; SCROLLBAR-SHADOW-COLOR: #2c6daa; SCROLLBAR-3DLIGHT-COLOR: #dce5f0; SCROLLBAR-ARROW-COLOR: #2c6daa;  SCROLLBAR-TRACK-COLOR: #dce5f0;  SCROLLBAR-DARKSHADOW-COLOR: #dce5f0; overflow-x:hidden;}
body{overflow-x:hidden; background:url(/skin/newadminhtml/images/main/leftbg.jpg) left top repeat-y #f2f0f5; width:194px;}
</style>
</head>
<body onselectstart="return false;" ondragstart="return false;" oncontextmenu="return false;">
<div id="left-top">
	<div><img src="/skin/newadminhtml/images/main/member.gif" width="44" height="44" /></div>
    <span>用户：{{_user.user_name}}<br>角色：{% if _user.is_admin==1 %}超级管理员{% else %}子管理员{% endif %}</span>
</div>
    <div style="float: left" id="my_menu" class="sdmenu">
        {% if bigClass==1 %}
<!--kpi考核 -->
	        {% if _user.is_admin==1 %}  
		      <div class="collapsed">
		        <span>考核设置</span>
		        <a href="{{helper.createUrl(['p':'department/index'])}}" target="mainFrame" onFocus="this.blur()">部门管理</a>
		        <a href="{{helper.createUrl(['p':'firm/staff'])}}" target="mainFrame" onFocus="this.blur()">人员管理</a>
		        <a href="{{helper.createUrl(['p':'department/async'])}}" target="mainFrame" onFocus="this.blur()">同步钉钉</a>
		      </div>
		    {% endif %}
		      <div>
		        <span>考核管理</span>
		        <a href="{{helper.createUrl(['p':'quota/index'])}}" target="mainFrame" onFocus="this.blur()">指标库管理</a>
		        <a href="{{helper.createUrl(['p':'firm/reportlist'])}}" target="mainFrame" onFocus="this.blur()">新建考核表</a>
		        <a href="{{helper.createUrl(['p':'report/list'])}}" target="mainFrame" onFocus="this.blur()">考评表管理</a>
		        <a href="{{helper.createUrl(['p':'report/reporttpl'])}}" target="mainFrame" onFocus="this.blur()">考评表模版</a>
		        <a href="{{helper.createUrl(['p':'stores/list'])}}" target="mainFrame" onFocus="this.blur()">归档记录</a>
		      </div>
		    {% if _user.is_admin==1 %}  
		      <div>
		        <span>管理设置</span>
		        <a href="{{helper.createUrl(['p':'user/index'])}}" target="mainFrame" onFocus="this.blur()">管理员设置</a>
		      </div>
		    {% endif %}
		{% elseif bigClass==2 %}
<!--项目考核 -->  
		   <div>
              <span>考核管理</span>
              <a href="{{helper.createUrl(['p':'firm/pointreportlist'])}}" target="mainFrame" onFocus="this.blur()">新建积分表</a>
              <a href="{{helper.createUrl(['p':'pointreport/list'])}}" target="mainFrame" onFocus="this.blur()">积分表管理</a>
              <a href="{{helper.createUrl(['p':'pointreport/reporttpl'])}}" target="mainFrame" onFocus="this.blur()">积分表模版</a>
              <a href="{{helper.createUrl(['p':'pointstores/list'])}}" target="mainFrame" onFocus="this.blur()">归档记录</a>
            </div>
		    <div>
              <span>管理设置</span>
              <a href="{{helper.createUrl(['p':'group/index'])}}" target="mainFrame" onFocus="this.blur()">审核组</a>
              <a href="{{helper.createUrl(['p':'group/groupuser'])}}" target="mainFrame" onFocus="this.blur()">审核组人员</a
            </div>
		{% elseif bigClass==3 %}
<!--人事档案管理 -->		
		
		
		
		{% else %}
<!--工资管理 -->		
		
		
		{% endif %}
	    
    </div>
</body>
</html>