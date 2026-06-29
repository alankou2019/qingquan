<div class="warp" id="evaluation">
	<style>
		.mobile_module_panel{margin:0.5rem 0.6rem 0.65rem;background:#fff;border-radius:0.35rem;box-shadow:0 0.08rem 0.24rem rgba(15,23,42,0.08);overflow:hidden;}
		.mobile_module_panel .panel_title{height:1.75rem;line-height:1.75rem;padding:0 0.7rem;border-bottom:1px solid #edf0f5;color:#111827;font-size:0.68rem;font-weight:bold;}
		.mobile_module_panel .module_tile{display:flex;align-items:center;padding:0.78rem 0.7rem;}
		.mobile_module_panel .module_icon{width:1.85rem;height:1.85rem;border-radius:50%;line-height:1.85rem;text-align:center;color:#fff;font-size:0.72rem;font-weight:bold;background:#19b59b;margin-right:0.62rem;flex:0 0 1.85rem;}
		.mobile_module_panel .module_copy{flex:1;min-width:0;}
		.mobile_module_panel .module_copy .title{font-size:0.72rem;color:#111827;line-height:1.05rem;}
		.mobile_module_panel .module_copy .txt{font-size:0.55rem;color:#8a94a6;line-height:0.82rem;margin-top:0.08rem;}
		.mobile_module_panel .module_arrow{font-size:0.78rem;color:#b7bfcc;padding-left:0.25rem;}
	</style>
    <div class="menu_box">
        <img src="/skin/frontend/default/bs/images/menu_list.png" alt="" class="menu_img" />
        <ul class="menu_ul">
            <li>
                <a href="{{helper.createUrl(['p':'bs/newindex'])}}">KPI考评</a>
            </li>
            <li>
                <a href="{{helper.createUrl(['p':'bspoint/newindex'])}}">积分考评</a>
            </li>
            {% browser != 'dding'%}
            <li>
                <a href="{{helper.createUrl(['p':'wp/logout?do_action=logout'])}}">退出</a>
            </li>
            {% endif %}
        </ul>
    </div>
  	<div class="mui-content">
		<div class="evaluation_top">
			<div class="header_box">
				<img src="{{userinfo.avatar}}" class="header_img" alt=""  onerror="this.src='/favicon.ico'"/>
				<div class="name">
					{{userinfo.name}}
				</div>
                {%if userinfo.dname %}
				<div class="txt">
					部门:{{userinfo.dname}}
				</div>{%endif%}
			</div>
		</div>
		<ul class="screen_ul">
			<li class="clear">
				<div class="fl left">
					<img src="/skin/frontend/default/bs/images/icon_company.png" alt="" />
					<span>考核模式</span>
				</div>
				<div class="fr right">
					{% if controller_name=='bs' %}
                        <span>KPI</span>
                    {% else %}
                        <span>积分考评</span>
                    {% endif %}
				</div>
			</li>
		</ul>
		{% if hasSalaryMobile %}
		<div class="mobile_module_panel">
			<div class="panel_title">员工服务</div>
			<div class="module_tile" onclick="window.location.href = '{{helper.createUrl(['p':'bs/salary'])}}'">
				<div class="module_icon">薪</div>
				<div class="module_copy">
					<div class="title">薪酬查询</div>
					<div class="txt">当月工资条、当年薪酬、往年薪酬</div>
				</div>
				<div class="module_arrow">&gt;</div>
			</div>
		</div>
		{% endif %}
		<div class="evaluation_con">
			<ul class="evaluation_ul clear"> 
				<li class="clear fl">
					<div class="list_con" onclick="window.location.href = '{{helper.createUrl(['p':'bspoint/index','type':3])}}'">
						<img src="/skin/frontend/default/bs/images/icon_1.png" class="fl" alt="" />
						<div class="fl text_box">
							<div class="title">
								看分
							</div>
							{% if pointingnum > 0 %}
								<div class="txt">
									您有 <span class="fc_4da9ec">{{pointingnum}}</span> 张被考评表没有完成评分
								</div>
							{% else %}
								<div class="txt">
									查看自己正在<div width="10px;"></div>进行的考核表
								</div>
							{% endif %}
							
						</div>
					</div>
				</li>
				<li class="clear fl">
					<div class="list_con" onclick="window.location.href = '{{helper.createUrl(['p':'bspoint/index','type':1])}}'">
						<img src="/skin/frontend/default/bs/images/icon_2.png" class="fl" alt="" />
						<div class="fl text_box">
							<div class="title">
								评分
							</div>
							{% if nopointnum > 0 %}
								<div class="txt">
									您还有 <span class="fc_4da9ec">{{nopointnum}}</span> 张表<div width="10px;"></div>没有完成评分
								</div>
							{% else %}
								<div class="txt">
									需要评分考评表
								</div>
							{% endif %}
						</div>
					</div>
				</li>
				<li class="clear fl">
					<div class="list_con" onclick="window.location.href = '{{helper.createUrl(['p':'bspoint/searchlist','searchuser':'self'])}}'">
						<img src="/skin/frontend/default/bs/images/icon_3.png" class="fl" alt="" />
						<div class="fl text_box">
							<div class="title">
								查看
							</div>
							<div class="txt">
								查看历史成绩
							</div>
						</div>
					</div>
				</li>
				<li class="clear fl">
					<div class="list_con" onclick="window.location.href = '{{helper.createUrl(['p':'bspoint/search'])}}'">
						<img src="/skin/frontend/default/bs/images/icon_4.png" class="fl" alt="" />
						<div class="fl text_box">
							<div class="title">
								查询
							</div>
							<div class="txt">
								有查看权限的查看他人历史成绩
							</div>
						</div>
					</div>
				</li>
			</ul>
		</div>
	</div>
</div>

