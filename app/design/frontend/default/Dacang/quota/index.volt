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
            <li class="on"><a href="#">指标库列表</a></li>
        </ul>
    </div>
    <!--头部TAB切换-->
    
    <!--搜索-->
    <div class="search_box">
    
        <form action="searchForm" name="searchForm" style="display:inline">
        <select class="screen" name="depart_id">
            <option value="">请选择部门</option>
            {% for depart in departlist %}
                <option value="{{depart.id}}">{{depart.delimiter}}{{depart.name}}</option>
            {% endfor %}
        </select>
        <input type="text" name="name" value="" placeholder="请输入指标名称" class="search"/>
        <!--搜索输入框-->
        <!--搜索按钮-->
        <button class="search_btn btn1"  type="button" onclick="searchData();"><i class="iconfont icon-llhomesearch"></i>搜索</button>
        </form>
        
        <!--搜索按钮-->
        <button class="operate" onclick="window.location='{{helper.createUrl(['p':'quota/edit'])}}';"><i class="iconfont icon-tianjia"></i>添加指标</button>
        <button class="optionbutton" type="button" onclick="importexcel()">导入excel指标库</button>
        
        <button class="optionbutton" type="button" onclick="reportexcel()">excel模板下载</button>
        
        <button class="optionbutton" type="button" onclick="selectquota()">参考指标库</button>
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
            <th class="w200"><span>指标名称</span></th>  
            <th class="w200"><span>评分方式</span></th>  
            <th class="w200"><span>所属部门</span></th>  
            <th><span>评分标准</span></th>  
        </tr>
        <!--head部分-->
        <!--列表部分-->
        {% for item in dataList.items %}
        
            <tr>
                <td class="check_box"><label class="radio_check"><input type="checkbox" name="radio_check" class="item_checkbox" value="{{item['id']}}"/>{{item.id}}</label></td>
                <td class="operate">
                    <button class="btn" onclick="window.location='{{helper.createUrl(['p':'quota/edit','id':item['id']])}}';"><i class="iconfont icon-caozuo"></i>编辑</button>
                    <button class="delete" onclick="listTable.remove({{item['id']}},'您确认要删除吗?');"><i class="iconfont icon-shanchu"></i>删除</button>
                </td>
                <td class="name"><span class="txt" title="{{item['name']}}">{{helper.substr(item['name'],0,15)}}</span></td>
                <td class="name"><span class="txt">{{quotatype[item['type']]}}</span></td>  
                <td class="name"><span class="txt">{{departlist[item['depart_id']].name}}</span></td>  
                <td class="name">
                    <span class="txt" title="{{item['point_desc']}}">{{helper.substr(item['point_desc'],0,50)}}</span>
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
            <label class="radio_check ck_all"><input type="checkbox" name="radio_check"/>全选</label>
        </div>
        <div class="all_delete">
            <button onclick="removeMore();">批量删除</button>
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
        <form method="post" action="{{helper.createUrl(['p':'quota/uploadexcel'])}}" enctype="multipart/form-data" id='uploadexccelfrom'>
         <input  type="file" name="exceltpl" id='uploadexcelinput' onchange="fileChange(this);" accept="application/vnd.ms-excel"/>
         <input type="submit"  value="导入" />
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
        listTable.filter.depart_id = Utils.trim(document.forms['searchForm'].elements['depart_id'].value);
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
     
     
     //上传excel
     function importexcel()
     {
         $('#uploadexcelinput').click();
     }
     
     //上传excel 表单提交
     function fileChange()
     {
         $('#uploadexccelfrom').submit() ;
     }
     //excel 导入
     function reportexcel()
     {
         window.location.href = "{{helper.createUrl(['p':'quota/exportexceltpl'])}}";
     }
     
     //选择现有指标
     function selectquota()
     {
        //判断当前报表方式是自定义 还是 模板
        layer.open({
            type: 2,
            title: '选择现有指标',
            shadeClose: true,
            shade: 0.8,
            area: ['60%', '80%'],
            content:"{{helper.createUrl(['p':'quota/quotatpl'])}}",
            btn: ['确定', '取消'],
            yes: function(index){
                //获取但会的指标id  和 指标名称的 array
                var res = window["layui-layer-iframe" + index].callbackdata();
                var quotaid  = res.id ;
                var departid = res.departid ;
                
                if(quotaid == ''){
                    layer.alert('请选择指标') ;
                }
                
                if(departid == ''){
                    layer.alert('请选择部门') ;
                }
                
                addquota(departid,quotaid);
                
                //选址指标后回调
                layer.close(index);
                listTable.loadList();
            }
        });
     }
     
     
     
     function addquota(departid,quotaids)
     {  
        //添加指标到指标库
        $.ajax({
             type: "POST",
             url: "{{helper.createUrl(['p':'quota/addquota'])}}",
             data: {"departid":departid,"quotaids":quotaids},
             dataType: "json",
             success: function(res){
                var res = eval(res);
                 if(res.status == 'y'){
                    return true ;
                 }else{
                     return false ;
                 }
             }
        });
     }
    </script>
{% endif %}