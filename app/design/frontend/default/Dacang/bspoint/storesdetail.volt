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
								被考核人<div style="float: right;margin-left:170px;">总分：{{totalpoint}}</div>
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
					考核指标
				</div>
				<p>点击指标查看评分标准</p>
				<ul class="score_details_list_ul clear">
					<!--循环需要得分的指标 -->
					{% if details %}
						{% for item in details %}
							<li class="fl clear">
								<div class="fl left_tit" onclick="tancengone(this)" data-txt ="{{item['point_desc']}}">
									{{item['qname']}}
									<!--每一项指标的指标id -->
									<input type="hidden" name="quotaids[]" value="{{item['quota_id']}}"/>
									<p>{{helper.substr(item['point_desc'],0,10)}}</p>
								</div>
								
								<div class="fr fen">
									<p class="pointright"> &nbsp;&nbsp;&nbsp;&nbsp;评分：{{helper.del0(item['report_point'])}}</p>
									<p class="pointrighttime">{{helper.formatDateTime(item['report_time'])}}</p>
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
                <input type="hidden" name="quota_id"> 
                <input type="hidden" name="id"> 
                <textarea name="content" id="quotacomment" cols="30" rows="10"></textarea>
                <div class="clear sub_btn_box">
                    <button type="button" class="sub_btn fl" onclick="javascript:comment_quota()">提交</button>
                    <button type="button" class="sub_btn fr" onclick="javascript:get_quota_comment()">查看点评记录</button>
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
//点评框显示
function comment_quota_show(obj)
{
    //先清空上一次的值
    $('.comment_quota_div').find('[name="content"]').val('');
    
    var content=$.trim($(obj).attr('qc_comment'));
    var id=$(obj).attr('qc_id');
    var quota_id=$(obj).attr('quota_id');
    if(content){
        $('.comment_quota_div').find('[name="content"]').val(content);
    }
    if(id){
        $('.comment_quota_div').find('[name="id"]').val(id);
    }
    $('.comment_quota_div').find('[name="quota_id"]').val(quota_id);
    $('.comment_quota_div').show();
}

//点评指标
function comment_quota()
{
    var  content=$.trim($('#quotacomment').val());
    if(!content){
        tanceng('请输入点评内容'); return false;
    }
    
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
    if(!quota_id){
        return false;
    }
    $('.comment_quota_div').hide();
    $.ajax({
        type: "POST",
        url: "{{helper.createUrl(['p':'bs/getquotacomment'])}}",
        data: {"quota_id":quota_id,"page":page},
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