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
            <li class="on"><a href="#">部门列表</a></li>
        </ul>
    </div>
    <!--头部TAB切换-->
    
    <!--搜索-->
    <div class="search_box">


            <button class="optionbutton" type="button" onclick="importexcel()">导入excel</button>
            <button class="optionbutton" type="button" onclick="reportexcel()">excel模板下载</button>
    
    	
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
            <th style=" width:250px;"><span>部门名称</span></th>  
            <th><span>备注</span></th>  
        </tr>
        <!--head部分-->
        <!--列表部分-->
        {% for item in dataList.items %}
        	<tr>
            	<td class="check_box"><label class="radio_check"><input type="checkbox" name="radio_check" class="item_checkbox" value="{{item.id}}"/>{{item.id}}</label></td>
                <td class="name"><span class="txt">{{item.delimiter}}{{item.name}}</span></td>  
                <td class="name"><span class="txt">{{item.remark}}</span></td>  
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
        <div class="page_box ">
            
            <div class="page" id="listtable_page"></div>
                         
        </div>
    </div>
    <!--表格底部-->
    </div>
    <!--表格-->

    <!--上传excel  form-->
          <div style="display: none;">
          		<form method="post" action="{{helper.createUrl(['p':'department/uploadexcel'])}}" enctype="multipart/form-data" id='uploadexccelfrom'>
                <input  type="file" name="exceltpl" id='uploadexcelinput' onchange="fileChange(this);" accept="application/vnd.ms-excel"/>
                <input type="submit"  value="导入" />
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



    //上传excel
         function importexcel()
         {
        	 $('#uploadexcelinput').click();
         }

         //excel 导入
    	 function reportexcel()
    	 {
    		 window.location.href = "{{helper.createUrl(['p':'department/exportexceltpl'])}}";
    	 }

//上传excel 表单提交
     function fileChange()
     {
         $('#uploadexccelfrom').submit() ;
     }



	 function searchData()
	 {
    	listTable.filter.name = Utils.trim(document.forms['searchForm'].elements['name'].value);
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
	 
    </script>
{% endif %}