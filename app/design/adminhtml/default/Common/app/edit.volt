<script src="/skin/adminhtml/default/js/form.js"></script>
<!--滚动条-->
<script
	src="/skin/adminhtml/default/libs/nicescroll/jquery.nicescroll.js"></script>
<div class="full_box">
	<div class="full_title">

		<span style="float: right; cursor: pointer;"
			onclick="window.location=window.location;"><i
			class="iconfont icon-shuaxin"></i></span> <span class="name">{% if item %}编辑{% else %}新增{% endif %}应用</span> <a
			class="go_back" onclick="window.location='{{helper.createUrl(['p':'app/index'])}}';"> <i
			class="iconfont icon-fanhui"></i> <span>返回应用列表</span>
		</a>

	</div>

	<div class="full_cont">
		<!--表单-->
		<form action="{{helper.createUrl(['p':'app/save'])}}" method="post" class="form_full" id="dataForm" name="dataForm" enctype="multipart/form-data">
		     <input type="hidden" name="id" value="">
			<div class="sub_title">应用基本信息</div>
			<ul class="list_form_full">

				<li class="posi_lm">

					<div class="left posi_l must">应用名称:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="app_name"  datatype="*2-20"  value="" errormsg="登录名至少2个字符,最多20个字符！" maxlength="20" />
						</div>
					</div>
				</li>

				{% if item %}
				<li class="posi_lm">

					<div class="left posi_l must">应用key:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="hidden" name="app_key" id="app_key_value" datatype="*" value="" nullmsg="应用key不能为空"/>
                             <span id="app_key_text">{{item.app_key}}</span>
                             <button type="button" onclick="resetAppkey()" class="f_btn active" style=" border: 1px solid #4560e6; color: #fff; background-color:#4560e6;height: 38px;margin: 0 20px;width: 138px;">重置应用KEY</button>
						</div>
					</div>
				</li>
                {% endif %}

				<li class="posi_lm">

					<div class="left posi_l">应用描述:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<textarea name="app_desc" rows='3' value="" nullmsg="应用描述不能为空" ></textarea>
						</div>
					</div>
				</li>

				<li class="posi_lm">
					<div class="left posi_l must">公钥:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<textarea name="public_key" rows='3'  datatype="*" value="" nullmsg="公钥不能为空" ></textarea>
						</div>
					</div>
				</li>
				<li class="posi_lm">
					<div class="left posi_l must">私钥:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<textarea name="private_key" rows='3'  datatype="*" value="" nullmsg="私钥不能为空" ></textarea>
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

function resetAppkey() {
    var s = [];
    var hexDigits = "0123456789abcdef";
    for (var i = 0; i < 36; i++) {
        s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
    }
    s[14] = "4";  // bits 12-15 of the time_hi_and_version field to 0010
    s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1);  // bits 6-7 of the clock_seq_hi_and_reserved to 01
    s[8] = s[13] = s[18] = s[23] = "";
 
    var uuid = s.join("");
	$("#app_key_value").val(uuid);
	$("#app_key_text").html(uuid);
	
}

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