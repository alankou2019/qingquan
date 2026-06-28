<script src="/skin/adminhtml/default/js/form.js"></script>
<script type="text/javascript" src="/skin/adminhtml/default/libs/laydate/laydate.js" ></script>
<!--滚动条-->
<script
	src="/skin/adminhtml/default/libs/nicescroll/jquery.nicescroll.js"></script>
<div class="full_box">
	<div class="full_title">

		<span style="float: right; cursor: pointer;"
			onclick="window.location=window.location;"><i
			class="iconfont icon-shuaxin"></i></span> <span class= "name">修改密码</span>
		</a>

	</div>

	<div class="full_cont">
		<!--表单-->
		<form action="{{helper.createUrl(['p':'companyuser/savepassword'])}}" method="post" class="form_full" id="dataForm" name="dataForm" enctype="multipart/form-data">
		    <input type="hidden" name="id" value="{{item.id}}">
			<div class="sub_title">用户基本信息</div>
			<ul class="list_form_full">

				<li class="posi_lm">
					<div class="left posi_l must">用户名称:</div>
					<div class="right posi_m">
						<div class="input_clear line30">
							{{item.name}}
						</div>
					</div>
				</li>

				<li class="posi_lm">
					<div class="left posi_l">密码:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="password" name="new_pass" ignore="ignore"  datatype="*6-16"  value="" errormsg="请输入正确格式的密码"  maxlength="16" minlength='6' />
						</div>   <div class="prompt_box">
							 不修改密码无需填写						</div>
					</div>
				</li>

			</ul>

			<div class="online_btn_box">
				<button type="reset" class="f_btn">取消</button>
				<button type="button" class="f_btn active" id="btnSubmit">保存</button>
			</div>
		</form>

	</div>
</div>
<script src="/skin/adminhtml/default/js/common.js"></script>
<script src="/skin/adminhtml/default/js/ljk.js"></script>
<script src="/skin/adminhtml/default/js/form.js"></script>
<script>
	$(function() {
		//滚动条优化
		$("html").niceScroll({
			cursorcolor : "#ccc"
		});
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