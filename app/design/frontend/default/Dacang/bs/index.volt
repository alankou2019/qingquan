<style>
.hellostyle{
    width:3px;
    background-color: #f7f7f7;
    padding: 0.125rem 0;
    border: none;
    font-size: 0.8rem;
    color: #666;
    border-bottom: 2px solid #ccc;
    height: 2rem;
    vertical-align: middle;
    position: relative;
    transition: background-color .1s linear;
    text-align: center;
    white-space: nowrap;
    text-overflow: ellipsis;
    line-height: 38px;
    display: table-cell;
    overflow: hidden;
}
#score .score_list_ul li a {
    position: relative;
}
#score .score_list_ul .score_total {
    position: absolute;
    left: 66%;
    top: 40%;
    transform: translate(-50%, -50%);
    font-size: 0.68rem;
    line-height: 1;
    color: #f39800;
    white-space: nowrap;
    text-align: center;
}
#score .score_list_ul .score_submit_stat {
    position: absolute;
    left: 66%;
    top: 64%;
    transform: translate(-50%, -50%);
    font-size: 0.42rem;
    line-height: 1;
    color: #999;
    white-space: nowrap;
    text-align: center;
}
</style>

<div class="warp" id="score">
    <div class="mui-content has_top" style="padding-top: 0;">
        <div id="slider" class="mui-slider">
            <div class="slider_top" style="position:relative;padding-top:-2.75rem;">
                <div id="sliderSegmentedControl" class="mui-segmented-control" style="width:100%;">
                	<a class="mui-control-item {% if type==3 %}mui-active{% endif %}" href="#item3mobile">被考核表</a>
                    <a class="mui-control-item {% if type==1 %}mui-active{% endif %}" href="#item1mobile">参与评分</a>
                    <a class="mui-control-item {% if type==2 %}mui-active{% endif %}" href="#item2mobile">已评分</a>
                    <span class="clear hellostyle" onclick="location.href='{{helper.createUrl(['p':'bs/search'])}}'">
                                        查询历史
                    </span>
                </div>
            </div>

            <div class="mui-slider-group">
                <div id="item1mobile" class="mui-control-content {% if type==1 %}mui-active{% endif %}" urlrequest='needpointlist'>
                    <ul class="score_list_ul">
                    	{% for item in needdatalist %}
                        <li>
                            <a href="{{helper.createUrl(['p':'bs/pointdetail','id':item.reportId,'uid':item.id,'state':item.reporttime])}}" class="clear">
                                <img src="{{item.avatar}}" class="header_img fl" alt="{{item.name}}" onerror="this.src='/favicon.ico'"/>
                                <div class="fl user_msg">
                                    <div class="name">
                                        {{item.name}} {{item.rname}} 
                                    </div>
                                    <div>
                                      	{{item.dname}}
                                       </div>
                                </div>
                                <div class="fr time">
                                    {{helper.formatDateTime(item.created,'Y.m.d')}}
                                </div>
                            </a>
                        </li>
                        {% endfor %}
                    </ul>
                </div>
                <div id="item2mobile" class="mui-control-content  {% if type==2 %}mui-active{% endif %}" urlrequest='haspointlist'>
                    <ul class="score_list_ul">
                    	{% for hasitem in hasdatalist %}
                    		<li>
                             <a href="{{helper.createUrl(['p':'bs/pointdetail','id':hasitem.reportId,'uid':hasitem.id,'state':1])}}" class="clear">
                                 <img src="{{hasitem.avatar}}" class="header_img fl" alt="{{hasitem.name}}" onerror="this.src='/favicon.ico'"/>
                                 <div class="fl user_msg">
                                     <div class="name">
                                      	  {{hasitem.name}} {{hasitem.rname}} 
                                     </div>
                                     <div>
                                            {{hasitem.dname}}
                                     </div>
                                 </div>
                                 <div class="score_total">总分 {{helper.del0(hasitem.totalpoint)}}</div>
                                 <div class="score_submit_stat">已提交分数{{hasitem.submitted_count}}人（共{{hasitem.total_count}}人）</div>
                                 <div class="fr time">
                                     {{helper.formatDateTime(hasitem.created,'Y.m.d')}}
                                 </div>
                             </a>
                         </li>
                    	{% endfor %}
                    </ul>
                </div>
                <div id="item3mobile" class="mui-control-content  {% if type==3 %}mui-active{% endif %}" urlrequest='reportinglist'>
                    <ul class="score_list_ul">
                    	{% for ingitem in reportinglist %}
                    		<li>
                             <a href="{{helper.createUrl(['p':'bs/reportingdetail','id':ingitem.reportId])}}" class="clear">
                                 <div class="fl user_msg">
                                     <div style="padding-top:6px;font-size: 0.8rem;color: black;font-weight:bold;">
                                      	  {{ingitem.rname}}
                                     </div>
                                 </div>
                                 <div class="fr time">
                                     {{helper.formatDateTime(ingitem.created,'Y.m.d')}}
                                 </div>
                             </a>
                         </li>
                    	{% endfor %}
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">

$('.mui-control-item').click(function(){
	page=2;
})
//上拉加载更多
page=2;
$(document).ready(function(){
	$(window).scroll(function(){
		//已经滚动到底部
		if($(document).scrollTop() >= $(document).height()-$(window).height()){
			var obj=$('div .mui-slider-group').find('.mui-active');
			var request_url=obj.attr('urlrequest');
			if(obj.find('.nodata').length>0){
				return false;
			}
			$.ajax({
                type: "POST",
                url: "{{helper.createUrl(['p':'bs/ajaxrequest'])}}",
                data: {'request_url':request_url,'page':page},
                dataType: "json",
                success: function(res){
                    if(res.status == 'y'){
                       if(res.data){
                    	   obj.find('ul').append(res.data);
                       }else{
                    	   obj.find('ul').append('<li class="nodata"></li>');
                       }
                    	  
                    }else{
                       tanceng(res.error) ;
                    }
                    
                    page++;
                }
           });
		}
	})
})
</script>
