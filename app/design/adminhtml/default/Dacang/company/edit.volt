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
					<div class="left posi_l ">通讯平台:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<select name="app_platform" class="screen">
								<option value="dingding">钉钉</option>
								<option value="wecom">企业微信</option>
								<option value="feishu">飞书</option>
								<option value="manual">手工/Excel</option>
							</select>
                            <small style="" class="help-block prompt_box"><i class="fa fa-times-circle-o"></i>用于企业后台员工同步、员工手机端登录方式和后续消息通知。</small>
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

			<div class="sub_title">模块授权</div>
			<style>
				.module_auth_wrap{padding:10px 18px 0 18px;}
				.module_auth_grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
				.module_auth_card{border:1px solid #d9e2ef;background:#fbfdff;padding:12px;}
				.module_auth_card.open{border-color:#b9d8f6;background:#f8fbff;}
				.module_auth_head{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;}
				.module_auth_name{font-size:14px;font-weight:bold;color:#233142;}
				.module_auth_code{font-size:12px;color:#7b8794;margin-left:6px;font-weight:normal;}
				.module_auth_note{color:#64748b;line-height:22px;margin-bottom:8px;}
				.module_auth_features{border-top:1px dashed #d6dee9;padding-top:8px;display:grid;grid-template-columns:1fr 1fr;gap:6px 10px;}
				.module_auth_features label,.module_auth_head label{color:#334155;line-height:24px;}
				.module_auth_tips{margin:12px 0 0;color:#64748b;line-height:22px;}
			</style>
			<div class="module_auth_wrap">
				<div class="module_auth_grid">
					{% for module in moduleViewList %}
					<div class="module_auth_card {% if module['enabled'] %}open{% endif %}">
						<div class="module_auth_head">
							<div class="module_auth_name">{{module['name']}}<span class="module_auth_code">{{module['code']}}</span></div>
							<label>
								<input type="checkbox" name="module_auth[{{module['code']}}][_module]" value="1" {% if module['enabled'] %}checked="checked"{% endif %} {% if module['readonly'] %}disabled="disabled"{% endif %}/>
								{% if module['readonly'] %}原有模块{% else %}启用{% endif %}
							</label>
						</div>
						<div class="module_auth_note">{{module['note']}}</div>
						<div class="module_auth_features">
							{% for feature in module['features'] %}
							<label>
								<input type="checkbox" name="module_auth[{{module['code']}}][{{feature['code']}}]" value="1" {% if feature['enabled'] %}checked="checked"{% endif %} {% if module['readonly'] %}disabled="disabled"{% endif %}/>
								{{feature['name']}}
							</label>
							{% endfor %}
						</div>
					</div>
					{% endfor %}
				</div>
				<div class="module_auth_tips">
					说明：未开通模块时，企业管理后台不显示对应入口；关闭模块只隐藏入口，不删除历史业务数据。绩效考核为原有在用模块，本次只展示保留状态，不提供关闭操作。
				</div>
			</div>

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
