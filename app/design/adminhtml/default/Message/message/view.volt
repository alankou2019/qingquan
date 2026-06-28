<script src="/skin/adminhtml/default/js/form.js"></script>
<!--滚动条-->
<script
	src="/skin/adminhtml/default/libs/nicescroll/jquery.nicescroll.js"></script>
<div class="full_box">
	<div class="full_title">

		<span style="float: right; cursor: pointer;"
			onclick="window.location=window.location;"><i
			class="iconfont icon-shuaxin"></i></span> <span class="name">查看留言</span> <a
			class="go_back" onclick="window.location='{{helper.createUrl(['p':'message/index'])}}';"> <i
			class="iconfont icon-fanhui"></i> <span>返回留言列表</span>
		</a>

	</div>

	<div class="full_cont">
		<!--表单-->
		<form action="{{helper.createUrl(['p':'message/save'])}}" method="post" class="form_full" id="dataForm" name="dataForm" enctype="multipart/form-data">
             <input type="hidden" name="_has_key" value="" />
			<div class="sub_title">留言详细信息</div>
			<ul class="list_form_full">
				<li class="posi_lm">
					<div class="left posi_l">用户名:</div>
					<div class="right posi_m">
						<div class="txt">
							<span>{{item.nickname}}</span>
						</div>
					</div>
				</li>
				<li class="posi_lm">
					<div class="left posi_l">电话号码:</div>
					<div class="right posi_m">
						<div class="txt">
							<span>{{item.phone}}</span>
						</div>
					</div>
				</li>
				<li class="posi_lm">
					<div class="left posi_l">公司名称:</div>
					<div class="right posi_m">
						<div class="txt">
							<span>{{item.company}}</span>
						</div>
					</div>
				</li>
				<li class="posi_lm">
					<div class="left posi_l">公司地址:</div>
					<div class="right posi_m">
						<div class="txt">
							<span>{{item.company_addr}}</span>
						</div>
					</div>
				</li>
				<li class="posi_lm">
					<div class="left posi_l">合作业务:</div>
					<div class="right posi_m">
						<div class="txt">
							<span>{{item.cooper_service}}</span>
						</div>
					</div>
				</li>
				<li class="posi_lm">
					<div class="left posi_l">建筑面积:</div>
					<div class="right posi_m">
						<div class="txt">
							<span>{{item.covered_area}}</span>
						</div>
					</div>
				</li>
				<li class="posi_lm">
					<div class="left posi_l">咨询时间:</div>
					<div class="right posi_m">
						<div class="txt">
							<span>{{helper.formatDateTime(item.inputtime)}}</span>
						</div>
					</div>
				</li>
				<li class="posi_lm">
						<div class="left posi_l">IP:</div>
						<div class="right posi_m">
							<div class="txt">
								<span>{{item.ip}}</span>
							</div>
						</div>
					</li>
				</ul>

		</form>

	</div>
</div>
<script src="/skin/adminhtml/default/js/common.js"
	type="text/javascript" charset="utf-8"></script>
<script src="/skin/adminhtml/default/js/check.js" type="text/javascript"
	charset="utf-8"></script>
<script src="/skin/adminhtml/default/js/ljk.js"></script>
<script src="/skin/adminhtml/default/js/form.js"></script>
<script>
	$(function() {
		//滚动条优化
		$("html").niceScroll({
			cursorcolor : "#ccc"
		});
		//商品上架单选框美化
		$(".time_radio").CheckBox();
		Utils.validate("#dataForm","#btnSubmit",function(curform){
			   $("#btnSubmit").attr('disabled','disabled');
		       $("#btnSubmit").html('处理中..');       
		       return true;
		});
		{% if item %}
		var formObj = new Form('dataForm');
		formObj.init({{item|json_encode}});
		{% endif %}

	});
</script>