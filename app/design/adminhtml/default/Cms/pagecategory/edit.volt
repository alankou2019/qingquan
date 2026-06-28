<script src="/skin/adminhtml/default/js/form.js"></script>
<script type="text/javascript" src="/skin/adminhtml/default/libs/laydate/laydate.js" ></script>
<!--滚动条-->
<script
	src="/skin/adminhtml/default/libs/nicescroll/jquery.nicescroll.js"></script>
<div class="full_box">
	<div class="full_title">

		<span style="float: right; cursor: pointer;"
			onclick="window.location=window.location;"><i
			class="iconfont icon-shuaxin"></i></span> <span class="name">{% if item %}编辑{% else %}新增{% endif %}单页分类</span> <a
			class="go_back" onclick="window.location='{{helper.createUrl(['p':'articlecategory/index'])}}';"> <i
			class="iconfont icon-fanhui"></i> <span>返回单页分类列表</span>
		</a>

	</div>

	<div class="full_cont">
		<!--表单-->
		<form action="{{helper.createUrl(['p':'pagecategory/save'])}}" method="post" class="form_full" id="dataForm" name="dataForm" enctype="multipart/form-data">
		     <input type="hidden" name="id" value="">
			<div class="sub_title">单页分类基本信息</div>
			<ul class="list_form_full">

				<li class="posi_lm wrong">
					<div class="left posi_l must">上级分类:</div>
					<div class="right posi_m">
						<select name="parent_id" class="select_name" datatype="n" errormsg="请选择上级分类！">
							<option value="0">选择单页分类</option>
							{% for category in categorys %}
							<option value="{{ category['id']}}">{{category['delimiter']}}{{category['name']}}</option>
							{% endfor %}
                        </select>
				</li>
				
				<li class="posi_lm">

					<div class="left posi_l ">分类名称:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="name"  datatype="*2-16"  value="" errormsg="分类名称至少2个字符,最多16个字符！" maxlength="16"/>
						</div>
					</div>
				</li>
				<li class="posi_lm">

					<div class="left posi_l ">分类标识:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="keycode"    value="" maxlength="16"/>
						</div>
                         <div class="prompt_box">
							为空将自动根据分类名称拼音生成
						</div>
					</div>
				</li>
                
				<li class="posi_lm">

					<div class="left posi_l">Banner:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="file" name="pic" accept="image/*"/>
                            <input type="hidden" name="pic" />
                            {% if item.pic %}
                            	<br />
                            	<a href="{{item.pic}}" target="_blank"><img src="{{item.pic}}" width="100" height="100"/></a>
                            {% endif %}
						</div>
					</div>
				</li>
				
				<li class="posi_lm">

					<div class="left posi_l ">排序:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="sort" value="" />
						</div>
                                                <div class="prompt_box">
							排序请填写数字，最小的排在前面！
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