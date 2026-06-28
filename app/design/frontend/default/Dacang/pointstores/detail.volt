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
		<span class= "name">归档详情</span>
		<a class="go_back" onclick="window.location='{{helper.createUrl(['p':'pointstores/list'])}}';">
			<i class="iconfont icon-fanhui"></i>
			<span>返回归档历史</span>
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
					<div class="left posi_l must">考评表名称:</div>
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
                    <div class="left posi_l must">权重总额:</div>
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
						    	<th>权重</th>
						    	<th>评分人</th>
						    	<th>操作</th>
						  	</tr>
						  	<tbody class="reporttr">
						  		{% for itemquota in reportquotalist.items %}
						  		<tr>
							    	<td>
							    		<span>{{itemquota.quota_name}}</span>
							    	</td>
							    	<td>
							    		{{quotatype[itemquota.type]}}
							    	</td>
							    	<td>
                                        {% if itemquota.quota_type == helper.getExtratype() %}
                                            <span>加减分不参与总权重的计算</span><input type='hidden' name='quotavalue[]'  value='{{itemquota.quota_value}}' readonly='readonly'/>
                                        {% else %}
                                            <!--权重-->
                                            <input type="text" name='quotavalue[]' value='{{itemquota.quota_value}}' class="avgpoint" readonly="readonly"/>
                                        {% endif %}
                                    </td>
							    	<td>
							    		<span>{{itemquota.report_user_name}}</span>
							    	</td>
							    	<td>
							    		<button type='button' onclick='showpoint({{item.id}},{{itemquota.quota_id}},{{itemquota.id}})' class='mybutton'> 查看积分记录  </button>
<!-- 							    		<button type="button" onclick="showcomment({{item.id}},{{itemquota.quota_id}},{{itemquota.id}})" class="mybutton">指标点评</button> -->
							    	</td>
							  	</tr>
							  	{% endfor %}
							</tbody>
						</table>
					</div>
				</li>
				
			</ul>
			<div class="online_btn_box" onclick="window.location='{{helper.createUrl(['p':'pointstores/list'])}}';">
				<button type="reset" class="f_btn">返回</button>
			</div>
		</form>
	</div>
</div>

<script src="/skin/adminhtml/default/js/common.js"></script>
<script src="/skin/adminhtml/default/js/ljk.js"></script>
<script src="/skin/adminhtml/default/js/form.js"></script>
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
	function showpoint(reportId,quotaId,sid)
	{
		if(!reportId || !quotaId || !sid){
			layer.msg('参数错误') ;  return false ;
		}

		layer.open({
			type: 2,
		  	title: '查看评分情况',
		    shadeClose: true,
		  	shade: 0.8,
		  	area:['800px','520px'],
		  	content:"{{helper.createUrl(['p':'pointstores/showpoint'])}}"+'?reportId='+reportId+'&quotaId='+quotaId+'&sid='+sid,
		  	btn: ['确定', '取消'],
		  	yes: function(index){
		  		layer.close(index);
		  	}
	  	});
	}
	
	//查看指标点评情况
    function showcomment(reportId,quotaId,sid)
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
            content:"{{helper.createUrl(['p':'pointstores/quota_comment'])}}"+'?report_id='+reportId+'&quota_id='+quotaId+'&sid='+sid,
            btn: ['确定', '取消'],
            yes: function(index){
                layer.close(index);
            }
        });
    }

</script>