<script src="/skin/adminhtml/default/js/form.js"></script>
<script type="text/javascript" src="/skin/adminhtml/default/libs/laydate/laydate.js" ></script>
<!--滚动条-->
<script
	src="/skin/adminhtml/default/libs/nicescroll/jquery.nicescroll.js"></script>
<div class="full_box">
	<div class="full_title">

		<span style="float: right; cursor: pointer;"
			onclick="window.location=window.location;"><i
			class="iconfont icon-shuaxin"></i></span> <span class="name">{% if item %}编辑{% else %}新增{% endif %}公司</span> <a
			class="go_back" onclick="window.location='{{helper.createUrl(['p':'company/index'])}}';"> <i
			class="iconfont icon-fanhui"></i> <span>返回公司列表</span>
		</a>

	</div>

	<div class="full_cont">
		<!--表单-->
		<form action="{{helper.createUrl(['p':'company/save'])}}" method="post" class="form_full" id="dataForm" name="dataForm" enctype="multipart/form-data">
		     <input type="hidden" name="id" value="">
			<div class="sub_title">公司信息</div>
			<ul class="list_form_full">

				<li class="posi_lm">
					<div class="left posi_l must">公司名称:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="name"  datatype="*"  value="" errormsg="公司名称不能为空！" maxlength="64" autocomplete="off"/>
							<small style="" class="help-block prompt_box" ><i class="fa fa-times-circle-o"></i>请填写公司名称，请勿重复！</small>
						</div>
					</div>
				</li>
                
               {%if item %}
                  <li class="posi_lm">
                        <div class="left posi_l ">应用启用时间:</div>
                        <div class="right posi_m">
                            <div class="input_clear">
                                {{helper.formatDateTime(item.join_time)}}
                            </div>
                        </div>
                    </li>
                {% endif %}
                
                               {%if item %}
                  <li class="posi_lm">
                        <div class="left posi_l ">前台访问地址:</div>
                        <div class="right posi_m">
                            <div class="input_clear">
                                {{helper.createUrl(['p':'bs/newindex','m':'front','_f':'1'])}}/{{item.hash_key}}
                                <br />
                                <small style="" class="help-block prompt_box"><i class="fa fa-times-circle-o"></i>钉钉设置中的首页地址！</small>
                            </div>
                        </div>
                    </li>
                {% endif %}
                
                 {%if item %}
                  <li class="posi_lm">
                        <div class="left posi_l ">后台地址:</div>
                        <div class="right posi_m">
                            <div class="input_clear">
                                {{helper.createUrl(['p':'login/index','m':'front','_f':'1'])}}/{{item.hash_key}}
                                <br />
                                <small style="" class="help-block prompt_box"><i class="fa fa-times-circle-o"></i>钉钉设置中的后台地址！</small>
                            </div>
                        </div>
                    </li>
                {% endif %}
                
                  <li class="posi_lm">
                        <div class="left posi_l ">所属行业:</div>
                        <div class="right posi_m">
                            <div class="input_clear">
                                <input type="text" name="industry"  maxlength="16" autocomplete="off"/>
                            </div>
                        </div>
                    </li>
                    
                 <li class="posi_lm">
					<div class="left posi_l ">appKey:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="corpid"    value=""  maxlength="80" autocomplete="off"/>
                            <small style="" class="help-block prompt_box"><i class="fa fa-times-circle-o"></i>企业应用->应用凭证/->AppKey</small>
						</div>
					</div>
				</li>
                
                 <li class="posi_lm">
					<div class="left posi_l ">appSecret:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="corpsecret"    value=""  maxlength="80" autocomplete="off"/>
                            <small style="" class="help-block prompt_box"><i class="fa fa-times-circle-o"></i>企业应用->应用凭证->AppSecret</small>
						</div>
					</div>
				</li>
                                 <li class="posi_lm">
					<div class="left posi_l ">SSOsecret:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="ssosecret"  value=""  maxlength="80" autocomplete="off"/>
                            <small style="" class="help-block prompt_box"><i class="fa fa-times-circle-o"></i>企业应用->工作台设置->SSOsecret</small>
						</div>
					</div>
				</li>
				
				<li class="posi_lm">
					<div class="left posi_l ">agentid:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="agentid"    value=""  maxlength="80" autocomplete="off"/>
                            <small style="" class="help-block prompt_box"><i class="fa fa-times-circle-o"></i>企业应用->应用凭证->AgentId</small>
						</div>
					</div>
				</li>
				
				<li class="posi_lm">
					<div class="left posi_l ">联系人:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="contact" ignore="ignore"  datatype="*"  value="" errormsg="联系人不能为空！" maxlength="16" autocomplete="off"/>
						</div>
					</div>
				</li>
				
				<li class="posi_lm">
					<div class="left posi_l">联系电话:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="phone"  ignore="ignore" datatype="*"  value="" errormsg="联系电话不能为空！" autocomplete="off"/>
							<small style="" class="help-block prompt_box"><i class="fa fa-times-circle-o"></i>请填写11位手机号码！</small>
						</div>
					</div>
				</li>
                
               <li class="posi_lm">
					<div class="left posi_l">公司地址:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="address"  ignore="ignore" datatype="*"  value="" errormsg="公司地址不能为空！" autocomplete="off"/>
						</div>
					</div>
				</li>
				
				<li class="posi_lm">
					<div class="left posi_l">状态:</div>
					<div class="right">
						<div class="txt">
						<input type="radio" name="status" checked="checked" value="0">未激活&nbsp;&nbsp;
                        <input type="radio" name="status" value="1">试用期&nbsp;&nbsp;
                        <input type="radio" name="status" value="2">正常&nbsp;&nbsp;
						</div>
					</div>
				</li>
				
				<li class="posi_lm">
                    <div class="left posi_l">积分考核:</div>
                    <div class="right">
                        <div class="txt">
                        <input type="radio" name="pointstatus" checked="checked" value="0">不启用
                        <input type="radio" name="pointstatus" value="1">启用&nbsp;&nbsp;
                        </div>
                    </div>
                </li>
                
                <li class="posi_lm">

					<div class="left posi_l">过期时间:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="expire_time" value=""  class="laydate-icon dateinput"  readonly="readonly"/>
						</div>
					</div>
				</li>
				
				<li class="posi_lm">
					<div class="left posi_l">人数限制:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="personlimit"   datatype="*"  value="" errormsg="人数限制不能为空！" autocomplete="off"/>
						</div>
					</div>
				</li>
				<li class="posi_lm">
					<div class="left posi_l">单人考评表数目限制:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="reportlimit"   datatype="*"  value="" errormsg="单人考评表数目限制不能唯恐！" autocomplete="off"/>
						</div>
					</div>
				</li>
				<li class="posi_lm">
					<div class="left posi_l">考评表模版数限制:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="reporttpllimit"  datatype="*"  value="" errormsg="考评表模版数限制不能为空！" autocomplete="off"/>
						</div>
					</div>
				</li>
				<li class="posi_lm">

					<div class="left posi_l ">corpId:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<textarea name="remark"></textarea>
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
		//单选框美化
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
