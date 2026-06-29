{% if full_page %}
<script src="/skin/adminhtml/default/libs/nicescroll/jquery.nicescroll.js"></script>
<script src="/skin/adminhtml/default/libs/cookie/cookie.min.js"></script>
<script src="/skin/adminhtml/default/js/jquery-migrate-1.2.1.js"></script>
<script src="/skin/adminhtml/default/libs/colResizable/colResizable-1.3.min.js" type="text/javascript" charset="utf-8"></script>
<script src="/skin/adminhtml/default/js/check.js" type="text/javascript" charset="utf-8"></script>
<script src="/skin/adminhtml/default/libs/layer/layer.js" type="text/javascript" charset="utf-8"></script>
<div class="full_box">
    <div class="head_tab clear">
        <ul>
            <li class="on"><a href="#">工资项目</a></li>
        </ul>
    </div>

    <div class="search_box">
        <form action="searchForm" name="searchForm" style="display:inline">
            <select class="screen" name="company_id" id="company_id">
                <option value="0">全部企业</option>
                {% for company in companies %}
                <option value="{{company.id}}" {% if dataList.filter['company_id'] == company.id %}selected="selected"{% endif %}>{{company.name}}</option>
                {% endfor %}
            </select>
            <input type="text" name="keywords" value="{{dataList.filter['keywords']}}" placeholder="请输入工资项目名称" class="search"/>
            <button class="search_btn btn1" type="button" onclick="searchData();"><i class="iconfont icon-llhomesearch"></i>搜索</button>
        </form>

        <button class="operate" onclick="window.location='{{helper.createUrl(['p':'salaryproject/new'])}}';"><i class="iconfont icon-tianjia"></i>新增工资项目</button>
        <button class="operate" onclick="initProjects();"><i class="iconfont icon-tianjia"></i>初始化通用项目</button>

        <div class="search_right">
            <span class="num">共 <span id="recordCount"></span> 条记录</span>
            <a href="javascript:void(0)" onclick="window.location=window.location"><i class="iconfont icon-shuaxin"></i></a>
        </div>
    </div>

    <div class="table_body" id="listTable">
{% endif %}
    <table class="table_box">
        <tr class="table_head">
            <th class="check_box"><label class="radio_check ck_all"><input type="checkbox" name="radio_check"/></label></th>
            <th class="operate"><span>操作</span></th>
            <th width="14%"><span>企业</span></th>
            <th width="14%"><span>项目名称</span></th>
            <th width="9%"><span>项目类型</span></th>
            <th width="8%"><span>方向</span></th>
            <th width="10%"><span>计算方式</span></th>
            <th width="10%"><span>关联模块</span></th>
            <th width="8%"><span>计入应发</span></th>
            <th width="8%"><span>计入应扣</span></th>
            <th width="8%"><span>计入实发</span></th>
            <th width="7%"><span>状态</span></th>
        </tr>
        {% for item in dataList.items %}
        <tr>
            <td class="check_box"><label class="radio_check"><input type="checkbox" name="radio_check" class="item_checkbox" value="{{item.id}}"/>{{item.id}}</label></td>
            <td class="operate">
                <button class="btn" onclick="window.location='{{helper.createUrl(['p':'salaryproject/edit','id':item.id])}}';"><i class="iconfont icon-caozuo"></i>编辑</button>
                <button class="delete" onclick="listTable.remove({{item.id}},'确认停用并删除该工资项目吗？');"><i class="iconfont icon-shanchu"></i>删除</button>
            </td>
            <td class="name"><span class="txt" title="{{item.company_name}}">{{helper.substr(item.company_name,0,20)}}</span></td>
            <td class="name"><span class="txt" title="{{item.name}}">{{helper.substr(item.name,0,20)}}</span></td>
            <td class="name"><span class="txt">{{item.source_type_label}}</span></td>
            <td class="name"><span class="txt">{{item.direction_label}}</span></td>
            <td class="name"><span class="txt">{{item.calculation_mode_label}}</span></td>
            <td class="name"><span class="txt">{{item.linked_module}}</span></td>
            <td class="name"><span class="txt">{% if item.include_earning %}是{% else %}否{% endif %}</span></td>
            <td class="name"><span class="txt">{% if item.include_deduction %}是{% else %}否{% endif %}</span></td>
            <td class="name"><span class="txt">{% if item.include_net %}是{% else %}否{% endif %}</span></td>
            <td class="name"><span class="txt">{{item.status_label}}</span></td>
        </tr>
        {% endfor %}
    </table>
{% if full_page %}
    </div>
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
</div>

<script type="text/javascript" src="/skin/adminhtml/default/js/switch.js"></script>
<script type="text/javascript" src="/skin/adminhtml/default/js/zel.js"></script>
<script src="/skin/adminhtml/default/js/jquery.pagination.js"></script>
<script src="/skin/adminhtml/default/js/listTable.js"></script>
<script>
    $(".table_box").colResizable();
    $(".radio_check").CheckBox();
    listTable.recordCount = {{dataList.count}};
    listTable.pageCount = {{dataList.pageCount}};
    listTable.currentPage = {{dataList.currentPage}};
    listTable.pageSize = {{dataList.pageSize}};
    listTable.init();

    function searchData()
    {
        listTable.filter.company_id = Utils.trim(document.forms['searchForm'].elements['company_id'].value);
        listTable.filter.keywords = Utils.trim(document.forms['searchForm'].elements['keywords'].value);
        listTable.filter.page = 1;
        listTable.loadList();
    }

    function initProjects()
    {
        var companyId = Utils.trim(document.forms['searchForm'].elements['company_id'].value);
        if(!companyId || companyId == "0"){
            Utils.alert("请先选择一家企业");
            return;
        }
        window.location = "{{helper.createUrl(['p':'salaryproject/init'])}}?company_id=" + companyId;
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
            Utils.alert("请选择要删除的工资项目");
        }else{
            listTable.remove(id,"确认停用并删除选中的工资项目吗？");
        }
    }
</script>
{% endif %}
