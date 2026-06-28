<script src="/skin/adminhtml/default/js/form.js"></script>
<!--滚动条-->
<script src="/skin/adminhtml/default/libs/nicescroll/jquery.nicescroll.js"></script>
<script type="text/javascript" charset="utf-8" src="/skin/adminhtml/default/libs/UEditor/ueditor.config.js"></script>
<script type="text/javascript" charset="utf-8" src="/skin/adminhtml/default/libs/UEditor/ueditor.all.min.js"></script>
<script type="text/javascript" charset="utf-8" src="/skin/adminhtml/default/libs/UEditor/zh-cn.js"></script>
<div class="full_box">
	<div class="full_title">

		<span style="float: right; cursor: pointer;"
			onclick="window.location=window.location;"><i
			class="iconfont icon-shuaxin"></i></span> <span class="name">{% if item %}编辑{% else %}新增{% endif %}支付方式</span> <a
			class="go_back" onclick="window.location='{{helper.createUrl(['p':'payment/index'])}}';"> <i
			class="iconfont icon-fanhui"></i> <span>返回支付方式列表</span>
		</a>

	</div>

	<div class="full_cont">
		<!--表单-->
		<form action="{{helper.createUrl(['p':'payment/save'])}}" method="post" class="form_full" id="dataForm" name="dataForm" enctype="multipart/form-data">
		     <input type="hidden" name="id" value="">
             <input type="hidden" name="_has_key" value="" />
			<div class="sub_title">支付方式基本信息</div>
			<ul class="list_form_full">

				<li class="posi_lm">
					<div class="left posi_l must">支付方式名称:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="name"  datatype="*2-16"  value="" errormsg="支付方式名称至少2个字符,最多16个字符！" maxlength="16"/>
						</div>
					</div>
				</li>
				
			
				<li class="posi_lm">
					<div class="left posi_l">简单描述:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<textarea name="description">{{item.description}}</textarea> 
						</div>
					</div>
				</li>
				
				<li class="posi_lm">

					<div class="left posi_l must">手续费设置:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input name="poundage_type" class="time_radio" type="radio" value="1" onclick="$('#paymentFeeText').text('商品总额的百分比：');" checked="checked" />按商品总额的百分比
							<input name="poundage_type" class="time_radio" type="radio" value="2" onclick="$('#paymentFeeText').text('固定收取的手续费：');" />按固定金额
							<span id="paymentFeeText">{说明}</span><input class="small" name="poundage" type="text" value=""  pattern="required" alt="费用不能为空！" />
						</div>
					</div>
				</li>
				
				
				<li class="posi_lm">
					<div class="left posi_l">应用客户端:</div>
					<div class="right posi_m">
						<div class="input_clear">
						<input name="client_type" type="radio" value="1" checked="checked" class="time_radio"/>PC电脑
						<input name="client_type" type="radio" value="2" class="time_radio"/>MOBILE移动端
						<input name="client_type" type="radio" value="3" class="time_radio"/>通用
						<label>在不同的客户端访问时，会显示不同的支付方式</label>
						</div>
					</div>
				</li>
				<li class="posi_lm">

					<div class="left posi_l">图标:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="file" name="logo" accept="image/*"/>
                            <input type="hidden" name="logo" />
                            {% if item.logo %}
                            	<br />
                            	<a href="{{item.logo}}" target="_blank"><img src="{{item.logo}}" width="100" height="100"/></a>
                            {% endif %}
						</div>
					</div>
				</li>	
				
				<li class="posi_lm">

					<div class="left posi_l">排序:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="order" value="0"/>
						</div>
					</div>
				</li>
				
				<li class="posi_lm">

					<div class="left posi_l">支付说明:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<textarea name="note" id="content"  style="width:100%;max-width:1000px;height:300px;"></textarea>
						</div>
					</div>
				</li>
				
				<li class="posi_lm">

					<div class="left posi_l must">开启:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input name="status" type="radio" value="0" class="time_radio" checked="checked" />启用
							<input name="status" type="radio" value="1" class="time_radio"/>禁用
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
	function getEditors(parameter){
		var ue = parameter.ue;
		var e = parameter.e;
		ue = UE.getEditor(e,{toolbars: [
			    [
			        'undo', //撤销
			        'redo', //重做
			        'bold', //加粗
			        'indent', //首行缩进
			        'italic', //斜体
			        'underline', //下划线
			        'strikethrough', //删除线
			        'subscript', //下标
			        'fontborder', //字符边框
			        'superscript', //上标
			        'formatmatch', //格式刷
			        'removeformat', //清除格式
			        'unlink', //取消链接
			        'cleardoc', //清空文档
			        'fontfamily', //字体
			        'fontsize', //字号
			        'paragraph', //段落格式
			        'simpleupload', //单图上传
			        'insertimage', //多图上传
			        'link', //超链接
			        'spechars', //特殊字符
			        'searchreplace', //查询替换
			        'help', //帮助
			        'justifyleft', //居左对齐
			        'justifyright', //居右对齐
			        'justifycenter', //居中对齐
			        'justifyjustify', //两端对齐
			        'forecolor', //字体颜色
			        'backcolor', //背景色
			        'fullscreen', //全屏
			        'imagecenter', //居中
			        'lineheight', //行间距
			        'edittip ', //编辑提示
			        'touppercase', //字母大写
			        'tolowercase', //字母小写
			       ]
				]
			}
		);
		return ue;
	}

	$(function() {
		//滚动条优化
		$("html").niceScroll({
			cursorcolor : "#ccc"
		});
		
		getEditors({ue:"ue2",e:"content"});
		
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