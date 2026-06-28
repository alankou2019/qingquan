<script src="/skin/adminhtml/default/js/form.js"></script>
<script type="text/javascript" src="/skin/adminhtml/default/libs/laydate/laydate.js" ></script>
<!--滚动条-->
<script
	src="/skin/adminhtml/default/libs/nicescroll/jquery.nicescroll.js"></script>
<div class="full_box">
	<div class="full_title">

		<span style="float: right; cursor: pointer;"
			onclick="window.location=window.location;"><i
			class="iconfont icon-shuaxin"></i></span> <span class="name">{% if item %}编辑{% else %}新增{% endif %}广告</span> <a
			class="go_back" onclick="window.location='{{helper.createUrl(['p':'advert/index'])}}';"> <i
			class="iconfont icon-fanhui"></i> <span>返回广告列表</span>
		</a>

	</div>

	<div class="full_cont">
		<!--表单-->
		<form action="{{helper.createUrl(['p':'advert/save'])}}" method="post" class="form_full" id="dataForm" name="dataForm" enctype="multipart/form-data">
		     <input type="hidden" name="id" value="">
			<div class="sub_title">广告基本信息</div>
			<ul class="list_form_full">

				<li class="posi_lm">

					<div class="left posi_l must">广告名称:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="name"  datatype="*4-16"  value="" errormsg="广告名称至少4个字符,最多16个字符！" maxlength="16"/>
						</div>
					</div>
				</li>

				<li class="posi_lm">
					<div class="left posi_l must">广告位置:</div>
					<div class="right posi_m">
						<select name="position_id" class="select_name" datatype="n" errormsg="请选择广告位置..！">
							<option value="">请选择..</option>
							{% for advp in advertPositions %}
                            <option value="{{ advp.id}}">{{ advp.name}}</option>
                            {% endfor %}
						</select>
					</div>
				</li>
                
                <li class="posi_lm">

					<div class="left posi_l">链接地址:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="link"  datatype="url"  ignore="ignore" value="" errormsg="链接地址格式错误！" maxlength="100"/>
						</div>
					</div>
				</li>
				

				<li class="posi_lm">

					<div class="left posi_l">广告图片:</div>
					<div class="right posi_m">
						<div class="input_clear">
                            <input type="hidden" name="content" value="{{item.content}}"/>
							<input type="file" name="content" accept="image/*"/>
                            {% if item.content %}
                            	<br />
                            	<a href="{{item.content}}" target="_blank"><img src="{{item.content}}" width="100" height="100"/></a>
                            {% endif %}
						</div>
					</div>
				</li>
                
                <li class="posi_lm">

					<div class="left posi_l must">开始时间:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="start_time" value="" datatype="*" class="laydate-icon dateinput" maxlength="16" readonly="readonly"/>
						</div>
					</div>
				</li>
                
                <li class="posi_lm">

					<div class="left posi_l must">结束时间:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="end_time" value=""  datatype="*" class="laydate-icon dateinput" maxlength="20" readonly="readonly"/>
						</div>
					</div>
				</li>
				
				                <li class="posi_lm">

                    <div class="left posi_l must">排序:</div>
                    <div class="right posi_m">
                        <div class="input_clear">
                            <input type="text" name="sort"  datatype="n"  value="0" errormsg="排序不能为空" maxlength="4"/>
                        </div>
                    </div>
                </li>

				<li class="posi_lm">

					<div class="left posi_l ">广告描述:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<textarea name="description"></textarea>
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