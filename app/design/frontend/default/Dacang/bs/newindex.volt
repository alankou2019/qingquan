<div class="warp" id="evaluation">
    {% if pointmofule %}
        <div class="menu_box">
	        <img src="/skin/frontend/default/bs/images/menu_list.png" alt="" class="menu_img" />
	        <ul class="menu_ul">
	            <li>
	                <a href="{{helper.createUrl(['p':'bs/newindex'])}}">KPI考评</a>
	            </li>
	            <li>
	                <a href="{{helper.createUrl(['p':'bspoint/newindex'])}}">积分考评</a>
	            </li>

	            {%  if bro != 'dding' %}
	            <li>
                	<a href="{{helper.createUrl(['p':'wp/logout?do_action=logout'])}}">退出</a>
                </li>
                {% endif %}

	        </ul>
	    </div>
    {% endif %}
    
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
		<div class="evaluation_con">
			<ul class="evaluation_ul clear"> 
				<li class="clear fl">
					<div class="list_con" onclick="window.location.href = '{{helper.createUrl(['p':'bs/index','type':3])}}'">
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
					<div class="list_con" onclick="window.location.href = '{{helper.createUrl(['p':'bs/index','type':1])}}'">
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
					<div class="list_con" onclick="window.location.href = '{{helper.createUrl(['p':'bs/searchlist','searchuser':'self'])}}'">
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
					<div class="list_con" onclick="window.location.href = '{{helper.createUrl(['p':'bs/search'])}}'">
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
