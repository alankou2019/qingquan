<script src="/skin/adminhtml/default/js/form.js"></script>
<script type="text/javascript" src="/skin/adminhtml/default/libs/laydate/laydate.js" ></script>
<style>
.miniapp-summary { margin: 10px 0 18px; padding: 14px 18px; border: 1px solid #cddcff; background: #f5f8ff; color: #34527a; line-height: 26px; }
.miniapp-modules { display: flex; flex-wrap: wrap; gap: 12px; max-width: 820px; }
.miniapp-module { width: 220px; padding: 14px 16px; border: 1px solid #d7e1f2; background: #fff; }
.miniapp-module strong { display: block; margin-bottom: 5px; color: #183153; }
.miniapp-module small { color: #718096; }
</style>
<!--滚动条-->
<script
	src="/skin/adminhtml/default/libs/nicescroll/jquery.nicescroll.js"></script>
<div class="full_box">
	<div class="full_title">

		<span style="float: right; cursor: pointer;"
			onclick="window.location=window.location;"><i
			class="iconfont icon-shuaxin"></i></span> <span class="name">{% if item %}编辑公司{% elseif platform == 'wecom' %}新增企业微信公司{% elseif platform == 'miniapp' %}新增小程序客户{% else %}新增钉钉公司{% endif %}</span> <a
			class="go_back" onclick="window.location='{{helper.createUrl(['p':'company/index'])}}';"> <i
			class="iconfont icon-fanhui"></i> <span>返回公司列表</span>
		</a>

	</div>

	<div class="full_cont">
		<!--表单-->
		<form action="{{helper.createUrl(['p':'company/save'])}}" method="post" class="form_full" id="dataForm" name="dataForm" enctype="multipart/form-data">
		     <input type="hidden" name="id" value="">
		     <input type="hidden" name="platform" value="{{platform}}">
		     <input type="hidden" name="application_id" value="{% if registrationApplication %}{{registrationApplication.id}}{% endif %}">
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
                
                               {% if item and platform == 'dingding' %}
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
                
                 {% if item and platform == 'dingding' %}
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

				{% if platform == 'wecom' and not item %}
				<li class="posi_lm"><div class="left posi_l"></div><div class="right posi_m"><strong style="font-size:18px;">企业微信自建应用参数</strong></div></li>
				<li class="posi_lm"><div class="left posi_l must">CorpID:</div><div class="right posi_m"><div class="input_clear"><input type="text" name="wecom_corp_id" datatype="*" maxlength="128" autocomplete="off"/><small class="help-block prompt_box">企业微信管理后台 → 我的企业 → 企业ID</small></div></div></li>
				<li class="posi_lm"><div class="left posi_l must">AgentID:</div><div class="right posi_m"><div class="input_clear"><input type="text" name="wecom_agent_id" datatype="*" maxlength="64" autocomplete="off"/><small class="help-block prompt_box">企业微信自建应用详情页中的AgentId</small></div></div></li>
				<li class="posi_lm"><div class="left posi_l must">Secret:</div><div class="right posi_m"><div class="input_clear"><input type="password" name="wecom_secret" datatype="*" maxlength="255" autocomplete="new-password"/><small class="help-block prompt_box">企业微信自建应用的Secret，将加密保存</small></div></div></li>
				<li class="posi_lm"><div class="left posi_l">回调Token:</div><div class="right posi_m"><div class="input_clear"><input type="text" name="wecom_callback_token" maxlength="255" autocomplete="off"/></div></div></li>
				<li class="posi_lm"><div class="left posi_l">EncodingAESKey:</div><div class="right posi_m"><div class="input_clear"><input type="text" name="wecom_encoding_aes_key" maxlength="255" autocomplete="off"/></div></div></li>
				{% endif %}
                    
				{% if platform == 'dingding' %}
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
				{% else %}
				<input type="hidden" name="corpid" value="">
				<input type="hidden" name="corpsecret" value="">
				<input type="hidden" name="ssosecret" value="">
				<input type="hidden" name="agentid" value="0">
				{% endif %}
				{% if platform == 'miniapp' %}
				<div class="miniapp-summary">总管理员手机号将作为企业小程序登录账号。新企业自动生成企业字母编码，总管理员工号为“企业编码001”，初始密码为 dc123456。</div>
				{% if item %}
				<li class="posi_lm"><div class="left posi_l">企业编码:</div><div class="right posi_m"><div class="input_clear"><input type="text" value="{{item.company_code}}" readonly="readonly"/></div></div></li>
				{% endif %}
				<li class="posi_lm"><div class="left posi_l must">总管理员姓名:</div><div class="right posi_m"><div class="input_clear"><input type="text" name="miniapp_admin_name" datatype="*" value="{% if item %}{{item.miniapp_admin_name}}{% endif %}" maxlength="64" autocomplete="off"/></div></div></li>
				<li class="posi_lm"><div class="left posi_l must">总管理员手机号:</div><div class="right posi_m"><div class="input_clear"><input type="text" name="miniapp_admin_mobile" datatype="m" value="{% if item %}{{item.miniapp_admin_mobile}}{% endif %}" maxlength="11" autocomplete="off"/><small class="help-block prompt_box">请填写11位手机号，手机号不能绑定其他企业。</small></div></div></li>
				<input type="hidden" name="contact" value="">
				<input type="hidden" name="phone" value="">
				{% else %}
				<li class="posi_lm"><div class="left posi_l">联系人:</div><div class="right posi_m"><div class="input_clear"><input type="text" name="contact" ignore="ignore" datatype="*" value="" errormsg="联系人不能为空！" maxlength="16" autocomplete="off"/></div></div></li>
				<li class="posi_lm"><div class="left posi_l">联系电话:</div><div class="right posi_m"><div class="input_clear"><input type="text" name="phone" ignore="ignore" datatype="*" value="" errormsg="联系电话不能为空！" autocomplete="off"/><small class="help-block prompt_box">请填写11位手机号码！</small></div></div></li>
				{% endif %}
                
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
						<input type="radio" name="status" {% if platform != 'miniapp' %}checked="checked"{% endif %} value="0">未激活&nbsp;&nbsp;
                        <input type="radio" name="status" {% if platform == 'miniapp' %}checked="checked"{% endif %} value="1">试用期&nbsp;&nbsp;
                        <input type="radio" name="status" value="2">正常&nbsp;&nbsp;
						</div>
					</div>
				</li>
				{% if platform == 'miniapp' %}
				<li class="posi_lm">
					<div class="left posi_l">开放模块:</div>
					<div class="right posi_m">
						<div class="miniapp-modules">
							<label class="miniapp-module"><input type="checkbox" name="miniapp_modules[]" value="salary" {% if 'salary' in miniappModules %}checked="checked"{% endif %}><strong>薪酬管理</strong><small>工资项目、核算、工资条和归档</small></label>
							<label class="miniapp-module"><input type="checkbox" name="miniapp_modules[]" value="points" {% if 'points' in miniappModules %}checked="checked"{% endif %}><strong>积分考核</strong><small>规则、加扣分、审批、排行和申诉</small></label>
							<label class="miniapp-module"><input type="checkbox" name="miniapp_modules[]" value="kpi" {% if 'kpi' in miniappModules %}checked="checked"{% endif %}><strong>KPI考核</strong><small>沿用旧系统KPI考核表和评分结果</small></label>
							<label class="miniapp-module"><input type="checkbox" name="miniapp_modules[]" value="commission" {% if 'commission' in miniappModules %}checked="checked"{% endif %}><strong>提成管理</strong><small>提成规则、核算和归档记录</small></label>
							<label class="miniapp-module"><input type="checkbox" name="miniapp_modules[]" value="promotion" {% if 'promotion' in miniappModules %}checked="checked"{% endif %}><strong>晋升管理</strong><small>晋升规则和员工晋升记录</small></label>
							<label class="miniapp-module"><input type="checkbox" name="miniapp_modules[]" value="annual" {% if 'annual' in miniappModules %}checked="checked"{% endif %}><strong>年度考核（预留）</strong><small>仅保存开通状态，暂不显示入口</small></label>
							<label class="miniapp-module"><input type="checkbox" name="miniapp_modules[]" value="training" {% if 'training' in miniappModules %}checked="checked"{% endif %}><strong>培训管理（预留）</strong><small>仅保存开通状态，暂不显示入口</small></label>
						</div>
					</div>
				</li>
				{% else %}
				<li class="posi_lm">
                    <div class="left posi_l">积分考核:</div>
                    <div class="right"><div class="txt"><input type="radio" name="pointstatus" checked="checked" value="0">不启用 <input type="radio" name="pointstatus" value="1">启用</div></div>
                </li>
				{% endif %}
                
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
							<input type="text" name="personlimit" datatype="*" value="{% if platform == 'miniapp' and not item %}20{% endif %}" errormsg="人数限制不能为空！" autocomplete="off"/>
						</div>
					</div>
				</li>
				{% if platform != 'miniapp' %}
				<li class="posi_lm"><div class="left posi_l">单人考评表数目限制:</div><div class="right posi_m"><div class="input_clear"><input type="text" name="reportlimit" datatype="*" value="" errormsg="单人考评表数目限制不能为空！" autocomplete="off"/></div></div></li>
				<li class="posi_lm"><div class="left posi_l">考评表模版数限制:</div><div class="right posi_m"><div class="input_clear"><input type="text" name="reporttpllimit" datatype="*" value="" errormsg="考评表模版数限制不能为空！" autocomplete="off"/></div></div></li>
				{% else %}
				<input type="hidden" name="reportlimit" value="0">
				<input type="hidden" name="reporttpllimit" value="0">
				{% endif %}
				<li class="posi_lm">

					<div class="left posi_l ">备注:</div>
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

		{% if applicationDraft %}
		var applicationFormObj = new Form('dataForm');
		applicationFormObj.init({{applicationDraft|json_encode}});
		{% endif %}
	});
</script>
