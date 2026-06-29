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
		
		
		
		{% elseif bigClass==4 %}
<!--薪酬管理 -->
		   <div>
              <span>薪酬管理</span>
              <a href="{{helper.createUrl(['p':'salary/index'])}}" target="mainFrame" onFocus="this.blur()">薪酬首页</a>
              <a href="{{helper.createUrl(['p':'salary/employeesync'])}}" target="mainFrame" onFocus="this.blur()">员工同步/导入</a>
              <a href="{{helper.createUrl(['p':'salary/auth'])}}" target="mainFrame" onFocus="this.blur()">薪酬查看授权</a>
              {% if salaryFeatures['payroll'] %}
              <a href="{{helper.createUrl(['p':'salary/payroll'])}}" target="mainFrame" onFocus="this.blur()">工资核算</a>
              {% endif %}
              {% if salaryFeatures['payslip'] %}
              <a href="{{helper.createUrl(['p':'salary/payslip'])}}" target="mainFrame" onFocus="this.blur()">工资条发放</a>
              {% endif %}
              {% if salaryFeatures['commission'] %}
              <a href="{{helper.createUrl(['p':'salary/commission'])}}" target="mainFrame" onFocus="this.blur()">提成核算</a>
              {% endif %}
              {% if salaryFeatures['performance_salary'] %}
              <a href="{{helper.createUrl(['p':'salary/performance'])}}" target="mainFrame" onFocus="this.blur()">绩效工资核算</a>
              {% endif %}
            </div>
		{% elseif bigClass==5 %}
<!--培训管理 -->
		   <div>
              <span>培训管理</span>
              <a href="{{helper.createUrl(['p':'training/index'])}}" target="mainFrame" onFocus="this.blur()">培训首页</a>
              {% if trainingFeatures['plan'] %}
              <a href="{{helper.createUrl(['p':'training/plan'])}}" target="mainFrame" onFocus="this.blur()">培训计划</a>
              {% endif %}
              {% if trainingFeatures['record'] %}
              <a href="{{helper.createUrl(['p':'training/record'])}}" target="mainFrame" onFocus="this.blur()">培训记录</a>
              {% endif %}
              {% if trainingFeatures['exam'] %}
              <a href="{{helper.createUrl(['p':'training/exam'])}}" target="mainFrame" onFocus="this.blur()">考试测评</a>
              {% endif %}
              {% if trainingFeatures['archive'] %}
              <a href="{{helper.createUrl(['p':'training/archive'])}}" target="mainFrame" onFocus="this.blur()">培训档案</a>
              {% endif %}
            </div>
		{% elseif bigClass==6 %}
<!--晋升管理 -->
		   <div>
              <span>晋升管理</span>
              <a href="{{helper.createUrl(['p':'promotion/index'])}}" target="mainFrame" onFocus="this.blur()">晋升首页</a>
              {% if promotionFeatures['channel'] %}
              <a href="{{helper.createUrl(['p':'promotion/channel'])}}" target="mainFrame" onFocus="this.blur()">晋升通道</a>
              {% endif %}
              {% if promotionFeatures['application'] %}
              <a href="{{helper.createUrl(['p':'promotion/application'])}}" target="mainFrame" onFocus="this.blur()">晋升申请</a>
              {% endif %}
              {% if promotionFeatures['review'] %}
              <a href="{{helper.createUrl(['p':'promotion/review'])}}" target="mainFrame" onFocus="this.blur()">晋升评审</a>
              {% endif %}
              {% if promotionFeatures['record'] %}
              <a href="{{helper.createUrl(['p':'promotion/record'])}}" target="mainFrame" onFocus="this.blur()">晋升记录</a>
              {% endif %}
            </div>
		{% else %}
<!--工资管理 -->		
		
		
		{% endif %}
	    
    </div>
</body>
</html>
