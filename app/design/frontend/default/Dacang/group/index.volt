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
            <li class="on"><a href="#">审核组</a></li>
        </ul>
    </div>
    <!--头部TAB切换-->

    <!--搜索-->
    <div class="search_box">

    	<form action="searchForm" name="searchForm" style="display:inline">
        <input type="text" name="name" value="" placeholder="关键字" class="search"/>
        <!--搜索输入框-->
        <!--搜索按钮-->
        <button class="search_btn btn1"  type="button" onclick="searchData();"><i class="iconfont icon-llhomesearch"></i>搜索</button>
        </form>
        <button class="operate" onclick="window.location='{{helper.createUrl(['p':'group/edit'])}}';"><i class="iconfont icon-tianjia"></i>添加审核组</button>
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
            <th class="operate"><span>操作</span></th>
            <th><span>名称</span></th>
            <th><span>描述</span></th>
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
                        <li><a href="{{helper.createUrl(['p':'group/edit','id':item['id']])}}">编辑</a></li>
                        <li><a href="javascript:addusertogroup({{item['id']}})">添加用户</a></li>
                        <li><a href="{{helper.createUrl(['p':'group/groupuser','group_id':item['id']])}}">查看用户</a></li>
                    </ul>
                </div>
                    <button class="delete" onclick="listTable.remove({{item['id']}},'您确认要删除吗?');"><i class="iconfont icon-shanchu"></i>删除</button>
                </td>
                <td class="name"><span class="txt">{{item['name']}}</span></td>
                <td class="name"><span class="txt">{{item['desc']}}</span></td>
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
            <button onclick="removeGroup();" style="width: 100px;">删除审核组</button>
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
		listTable.filter.page = 1;
		listTable.loadList();
	 }

	//移除审核组
     function removeGroup(id)
     {
         if(!id){
             var id = getCheckedBox();
         }
         
         if(id ==""){
             Utils.alert("请选择要移出的数据!");
         }else{
             listTable.remove(id,"您确认要删除考核组吗?");
         }
     }
	
	//添加用户到审核组
	function addusertogroup(gid)
	{
		if(!gid){
			return false;
		}
		
		layer.open({
            type: 2,
            title: '添加用户到审核组',
            shadeClose: true,
            shade: 0.8,
            area: ['50%', '60%'],
            content:"{{helper.createUrl(['p':'firm/simplelist'])}}",
            btn: ['确定', '取消'],
            yes: function(index){
                //获取返回的模版id  和 模版名称的 array
                var res = window["layui-layer-iframe" + index].callbackdata();
                var user_id = res.id ;
                layer.close(index);
                if(!user_id){
                    layer.msg('请选择用户！') ;  return false  ;
                }
                
                //添加审核人员
                $.ajax({
                    type: "POST",
                    url: "{{helper.createUrl(['p':'group/addusertogroup'])}}",
                    data: {'group_id':gid,'user_id':user_id},
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