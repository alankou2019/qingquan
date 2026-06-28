<style>
.mybutton{
	background-color:#4560e6;
	color:#fff;
	margin-left:15px;
	padding:5px;
}
.table_box td,.table_box th{text-align:center}
</style>
<script src="/skin/adminhtml/default/js/form.js"></script>
<script type="text/javascript" src="/skin/adminhtml/default/libs/laydate/laydate.js" ></script>
<!--编辑器-->
<script type="text/javascript" charset="utf-8" src="/skin/adminhtml/default/libs/UEditor/ueditor.config.js"></script>
<script type="text/javascript" charset="utf-8" src="/skin/adminhtml/default/libs/UEditor/ueditor.all.min.js"></script>
<script type="text/javascript" charset="utf-8" src="/skin/adminhtml/default/libs/UEditor/zh-cn.js"></script>
<!--编辑器-->


<!--滚动条-->
<script
	src="/skin/adminhtml/default/libs/nicescroll/jquery.nicescroll.js"></script>
<div class="full_box">
	<div class="full_title">

		<span style="float: right; cursor: pointer;" onclick="window.location=window.location;">
			<i class="iconfont icon-shuaxin"></i>
		</span>
		<span class= "name">考核评详情</span>
		<a class="go_back" onclick="window.location='{{helper.createUrl(['p':'report/list'])}}';">
			<i class="iconfont icon-fanhui"></i>
			<span>返回考核表列表</span>
		</a>
	</div>

	<div class="full_cont">
		<!--表单-->
		<form action="{{helper.createUrl(['p':'report/save'])}}" method="post" class="form_full" id="dataForm" name="dataForm" enctype="multipart/form-data">

			<!--参与报表的人员id-->
			<input type="hidden" name="userid" value="{{userid}}"/>
			<!--是否保存成为模版-->
			<input type="hidden" name="istpl" value="" id='istpl'/>

			<ul class="list_form_full">
				<li class="posi_lm wrong">
					<div class="left posi_l must">考核表名称:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="name"  datatype="*2-20"  value="{{item.name}}"  errormsg="考评表名称2个字符,最多20个字符！"  maxlength="20" readonly="readonly"/>
						</div>
                    </div>
				</li>

				<li class="posi_lm">
					<div class="left posi_l must">被评分人员:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" readonly="readonly" value="{{userstr}}"/>
						</div>
					</div>
				</li>

				<li class="posi_lm wrong">
					<div class="left posi_l must">设置权重总额:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="sum"  datatype="*1-20"  value="{{item.sum}}"  errormsg="权重总额"  maxlength="20" readonly="readonly"/>
						</div>
                    </div>
				</li>

				<li class="posi_lm">
					<div class="left posi_l must">考核表方式:</div>
					<div class="right posi_m">
						<div class="input_clear">
							{% if item.from == 1  %}
								考核表模版库
							{% else %}
								  自定义
							{% endif %}
						</div>
					</div>
				</li>

				<!--分割线-->
				<div class="sub_title"></div>
				<li class="posi_lm">
					<div class="left posi_l must">指标列表:</div>
					<div class="right posi_m reportitem">
						<table border="1"  width="60%" class="table_box">
						  	<tr>
						    	<th>指标名称</th>
						    	<th>评分方式</th>
						    	<th>考核状态</th>
						    	<th>权重</th>
						    	<th>评分人</th>
						    	<th>操作</th>
						  	</tr>
						  	<tbody class="reporttr">
						  		{% for itemquota in reportquotalist.items %}
						  		<?php $timearr = explode(',',$itemquota->reporttimes) ;  $namearr = explode(',',$itemquota->report_user_name);?>
						  		<tr>
							    	<td>
							    		<span>{{helper.substr(itemquota.quota_name,0,15)}}</span>
							    	</td>
							    	<td>
							    		<span>{{quotatype[itemquota.quota_type]}}</span>
							    	</td>
							    	{% if helper.checkStrpos(itemquota.reporttimes,'0,') == 'true' %}
								    	<td>
								    		<span class="statustext">未完成</span>
								    	</td>
							    	{% else %}
							    		<td>
								    		<span class="statustext">完成</span>
								    	</td>
							    	{% endif %}
							    	<td>
							    		{% if itemquota.quota_type == helper.getExtratype() %}
                                            <span>加减分不参与总权重的计算</span><input type='hidden' name='quotavalue[]'  value='{{itemquota.quota_value}}' readonly='readonly'/>
                                        {% else %}
                                            <!--权重-->
                                            <input type="text" name='quotavalue[]' value='{{itemquota.quota_value}}' class="avgpoint" readonly="readonly"/>
                                        {% endif %}
							    	</td>
							    	<td>
							    		<?php foreach($timearr as $timekey=>$time){ ?>
							    		{% if time == 0 %}
							    			<span class="statustext red">{{namearr[timekey]}}</span>
							    		{% else %}
							    			<span class="statustext blue">{{namearr[timekey]}}</span>
							    		{% endif %}
							    		<?php }  ?>
							    		
							    	</td>
							    	<td>
							    		<button type='button' onclick='showpoint({{item.id}},{{itemquota.quota_id}})' class='mybutton'> 查看评分情况  </button>
							    		<button type="button" onclick="showcomment({{item.id}},{{itemquota.quota_id}})" class="mybutton">指标点评</button>
							    	</td>

							  	</tr>
							  	{% endfor %}
							</tbody>
						</table>
					</div>
				</li>
			</ul>
			<div class="online_btn_box" onclick="window.location='{{helper.createUrl(['p':'report/list'])}}';">
				<button type="reset" class="f_btn">返回</button>
			</div>
		</form>
	</div>
</div>

<script src="/skin/adminhtml/default/js/common.js"></script>
<script src="/skin/adminhtml/default/js/ljk.js"></script>
<script src="/skin/adminhtml/default/js/form.js"></script>
<script src="/skin/adminhtml/default/js/afterload.js"></script>
<script>
	$(function() {

		//滚动条优化
		$("html").niceScroll({
			cursorcolor : "#ccc"
		});

		{% if item %}
			var formObj = new Form('dataForm');
			formObj.init({{item|json_encode}});
		{% endif %}
	});

	//查看评分情况
	function showpoint(reportId,quotaId)
	{
		if(!reportId || !quotaId){
			layer.msg('参数错误') ;  return false ;
		}
		   
		layer.open({
			type: 2,
		  	title: '查看评分情况',
		    shadeClose: true,
		  	shade: 0.8,
		  	area:['600px','520px'],
		  	content:"{{helper.createUrl(['p':'report/showpoint'])}}"+'?reportId='+reportId+'&quotaId='+quotaId,
		  	btn: ['确定', '取消'],
		  	yes: function(index){
		  		layer.close(index);
		  	}
	  	});
	}

	
	
	//查看指标点评情况
    function showcomment(reportId,quotaId)
    {
        if(!reportId || !quotaId){
            layer.msg('参数错误') ;  return false ;
        }

      layer.open({
            type: 2,
            title: '指标点评',
            shadeClose: true,
            shade: 0.8,
            area:['800px','520px'],
            content:"{{helper.createUrl(['p':'quota/quota_comment'])}}"+'?report_id='+reportId+'&quota_id='+quotaId,
            btn: ['确定', '取消'],
            yes: function(index){
                layer.close(index);
            }
        });
    }
</script>