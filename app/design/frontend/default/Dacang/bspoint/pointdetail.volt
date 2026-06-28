<style>
<!--
.totalpoint
{
    float: right;
    position: absolute;
    margin-left: 76%;
    margin-top: -6%;
}
-->
</style>
<div class="warp" id="score_details">
	<form id="pointfrom">
		<!--报表id -->
		<input type="hidden" name="reportId" value="{{reportinfo.id}}"/>
		<!--被考核人id -->
		<input type="hidden" name="uid" value="{{userinfo.id}}"/>
	  	<div class="mui-content has_footer">
			<div class="score_details_list score_details_list100">
				<div class="title">
					{{reportinfo.name}}
				</div>
				<ul class="score_details_list_ul clear">
					<li class="fl clear list_con">
						<img src="{{userinfo.avatar}}" class="header_img fl" alt="{{userinfo.name}}" onerror="this.src='/favicon.ico'"/>
						<div class="fl">
							<div class="txt_top">
								被考核人
							</div>
							<div class="txt_bottom">
								<span>{{userinfo.name}}</span>
								<span>{{userinfo.dname}}</span>
								<span class="totalpoint">{{totalpoint}}</span>
							</div>
						</div>
					</li>
				</ul>
			</div>
			<div class="score_details_list100 score_details_list">
				<div class="title">
					考核指标
				</div>
				<p>点击指标查看评分标准</p>
				<ul class="score_details_list_ul clear">
					<!--循环需要得分的指标 -->
					{% if details %}
						{% for item in details %}
							<li class="fl clear">
								<div class="fl left_tit" onclick="tancengone(this)" data-txt ="{{item.point_desc}}";>
									{{item.qname}} 
									<!--每一项指标的指标id -->
									<input type="hidden" name="quotaids[]" value="{{item.quota_id}}"/>
									<p>{{helper.substr(item.point_desc,0,10)}}</p>
								</div>

								<span class="jifenjilu">积分：{{helper.del0(item.report_point)}}</span>
								<div class="fr fen">
                                   <button class="inputfen"  type="button"  onclick="javascript:comment_quota_show(this)" quota_id='{{item.quota_id}}'  
                                        {% if item.type == 1 %} max="200"  defaultTishi="100分制"
	                                        {% elseif item.type==2 %} max="20" defaultTishi="10分制"
	                                        {% elseif item.type==3 %} max="{{item.quota_value}}" defaultTishi="满分 {{helper.del0(item.quota_value)}}"
	                                        {% elseif item.type==4 %} defaultTishi="加减分"
	                                        {% elseif item.type==5 %} max="10" defaultTishi="5分制"
                                        {% endif %}/>
                                   评分</button>
                                </div>
							</li>
						{% endfor %}
					{% endif %}
				</ul>
			</div>
		</div>
		
	</form>
	
	
    <div class="layer_box comment_quota_div">
        <div class="layer_con has_btn">
            <form action="" id="comment_quota_form">
                <input type="hidden" name="quota_id"> 
                <input type="hidden" name="report_id"> 
                
            <div>
                <span class="bspointspanwidth">提交评分：</span>
                <input type="number" name="point" value="" class='inputfen pointinput inputclassone fr'>
            </div>
            <div>
                <span class="bspointspanwidth">积分缘由：</span>
                <textarea name="reason" id="quotacomment" cols="30" rows="10" class="bspointtextarea"></textarea>
            </div>
                <div class="clear sub_btn_box">
                    <button type="button" class="sub_btn fl" onclick="javascript:comment_quota()">提交</button>
                    <button type="button" class="sub_btn fr" onclick="javascript:get_quota_comment()">查看历史评分</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="layer_box  quota_comment_div">
        <div class="layer_con all_screen">
            <ul class="commit_ul quota_comment_ul">
                
            </ul>
        </div>
    </div>  
</div>

