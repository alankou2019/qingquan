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
</style>

<div class="warp" id="score">
    <div class="mui-content has_top" style="padding-top: 0;">
        <div id="slider" class="mui-slider">
            <div class="slider_top" style="position:relative;padding-top:-2.75rem;">
                <div id="sliderSegmentedControl" class="mui-segmented-control" style="width:100%;">
                	<a class="mui-control-item" href="#item1mobile" position="1">待评分</a>
                	{% if is_check_user %}
                	<a class="mui-control-item" href="#item2mobile" position="2">待审核</a>
                	{% endif %}
                    <a class="mui-control-item" href="#item3mobile" position="3">被考核表</a>
                    <span class="clear hellostyle" onclick="location.href='{{helper.createUrl(['p':'bspoint/search'])}}'">
                                        查询历史
                    </span>
                </div>
            </div>

            <div class="mui-slider-group">
                <div id="item1mobile" class="mui-control-content {% if type==1 %}mui-active{% endif %}" urlrequest='needpointlist'>
                    <ul class="score_list_ul">
                    	{% for item in needdatalist %}
                        <li>
                            <a href="{{helper.createUrl(['p':'bspoint/pointdetail','id':item.reportId,'uid':item.id])}}" class="clear">
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
                
                {% if is_check_user %}
                <div id="item2mobile" class="mui-control-content  {% if type==2 %}mui-active{% endif %}" urlrequest='haspointlist'>
                    <ul class="score_list_ul">
                        {% for checkitem in checkingList %}
                            <li>
                             <a href="{{helper.createUrl(['p':'bspoint/checkdetail','id':checkitem['id']])}}" class="clear">
                                 <img src="{{hasitem.avatar}}" class="header_img fl" alt="{{hasitem.name}}" onerror="this.src='/favicon.ico'"/>
                                 <div class="fl user_msg">
                                     <div class="name">
                                          {{checkitem['name']}} 
                                     </div>
                                     <div>
                                        {{checkitem['dname']}}
                                       </div>
                                 </div>
                                 <div class="fr time">
                                     {{helper.formatDateTime(checkitem['created'],'Y.m.d')}}
                                 </div>
                             </a>
                         </li>
                        {% endfor %}
                    </ul>
                </div>
                {% endif %}
                <div id="item3mobile" class="mui-control-content  {% if type==3 %}mui-active{% endif %}" urlrequest='reportinglist'>
                    <ul class="score_list_ul">
                    	{% for ingitem in reportinglist %}
                    		<li>
                             <a href="{{helper.createUrl(['p':'bspoint/reportingdetail','id':ingitem.reportId])}}" class="clear">
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
	var position=$(this).attr('position');
	document.cookie="currentdiv="+position;
	page=2;
})
//上拉加载更多
page=2;
$(document).ready(function(){
	//先获取cookie的值   判断当前应该显示的div
	var position=document.cookie.split(";")[0].split('=')[1];
	if(!position){
	    position=1;
	}
	position=position-1;
	$('.mui-control-item').eq(position).addClass('mui-active');
	
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
                url: "{{helper.createUrl(['p':'bspoint/ajaxrequest'])}}",
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


