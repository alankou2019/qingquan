{% if full_page %}
<style>
.company-platform-button {
    width: auto !important;
    min-width: 205px;
    padding: 0 20px !important;
    white-space: nowrap;
}
.company-platform-label {
    display: inline-block;
    min-width: 66px;
    margin-right: 8px;
    padding: 5px 8px;
    border: 1px solid #d8e2f3;
    color: #476889;
    background: #f5f8fc;
    line-height: 18px;
    text-align: center;
    white-space: nowrap;
}
.company-platform-label.wecom {
    color: #16865d;
    border-color: #bee6d4;
    background: #f2fbf7;
}
.company-platform-label.feishu {
    color: #6254a7;
    border-color: #d9d3f4;
    background: #f7f5ff;
}
.company-platform-label.manual {
    color: #a05a12;
    border-color: #f0d3ad;
    background: #fff9f0;
}
</style>
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
            <li class="on"><a href="#">公司列表</a></li>
        </ul>
    </div>
    <!--头部TAB切换-->
    
    <!--搜索-->
    <div class="search_box">
    
        <form action="searchForm" name="searchForm" style="display:inline">
        <!--搜索筛选-->
        <select class="screen" name="filter">
            <option value="">请选择条件</option>
            <option value="name">公司名称</option>
            <option value="contact">联系人</option>
            <option value="phone">联系电话</option>
        </select>
        <!--搜索筛选-->             
        <!--搜索输入框-->
        <input type="text" name="keywords" value="" placeholder="请输入搜索条件" class="search"/>
        <!--搜索输入框-->
        <!--搜索按钮-->
        <button class="search_btn btn1"  type="button" onclick="searchData();"><i class="iconfont icon-llhomesearch"></i>搜索</button>
        </form>
        
        <!--搜索按钮-->
        <button class="operate company-platform-button" onclick="window.location='{{helper.createUrl(['p':'company/new','platform':'dingding'])}}';"><i class="iconfont icon-tianjia"></i>添加钉钉公司</button>
        <button class="operate company-platform-button" onclick="window.location='{{helper.createUrl(['p':'company/new','platform':'wecom'])}}';"><i class="iconfont icon-tianjia"></i>添加企业微信公司</button>
        <button class="operate company-platform-button" onclick="window.location='{{helper.createUrl(['p':'company/new','platform':'feishu'])}}';"><i class="iconfont icon-tianjia"></i>添加飞书公司</button>
        <button class="operate company-platform-button" onclick="window.location='{{helper.createUrl(['p':'company/new','platform':'manual'])}}';"><i class="iconfont icon-tianjia"></i>添加服务号企业</button>
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
            <th width="15%"><span>公司名称</span></th>  
            <th width="10%"><span>所属行业</span></th>
            <th width="10%"><span>联系电话</span></th>  
            <th><span>请求地址</span></th>
            <th width="5%"><span>状态</span></th>
            <th width="10%"><span>登录次数</span></th>
            <th width="10%"><span>过期时间</span></th>
            <th width="10%"><span>备注</span></th>
        </tr>
        <!--head部分-->
        <!--列表部分-->
        {% for item in dataList.items %}
            <tr>
                <td class="check_box"><label class="radio_check"><input type="checkbox" name="radio_check" class="item_checkbox" value="{{item.id}}"/>{{item.id}}</label></td>
                <td class="operate">
                    {% if item.platform == 'wecom' %}
                    <span class="company-platform-label wecom">企业微信</span>
                    <button class="btn" onclick="window.location='{{helper.createUrl(['p':'wecom/index','company_id':item.id])}}';"><i class="iconfont icon-caozuo"></i>编辑</button>
                    {% elseif item.platform == 'feishu' %}
                    <span class="company-platform-label feishu">飞书</span>
                    <button class="btn" onclick="window.location='{{helper.createUrl(['p':'feishu/index','company_id':item.id])}}';"><i class="iconfont icon-caozuo"></i>编辑</button>
                    {% elseif item.platform == 'manual' %}
                    <span class="company-platform-label manual">服务号</span>
                    <button class="btn" onclick="window.location='{{helper.createUrl(['p':'company/edit','id':item.id,'platform':'manual'])}}';"><i class="iconfont icon-caozuo"></i>编辑</button>
                    {% else %}
                    <span class="company-platform-label">钉钉</span>
                    <button class="btn" onclick="window.location='{{helper.createUrl(['p':'company/edit','id':item.id,'platform':'dingding'])}}';"><i class="iconfont icon-caozuo"></i>编辑</button>
                    {% endif %}
                    <button class="delete" onclick="listTable.remove({{item.id}},'您确认要删除吗?');"><i class="iconfont icon-shanchu"></i>删除</button>
                </td>
                <td class="name">
                    <span class="txt" title="{{item.name}}">{{helper.substr(item.name,0,30)}}</span>
                </td>            
                <td class="name">
                    <span class="txt">{{item.industry}}</span>
                </td> 
                <td class="name">
                    <span class="txt">{{item.phone}}</span>
                </td> 
                <td class="name">
                    <span class="txt">{{helper.createUrl(['p':'bs/index','id':item.hash_key,'m':'front','_f':'1'])}}</span>
                </td> 
                <td class="name">
                    <span class="txt">{{item.status}}</span>
                </td>
                <td class="name">
                    <span class="txt">{{item.loginnum}}</span>
                </td>
                <td class="name">
                    <span class="txt">{{item.expire_time}}</span>
                </td>
                <td class="name">
                    <span class="txt">{{item.remark}}</span>
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
        listTable.filter.filter = Utils.trim(document.forms['searchForm'].elements['filter'].value);
        listTable.filter.keywords = Utils.trim(document.forms['searchForm'].elements['keywords'].value);
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
