	
<link rel="stylesheet" href="/skin/frontend/default/bs/lib/iscroll/css/style.css">
<script type="text/javascript" src="/skin/frontend/default/bs/lib/iscroll/js/iscroll.js" ></script>
<div class="warp" id="score">
    <div class="mui-content has_top" style='padding-top: 0;'>
        <div id="slider" class="mui-slider">
            <div class="mui-slider-group">
                <div id="item1mobile" class="mui-control-content mui-active scroller">
                {% if dataList.datanum >= 1 %}
                    <ul class="score_list_ul">
                   		{% for item in dataList.items %}
                        <li>
                            <a href="{{helper.createUrl(['p':'bspoint/storesdetail','id':item.reportId,'uid':item.id,'sid':item.sid])}}" class="clear">
                                <img src="{{item.avatar}}" class="header_img fl" alt="{{item.name}}" onerror="this.src='/favicon.ico'"/>
                                <div class="fl user_msg">
                                    <div class="name">
                                        {{item.name}}
                                    </div>  
                                    <div>
                                      	{{item.dname}}
                                    </div>
                                </div>
                                <div class="fr time">
                                    {{helper.formatDateTime(item.createdtime,'Y.m.d')}}
                                </div>
                            </a>
                        </li>
                        {% endfor %}
                    </ul>
                    <div class="more"><i class="pull_icon"></i><span>上拉加载...</span></div>
                {% else %}
	            	<div style="text-align: center;">
	            		暂无数据…… 
	            	</div>
               {% endif %}	
                </div>
            </div>
        </div>
    </div>
</div>

<script>
	page = 1 ;
	pagesize = {{dataList.pagesize}} ;
	var myscroll = new iScroll("score",{
		onScrollMove:function(){
			if (this.y<(this.maxScrollY)) {
				$('.pull_icon').addClass('flip');
				$('.pull_icon').removeClass('loading');
				$('.more span').text('释放加载...');
			}else{
				$('.pull_icon').removeClass('flip loading');
				$('.more span').text('上拉加载...')
			}
		},
		onScrollEnd:function(){
			if ($('.pull_icon').hasClass('flip')) {
				$('.pull_icon').addClass('loading');
				$('.more span').text('加载中...');
				pullUpAction();
			}
		},
		onRefresh:function(){
			$('.more i').removeClass('flip');
			$('.more i').removeClass('loading');
			$('.more span').text('上拉加载...');
		}
		
	});
	
	function pullUpAction(){
		setTimeout(function(){
			var data = {{dataList.filter}} ;
			data['page'] = page ;
			$.ajax({
				type: "POST",
				url:"{{helper.createUrl(['p':'bspoint/searchlist'])}}",
				data: data,
				dataType:'json',
				success:function(data){
					var res = eval(data);
		   			if(res.status == 'y'){
		   				if(res.data.datanum < pagesize){
		   					$('.more').hide();
		   					myscroll.destroy();
		   				}
		   				$('.scroller ul').append(res.data.content);
		   			 }else{
		   				console.log(res.error) ;
		   			 }
					myscroll.refresh();
				},
				error:function(){
					console.log('error');
				},
			})
			myscroll.refresh();
		}, 100) ;
		page ++  ;
	}
</script>
