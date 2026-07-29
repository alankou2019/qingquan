    <!--第三方插件-->
    <!--滚动条-->
    <script src="/skin/adminhtml/default/libs/nicescroll/jquery.nicescroll.js"></script>
    <!--cookie-->
    <script src="/skin/adminhtml/default/libs/cookie/cookie.min.js"></script>
    <!--日历插件-->
    <script src="/skin/adminhtml/default/libs/laydate/laydate.js"></script>
    <!--表单验证-->
    <script src="/skin/adminhtml/default/libs/Validform/Validform_v5.3.2_min.js" type="text/javascript" charset="utf-8"></script>
    <!--图片上传-->
    <link rel="stylesheet" type="text/css" href="/skin/adminhtml/default/libs/upload/uploadify.css"/>
    <link rel="stylesheet" type="text/css" href="/skin/adminhtml/default/css/uploadify.path.css"/>
    <script src="/skin/adminhtml/default/libs/upload/jquery.uploadify.min.js"></script>
    <!--富文本编辑器UEditor-->
    <script type="text/javascript" charset="utf-8" src="/skin/adminhtml/default/libs/UEditor/ueditor.config.js"></script>
    <script type="text/javascript" charset="utf-8" src="/skin/adminhtml/default/libs/UEditor/ueditor.all.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="/skin/adminhtml/default/libs/UEditor/zh-cn.js"></script>
    <!--日期插件-->
    <script type="text/javascript" src="/skin/adminhtml/default/libs/laydate/laydate.js" ></script>
        <div class="main open" id="main">
            <div class="index_header posi_t clear">
                <div class="fl logo_box">
                    <div class="operation-brand"><span class="operation-brand-mark">DC</span><span class="operation-brand-copy"><strong>运营后台</strong><small>DA CANG CONSULTING</small></span></div>
                </div>
                <div class="fr">
                    <ul class="top_list">
                        <li id="timer">
                        </li>
                        <li>
                            <span>{{_adminUser.username}}</span>
                            <div>超级管理员</div>
                        </li>
                        <li onclick="iframeSon.location='{{helper.createUrl(['p':'index/dashboard'])}}';">
                            <a href="">
                                <i class="iconfont icon-shouyeshouye"></i>
                            </a>
                        </li>
                        <li onclick="window.location='{{helper.createUrl(['p':'login/logout'])}}';">
                            <a href="">
                                <i class="iconfont icon-kaiguan"></i>
                            </a>
                        </li>
                    </ul>
                    <div class="msg_con_box" id="msg_con_box">
                        <div class="clear tit_box">
                            <div class="fl tit">
                                未读通知
                            </div>
                            <div class="fr closed_msg" id="closed_msg">
                                <i class="iconfont icon-guanbi"></i>
                            </div>
                            
                        </div>
                        <ul class="msg_list">
                            <li class="clear">
                                <div class="fl">
                                    <span>[产品-交易]</span>
                                    <i class="iconfont icon-jinlingyingcaiwangtubiao96"></i>
                                    <span>有<span class="num">5</span>个订单需要退订</span>
                                </div>
                                <div class="fr handle">
                                    <a href="">查看</a>
                                    <a href="">忽略</a>
                                </div>
                            </li>
                            <li class="clear">
                                <div class="fl">
                                    <span>[产品-交易]</span>
                                    <i class="iconfont icon-jinlingyingcaiwangtubiao96"></i>
                                    <span>有<span class="num">5</span>个订单需要退订</span>
                                </div>
                                <div class="fr handle">
                                    <a href="">查看</a>
                                    <a href="">忽略</a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="index_left posi_l">
                <div class="nav_box clear">
                    <ul class="nav_1 fl" id="nav_1">
                        <li>
                            <div class="nav1_img">
                                <img src="/skin/adminhtml/default/images/nav1_1.png" alt="" />
                            </div>
                            <span class="nav1_txt">控制台</span>
                            <i class="iconfont icon-yousanjiao"></i>
                        </li>
                        <li class="active">
                            <div class="nav1_img">
                                <img src="/skin/adminhtml/default/images/nav1_1.png" alt="" />
                            </div>
                            <span class="nav1_txt">控制台</span>
                            <i class="iconfont icon-yousanjiao"></i>
                        </li>
                        <li>
                            <div class="nav1_img">
                                <img src="/skin/adminhtml/default/images/nav1_1.png" alt="" />
                            </div>
                            <span class="nav1_txt">控制台</span>
                            <i class="iconfont icon-yousanjiao"></i>
                        </li>
                    </ul>
                    <ul class="nav_2 fl" id="nav_2">
                        <li>
                            商品管理
                        </li>
                        <li class="active">
                            商品管理
                        </li>
                    </ul>
                </div>
                <div class="nav_switch_box" id="nav_switch">
                    <img src="/skin/adminhtml/default/images/closed.png" alt="" />
                </div>
            </div>
            <div class="index_content posi_m">
                <iframe src="" frameborder=no border=0 width="100%" height="100%" class="content_iframe" name="iframeSon" id="content_iframe"></iframe>
            </div>
        </div>
            <!--当前页js star-->
    <!--联动菜单-->
    <script src="/skin/adminhtml/default/js/linkage.js" type="text/javascript" charset="utf-8"></script>
    <script src="/skin/adminhtml/default/js/ljk.js"></script>
    <script>
	
		function getNowFormatDate() {
			var date = new Date();
			var seperator1 = "-";
			var seperator2 = ":";
			var month = date.getMonth() + 1;
			var minutes = date.getMinutes();
			var strDate = date.getDate();
			var seconds = date.getSeconds();
			if (month >= 1 && month <= 9) {
				month = "0" + month;
			}
			if (strDate >= 0 && strDate <= 9) {
				strDate = "0" + strDate;
			}
			if (minutes >= 0 && minutes <= 9) {
				minutes = "0" + minutes;
			}
			if (seconds >= 0 && seconds <= 9) {
				seconds = "0" + seconds;
			}
			var currentdate = date.getFullYear() + seperator1 + month + seperator1 + strDate
					+ " " + date.getHours() + seperator2 + minutes
					+ seperator2 + seconds;
			document.getElementById("timer").innerHTML="当前时间:"+currentdate;
		}
		window.setInterval('getNowFormatDate()',1000);
        window.onload = function(){
            //菜单功能实现 start:
            //菜单数据
            var data = [
                {
                    "n":["/skin/adminhtml/default/nav_icon/dashboard_ico.png","控制台"],
                    "s":[
                            {
                                "n":["控制台"],
                                "url":"/admin/index/dashboard",
                            }
                        ]
                },
                {
                    "n":["/skin/adminhtml/default/nav_icon/article_ico.png","文章管理"],
                    "s":[
                            {
                                "n":["文章列表"],
                                "url":"{{helper.createUrl(['p':'article/index'])}}",
                            },
                            {
                                "n":["文章分类"],
                                "url":"{{helper.createUrl(['p':'articlecategory/index'])}}",
                            }
                        ]
                },
				{
                    "n":["/skin/adminhtml/default/nav_icon/user_ico.png","用户管理"],
                    "s":[
                            {
                                "n":["用户列表"],
                                "url":"{{helper.createUrl(['p':'user/index'])}}",
                            }
                        ]
                },
			   {
                    "n":["/skin/adminhtml/default/nav_icon/ad_ico.png","广告管理"],
                    "s":[
                            {
                                "n":["广告列表"],
                                "url":"{{helper.createUrl(['p':'advert/index'])}}",
                            },
                            {
                                "n":["广告位列表"],
                                "url":"{{helper.createUrl(['p':'advertposition/index'])}}",
                            }
                        ]
                },
				{
                    "n":["/skin/adminhtml/default/nav_icon/setting_ico.png","业务管理"],
                    "s":[
                            {
                                "n":["公司管理"],
                                "url":"{{helper.createUrl(['p':'company/index'])}}",
                            },
                            {
                                "n":["指标模版"],
                                "url":"{{helper.createUrl(['p':'quotatpl/index'])}}",
                            }
                            
                        ]
                },
				
                {
                    "n":["/skin/adminhtml/default/nav_icon/setting_ico.png","系统管理"],
                    "s":[
					        {
                                "n":["系统设定"],
                                "url":"{{helper.createUrl(['p':'settings/config'])}}",
                            },
                            {
                                "n":["管理员列表"],
                                "url":"{{helper.createUrl(['p':'adminuser/index'])}}",
                            },
                            {
                                "n":["角色列表"],
                                "url":"{{helper.createUrl(['p':'adminrole/index'])}}",
                            },
							{
                            "n":["操作日志"],
                                "url":"{{helper.createUrl(['p':'adminlog/index'])}}",
                            },
                            {
                            "n":["菜单管理"],
                                "url":"{{helper.createUrl(['p':'menu/index'])}}",
                            }
                        ]
                }

				
            ];
            //获取菜单cookie保存的值，
            var cookie_nav = (cookie.get("nav_de") === "undefined") ? [] : cookie.get("nav_de");
            //从cookie中取出的值是字符串，而函数中要用的是一个数组，因此，这里需要进行一个判断，如果是字符串，应转为数组
            if(typeof(cookie_nav) === "string"){
                cookie_nav = cookie_nav.split(",");
            }
            //这里是要给联动插件传的参数
            //这是数据，
            var d = {
                data:data,
                selects:["#nav_1","#nav_2"],
                str:['<li>'+
                            '<div class="nav1_img">'+
                                '<img src="str" alt="" />'+
                            '</div>'+
                            '<span class="nav1_txt">str</span>'+
                            '<i class="iconfont icon-yousanjiao"></i>'+
                        '</li>',
                        '<li>str</li>'],
                replace:"str",
                defaultData:cookie_nav,
                storageData:[["pid","id"],["url"]],
                on:"active"
            }
            //这里是回调函数
            var call = {
                begin:getUrl,
                on:getUrl
            }
            //调用插件，生成导航。
            $("body").more(d,call);
            
            //将对应的页面显示到iframe中，同时用cookie记录当前选中的页面
            function getUrl(c){
                var num = d.selects.length;
                var ele = d.selects[num-1];
                var url = $(ele).children(".active").attr("data-url");
                $("#content_iframe").attr("src",url);
                
                //设置cookie，使得刷新页面之后还是能够进到上次关闭的页面
                cookie.set("nav_de",c);
            }
            
            //菜单功能实现 end。
            
            //菜单展开收缩函数
            $("#nav_switch").click(function(){
                var flag = $("#main").hasClass("open");
                if(flag){
                    $(this).find("img").attr("src","/skin/adminhtml/default/images/open.png");
                    $("#main").removeClass("open");
                }else{
                    $(this).find("img").attr("src","/skin/adminhtml/default/images/closed.png");
                    $("#main").addClass("open");
                }
            })
            
            //头部消息栏信息弹出框
            $("#li_msg_box").click(function(){
                var flag = $("#msg_con_box").is(":hidden");
                if(flag){
                    $("#msg_con_box").show();
                }else{
                    $("#msg_con_box").hide();
                }
                return false;
            })
            $("#msg_con_box").click(function(){
                return false;
            })
            $("#closed_msg,body").click(function(){
                $("#msg_con_box").hide();
            })
            IframeOnClick.track(document.getElementById("content_iframe"), function() {
                $("#msg_con_box").hide();
            });
            
        }
        

    </script>