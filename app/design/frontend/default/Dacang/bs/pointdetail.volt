<style>
<!--
.totalpoint
{
    float: right;
    position: absolute;
    margin-left: 76%;
    margin-top: -6%;
}
.resetmypoint
{
    background: #f05a4f !important;
    margin-bottom: 0.35rem;
}
.scorer_footer
{
    height: auto;
}
.scorer_footer .sub_btn
{
    display: block;
}
.comment_quota_div,
.quota_comment_div
{
    display: none;
    align-items: flex-end;
}
.comment_quota_div[style*="display: block"]
{
    display: flex !important;
}
.comment_quota_div .layer_con
{
    width: 100% !important;
    min-height: auto !important;
    margin: 0 !important;
    padding: 0.7rem 0.75rem 0.9rem !important;
    border-radius: 0.7rem 0.7rem 0 0;
    background: #fff;
}
.comment_sheet_handle
{
    width: 2rem;
    height: 0.18rem;
    margin: 0 auto 0.55rem;
    border-radius: 99px;
    background: #d5dbe3;
}
.comment_sheet_header
{
    display: flex;
    align-items: flex-start;
    gap: 0.4rem;
    margin-bottom: 0.55rem;
}
.comment_sheet_title
{
    flex: 1;
    color: #17212b;
    font-size: 0.82rem;
    font-weight: bold;
    line-height: 1.35;
}
.comment_sheet_close
{
    width: 1.35rem;
    height: 1.35rem;
    border: 0;
    border-radius: 50%;
    background: #f1f3f6;
    color: #66727f;
    font-size: 1rem;
    line-height: 1;
}
.comment_sheet_standard
{
    margin-bottom: 0.55rem;
    padding: 0.45rem 0.5rem;
    border-left: 3px solid #4ea6df;
    background: #f5f8fb;
    color: #596775;
    font-size: 0.62rem;
    line-height: 1.45;
}
.comment_sheet_label
{
    display: flex;
    justify-content: space-between;
    margin: 0.45rem 0 0.28rem;
    color: #4a5562;
    font-size: 0.62rem;
}
.comment_sheet_label span
{
    color: #9aa4b0;
}
.comment_quota_div textarea
{
    width: 100%;
    min-height: 4.6rem;
    border: 1px solid #cfd6df;
    border-radius: 0.35rem;
    padding: 0.45rem 0.5rem;
    color: #1f2933;
    font-size: 0.68rem;
    line-height: 1.45;
}
.comment_quota_div .sub_btn_box
{
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
    margin-top: 0.65rem;
}
.comment_quota_div .sub_btn_box .sub_btn
{
    width: 100%;
    height: 2rem;
    margin: 0;
    border-radius: 0.35rem;
    font-size: 0.72rem;
}
.comment_quota_div .sub_btn_box .comment_history_btn
{
    border: 1px solid #cfd6df;
    background: #fff !important;
    color: #435160 !important;
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
								
								<!--<span class="totalpoint">{{totalpoint}}</span>-->
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
								
								<div class="fr fen">
                                   <button class="inputfen"  type="button"  onclick="javascript:comment_quota_show(this)" quota_id='{{item.quota_id}}'  
                                        {% if item.qc_id %}qc_id="{{item.qc_id}}"{% endif %}
                                        {% if item.qc_comment %}qc_comment="{{item.qc_comment}}"{% endif %}
                                   >点评</button>
                                </div>
                                <div class="fr fen">
                                    <input type="number" name="quotaval[]" value="{{helper.del0(item.report_point)}}" class='inputfen pointinput inputclassone'
                                     data-qtype="{{item.type}}" data-qname="{{item.qname}}"
                                     {% if state %} readonly="readonly"{% endif %}
                                     {% if item.type == 1 %} max="200"  placeholder="100分制"
                                     {% elseif item.type==2 %} max="20" placeholder="10分制"
                                     {% elseif item.type==3 %} max="{{item.quota_value}}" placeholder="满分 {{helper.del0(item.quota_value)}}"
                                     {% elseif item.type==4 %} placeholder="加减分"
                                     {% elseif item.type==5 %} max="10" placeholder="5分制"
                                     {% endif %}/>
                                </div>
							</li>
						{% endfor %}
					{% endif %}
				</ul>
			</div>
		</div>
		
		<footer class="footer_sub_btn scorer_footer">
            {% if state %}
                {% if reportinfo.ispoint == 1 %}
                    <input class="sub_btn resetmypoint subbutton" value="重置评分" readonly="readonly"/>
                {% endif %}
            {% else %}
				<input class="sub_btn submitpoint subbutton" value="提交" readonly="readonly"/>
            {% endif %}
		</footer>
	</form>
	
	
    <div class="layer_box comment_quota_div">
        <div class="layer_con has_btn">
            <form action="" id="comment_quota_form">
                <input type="hidden" name="quota_id"> 
                <input type="hidden" name="id"> 
                <input type="hidden" name="rid" value="{{reportinfo.id}}">
                <div class="comment_sheet_handle"></div>
                <div class="comment_sheet_header">
                    <div class="comment_sheet_title">指标点评</div>
                    <button type="button" class="comment_sheet_close">×</button>
                </div>
                <div class="comment_sheet_standard"></div>
                <label class="comment_sheet_label">
                    <strong>点评内容</strong>
                    <span>（可选）填评分的具体事件或和缘由</span>
                </label>
                <textarea name="content" id="quotacomment" cols="30" rows="10" placeholder="例如：本月提交及时，数据准确；或说明扣分原因。"></textarea>
                <div class="clear sub_btn_box">
                    <button type="button" class="sub_btn fl" onclick="javascript:comment_quota()">提交</button>
                    <button type="button" class="sub_btn fr comment_history_btn" onclick="javascript:get_quota_comment()">查看点评记录</button>
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
	$('.pointinput').each(function(){
		var qtype = $(this).attr('data-qtype');
		var qname = $(this).attr('data-qname') || '';
		if(qtype == '4' && (qname.indexOf('减分') >= 0 || qname.indexOf('扣分') >= 0)){
			$(this).attr('placeholder', '0或负分');
		}
		if(qtype == '4' && (qname.indexOf('加分') >= 0 || qname.indexOf('奖励') >= 0)){
			$(this).attr('placeholder', '0或正分');
		}
	});

	$('.resetmypoint').click(function(){
        layer.open({
            content: '重置后将清空您已提交的评分，需要重新评分并提交，确认重置吗？',
            btn: ['确认重置', '取消'],
            yes: function(index){
                layer.close(index);
                $.ajax({
                    type: "POST",
                    url: "{{helper.createUrl(['p':'bs/resetmypoint'])}}",
                    data: $('#pointfrom').serialize(),
                    dataType: "json",
                    success: function(res){
                        if(res.status == 'y'){
                            window.location.href = "{{helper.createUrl(['p':'bs/pointdetail','id':reportinfo.id,'uid':userinfo.id])}}";
                        }else{
                            tanceng(res.error);
                        }
                    }
                });
            }
        });
    });

	$('.submitpoint').click(function(){
		//检查输入
		if(checkinput()){
			//ajax  提交数据
			$.ajax({
		   		 type: "POST",
		   		 url: "{{helper.createUrl(['p':'bs/setpoint'])}}",
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
	$(".comment_sheet_close").click(function(){
		$(".comment_quota_div").hide();
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
		var qtype = $('.pointinput').eq(i).attr('data-qtype');
		var qname = $('.pointinput').eq(i).attr('data-qname') || '';
		if(qtype == '4' && (qname.indexOf('减分') >= 0 || qname.indexOf('扣分') >= 0) && parseFloat(val) > 0){
			tanceng('减分项只能填写0或负数');  return false;
		}
		if(qtype == '4' && (qname.indexOf('加分') >= 0 || qname.indexOf('奖励') >= 0) && parseFloat(val) < 0){
			tanceng('加分项只能填写0或正数');  return false;
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
	//先清空上一次的值
	$('.comment_quota_div').find('[name="content"]').val('');
	var content='';
	var id='';
	var quota_id='';
	var qname='';
	var desc='';
	
    content=$.trim($(obj).attr('qc_comment'));
    id=$(obj).attr('qc_id');
    quota_id=$(obj).attr('quota_id');
    qname=$.trim($(obj).closest('li').find('.left_tit').contents().filter(function(){return this.nodeType == 3;}).text());
    desc=$.trim($(obj).closest('li').find('.left_tit').attr('data-txt'));
   
    $('.comment_quota_div').find('[name="content"]').val(content);
    $('.comment_quota_div').find('[name="id"]').val(id);
    $('.comment_quota_div').find('[name="quota_id"]').val(quota_id);
    $('.comment_quota_div').find('.comment_sheet_title').text(qname ? qname : '指标点评');
    $('.comment_quota_div').find('.comment_sheet_standard').text(desc ? desc : '点击指标可查看评分标准，点评可填写本项评分的具体事件或缘由。');
    $('.comment_quota_div').show();
}

//点评指标
function comment_quota()
{
	var  content=$.trim($('#quotacomment').val());
	
	$.ajax({
        type: "POST",
        url: "{{helper.createUrl(['p':'bs/commentquota'])}}",
        data: $('#comment_quota_form').serialize(),
        dataType: "json",
        success: function(res){
            if(res.status == 'y'){
               layer.open({
                    content: '点评成功，谢谢您的参与',
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
function get_quota_comment()
{
	var quota_id=$('.comment_quota_div').find('[name="quota_id"]').val();
	var rid='{{reportinfo.id}}';
	if(!quota_id){
		return false;
	}
	$('.comment_quota_div').hide();
	$.ajax({
        type: "POST",
        url: "{{helper.createUrl(['p':'bs/getquotacomment'])}}",
        data: {"quota_id":quota_id,"page":page,"rid":rid},
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