<script>
$(function(){
	$('.submitpoint').click(function(){
		//检查输入
		if(checkinput()){
			//ajax  提交数据
			$.ajax({
		   		 type: "POST",
		   		 url: "{{helper.createUrl(['p':'bspoint/setpoint'])}}",
		   		 data: $('#pointfrom').serialize(),
		   		 dataType: "json",
		   		 success: function(res){
		   			 if(res.status == 'y'){
		   				layer.open({
		   			         content: '评分成功，谢谢您的参与',
		   			         btn: '我知道了',
			   			     yes:function(index){
			   			    	layer.close(index);
			   			    	window.location.href = "{{helper.createUrl(['p':'bs/index','type':1])}}" ;
			   		        }
		   			    });
		   				
		   			 }else{
		   				tanceng(res.error) ;
		   			 }
		   		 }
	 	 	});
		}
	})
	
	//点击别处隐藏浮层
	$(".layer_box").click(function(){
		$(this).hide();
	})
	$(".layer_con").click(function(){
		return false;
	})
})


//检测输入值
function checkinput()
{	
	var len = $('.pointinput').length ;
	for(var i=0;i<len;i++){
		var max = $('.pointinput').eq(i).attr('max');
		var val = $('.pointinput').eq(i).val();
		
		if(val=='' || val==undefined){
			tanceng('请对指标进行评分');  return false ;
		}
		if(isNaN(val)){
			tanceng('请输入数字');  return false;
		}
		if(parseFloat(val) > parseFloat(max)){
			tanceng('评分有误，请从新输入') ;  return false;
		}
	}
	return true;
}

//点评框显示
function comment_quota_show(obj)
{
	var quotaId=$(obj).attr('quota_id');
	var max=$(obj).attr('max');
	var defaulttishi=$(obj).attr('defaulttishi');
	var reportId='{{reportinfo.id}}';
	
	//先清空上一次的值
	$('.comment_quota_div').find('[name="point"]').val('');
	$('.comment_quota_div').find('[name="reason"]').val('');
	
	//进行赋值
	$('.comment_quota_div').find('[name="quota_id"]').val(quotaId);
	$('.comment_quota_div').find('[name="report_id"]').val(reportId);
	$('.comment_quota_div').find('[name="point"]').attr('max',max);
	$('.comment_quota_div').find('[name="point"]').attr('placeholder',defaulttishi);
    $('.comment_quota_div').show();
}

//点评指标
function comment_quota()
{
	var point=$.trim($('.comment_quota_div').find('[name="point"]').val());
	var content=$.trim($('#quotacomment').val());
	if(!point){
		tanceng('请打分'); return false;
	}
	if(!content){
		tanceng('请输入评分理由'); return false;
	}
	
	$.ajax({
        type: "POST",
        url: "{{helper.createUrl(['p':'bspoint/savepoint'])}}",
        data: $('#comment_quota_form').serialize(),
        dataType: "json",
        success: function(res){
            if(res.status == 'y'){
               layer.open({
                    content: '评分成功，谢谢您的参与',
                    btn: '我知道了',
                    yes:function(index){
                       layer.close(index);
                       $('.comment_quota_div').hide();
                   }
               });
            }else{
               tanceng(res.error) ;
               $('.comment_quota_div').hide();
            }
        }
   });
    
}

page=1;
//获取此指标的点评
function get_quota_comment()
{
	var quota_id=$('.comment_quota_div').find('[name="quota_id"]').val();
	var report_id=$('.comment_quota_div').find('[name="report_id"]').val();
	if(!quota_id || !report_id){
		return false;
	}
	$('.comment_quota_div').hide();
	$.ajax({
        type: "POST",
        url: "{{helper.createUrl(['p':'bspoint/getitemdetail'])}}",
        data: {"quota_id":quota_id,"report_id":report_id,"page":page},
        dataType: "json",
        success: function(res){
            if(res.status == 'y'){
               $('.quota_comment_ul').html(res.data);
               $('.quota_comment_div').show();
               page++;
               
            }else{
               tanceng(res.error) ;
            }
        }
   });  
    
}


</script>