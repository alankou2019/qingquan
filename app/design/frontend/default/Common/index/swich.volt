<html>
<head>
<title>展开合闭按钮</title>
<link href="/skin/newadminhtml/css/css.css" type="text/css" rel="stylesheet" />
<link href="/skin/adminhtml/default/css/consulting-blue.css?v=20260730-2" type="text/css" rel="stylesheet" />
<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
<script language="javascript">
function switchSysBar(){
 if (parent.document.getElementById('attachucp').cols=="194,12,*"){
 document.getElementById('leftbar').style.display="";
 parent.document.getElementById('attachucp').cols="0,12,*";
 }
 else{
 parent.document.getElementById('attachucp').cols="194,12,*";
 document.getElementById('leftbar').style.display="none"
 }
}

</script>
</head>
<body class="consulting-switch-shell" marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" onselectstart="return false" oncontextmenu=return(false) style="overflow-x:hidden;">
<center>
<table height="100%" cellspacing="0" cellpadding="0" border="0" width="100%">
<tbody>
<tr>
<td bgcolor="#ededb1" width="1">
</td>
<td id="leftbar" style="display: none; background:url(/skin/newadminhtml/images/main/switchbg.jpg) repeat-y #d2d2d0 0px 0">
<a class="sidebar-toggle" onClick="switchSysBar()" href="javascript:void(0);" title="展开左侧菜单"><span aria-hidden="true">›</span></a>
</td>
<td id="rightbar"style="background:url(/skin/newadminhtml/images/main/switchbg.jpg) repeat-y #f2f0f5 0px 0">
<a class="sidebar-toggle" onClick="switchSysBar()" href="javascript:void(0);" title="收起左侧菜单"><span aria-hidden="true">‹</span></a>
</td>
</tr>
</tbody>
</table>
</center>
</body>
</html>