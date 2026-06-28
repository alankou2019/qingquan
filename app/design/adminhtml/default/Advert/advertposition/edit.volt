<script src="/skin/adminhtml/default/js/form.js"></script>
<script type="text/javascript" src="/skin/adminhtml/default/libs/laydate/laydate.js" ></script>
<!--滚动条-->
<script
	src="/skin/adminhtml/default/libs/nicescroll/jquery.nicescroll.js"></script>
<div class="full_box">
	<div class="full_title">

		<span style="float: right; cursor: pointer;"
			onclick="window.location=window.location;"><i
			class="iconfont icon-shuaxin"></i></span> <span class="name">{% if item %}编辑{% else %}新增{% endif %}广告位</span> <a
			class="go_back" onclick="window.location='{{helper.createUrl(['p':'advertposition/index'])}}';"> <i
			class="iconfont icon-fanhui"></i> <span>返回广告位列表</span>
		</a>

	</div>

	<div class="full_cont">
		<!--表单-->
		<form action="{{helper.createUrl(['p':'advertposition/save'])}}" method="post" class="form_full" id="dataForm" name="dataForm" enctype="multipart/form-data">
		     <input type="hidden" name="id" value="">
			<div class="sub_title">广告位基本信息</div>
			<ul class="list_form_full">

				<li class="posi_lm">

					<div class="left posi_l must">广告位名称:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="name"  datatype="*4-16"  value="" errormsg="广告位名称至少4个字符,最多16个字符！" maxlength="16"/>
						</div>
					</div>
				</li>

				<li class="posi_lm">
					<div class="left posi_l must">广告位标识:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="key_code"  datatype="*4-16"  value="" errormsg="广告位标识至少4个字符,最多16个字符！" maxlength="16"/>
						</div>
					</div>
				</li>

				<li class="posi_lm">

					<div class="left posi_l">状态:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="radio" name="status" value="1" {% if item.status %}checked {% endif %}>开启
							<input type="radio" name="status" value="0" {% if !item.status %}checked {% endif %}>关闭
						</div>
					</div>
				</li>

				<li class="posi_lm">

					<div class="left posi_l must">广告位宽X高:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="width"  datatype="n"  value=""  style="width:120px"/>
                            ~<input type="text" name="height"  datatype="n"  value="" style="width:120px"/>
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
		
		//日期插件
		$(".dateinput").click(function () {
	    	laydate({
				format: 'YYYY-MM-DD hh:mm:ss',
				istime: true
			})
	    })
		
		{% if item %}
		var formObj = new Form('dataForm');
		formObj.init({{item|json_encode}});
		{% endif %}

	});
</script>