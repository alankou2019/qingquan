<script src="/skin/adminhtml/default/js/form.js"></script>
<script type="text/javascript" src="/skin/adminhtml/default/libs/laydate/laydate.js" ></script>

<!--编辑器-->
<script type="text/javascript" charset="utf-8" src="/skin/adminhtml/default/libs/UEditor/ueditor.config.js"></script>
<script type="text/javascript" charset="utf-8" src="/skin/adminhtml/default/libs/UEditor/ueditor.all.min.js"></script>
<script type="text/javascript" charset="utf-8" src="/skin/adminhtml/default/libs/UEditor/zh-cn.js"></script>
<!--编辑器-->


<!--滚动条-->
<script
	src="/skin/adminhtml/default/libs/nicescroll/jquery.nicescroll.js"></script>
<div class="full_box">
	<div class="full_title">

		<span style="float: right; cursor: pointer;"
			onclick="window.location=window.location;"><i
			class="iconfont icon-shuaxin"></i></span> <span class= "name">{% if item %}编辑{% else %}新增{% endif %}指标库</span> <a
			class="go_back" onclick="window.location='{{helper.createUrl(['p':'quota/index'])}}';"> <i
			class="iconfont icon-fanhui"></i> <span>返回指标库列表</span>
		</a>

	</div>

	<div class="full_cont">
		<!--表单-->
		<form action="{{helper.createUrl(['p':'quota/save'])}}" method="post" class="form_full" id="dataForm" name="dataForm" enctype="multipart/form-data">
		    <input type="hidden" name="id" value="">
			<div class="sub_title">指标库基本信息</div>
			<ul class="list_form_full">

				<li class="posi_lm">
					<div class="left posi_l must">指标名称:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="name"  datatype="*2-16"  value=""  maxlength="16"/>
						</div>
					</div>
				</li>
				
				<li class="posi_lm">
					<div class="left posi_l must">部门:</div>
					<div class="right posi_m">
							<select name="depart_id" class="departselect">
								{% for departitem in departlist %}
									<option  value="{{departitem['id']}}"  {% if departitem['id'] == item.depart_id%}selected="selected"{% endif %} >{{departitem['name']}} </option>
								{% endfor %}
							</select>
					</div>
				</li>
				<!--
				<li class="posi_lm">
					<div class="left posi_l ">指标说明:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<textarea name="remark"></textarea>
						</div>
					</div>
				</li>
				-->
				<li class="posi_lm">
					<div class="left posi_l must">评分方式:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="radio" value='1' name='type' checked='checked'/>  百分制
							<input type="radio" value='2' name='type' {% if item.type == 2 %}checked='checked'{% endif %}/>  十分制
							<input type="radio" value='3' name='type' {% if item.type == 3 %}checked='checked'{% endif %}/>  权重制
							<input type="radio" value='4' name='type' {% if item.type == 4 %}checked='checked'{% endif %}/>  加减分制
							<input type="radio" value='5' name='type' {% if item.type == 5 %}checked='checked'{% endif %}/>  5分制
						</div>
					</div>
				</li>
				
				<li class="posi_lm">

					<div class="left posi_l ">评分标准:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<textarea name="point_desc"   style="width:100%;max-width:1000px;height:300px;" ></textarea>
						</div>
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
		
		//编辑器
		getEditors({ue:"ue2",e:"content"});
		
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
</script>