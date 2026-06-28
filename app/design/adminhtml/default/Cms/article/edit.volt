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
			class="iconfont icon-shuaxin"></i></span> <span class="name">{% if item %}编辑{% else %}新增{% endif %}文章</span> <a
			class="go_back" onclick="window.location='{{helper.createUrl(['p':'article/index'])}}';"> <i
			class="iconfont icon-fanhui"></i> <span>返回文章列表</span>
		</a>

	</div>
	<div class="full_cont">
		<!--表单-->
		<form action="{{helper.createUrl(['p':'article/save'])}}" method="post" class="form_full" id="dataForm" name="dataForm" enctype="multipart/form-data">
		     <input type="hidden" name="id" value="">
			<div class="sub_title">文章基本信息</div>
			<ul class="list_form_full">
				
				<li class="posi_lm">

					<div class="left posi_l must">标题:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="title" datatype="*2-16"  value="" errormsg="标题至少2个字符,最多30个字符！" maxlength="30" />
						</div>
					</div>
				</li>
				
				<li class="posi_lm wrong">
					<div class="left posi_l ">分类:</div>
					<div class="right posi_m">
						<select name="cat_id" class="select_name" datatype="n" errormsg="请选择上级分类！">
							<option value="">选择文章分类</option>
							{% for category in categorys %}
								<option value="{{category['id']}}">{{category['delimiter']}}{{category['name']}}</option>
							{% endfor %}
                        </select>
				</li>
				<li class="posi_lm wrong">
					<div class="left posi_l ">标签:</div>
					<div class="right posi_m">
						<div class="txt">
                        <input type="checkbox" name="flag[]" value="n" />普通&nbsp;&nbsp;
                        <input type="checkbox" name="flag[]" value="t" />推荐&nbsp;&nbsp;
                        <input type="checkbox" name="flag[]" value="h" />热门
                        </div>
				</li>

                <li class="posi_lm">
					<div class="left posi_l">显示:</div>
					<div class="right posi_m">
						<div class="txt">	
                        <input type="radio" name="show" value="1" />是&nbsp;&nbsp;<input type="radio" name="show" value="0" />否
						</div>
					</div>
				</li>
				
                
				<li class="posi_lm">

					<div class="left posi_l">封面:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="file" name="pic" accept="image/*"/>
                            <input type="hidden" name="pic" value="" />
                            {% if item.pic %}
                            	<br />
                            	<a href="{{item.pic}}" target="_blank"><img src="{{item.pic}}" width="100" height="100"/></a>
                            {% endif %}
						</div>
					</div>
				</li>
                
				<li class="posi_lm">

					<div class="left posi_l ">内容:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<textarea name="content" id="content"  style="width:100%;max-width:1000px;height:300px;"></textarea>
						</div>
					</div>
				</li>

				
				<li class="posi_lm">

					<div class="left posi_l ">SEO关键字:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="seo_keywords" value="" />
						</div>
					</div>
				</li>

				<li class="posi_lm">

					<div class="left posi_l ">SEO描述:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<textarea name="seo_description"></textarea>
						</div>
					</div>
				</li>
                
                <li class="posi_lm">

					<div class="left posi_l">链接地址:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="url"  datatype="url"  ignore="ignore" value="" errormsg="链接地址格式错误！" maxlength="100"/>
						</div>
					</div>
				</li>
                
                <li class="posi_lm">
					<div class="left posi_l ">排序:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="sort" value="0" />
							<small style="" class="help-block prompt_box"><i class="fa fa-times-circle-o"></i>请填写数字，越小越靠前！</small>
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