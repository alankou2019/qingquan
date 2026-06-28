<script src="/skin/adminhtml/default/js/form.js"></script>
<!--滚动条-->
<script
	src="/skin/adminhtml/default/libs/nicescroll/jquery.nicescroll.js"></script>
<div class="full_box">
	<div class="full_title">

		<span style="float: right; cursor: pointer;"
			onclick="window.location=window.location;"><i
			class="iconfont icon-shuaxin"></i></span> <span class="name">系统设定</span> 

	</div>

	<div class="full_cont">
		<!--表单-->
		<form action="{{helper.createUrl(['p':'settings/config'])}}" method="post" class="form_full" id="dataForm" name="dataForm" enctype="multipart/form-data">
             <input type="hidden" name="_has_key" value="" />
            <div class="head_tab clear" style="margin-bottom:10px;">
				<ul id="tabNav" class="tabNav">
                  {% for configGroup in configGroups %}
					<li data-id="{{configGroup['id']}}"><a href="javascript:void(0)">{{configGroup['name']}}</a></li>
                  {% endfor %}
				</ul>
			</div>
			
             {% for configGroup in configGroups %}
            <ul class="list_form_full tabContent" style="display:none"  id="config_{{configGroup['id']}}">
            	{% for item in configGroup['items'] %}
				<li class="posi_lm">
					<div class="left posi_l" style="width:100px;">{{item.name}}:</div>
					<div class="right posi_m" style="padding-left:10px;">
						<div class="input_clear">
                        
                        	{%if item.type == 1%}
							<input type="text" name="{{item.code}}" value="{{item.value}}" />
                            {%elseif item.type ==2%}
                            <textarea name="{{item.code}}" rows="4" style="width:100%">{{item.value}}</textarea>
                            {%elseif item.type ==3%}
                            {% set tempValues = helper.explode(';',item.values)%}
                            {%for tempValueItem in tempValues%}
                            	{%set  valueItem = helper.explode('|',tempValueItem)%}
                            	<input type="radio"  name="{{item.code}}" value="{{valueItem[0]}}" {%if valueItem[0] == item.value%}checked="checked"{%endif%} >{{valueItem[1]}}
                            {%endfor%}	
                            {%elseif item.type ==4%}
                            <textarea name="{{item.code}}" rows="6" style="width:100%">{{item.value}}</textarea>
                             {%elseif item.type ==5%}
                             {% set tempValues = helper.explode(';',item.values)%}
                              <select name="{{item.code}}">
                              	<option value="">请选择..</option>
                             {%for tempValueItem in tempValues%}
                            	{%set  valueItem = helper.explode('|',tempValueItem)%}
                            	 <option {%if valueItem[0] == item.value%}selected="selected" {%endif%}  value="{{valueItem[0]}}">{{valueItem[1]}}</option>
                             {%endfor%}	
                             </select>
                            {%elseif item.type ==6%}
                           		 <input type="hidden" value="{{item.value}}" name="{{item.code}}" />
                                 <input type="file" name="{{item.code}}" />
                                 {%if item.value %}
                                 	<br />
                                    <a href="{{item.value}}" target="_blank"><img src="{{item.value}}" width="100" height="100" /></a>
                                 {%endif%}
                            {%endif%}
						</div>
                        {% if item.help %}
                        <div class="prompt_box">
							 {{item.help}}
						</div>
                        {% endif %}
					</div>
				</li>
                {% endfor %}
			</ul>
             {% endfor %}

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
		
		$("#tabNav li").click(function(){
			$(".tabContent").hide();
			var id = $(this).attr('data-id');
			$("#config_"+id).show();
			$("#tabNav li").removeClass('on');
			$(this).addClass('on');
		});
		$("#tabNav li").eq(0).trigger('click');
		
		{% if item %}
		var formObj = new Form('dataForm');
		formObj.init({{item|json_encode}});
		{% endif %}

	});
</script>