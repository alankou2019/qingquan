<style>
.mybutton{
	background-color:#4560e6;
	color:#fff;
	margin-left:15px;
	padding:5px;
}
.table_box td,.table_box th{text-align:center}
.setweight{
	float:right ;
}
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
		<span class= "name">编辑考评表</span>
		<a class="go_back" onclick="window.location='{{helper.createUrl(['p':'report/list'])}}';">
			<i class="iconfont icon-fanhui"></i>
			<span>返回考评表列表</span>
		</a>
	</div>

	<div class="full_cont">
		<!--表单-->
		<form action="{{helper.createUrl(['p':'report/save'])}}" method="post" class="form_full" id="dataForm" name="dataForm" enctype="multipart/form-data">
			<!--报表id-->
			<input type="hidden" name="id" value="{{item.id}}"/>
			<!--参与报表的人员id-->
			<input type="hidden" name="userid" value="{{userid}}"/>
			<!--是否保存成为模版-->
			<input type="hidden" name="istpl" value="" id='istpl'/>

			<ul class="list_form_full">
			
			    <li class="posi_lm">
                    <div class="left posi_l must">自动执行考核:</div>
                    <div class="right posi_m">
                            <select name="auto_run_date" class="departselect">
                                <option  value="" >请选择</option>
                                {% for date in datearr %}
                                    <option  value="{{date}}"  {% if item.auto_run_date == date %}selected="selected"{% endif %} >{{date}}</option>
                                {% endfor %}
                            </select>
                            <p> 设置自动执行考核的时间，表示每月的1-31号，如不想自动执行可不选择</p>
                    </div>
                </li>
				<li class="posi_lm wrong">
					<div class="left posi_l must">考评表名称:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<input type="text" name="name"  datatype="*2-20"  value="{{item.name}}"  errormsg="考评表名称2个字符,最多20个字符！"  maxlength="20" />
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
							<input type="text" name="sum"  datatype="*1-20"  value="{{item.sum}}"  errormsg="权重总额"  maxlength="20"  id='sumpoint'/>
						</div>
                    </div>
				</li>
				
				<li class="posi_lm">
					<div class="left posi_l must">选择指标:</div>
					<div class="right posi_m">
						<div class="input_clear">
							<button type="button" class="mybutton changequota"  onclick="changequota()" >选择指标</button>
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
						    	<th>设置权重</th>
						    	<th>评分人</th>
						    	<th>操作</th>
						  	</tr>
						  	<tbody class="reporttr">
						  		{% for itemquota in reportquotalist.items %}
						  		<tr>
							    	<td>
							    		<input type="hidden" name="quotaids[]" value="{{itemquota.quota_id}}"/>
							    		<span>{{helper.substr(itemquota.quota_name,0,15)}}</span>
							    	</td>
							    	<td>
							    		{{quotatype[itemquota.type]}}
							    	</td>
							    	    
							    	<td>
							    	    {% if itemquota.quota_type == helper.getExtratype() %}
							    	        <span>加减分不参与总权重的计算</span><input type='hidden' name='quotavalue[]'  value='{{itemquota.quota_value}}' readonly='readonly'/>
							    	    {% else %}
							    	        <!--权重-->
                                            <input type="text" name='quotavalue[]' value='{{itemquota.quota_value}}' class="avgpoint" />
							    	    {% endif %}
							    		
							    	</td>
							    	<td class='pingfenren'>
							    		<span class="reportusernames">{{itemquota.report_user_name}}</span>
							    		<button type='button' onclick='selectstaff(this)' class='mybutton selectbutton'> 选择 </button>
							    		<!--评分人的id-->
							    		<input type="hidden" name="reportuserid[]" value="{{itemquota['reportuserids']}}" class="reportuserids"/>
							    		<!--评分人权重比列-->
							    		<input type="hidden" name="reportuserweight[]" value="{{itemquota['reportuserweight']}}" class="hasreportuserweight"/>
										
							    		<button type='button' onclick='setweight(this,1)' class='mybutton setweight'> 评分人权重比列 </button>
							    	</td>
							    	<td >
							    		<button type="button" onclick="deltr(this)" class="mybutton"> 删除  </button>
							    		<button type='button' onclick='copy(this)'  class="mybutton"> 复制  </button>
							    		<button type='button' onclick='self(this)'  class="mybutton"> 自评 </button>
							    	</td>
							  	</tr>
							  	{% endfor %}
							</tbody>
						</table>
					</div>
				</li>
				
				
			</ul>
			<div class="online_btn_box">
				<button type="reset" class="f_btn" onclick="window.location='{{helper.createUrl(['p':'report/list'])}}';" >返回</button>
				<button type="button" class="f_btn active" onclick="submitform(this);">保存</button>
				<button type="button" class="f_btn active" onclick="submitform(this);"  istpl='1'>保存并生成模版</button>
			</div>
		</form>
	</div>
