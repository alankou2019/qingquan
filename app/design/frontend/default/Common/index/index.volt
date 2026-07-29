<html>
<head>
</head>

<frameset rows="95,*" cols="*" frameborder="no" border="0" framespacing="0">
<!--top样式-->
<frame src="{{helper.createUrl(['p':'index/top','bigClass':bigClass])}}" name="topframe" scrolling="no" noresize id="topframe" title="topframe" />
<!--contact样式-->
<frameset id="attachucp" framespacing="0" border="0" frameborder="no" cols="194,12,*" rows="*">
    <frame scrolling="auto" noresize="" frameborder="no" name="leftFrame" src="{{helper.createUrl(['p':'index/left','bigClass':bigClass])}}"></frame>
    <frame id="leftbar" scrolling="no" noresize="" name="switchFrame" src="{{helper.createUrl(['p':'index/swich'])}}"></frame>
    <frame scrolling="auto" noresize="" border="0" name="mainFrame" 
        {% if bigClass==1 %}
            src="{{helper.createUrl(['p':'quota/index'])}}"
        {% elseif bigClass==2 %}
            src="{{helper.createUrl(['p':'firm/pointreportlist'])}}"
        {% elseif bigClass==3 %}
            src="{{helper.createUrl(['p':'index/kong'])}}"
        {% elseif bigClass==4 %}
            src="{{helper.createUrl(['p':'salary/index'])}}"
        {% else %}
            src="{{helper.createUrl(['p':'index/kong'])}}"
        {% endif %}
    ></frame>
</frameset>
</frameset>
</html>
