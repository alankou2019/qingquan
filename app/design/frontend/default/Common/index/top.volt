<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>大仓考评｜企业管理后台</title>
<link href="/skin/newadminhtml/css/css.css" type="text/css" rel="stylesheet" />
<link href="/skin/adminhtml/default/css/consulting-blue.css?v=20260730-2" type="text/css" rel="stylesheet" />
</head>
<body class="consulting-top-shell" onselectstart="return false" oncontextmenu=return(false) style="overflow-x:hidden;">
<!--禁止网页另存为-->
<noscript><iframe scr="*.htm"></iframe></noscript>
<!--禁止网页另存为-->
<table width="100%" border="0" cellspacing="0" cellpadding="0" id="header">
  <tr>
    <td rowspan="2" align="left" valign="top" id="logo"><img src="/skin/newadminhtml/images/main/logo.jpg" width="74" height="64"></td>
    <td align="left" valign="bottom">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td align="left" valign="bottom" id="header-name">大仓考评<small>DA CANG CONSULTING</small></td>
        <td align="right" valign="top" id="header-right">
            <a href="javascript:logout();"  class="admin-out">安全退出</a>
            <span>
<!-- 日历 -->
  <SCRIPT type="text/javascript" src="/skin/newadminhtml/js/clock.js"></SCRIPT>
  <SCRIPT type="text/javascript">showcal();</SCRIPT>
            </span>
        </td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td align="left" valign="bottom">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td align="left" valign="top" id="header-admin">企业管理后台</td>
        <td align="left" valign="bottom" id="header-menu">
        <a href="{{helper.createUrl(['p':'index/index','bigClass':'3'])}}" onFocus="this.blur()" target="_top"
        {% if bigClass==3 %} id="menuon" {% endif %}>人员信息</a>

        <a href="{{helper.createUrl(['p':'index/index','bigClass':'1'])}}" onFocus="this.blur()" target="_top" 
        {% if bigClass==1 %} id="menuon" {% endif %}>KPI考核</a>
        
        {% if ispoint %}
            <a href="{{helper.createUrl(['p':'index/index','bigClass':'2'])}}" onFocus="this.blur()" target="_top"
            {% if bigClass==2 %} id="menuon" {% endif %} >积分考核</a>
        {% endif %}

        {% if hasSalaryModule %}
            <a href="{{helper.createUrl(['p':'index/index','bigClass':'4'])}}" onFocus="this.blur()" target="_top"
            {% if bigClass==4 %} id="menuon" {% endif %} >薪酬管理</a>
        {% endif %}
        {% if hasTrainingModule %}
            <a href="{{helper.createUrl(['p':'index/index','bigClass':'5'])}}" onFocus="this.blur()" target="_top"
            {% if bigClass==5 %} id="menuon" {% endif %} >培训管理</a>
        {% endif %}
        {% if hasPromotionModule %}
            <a href="{{helper.createUrl(['p':'index/index','bigClass':'6'])}}" onFocus="this.blur()" target="_top"
            {% if bigClass==6 %} id="menuon" {% endif %} >晋升管理</a>
        {% endif %}
        
        
        </td>
      </tr>
    </table></td>
  </tr>
</table>
</body>
<script type="text/javascript">
function logout()
{
	window.top.location.href="{{helper.createUrl(['p':'login/logout'])}}";	
}
</script>
</html>