</div>


<!--设置评分人权重弹层-->
<div id="layerdiv" style="display: none;">
	<table border="1"  width="60%" class="table_box">
	  	<tr>
	    	<th>评分人</th>
	    	<th>设置权重</th>
	  	</tr>
	  	<tbody class="reportusertbody">

		</tbody>
	</table>
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


	//设置每一项指标的  多个评分人的权重
	function setweight(obj,isalert=0)
	{
		var resids = $(obj).parent().find('.reportuserids').val() ;
		var resnames = $(obj).parent().find('.reportusernames').html() ;
		if($(obj).parent().find('.hasreportuserweight').val()){
			var weight = $(obj).parent().find('.hasreportuserweight').val() ;
		}else{
			var weight = $(obj).parent().find('.reportuserweight').val() ;
		}
		

		
		if(resids == '' || resids == undefined){
			layer.msg('请先选择评分人');  return false ;
		}

		//所选择的评分人id
		var reportuserids = resids.split(',');
		//所选择的评分人名称
		var reportusernames = resnames.split(',');
		//元素个数
		var len = reportuserids.length ;
		
		
		//权重比列
		if(weight != ''){
			var weightpoint = weight.split(',');
			var weightpointlen = weightpoint.length ;
			if(weightpointlen != len){
				var avg = parseFloat(100 / len).toFixed(2);
			}
		}else{
			var len = reportuserids.length ;
			var avg = parseFloat(100 / len).toFixed(2);
		}
		
		var html = '';

		for(var i=0;i<len;i++){
			if(avg){
				var avgpoint = avg ;
			}else{
				var avgpoint = weightpoint[i] ;
			}
			
			html += "<tr>";
			html += "<td>"+reportusernames[i]+"</td>";
			html += "<td><input type='text' name='reportuser_weight[]' value='"+avgpoint+"' class='reportuserweightpoint'/></td>";
    		html += "</tr>";
		}

		$('.reportusertbody').html(html);

		if(isalert == 1){
			layer.open({
				type:1,
	            title:['设置评分人的权重','color:#333,font-size: 14px'],
	            area:['400px','300px'],
	            content:$('#layerdiv'),
	            btn: ['确定', '取消'],
			  	yes: function(index){
			  		var point = reckon() ;
			  		if(point != 100){
			  			layer.msg('请从新设置评分人的权重，权重总和必须为100'); return false ;
			  		}else{
			  			//将评分人的权重分数   生成一个隐藏域
			  			addweight(obj);
			  			layer.close(index);
			  		}
			  	}
		  	});
		}
	}

	//计算所有评分人的权重相加的分数
	function reckon()
	{
		var point = 0 ;
		var len = $('.reportuserweightpoint').length ;
		if(len > 0){
			for(var i=0;i<len;i++){
				point = point + parseFloat($('.reportuserweightpoint').eq(i).val());
			}
		}
		return point ;

	}

	//将评分人的权重分数   生成一个隐藏域
	function addweight(obj)
	{
		var weight = '';
		var len = $('.reportuserweightpoint').length ;
		if(len > 0){
			for(var i=0;i<len;i++){
				if(weight == ''){
					weight += parseFloat($('.reportuserweightpoint').eq(i).val());
				}else{
					weight += ','+parseFloat($('.reportuserweightpoint').eq(i).val());
				}

			}
		}
		
		
		if($(obj).parent().find('.hasreportuserweight').val()){
			$(obj).parent().find('.hasreportuserweight').val(weight) ;
		}else{
			$(obj).parent().find('.reportuserweight').val(weight);
		}
	}


	
	//删除指标
	function deltr(obj)
	{
		//判断当前选择的指标的数量  只有大于1 才能删除
		var length = $('.reporttr tr').length ;
		if(length > 1){
			$(obj).parent().parent().remove() ;
		}else{
			layer.msg('您至少要选择一个指标！');
		}
		quotaavg();

	}
	
	
	
	//计算当前页面的指标个数  算出平均分
	function quotaavg()
	{
		var len = $('.avgpoint').length;
		var sum = parseFloat($('#sumpoint').val());
		if(sum == '' || sum == undefined){
			sum = 100 ;
		}
		var avg = parseFloat(sum / len).toFixed(2);
		for(var i=0;i<len;i++){
			$('.avgpoint').eq(i).val(avg);
		}
	}
	
	
	
	//选择指标
	function changequota()
	{
		layer.open({
			type: 2,
		  	title: '选择指标',
		    shadeClose: true,
		  	shade: 0.8,
		  	area: ['60%', '80%'],
		  	content:"{{helper.createUrl(['p':'quota/simplelist'])}}",
		  	btn: ['确定', '取消'],
		  	yes: function(index){
		  		//获取但会的指标id  和 指标名称的 array
		  		var res = window["layui-layer-iframe" + index].callbackdata();
		  		var quotaid = res.id ;
		  		if(!quotaid){
		  			layer.close(index);
		  			layer.msg('请选择指标！') ;  return false  ;
		  		}
		  		//选址指标后回调
		  		afterquota(res) ;
		  		layer.close(index);

		  	}
	  	});
	}

	//选址指标后回调
	function afterquota(res)
	{
		//不参与总权重的指标类型
        var extratype={{helper.getExtratype()}};
		
		//获取指标id  指标名称数组
		var quotaids   = res.id.split(',');
		var quotanames = res.quota.split(',');
		var quotatype  = res.quotatype.split(',');
		var quotatypeval  = res.quotatypeval.split(',');
		
		var len = quotaids.length;
		var truelen=0;
        for(var i=0;i<len;i++){
            //排除加减分
            if(quotatypeval[i]!=extratype){
                truelen++;
            }
        }
        
		//计算平均分
		var sum = parseFloat($('#sumpoint').val());
		if(sum == '' || sum == undefined){
			sum = 100 ;
		}
		var avg = parseFloat(sum / truelen).toFixed(2);
		//生成html
		var html = "" ;
		for(var i=0;i<len;i++){
			html += "<tr>";
			html += "<td><span>"+quotanames[i]+"</span><input type='hidden' name='quotaids[]' value='"+quotaids[i]+"'/></td>";
			html += "<td><span>"+quotatype[i]+"</span></td>";
			if((quotatypeval[i] - extratype)!=0){
                html += "<td><input type='text' class='avgpoint'  name='quotavalue[]' value='"+avg+"'/></td>";
            }else{
                html += "<td><span>加减分不参与总权重的计算</span><input type='hidden' name='quotavalue[]'  value='0' readonly='readonly'/></td>";
            }
			html += "<td class='pingfenren'><span class='reportusernames'></span><button type='button' onclick='selectstaff(this)' class='mybutton selectbutton'> 选择 </button>";
				html += "<input type='hidden' name='reportuserid[]' value='' class='reportuserids'/><input type='hidden' name='reportuserweight[]' value='' class='reportuserweight'/></td>";
    		html += "<td><button type='button' onclick='deltr(this)' class='mybutton'> 删除  </button><button type='button' onclick='copy(this)' class='mybutton'> 复制  </button>";
    		html += "<button type='button' onclick='self(this)'  class='mybutton'> 自评 </button></td>";
    		
    		html += "</tr>";
		}

		//追加html元素
		$('.reporttr').append(html);
		quotaavg();
	}
	
	
	//选择每项指标的评分人
	function selectstaff(obj)
	{
	    layer.open({
			type: 2,
		  	title: '选择指标评分人',
		    shadeClose: true,
		  	shade: 0.8,
		  	area: ['60%', '80%'],
		  	content:"{{helper.createUrl(['p':'firm/simplelist'])}}",
		  	btn: ['确定', '取消'],
		  	yes: function(index){
		  		var res = window["layui-layer-iframe" + index].callbackdata();
		  		var reportuserid = res.id ;
		  		if(!reportuserid){
		  			layer.close(index);
		  			layer.msg('请选择评分人！') ;  return false  ;
		  		}
		  		//选址评分人后回调
		  		afterreportuser(obj,res) ;
		  		layer.close(index);
		  	}
	  	});
	}
	
	
	//选址评分人后回调
	function afterreportuser(obj,res)
	{
		$(obj).prev().html(res.reportuser);
		$(obj).next().val(res.id);
		//判断是否是加减分的选择评分人
		if($(obj).hasClass('suibianla')){
			return  ;
		}
		
		//增加一个设置评分人权重的button
		var setweightutton = "<button type='button' onclick='setweight(this,1)' class='mybutton setweight'> 评分人权重比列 </button>";

		//防治重复生成
		if($(obj).parent().find('.setweight').html() == undefined){
			$(obj).parent().append(setweightutton) ;
		}
		
		setweight(obj);
		addweight(obj);
	}
	
	
	
	
	
	//提交Form
	function submitform(obj)
	{
		$(obj).attr('disabled','disabled');
		var submittext = $(obj).html();
		$(obj).html('处理中..');
		if($(obj).attr('istpl') == '1'){
	    	$('#istpl').val('1');
	    }
		
	    $.ajax({
	   		 type: "POST",
	   		 url: "{{helper.createUrl(['p':'report/save'])}}",
	   		 data: $('#dataForm').serialize(),
	   		 dataType: "json",
	   		 success: function(res){
			 	if(res.status == 'y'){
			 		layer.msg('考评表设置成功',function(index){
			 			window.location.href = "{{helper.createUrl(['p':'report/list'])}}";
			 		}) ;

	   			 }else{
	   				 layer.msg(res.error) ;
	   				 $(obj).removeAttr('disabled');
	   				 $(obj).html(submittext);
	   			 }
	   		 }
   	 	});

	    return false ;
	}

	
	//复制评分人
	function copy(obj)
	{
		//获取当前节点的位置
		var prevtr = $(obj).parent().parent().prev()  ;
		if(prevtr.length == 0){
			return ;
		}
		
		//复制上一个评分人的节点信息到本节点
		var prevpingfenren = prevtr.find('.pingfenren').html() ;
		$(obj).parent().prev().html(prevpingfenren) ;

	}
	
	//自评
	function self(obj)
	{
		var selfid={{userid}};
		var selfname='{{userstr}}';
		var oldreportusernames=$(obj).parent().prev().find('.reportusernames').html();
		var oldreportuserids=$(obj).parent().prev().find('.reportuserids').val();
		
		if(oldreportuserids){
			//判断当前是否 已经选择了自评人  已经选择过 则不做处理
			var isExists=$.inArray(selfid.toString(),oldreportuserids.split(','));
		    if(isExists>=0){
		    	return false ;
		    }
	        $(obj).parent().prev().find('.reportusernames').html(oldreportusernames+','+selfname);
	        $(obj).parent().prev().find('.reportuserids').val(oldreportuserids+','+selfid);
	    }else{
	        $(obj).parent().prev().find('.reportusernames').html(selfname);
	        $(obj).parent().prev().find('.reportuserids').val(selfid);
	    }
		
		var newobj=$(obj).parent().prev().find('.selectbutton');
		
		//增加一个设置评分人权重的button
        var setweightutton = "<button type='button' onclick='setweight(this,1)' class='mybutton setweight'> 评分人权重比列 </button>";

        //防治重复生成
        if($(newobj).parent().find('.setweight').html() == undefined){
            $(newobj).parent().append(setweightutton) ;
        }
        
        setweight(newobj);
        addweight(newobj);
	}

</script>