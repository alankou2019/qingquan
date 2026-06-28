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
            <li class="on"><a href="#">人员管理</a></li>
        </ul>
    </div>
    <!--头部TAB切换-->

    <!--搜索-->
    <div class="search_box">

    	<form action="searchForm" name="searchForm" style="display:inline">
    	<select class="screen" name="department_id">
            <option value="">请选择部门</option>
            {% for depart in departlist %}
            	<option value="{{depart.id}}">{{depart.delimiter}}{{depart.name}}</option>
            {% endfor %}
        </select>
        <input type="text" name="name" value="" placeholder="请输入用户名称" class="search"/>
        <!--搜索输入框-->
        <!--搜索按钮-->
        <button class="search_btn btn1"  type="button" onclick="searchData();"><i class="iconfont icon-llhomesearch"></i>搜索</button>
        </form>
        <!--右侧配置-->
        <div class="search_right">
            <span class="num">共<span id="recordCount">{{dataList.count}}</span>条记录</span>
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
            <th class="operate"><span>设置考核范围及归档查看权限</span></th>
            <th class="w200"><span>部门</span></th>
            <th><span>姓名</span></th>
        </tr>
        <!--head部分-->
        <!--列表部分-->
        {% for item in dataList.items %}

        	<tr>
            	<td class="check_box">
            		<label class="radio_check">
            			<input type="checkbox" name="radio_check" class="item_checkbox" value="{{item['id']}}"/>{{item.id}}
            		</label>
            	</td>
            	<td class="operate">
                <div class="handle">
					<i class="iconfont icon-caozuo"></i>
					操作
					<i class="iconfont icon-sanjiao sanjiao"></i>
					<span class="title" style=" padding-left:30px;">查看权限</span>
					<ul class="list">
						<li><a onclick="setright({{item['id']}},1)">查看自己(默认)</a></li>
						<li><a onclick="setright({{item['id']}},2)">查看所在部门</a></li>
						<li><a onclick="setright({{item['id']}},3)">查看所有</a></li>
						<li><a onclick="addreport({{item['id']}})">添加进考核组</a></li>
						<li><a onclick="window.location='{{helper.createUrl(['p':'user/userviewrole','user_id':item['id']])}}';"">配置查看权限</a></li>
						<li><a onclick="window.location='{{helper.createUrl(['p':'companyuser/changepassword','id':item['id']])}}';"">修改密码</a></li>

					</ul>
				</div>
                </td>
                <td class="name"><span class="txt">{{item['departmentname']}}</span></td>
                <td class="name"><span class="txt">{{item['name']}}</span></td>
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
            <button onclick="addreport();" style="width: 100px;">添加进考核组</button>
        </div>
        <div class="page_box ">
            <div class="page" id="listtable_page"></div>

        </div>
    </div>
    <!--表格底部-->
    </div>
   	<!--表格-->

   	<!--生成报表隐藏Form-->
   	<div style="display: none;">
   		<form action="{{helper.createUrl(['p':'report/new'])}}" id='reportform'>
   			<input type="hidden" name='userid' id='reportuserids'/>
   		</form>
   	</div>
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
		listTable.filter.page = 1;
		listTable.loadList();
	 }

	 //设置权限
	 function setright(id,right)
	 {
		 if(!id || !right){
			 return false ;
		 }

		 $.ajax({
	   		 type: "POST",
	   		 url: "{{helper.createUrl(['p':'firm/setright'])}}",
	   		 data: {id:id,right:right},
	   		 dataType: "json",
	   		 success: function(res){
			 	var res = eval(res);
	   			 if(res.status == 'y'){
			 		layer.msg('添加成功') ;

	   			 }else{
	   				 layer.msg(res.error) ;
	   			 }
	   		 }
  	 	});
	 }
	 
	 
	 //添加近考核管理
	 function addreport(id)
	 {
		 if(!id){
			 var id = getCheckedBox();
		 }
		 
		 if(!id){
			 return false ;
		 }
		 
		 $.ajax({
	   		 type: "POST",
	   		 url: "{{helper.createUrl(['p':'firm/addreport'])}}",
	   		 data: {id:id},
	   		 dataType: "json",
	   		 success: function(res){
			 	var res = eval(res);
	   			 if(res.status == 'y'){
			 		layer.msg(res.data) ;
	   			 }else{
	   				 layer.msg(res.error) ;
	   			 }
	   		 }
  	 	});
	 }
	 
	 
	 //添加到审核组
     function addgroup(id)
     {
         if(!id){
             var id = getCheckedBox();
         }
         
         if(!id){
             return false ;
         }
         
         $.ajax({
             type: "POST",
             url: "{{helper.createUrl(['p':'firm/addgroup'])}}",
             data: {id:id},
             dataType: "json",
             success: function(res){
                var res = eval(res);
                 if(res.status == 'y'){
                    layer.msg(res.data) ;
                 }else{
                     layer.msg(res.error) ;
                 }
             }
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