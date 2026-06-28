<script src="/skin/adminhtml/default/js/form.js"></script>
<!--滚动条-->
<script
	src="/skin/adminhtml/default/libs/nicescroll/jquery.nicescroll.js"></script>
<script src="/skin/common/js/linkagesel/linkagesel-min.js"></script>
<div class="full_box">
	<div class="full_title">

		<span style="float: right; cursor: pointer;"
			onclick="window.location=window.location;"><i
			class="iconfont icon-shuaxin"></i></span> <span class="name">{% if item %}编辑{% else %}新增{% endif %}地区</span> <a
			class="go_back" onclick="window.location='{{helper.createUrl(['p':'adminuser/index'])}}';"> <i
			class="iconfont icon-fanhui"></i> <span>返回地区列表</span>
		</a>

	</div>

	<div class="full_cont">
		<!--表单-->
		<form action="{{helper.createUrl(['p':'area/save'])}}" method="post" class="form_full" id="dataForm" name="dataForm" enctype="multipart/form-data">
		     <input type="hidden" name="id" value="">
             <input type="hidden" name="_has_key" value="" />
             <input type="hidden" name="path" value=""  id="path" />
             <input type="hidden" name="parent_id" value=""/>
             
			<div class="sub_title">地区基本信息</div>
			<ul class="list_form_full">
            
				<li class="posi_lm">
					<div class="left posi_l must">地区名称:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="name"  datatype="*2-16"  value="" errormsg="地区名称至少2个字符,最多16个字符！" maxlength="16"/>
						</div>
					</div>
				</li>
                <li class="posi_lm">
					<div class="left posi_l">上级地区:</div>
					<div class="right posi_m">
						<div class="input_clear">
                         {% if item == null %}
						<select id="area_parent_id" name="" style="width:140px;border: 1px solid #ddd; padding: 6px 0; " >
							
						</select>
                        {% else%}
                        	<div style="line-height:30px;">{{item.parent_name}}</div>
                        {% endif %}
						</div>
                       
					</div>
				</li>
                
				<li class="posi_lm">
					<div class="left posi_l">所属大区:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="region" maxlength="16" />
						</div>
					</div> 
                    <div  class="prompt_box">
    							默认只有省级地区才需要填写大区域，目前全国几大区域有：华北、东北、华东、华南、华中、西南、西北、港澳台、海外。
						</div>
				</li>

				<li class="posi_lm">
					<div class="left posi_l">拼音:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="pinyin"  maxlength="16"/>
						</div>
                          <div class="prompt_box">
							不填写系统将自动生成
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
		{% if item %}
		var formObj = new Form('dataForm');
		formObj.init({{item|json_encode}});
		{% endif %}
	
	    {% if item == null %}
		 var opts = {
            ajax: "{{helper.createUrl(['p':'index/area'])}}",
            selStyle: "width:140px;border: 1px solid #ddd; padding: 6px 0;margin-left:5px;",
			level:3,
			autoLink:false,
			defVal:[{{path}}],
			head:"请选择地区",
            select: "#area_parent_id",
			loaderImg:"/skin/common/js/linkagesel/ui-anim_basic_16x16.gif"
   		 }; 
         var linkageSel = new LinkageSel(opts);
		 linkageSel.onChange(function() {
              var path = linkageSel.getSelectedArr().join("_");
			  $("#path").val(path);
         });
	    {% endif %}
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

		

	});
</script>