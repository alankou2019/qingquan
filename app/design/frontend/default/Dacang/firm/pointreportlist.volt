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
            <li class="on"><a href="#">考核范围人员</a></li>
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
        <button style=" background-color: #4560e6; border: 1px solid #4560e6;color: #ffffff; line-height:30px; height:32px;width: 100px;" type="button" onclick="makereport()">新建积分表</button>
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
    <table class="table_box" style="width: 70%;">
        <!--head部分-->
        <tr class="table_head">
            <th class="check_box"><label class="radio_check ck_all"><input type="checkbox" name="radio_check"/></label></th>
            <th class="operate"><span>考核范围人员设置</span></th>
            <th><span>部门</span></th>
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
					<span class="title" style=" padding-left:30px;">操作列表</span>
					<ul class="list">
						<li><a onclick="removereport({{item['id']}})">移除考核管理</a></li>
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
            <button onclick="removereport();" style="width: 100px;">移除考核组</button>
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
   		<form action="{{helper.createUrl(['p':'pointreport/new'])}}" id='reportform'>
   			<input type="hidden" name='userid' id='reportuserids'/>
   		</form>
   	</div>
    <!--当前页js star-->
    <script type="text/javascript" src="/skin/adminhtml/default/js/switch.js" ></script>
    <script type="text/javascript" src="/skin/adminhtml/default/js/zel.js" ></script>
    <script src="/skin/adminhtml/default/js/jquery.pagination.js"></script>
    <script src="/skin/adminhtml/default/js/listTable.js"></script>
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


	 //生成报表
	 function makereport()
	 {
		 var id = "";
		 var num=0 ;
		 $(".item_checkbox").each(function(){
			  if(this.checked){
				  num ++ ;
				  if(id!=""){
					  id +=","+this.value;
				  }else{
					  id += this.value;
				  }
			  }
		 });

		 if(num > 1){
			 Utils.alert("只能选择一个"); return false ;
		 }

		 if(id ==""){
			 Utils.alert("请选择要建立积分考评表的人员!");
		 }else{
			 $('#reportuserids').val(id);
			 $('#reportform').submit();
		 }
	 }
	 
	 
	 //添加近考核管理
	 function removereport(id)
	 {
		 if(!id){
			 var id = getCheckedBox();
		 }
		 
		 if(!id){
			 return false ;
		 }
		 
		 $.ajax({
	   		 type: "POST",
	   		 url: "{{helper.createUrl(['p':'firm/removereport'])}}",
	   		 data: {id:id},
	   		 dataType: "json",
	   		 success: function(res){
			 	var res = eval(res);
	   			 if(res.status == 'y'){
			 		layer.msg('移除成功') ;
			 		listTable.loadList();
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