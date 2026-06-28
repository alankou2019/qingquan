            <!--轮播JS-->
        <script src="/skin/adminhtml/default/libs/superslide/jquery.superslide.js" type="text/javascript" charset="utf-8"></script>
            <div class="login_body">
            <!--轮播-->
            <div class="banner">
                <!--序列点-->
                <div class="hd">
                    <ul>
                        <li></li>
                        <li></li>
                    </ul>
                </div>
                <!--序列点-->
                <!--轮播内容-->
                <div class="bd">
                    <ul>
                        <li><img src="/skin/adminhtml/default/images/11.png"/></li>
                        <li><img src="/skin/adminhtml/default/images/22.png"/></li>
                    </ul>
                </div>
                <!--轮播内容-->
            </div>
            <!--轮播-->
            
            <!--登录-->
            <div class="login_box">
                <div class="logo">
                    <img src="/skin/adminhtml/default/images/1_11.png" />
                    <span>绩效管理系统</span>
                </div>
                <div class="box">
                    <form action="{{helper.createUrl(['p':'login/doLogin'])}}" name="login" method="post" class="login_form" id="loginForm">
                        <ul>
                            <li>
                                <input type="text" class="text name" name="username" placeholder="请输入用户名" datatype="s5-16" errormsg="用户名至少5个字符,最多16个字符！" nullmsg="请输入用户名！"/>                            
                            </li>
                            <li>
                                <input type="password" class="text psd" name="password" placeholder="请输入密码" datatype="*6-16" nullmsg="请输入密码！" errormsg="密码范围在6~16位之间！"/>                            
                            </li>
                            <li class="yzm_li">
                                <input type="text" class="text yzm" name="code" placeholder="请输入验证码" maxlength="4" datatype="s4-4" nullmsg="请输入验证码！" errormsg="验证码错误"/>
                                <img src="{{helper.createUrl(['p':'login/captcha'])}}" id="captchaImg" alt="看不清点击刷新" title="看不清点击刷新" onClick="changeCaptcha()"  class="yzm_img"/>
                            </li>
                            <div class="tip_msg"></div>
                            <input type="button"  id="submitBtn" class="submit" value="登录"/>
                        </ul>
                    </form>
                </div>
            </div>
            <!--登录-->
        </div>
    <script>
	
		function changeCaptcha()
		{
			var $captcha = $("#captchaImg");
			var src = $captcha.attr('data-src');
			if(typeof(src)=='undefined'){
				src = $captcha.attr('src');
				$captcha.attr('data-src',src);
			}
		    src +="?r="+Math.random();
			$captcha.attr('src',src);
		} 
		
        window.onload = function(){
            //banner轮播
            jQuery(".banner").slide({mainCell:".bd ul",autoPlay:true,mouseOverStop:false});
            
            //输入框边框颜色
            $(".login_box .text").focus(function () {
                $(this).parent().addClass('on');
            });
            $(".login_box .text").blur(function () {
                $(this).parent().removeClass('on');
            })
            
		   $("#loginForm").Validform({
				btnSubmit:'#submitBtn',
                tiptype:function(msg,o,cssctl){
					var objtip=$(".tip_msg");
					if(o.type==3){
						objtip.text(msg);  
						objtip.addClass("Validform_checktip");
						objtip.addClass("Validform_wrong");
						
					}else{
						objtip.removeClass("Validform_checktip");
						objtip.removeClass("Validform_wrong");
						objtip.text('');  
					}
                },
				beforeSubmit:function(curform){
					$("#submitBtn").attr('disabled','disabled');
					$("#submitBtn").html('登录中...');
					$.ajax({
						url:$("#loginForm").attr('action'),
						data:$("#loginForm").serialize(),
						dataType:"json",
						type:"POST",
						success: function(res){
							if(res.status=='y')
							{
								$("#submitBtn").html('跳转中..');
								window.location="{{helper.createUrl(['p':'index/index'])}}";
							}else
							{
								changeCaptcha();
								var objtip=$(".tip_msg");
								objtip.text(res.error);  
								objtip.addClass("Validform_checktip");
								objtip.addClass("Validform_wrong");
								$("#submitBtn").removeAttr('disabled');
								$("#submitBtn").html('登录');
							}
						},
						error: function(){
							$("#submitBtn").removeAttr('disabled');
							$("#submitBtn").html('登录');
						}
					});
					return false;
				}
            });
		
        }
    </script>