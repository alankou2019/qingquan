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
							</div>
						</div>
					</li>
				</ul>
			</div>
			<div class="score_details_list100 score_details_list">
				<div class="title">
					带审核指标
				</div>
				<p>点击查看评分缘由</p>
				<ul class="score_details_list_ul clear">
					<!--循环需要得分的指标 -->
					{% if details %}
						{% for item in details %}
							<li class="fl clear">
								<div class="fl left_tit"  style="width: 18rem;">
								    <span class="fr ">
								       {% if item.checkstatus===0 %}
								       <button class="inputfen"  type="button">已驳回</button>
								       {% elseif item.checkstatus==1 %}
								       <button class="inputfen"  type="button">已通过</button>
								       {% else %}
								       <button class="inputfen"  type="button"  onclick="javascript:pointcheck(this)" option='yes' did='{{item.id}}'>通过</button>
                                       <button class="inputfen"  type="button"  onclick="javascript:pointcheck(this)" option='no' did='{{item.id}}'>驳回</button>
								       {% endif %}
                                       
                                       <button class="inputfen"  type="button"  onclick="javascript:checkspeed(this)"  did='{{item.id}}'>审核进度</button>
                                    </span>
									{{item.qname}} {{item.point}}<span >分</span>
									<!--每一项指标的指标id -->
									<p onclick="tancengone(this)" data-txt ="{{item.reason}}";>{{helper.substr(item.reason,0,10)}}</p>
									
								</div>
								
							</li>
						{% endfor %}
					{% else %}
					   <div style="text-align: center;">
	                        暂无数据…… 
	                    </div>
					{% endif %}
				</ul>
			</div>
		</div>
	</form>
    
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

//审核进度
function checkspeed(obj)
{
	var obj=$(obj);
    var id=obj.attr('did');
    
    $.ajax({
        type: "POST",
        url: "{{helper.createUrl(['p':'bspoint/checkspeed'])}}",
        data: {"id":id},
        dataType: "json",
        success: function(res){
            if(res.status == 'y'){
               $('.quota_comment_ul').html(res.data);
               $('.quota_comment_div').show();
               
               changecolor();
            }else{
               tanceng(res.error) ;
            }
        }
   }); 
    
}
//积分记录审核
function pointcheck(obj)
{
	var obj=$(obj);
	var id=obj.attr('did');
	var option=obj.attr('option');
	$.ajax({
        type: "POST",
        url: "{{helper.createUrl(['p':'bspoint/pointcheck'])}}",
        data: {'id':id,'option':option},
        dataType: "json",
        success: function(res){
            if(res.status == 'y'){
               layer.open({
                    content: '设置成功',
                    btn: '我知道了',
                    yes:function(index){
                       layer.close(index);
                       window.location.href = window.location.href ;
                   }
               });
               
            }else{
               tanceng(res.error) ;
            }
        }
   });
	
}

</script>