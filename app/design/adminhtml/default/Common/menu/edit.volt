<script src="/skin/adminhtml/default/js/form.js"></script>
<script type="text/javascript" src="/skin/adminhtml/default/libs/laydate/laydate.js" ></script>
<!--滚动条-->
<script
	src="/skin/adminhtml/default/libs/nicescroll/jquery.nicescroll.js"></script>
<div class="full_box">
	<div class="full_title">

		<span style="float: right; cursor: pointer;"
			onclick="window.location=window.location;"><i
			class="iconfont icon-shuaxin"></i></span> <span class="name">{% if item %}编辑{% else %}新增{% endif %}菜单</span> <a
			class="go_back" onclick="window.location='{{helper.createUrl(['p':'menu/index'])}}';"> <i
			class="iconfont icon-fanhui"></i> <span>返回菜单列表</span>
		</a>

	</div>

	<div class="full_cont">
		<!--表单-->
		<form action="{{helper.createUrl(['p':'menu/save'])}}" method="post" class="form_full" id="dataForm" name="dataForm" enctype="multipart/form-data">
		     <input type="hidden" name="id" value="">
			<div class="sub_title">菜单基本信息</div>
			<ul class="list_form_full">
				<li class="posi_lm">
					<div class="left posi_l must">菜单名称:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="name"  datatype="*2-16"  value="" errormsg="菜单名称至少2个字符,最多16个字符！" maxlength="16"/>
						</div>
					</div>
				</li>
				<li class="posi_lm wrong">
					<div class="left posi_l">上级菜单:</div>
					<div class="right posi_m">
						<select name="parent_id" class="select_name" datatype="n" errormsg="请选择上级菜单！">
							<option value="0">顶级菜单</option>
							{% for category in categorys %}
							<option value="{{ category['id']}}">{{category['delimiter']}}{{category['name']}}</option>
							{% endfor %}
                        </select>
				</li>
				<li class="posi_lm">
					<div class="left posi_l">是否新窗口:</div>
					<div class="right posi_m">
						<div class="txt" style="line-height:30px;">	
                        <input type="radio" name="new_window" value="1" />是&nbsp;&nbsp;<input type="radio" name="show" value="0" checked/>否
						</div>
					</div>
				</li>
				<li class="posi_lm">
					<div class="left posi_l ">排序:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="sort" value="0" />
							<small style="" class="help-block prompt_box"><i class="fa fa-times-circle-o"></i>排序请填写数字，越小越靠前！</small>
						</div>
					</div>
				</li>
				<li class="posi_lm">

					<div class="left posi_l">网址:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="link" ignore="ignore" value=""/>
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