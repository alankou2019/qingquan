<script src="/skin/adminhtml/default/js/check.js" type="text/javascript" charset="utf-8"></script>
<div class="role_edit">
			<!--顶部-->
			<div class="title">
				<span class="name">文章分类</span>
				<a   href="{{helper.createUrl(['p':'articlecategory/new'])}}" class="edit manage on">新增</a>
               <i style="float:right;color: #4560e6;margin: 0 35px 0 5px; cursor:pointer" class="iconfont icon-shuaxin" onclick="window.location=window.location"></i>
			</div>
			<!--顶部-->
			<!--列表头部信息-->
			<div class="tags">
				<div class="fl">
					<span class="sort">排序</span>
					<span class="name">分类名称</span>
				</div>
				<div class="fr">
					<span class="operate">操作</span>
				</div>
			</div>
			<!--列表头部信息-->
			<!--列表主体-->
			<div class="list_box">
				<!--一级分类-->
				{% for item in dataList.items %}
				<div class="one">
					<!--左侧内容-->
					<label class="radio_check"><input type="checkbox" name="radio_check" value="{{item['id']}}"/></label>
					<img src="/skin/adminhtml/default/images/1_16.png" class="state"/>
					<input type="text" value="{{item['sort']}}" class="sort" onchange="changeField({{item['id']}},this,'sort');"/>
					<input type="text" value="{{item['name']}}" class="name" onchange="changeField({{item['id']}},this,'name');"/>
					<a class="add_class" href="{{helper.createUrl(['p':'articlecategory/edit','id':item['id'],'haskey':1])}}"><img src="/skin/adminhtml/default/images/1_18.png"/><span  class="txt">新增下级</span> </a>
					<!--左侧内容-->
					<!--右侧内容-->
					<div class="fr">
						<button class="handle" onclick="window.location='{{helper.createUrl(['p':'articlecategory/edit','id':item['id']])}}'"><i class="iconfont icon-iconfontbianji"></i>操作</button>
						<button class="delete" data-id="{{item['id']}}"><i class="iconfont icon-shanchu"></i>删除</button>
					</div>
					<!--右侧内容-->
					{% if item['child']%}
					{% for it in item['child'] %}
					<!--二级分类-->
					<div class="two">
						<!--左侧内容-->
						<label class="radio_check"><input type="checkbox" name="radio_check" value="{{it['id']}}"/></label>
						<img src="/skin/adminhtml/default/images/1_16.png" class="state"/>
						<input type="text" value="{{it['sort']}}" class="sort" onchange="changeField({{it['id']}},this,'sort');"/>
						<span class="line"><span class="border"></span></span>
						<input type="text" value="{{it['name']}}" class="name" onchange="changeField({{it['id']}},this,'name');"/>
						<a class="add_class" href="{{helper.createUrl(['p':'articlecategory/edit','id':it['id'],'haskey':1])}}"><img src="/skin/adminhtml/default/images/1_18.png"/><span  class="txt">新增下级</span> </a>
						<!--左侧内容-->
						<!--右侧内容-->
						<div class="fr">
							<button class="handle" onclick="window.location='{{helper.createUrl(['p':'articlecategory/edit','id':it['id']])}}'"><i class="iconfont icon-iconfontbianji"></i>操作</button>
							<button class="delete" data-id="{{it['id']}}"><i class="iconfont icon-shanchu"></i>删除</button>
						</div>
						<!--右侧内容-->
						<!--三级分类-->
						{% if it['child']%}
						{% for i in it['child'] %}
						<div class="three">
							<!--左侧内容-->
							<label class="radio_check"><input type="checkbox" name="radio_check" value="{{i['id']}}"/></label>
							<img src="/skin/adminhtml/default/images/1_16.png" class="state"/>
							<input type="text" value="{{i['sort']}}" class="sort" onchange="changeField({{i['id']}},this,'sort');"/>
							<span class="line"><span class="border"></span></span>
							<input type="text" value="{{i['name']}}" class="name" onchange="changeField({{i['id']}},this,'name');"/>
							<a class="add_class" href="{{helper.createUrl(['p':'articlecategory/edit','id':i['id'],'haskey':1])}}"><img src="/skin/adminhtml/default/images/1_18.png"/><span  class="txt">新增下级</span> </a>
							<!--左侧内容-->
							<!--右侧内容-->
							<div class="fr">
								<button class="handle" onclick="window.location='{{helper.createUrl(['p':'articlecategory/edit','id':i['id']])}}'"><i class="iconfont icon-iconfontbianji"></i>操作</button>
								<button class="delete" data-id="{{i['id']}}"><i class="iconfont icon-shanchu"></i>删除</button>
							</div>
							<!--右侧内容-->
						</div>
						{% endfor %}
						{% endif %}
						<!--三级分类-->
					</div>
					{% endfor %}
					{% endif %}
					<!--二级分类-->
				</div>
				{% endfor %}
				<!--一级分类-->
				<!--列表底部-->
				<div class="list_foot">
					<label class="radio_check ck_all"><input type="checkbox" name="radio_check"/>全选</label>
					<button class="all_delete">全部删除</button>
				</div>
				<!--列表底部-->
			</div>
			<!--列表主体-->
		</div>
        	<!--当前页js star-->
	<script type="text/javascript" src="/skin/adminhtml/default/js/zel.js" ></script>
	<script>
		(function() {
			//单选框美化
		$(".radio_check").CheckBox();
		//展开收缩
			$(".list_box .state").click(function () {
				var hasOn = $(this).hasClass('on');
				var one = $(this).parent().hasClass('one');
				var two = $(this).parent().hasClass('two');
				if (hasOn) {
					$(this).attr('src','/skin/adminhtml/default/images/1_16.png');
					$(this).removeClass('on');
					if (one) {
						$(this).parent().find('.two').hide();
					}
					else if(two){
						$(this).parent().find('.three').hide();
					}
				} else{
					$(this).attr('src','/skin/adminhtml/default/images/1_17.png');
					$(this).addClass('on');					
					if (one) {
						$(this).parent().find('.two').show();
					}
					else if(two){
						$(this).parent().find('.three').show();
					}
				}
				
			});
			//单条删除
		$('.list_box .delete').click(function(e) {
			var listLi =$(this).parent().parent();
			var scrollTop = $(document).scrollTop();
			var obj = this;
			layer.confirm('是否删除？', {
				btn: ['删除', '取消'], //按钮
				shade: 0,
				offset: [100, 0],
			}, function() {
				//点击删除执行的事件
				var id = $(obj).attr('data-id');
				$.post("{{helper.createUrl(['p':'articlecategory/remove'])}}",{ids:id},function(res){
					if(res.status=='y'){
						listLi.remove();
						layer.msg('删除成功', {
							offset:0,
							shift:0,
						});
					}else{
						layer.msg('删除失败', {
							offset:0,
							shift:0,
						});
					}
				},'JSON');
				
			
			}, function() {
				layer.msg('您取消了删除', {
					offset:0,
					shift:0,
				});
				
			});
		});
		//批量删除
		$('.all_delete').click(function(e) {
			var hasson =$('.list_box .radio_check').hasClass('on');
			layer.confirm('是否删除选中信息？', {
				btn: ['删除', '取消'], //按钮
				shade: 0,
				offset: [100, 0],
			}, function() {
				if(hasson){
					//点击删除执行的事件
					var ids = checkId();
					$.post("{{helper.createUrl(['p':'articlecategory/remove'])}}",{ids:ids},function(res){
						if(res.status=='y')
						{
							$('.list_box .radio_check.on').parent().remove();
							layer.msg('删除成功', {
								offset:0,
								shift:0,
							});
						}else{
							layer.msg('删除失败', {
								offset:0,
								shift:0,
							});
						}
					},'JSON');
				}
				else{
					layer.msg('请至少选中一条信息！', {
					offset:0,
					shift:0,
				});
				}
				
			
			}, function() {
				layer.msg('您取消了删除', {
					offset:0,
					shift:0,
				});
				
			});
		});
		}())
		
		// 获取选中的id
		function checkId()
		{
			var id = "";
			 $("input[name='radio_check']").each(function(){
				  if(this.checked){
					  if(id!=""){
						  id +=","+this.value;
					  }else{
						  id += this.value;
					  }
				  }
			 });
			 if(id ==""){
				 Utils.alert("请选择要删除的数据!");
				 return false;
			 }
			 return id;
		}
		//修改排序，名称
		function changeField(id,obj,field)
		{
			var selectedValue = obj.value;
			$.post("{{helper.createUrl(['p':'articlecategory/changefield'])}}",{"id":id,"value":selectedValue,field:field});
		}
	</script>