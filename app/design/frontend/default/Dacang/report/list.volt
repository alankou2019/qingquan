{% if full_page %}
<!--滚动条-->
<script src="/skin/adminhtml/default/libs/nicescroll/jquery.nicescroll.js"></script>
<!--cookie-->
<script src="/skin/adminhtml/default/libs/cookie/cookie.min.js"></script>
<!--表格拖动-->
<!--由于表单拖动需要migrate属性，而高版本已经没有了，所以要引入这个插件-->
<script src="/skin/adminhtml/default/js/jquery-migrate-1.2.1.js"></script>
<script src="/skin/adminhtml/default/libs/colResizable/colResizable-1.3.min.js" type="text/javascript" charset="utf-8"></script>
<!--单选框复选框美化-->
<script src="/skin/adminhtml/default/js/check.js" type="text/javascript" charset="utf-8"></script>
<!--弹框-->
<script src="/skin/adminhtml/default/libs/layer/layer.js" type="text/javascript" charset="utf-8"></script>
    <div class="full_box">
    <!--头部TAB切换 -->
    <div class="head_tab clear">
        <ul>
            <li class="on"><a href="#">KPI考评表列表</a></li>
        </ul>
    </div>
    <!--头部TAB切换-->
    
    <!--搜索-->
    <div class="search_box">
    
    	<form action="searchForm" name="searchForm" style="display:inline">
    	<select class="screen" name="department_id">
            <option value="">请选择部门</option>
            {% for depart in departlist %}
            	<option value="{{depart.dingding_id}}">{{depart.delimiter}}{{depart.name}}</option>
            {% endfor %}
        </select>
        <select class="screen" name="reportstatus">
            <option value="">完成状态</option>
            {% for key,statusitem in reportstatus %}
                <option value="{{key}}">{{statusitem}}</option>
            {% endfor %}
        </select>
        <input type="text" name="name" value="" placeholder="请输入用户名称" class="search"/>
        <!--搜索输入框-->
        <!--搜索按钮-->
        <button class="search_btn btn1"  type="button" onclick="searchData();"><i class="iconfont icon-llhomesearch"></i>搜索</button>
        </form>
        <button onclick="allpointing();" class="all_delete_button">全员考评</button>
        <button onclick="allpointed();"  class="all_delete_button">全员公示</button>
        <button onclick="allsavestores();" class="all_delete_button">全员归档</button>
        
        <!--搜索按钮-->
        <!--右侧配置-->
        <div class="search_right">
            <span class="num">共<span id="recordCount"></span>条记录</span>
            <a href="javascript:void(0)" onclick="window.location=window.location"><i class="iconfont icon-shuaxin"></i></a>    
        </div>
        <!--右侧配置-->
    </div>
    <!--搜索-->
    
    <!--表格-->
    <div class="table_body" id="listTable">
     <!--表格主体-->
{% endif %}
    <table class="table_box">
        <!--head部分-->
        <tr class="table_head">
            <th class="check_box"><label class="radio_check ck_all"><input type="checkbox" name="radio_check"/></label></th>
            <th class="operate"><span>操作</span></th>
            <th><span>被评分人姓名</span></th>  
            <th><span>所属部门</span></th>  
            <th width="20%"><span>考评表名称</span></th>  
            <th><span>是否完成</span></th>  
            <th><span>总分数</span></th>
            <th><span>考评状态</span></th>
        </tr>
        <!--head部分-->
        <!--列表部分-->
        {% for item in dataList.items %}
        
        	<tr>
            	<td class="check_box"><label class="radio_check"><input type="checkbox" name="radio_check" class="item_checkbox" value="{{item['id']}}"/></label></td>
                <td class="operate">
                <div class="handle">
					<i class="iconfont icon-caozuo"></i>
					操作
					<i class="iconfont icon-sanjiao sanjiao"></i>
					<span class="title" style=" padding-left:30px;">操作列表</span>
					<ul class="list">
						<li><a href="{{helper.createUrl(['p':'report/detail','id':item['id']])}}">查看详情</a></li>
						{% if item['status'] == 1 %}
							<!--考核完成过后     显示公示、归档操作-->
							<!--公示操作 既是给被评分人发一个消息   然后被评分人在客户端看他的各项指标打分情况-->
							<!--归档操作 既是清空此次具体的评分值 将评分值添加到一张归档历史记录表里面  -->
							
							{% if item['ispub'] == 0 %}
								<li><a onclick="pointed({{item['id']}})">公示</a></li>
							{% endif %}
							{% if item['ispoint'] == 1 %}
								<li><a onclick="resetPoint({{item['id']}})">重置</a></li>
							{% endif %}
							<li><a onclick="savestores({{item['id']}})">归档</a></li>
						{% else %}
							<!--进行考评  既是发消息给评分人  提示进行考核-->
							{% if item['ispoint'] == 0 %}
								<li><a href="{{helper.createUrl(['p':'report/edit','id':item['id']])}}">编辑</a></li>
								<li><a onclick="pointing({{item['id']}})">进行考评</a></li>
						    {% else %}
						        <li><a onclick="comeback({{item['id']}})">撤销</a></li>
						        <li><a onclick="resetPoint({{item['id']}})">重置</a></li>
							{% endif %}
						{% endif %}
					</ul>
				</div>
                    <button class="delete" onclick="listTable.remove({{item['id']}},'您确认要删除吗?');"><i class="iconfont icon-shanchu"></i>删除</button>
                </td>
                <td class="name"><span class="txt">{{item['uname']}}</span></td>  
                <td class="name"><span class="txt">{{departone[item['department_id']]}}</span></td>  
                <td class="name"><span class="txt">{{item['reportname']}}</span></td>
                <td class="name"><span class="txt statustext">{{reportstatus[item['status']]}}</span></td>  
                <td class="name"><span class="txt">{{item['totalpoint']}}</span></td>
                <td class="name"><span class="txt">{{item['statusdesc']}}</span></td>
            </tr>
        {% endfor %}
        <!--列表部分-->
    </table>
{% if full_page %}
    <!--表格主体-->
    </div>
    <!--表格底部-->
    <div class="table_foot">
        <div class="all_check">
            <label class="radio_check ck_all"><input type="checkbox" name="radio_check"/>全选</label>
        </div>
        <div class="all_delete">
            <button onclick="removeMore();">批量删除</button>
        </div>
        <div class="all_delete">
            <button onclick="pointing();">批量考评</button>
        </div>
        <div class="all_delete">
            <button onclick="comeback();">批量撤销</button>
        </div>
        <div class="all_delete">
            <button onclick="resetPoint();">批量重置</button>
        </div>
        <div class="all_delete">
            <button onclick="pointed();">批量公示</button>
        </div>
        <div class="all_delete">
            <button onclick="savestores();">批量归档</button>
        </div>

        <div class="page_box ">
            
            <div class="page" id="listtable_page"></div>
                         
        </div>
    </div>
    <!--表格底部-->
    </div>
    <!--表格-->   
   
            <!--当前页js star-->
    <script type="text/javascript" src="/skin/adminhtml/default/js/switch.js" ></script>
    <script type="text/javascript" src="/skin/adminhtml/default/js/zel.js" ></script>
    <script src="/skin/adminhtml/default/js/jquery.pagination.js"></script>
    <script src="/skin/adminhtml/default/js/listTable.js"></script>
    <script src="/skin/adminhtml/default/js/afterload.js"></script>
    <script>
        //表格拖动JS
		$(".table_box").colResizable();
		$(".radio_check").CheckBox();
		listTable.recordCount = {{dataList.count}};
		listTable.pageCount = {{dataList.pageCount}};
		listTable.currentPage = {{dataList.currentPage}};
		listTable.pageSize = {{dataList.pageSize}};
		listTable.init();
		
	 function searchData()
	 {
    	listTable.filter.name = Utils.trim(document.forms['searchForm'].elements['name'].value);
    	listTable.filter.department_id = Utils.trim(document.forms['searchForm'].elements['department_id'].value);
    	listTable.filter.reportstatus = Utils.trim(document.forms['searchForm'].elements['reportstatus'].value);
		listTable.filter.page = 1;
		listTable.loadList();
	 }
	 
	 function removeMore()
	 {
		 var id = "";
		 $(".item_checkbox").each(function(){
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
		 }else{
			 listTable.remove(id,"您确认要删除吗?");
		 }
	 }
	 
	 
	 //归档操作
	 function savestores(id)
	 {
		 if(!id){
			 id = getCheckedBox();
		 }
		 
		 if(id ==""){
			 Utils.alert("请选择要归档的数据!");
		 }
		 
		 layer.confirm('你确定要进行归档操作么？', {
			  btn: ['确定','取消'] //按钮
			}, function(){
				$.ajax({
			   		 type: "POST",
			   		 url: "{{helper.createUrl(['p':'report/stores'])}}",
			   		 data: {id:id},
			   		 dataType: "json",
			   		 success: function(res){
					 	var res = eval(res);
			   			 if(res.status == 'y'){
			   				layer.msg('归档成功') ; 
			   				location.reload() ;
			   			 }else{
			   				 layer.msg(res.error) ;
			   			 }
			   		 }
		  	 	});
		});
	 }
	 
	 
	//全员归档操作
     function allsavestores()
     {
         
         layer.confirm('你确定要进行所有的归档操作么？', {
              btn: ['确定','取消'] //按钮
            }, function(){
                $.ajax({
                     type: "POST",
                     url: "{{helper.createUrl(['p':'report/allstores'])}}",
                     dataType: "json",
                     success: function(res){
                        var res = eval(res);
                         if(res.status == 'y'){
                            layer.msg('归档成功') ; 
                            location.reload() ;
                         }else{
                             layer.msg(res.error) ;
                         }
                     }
                });
        });
     }
	
	
	 //进行考评操作
	 function pointing(id)
	 {
		 if(!id){
			 id = getCheckedBox();
		 }
		 
		 if(id ==""){
			 Utils.alert("请选择要考评的数据!");
		 }
		 
		 layer.confirm('进行考评后将不能在编辑，你确定要进行考评操作么？', {
			  btn: ['确定','取消'] //按钮
			}, function(){
				$.ajax({
			   		 type: "POST",
			   		 url: "{{helper.createUrl(['p':'report/pointing'])}}",
			   		 data: {id:id},
			   		 dataType: "json",
			   		 success: function(res){
					 	var res = eval(res);
			   			 if(res.status == 'y'){
			   				layer.msg('操作成功') ; 
			   				listTable.loadList();
			   			 }else{
			   				 layer.msg(res.error) ;
			   			 }
			   		 }
		  	 	});
		});
	 }
	 
	 
	 
	//全员考评操作
     function allpointing()
     {
         layer.confirm('你确定要进行所有的考评操作么？', {
              btn: ['确定','取消'] //按钮
            }, function(){
                $.ajax({
                     type: "POST",
                     url: "{{helper.createUrl(['p':'report/allpointing'])}}",
                     dataType: "json",
                     success: function(res){
                        var res = eval(res);
                         if(res.status == 'y'){
                            layer.msg('操作成功') ; 
                            listTable.loadList();
                         }else{
                             layer.msg(res.error) ;
                         }
                     }
                });
        });
     }
	 
	 
	//撤销
     function comeback(id)
     {
         if(!id){
             id = getCheckedBox();
         }
         
         if(id ==""){
             Utils.alert("请选择要撤销的数据!");
         }
         
         layer.confirm('只能撤销正在进行中的考评，你确定操作么？', {
              btn: ['确定','取消'] //按钮
            }, function(){
                $.ajax({
                     type: "POST",
                     url: "{{helper.createUrl(['p':'report/comeback'])}}",
                     data: {id:id},
                     dataType: "json",
                     success: function(res){
                        var res = eval(res);
                         if(res.status == 'y'){
                            layer.msg('操作成功') ; 
                            listTable.loadList();
                         }else{
                             layer.msg(res.error) ;
                         }
                     }
                });
        });
     }
	
	
	
	//重置评分
     function resetPoint(id)
     {
         if(!id){
             id = getCheckedBox();
         }
         
         if(id ==""){
             Utils.alert("请选择要重置的数据!");
         }
         
         layer.confirm('重置后将清空已提交的自评和评分，评分人可重新提交，确认重置么？', {
              btn: ['确定','取消'] //按钮
            }, function(){
                $.ajax({
                     type: "POST",
                     url: "{{helper.createUrl(['p':'report/resetpoint'])}}",
                     data: {id:id},
                     dataType: "json",
                     success: function(res){
                        var res = eval(res);
                         if(res.status == 'y'){
                            layer.msg('重置成功') ; 
                            listTable.loadList();
                         }else{
                             layer.msg(res.error) ;
                         }
                     }
                });
        });
     }
	
	
	
	//公示操作
	 function pointed(id)
	 {
		 if(!id){
			 id = getCheckedBox();
		 }
		 
		 if(id ==""){
			 Utils.alert("请选择要公示的数据!");
		 }
		 
		 layer.confirm('你确定要进行公示操作么？', {
			  btn: ['确定','取消'] //按钮
			}, function(){
				$.ajax({
			   		 type: "POST",
			   		 url: "{{helper.createUrl(['p':'report/pointed'])}}",
			   		 data: {id:id},
			   		 dataType: "json",
			   		 success: function(res){
					 	var res = eval(res);
			   			 if(res.status == 'y'){
			   				layer.msg('操作成功') ; 
			   				listTable.loadList();
			   			 }else{
			   				 layer.msg(res.error) ;
			   			 }
			   		 }
		  	 	});
		});
	 }
	
	
	
	//全员公示操作
     function allpointed()
     {
         layer.confirm('你确定要进行所有的公示操作么？', {
              btn: ['确定','取消'] //按钮
            }, function(){
                $.ajax({
                     type: "POST",
                     url: "{{helper.createUrl(['p':'report/allpointed'])}}",
                     dataType: "json",
                     success: function(res){
                        var res = eval(res);
                         if(res.status == 'y'){
                            layer.msg('操作成功') ; 
                            listTable.loadList();
                         }else{
                             layer.msg(res.error) ;
                         }
                     }
                });
        });
     }
	
	
	//获取选中的box
	function getCheckedBox()
	{
		var id = "";
		$(".item_checkbox").each(function(){
			  if(this.checked){
				  if(id!=""){
					  id +=","+this.value;
				  }else{
					  id += this.value;
				  }
			  }
		});
		
		return id ;
	}
	
    </script>
{% endif %}
