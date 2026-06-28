<script src="/skin/adminhtml/default/js/form.js"></script>
<!--滚动条-->
<script
	src="/skin/adminhtml/default/libs/nicescroll/jquery.nicescroll.js"></script>
<div class="full_box">
	<div class="full_title">

		<span style="float: right; cursor: pointer;"
			onclick="window.location=window.location;"><i
			class="iconfont icon-shuaxin"></i></span> <span class="name">{% if item %}编辑{% else %}新增{% endif %}会员</span> <a
			class="go_back" onclick="window.location='{{helper.createUrl(['p':'user/index'])}}';"> <i
			class="iconfont icon-fanhui"></i> <span>返回会员列表</span>
		</a>

	</div>

	<div class="full_cont">
		<!--表单-->
		<form action="{{helper.createUrl(['p':'user/save'])}}" method="post" class="form_full" id="dataForm" name="dataForm" enctype="multipart/form-data">
		     <input type="hidden" name="user_id" value="">
             <input type="hidden" name="_has_key" value="" />
			<div class="sub_title">会员基本信息</div>
			<ul class="list_form_full">

				<li class="posi_lm">
					<div class="left posi_l must">用户名:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="user_name"  datatype="*6-11"  value="" errormsg="用户名至少6个字符,最多10个字符！" maxlength="11"/>
						</div>
					</div>
				</li>

				<li class="posi_lm">

					<div class="left posi_l">真实姓名:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="true_name" value="" maxlength="16"/>
						</div>
					</div>
				</li>
				
				<li class="posi_lm">
					<div class="left posi_l must">手机号码:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="phone" datatype="m"  />
						</div>
					</div>
				</li>
				
				<li class="posi_lm">

					<div class="left posi_l">Email:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="email" ignore="ignore" datatype="e" value=""  maxlength="40"/>
						</div>
					</div>
				</li>
				
				<li class="posi_lm">
					<div class="left posi_l">QQ:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="qq" value="" maxlength="40"/>
						</div>
					</div>
				</li>
				
				<li class="posi_lm">

					<div class="left posi_l">性别:</div>
					<div class="right posi_m">
						<!--这里是时间下拉列表-->
						<select name="sex" class="select_name" datatype="n" errormsg="请选择！">
							<option value="1">男</option>
							<option value="2">女</option>
						</select>
					</div>
				</li>
		
				<li class="posi_lm">

					<div class="left posi_l">头像:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="file" name="avatar" accept="image/*"/>
                            <input type="hidden" name="avatar" />
                            {% if item.avatar %}
                            	<br />
                            	<a href="{{item.avatar}}" target="_blank"><img src="{{item.avatar}}" width="100" height="100"/></a>
                            {% endif %}
						</div>
					</div>
				</li>	
				
				<li class="posi_lm">

					<div class="left posi_l {% if item is empty %}must{% endif %}">密码:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="password" name="password" {% if item %}ignore="ignore"{% endif %} datatype="*6-16"  value="" errormsg="登录名至少6个字符,最多16个字符！" maxlength="16" />
						</div>
					</div>
				</li>

				<li class="posi_lm">

					<div class="left posi_l {% if item is empty %}must{% endif %}">重复密码:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="password" name="repassword" {% if item %}ignore="ignore"{% endif %} datatype="*" recheck="password"  errormsg="您两次输入的密码不一致！" maxlength="16"/>
						</div>
					</div>
				</li>

			</ul>

			<div class="online_btn_box">
				<button type="reset" class="f_btn">重置</button>
				<button type="button" class="f_btn active" id="btnSubmit">确认</button>
			</div>
		</form>

	</div>
</div>
<script src="/skin/adminhtml/default/js/common.js"
	type="text/javascript" charset="utf-8"></script>
<script src="/skin/adminhtml/default/js/check.js" type="text/javascript"
	charset="utf-8"></script>
<script src="/skin/adminhtml/default/js/ljk.js"></script>
<script src="/skin/adminhtml/default/js/form.js"></script>
<script>
	$(function() {
		//滚动条优化
		$("html").niceScroll({
			cursorcolor : "#ccc"
		});
		//商品上架单选框美化
		$(".time_radio").CheckBox();
		Utils.validate("#dataForm","#btnSubmit",function(curform){
			   $("#btnSubmit").attr('disabled','disabled');
		       $("#btnSubmit").html('处理中..');       
		       return true;
		});
		{% if item %}
		var formObj = new Form('dataForm');
		formObj.init({{item|json_encode}});
		{% endif %}

	});
</script>