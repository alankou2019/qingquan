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
<!--日历插件-->
<script src="/skin/adminhtml/default/libs/laydate/laydate.js"></script>
    <div class="full_box">
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
	        <!--搜索输入框-->
	        <input type="text" name="name" value="" placeholder="请输入人员名称" class="search"/>
	        <!--搜索输入框-->
	        <!--搜索按钮-->
	        <button class="search_btn btn1"  type="button" onclick="searchData();"><i class="iconfont icon-llhomesearch"></i>搜索</button>
        </form>
        
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
            <th class="check_box"><label class="radio_check ck_all checkall"><input type="checkbox" name="radio_check"/></label></th>
            <th><span>姓名</span></th>
            <th><span>部门</span></th>
        </tr>
        <!--head部分-->
        <!--列表部分-->
        {% for item in dataList.items %}
        	<tr>
            	<td class="check_box">
            		<label class="radio_check">
            		<input type=checkbox name='radio_check' class="item_checkbox" value="{{item['id']}}" reportuser="{{item['name']}}"/>
            		</label>
            	</td>
                <td class="name">
                    <span class="txt">{{item['name']}}</span>
                </td>            
                <td class="name">
                    <span class="txt">{{item['departmentname']}}</span>
                </td>
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
	 
	 //定义两个全局变量
	 ids = new Array();
	 names = new Array();
	 
	 //为选择评分人  绑定点击事件
	 $("body").on("click",".item_checkbox", function () {
		 var id = $(this).val() ;
		 var name = $(this).attr('reportuser') ;
		 //判断是选中还是 取消的事件
		 if($(this).parent().hasClass('on')){
			//选中事件  
			 ids.push(id) ;
			 names.push(name);
		 }else{
			//取消
			 ids.splice($.inArray(id,ids),1);
			 names.splice($.inArray(name,names),1);
		 }
	 }) ;
	 
	 
	 //选择所有
	 $("body").on("click",".checkall",function(){
		//判断是选中还是 取消的事件
		 if($(this).hasClass('on')){
			 ids = [] ;
			 names = [] ;
			//选中事件  
			 var len = $('.item_checkbox').length ;
			 for(var i=0;i<len;i++){
				 var id = $('.item_checkbox').eq(i).val();
				 var name = $('.item_checkbox').eq(i).attr('reportuser');
				 ids.push(id) ;
				 names.push(name);
			 }
		 }else{
			//取消
			 ids = [] ;
			 names = [] ;
		 }
	 })
	 
	 var callbackdata = function(){
			//合并全局数据
			var id = ids.join(',');
			var reportuser = names.join(',');
			var arr = new Array();
				arr['id'] = id;
				arr['reportuser'] = reportuser ;
			return arr ;
	 }
    </script>
{% endif %}