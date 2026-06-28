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
									被考核人 <div style="float: right;margin-left:210px;">总分：{{totalpoint}}</div>
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
	<!-- 			    <div class="fr fen"> -->
	<!--                     <button class="addquota"  type="button" onclick="javascript:comment_quota_show(this)">增加指标</button> -->
	<!--                 </div> -->
					<div class="title">
						考核指标
					</div>
					
					<p>点击指标查看评分标准</p>
					
					<ul class="score_details_list_ul clear">
						<!--循环需要得分的指标 -->
						{% if details %}
							{% for item in details %}
								<li class="fl clear">
									<div class="fl left_tit"  onclick="tancengone(this)" data-txt ="{{item['point_desc']}}" style="width: 52%;">
										{{item['qname']}} 
										<!--每一项指标的指标id -->
										<input type="hidden" name="quotaids[]" value="{{item.id}}"/>
											<p>{{helper.substr(item['point_desc'],0,11)}}</p>
										
									</div>
									
	                                
									<div class="fr fen">
										<p class="pointright"> &nbsp;&nbsp;&nbsp;&nbsp;积分：{{helper.del0(item['report_point'])}}</p>
										<button class="inputfen"  type="button"  onclick="javascript:get_quota_comment({{item['id']}},{{item['report_id']}})"  did='{{item['id']}}'>积分记录</button>
									</div>
								</li>
							{% endfor %}
						{% endif %}
					</ul>
				</div>
			</div>
			<footer class="footer_sub_btn">
					<input  class="sub_btn subbutton" value="返回" onclick="javascript:history.back(-1);" readonly="readonly"/>
				
			</footer>
		</form>
		
		<div class="layer_box comment_quota_div">
	        <div class="layer_con has_btn">
	            <form action="" id="comment_quota_form">
	                <input type="hidden" name="report_id" value="{{reportinfo.id}}">
	            <div>
	                <span class="bspointspanwidth">指标名称：</span>
	                <input type="text" name="name" value=""  id="quotaname">
	            </div>
	            <div>
	                <span class="bspointspanwidth">指标类型：</span>
	                <input type="radio" name="type" value="3" checked="checked"> 权重制
	                <input type="radio" name="type" value="4" > 加减分
	            </div>
	            <div>
	                <span class="bspointspanwidth">评分说明：</span>
	                <textarea name="point_desc" id="quotacomment" cols="30" rows="10" class="bspointtextarea"></textarea>
	            </div>
	                <div class="clear sub_btn_box">
	                    <button type="button" class="addquotabutton" onclick="javascript:comment_quota()">提交</button>
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
		//点击别处隐藏浮层
	    $(".layer_box").click(function(){
	        $(this).hide();
	    })
	    $(".layer_con").click(function(){
	        return false;
	    })
	})
	
	//返回
	function callback()
	{
		var url = "{{helper.createUrl(['p':'bs/index','type':2])}}";
		window.location.href = url ;
	}
	
	//点评框显示
	function comment_quota_show(obj)
	{
	    //先清空上一次的值
	    $('.comment_quota_div').find('[name="name"]').val('');
	    $('.comment_quota_div').find('[name="point_desc"]').val('');
	    $('.comment_quota_div').show();
	}
	
	//点评指标
	function comment_quota()
	{
	    var  name=$.trim($('#quotaname').val());
	    var  content=$.trim($('#quotacomment').val());
	    if(!name){
	    	tanceng('请输入指标名称'); return false;
	    }
	    if(!content){
	        tanceng('请输入评分说明'); return false;
	    }
	    
	    $.ajax({
	        type: "POST",
	        url: "{{helper.createUrl(['p':'bspoint/quotaapply'])}}",
	        data: $('#comment_quota_form').serialize(),
	        dataType: "json",
	        success: function(res){
	            if(res.status == 'y'){
	               layer.open({
	                    content: '添加成功，等待管理员的审核',
	                    btn: '我知道了',
	                    yes:function(index){
	                       layer.close(index);
	                       $('.comment_quota_div').hide();
	                       window.location.href = window.location.href;
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
	function get_quota_comment(quotaId,reportId)
	{
	    if(!quotaId ||  !reportId){
	        return false;
	    }
	    $('.comment_quota_div').hide();
	    $.ajax({
	        type: "POST",
	        url: "{{helper.createUrl(['p':'bspoint/getitemdetail'])}}",
	        data: {"quota_id":quotaId,"page":page,'report_id':reportId,'status':1},
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