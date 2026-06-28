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
            <th><span>评分用户</span></th>
            <th><span>评分时间</span></th>
            <th><span>评分</span></th>
            <th><span>缘由</span></th>
            <th style="width: 100px;"><span>状态</span></th>
        </tr>
        <!--head部分-->
        <!--列表部分-->
        {% for item in dataList.items %}
            <tr>        
                <td class="name">
                    <span class="txt">{{item.name}}</span>
                </td>
                <td class="name">
                    <span class="txt">{{helper.formatDateTime(item.report_time)}}</span>
                </td>
                <td class="name">
                    <span class="txt">{{item.report_point}}</span>
                </td>
                <td class="name">
                    <span class="txt">{{item.reason}}</span>
                </td>
                <td>
                    <span class="txt">
                        <div id="div1"  class="open1">
                            <div id="div2" class="open2"  detailid="{{item.id}}"></div>
                        </div>
                    </span>
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
    <script src="/skin/adminhtml/default/js/afterload.js"></script>
    <script>
        function upstat(obj)
        {
            var option=0;
            if($(obj).hasClass('close2')){
                $(obj).removeClass('close2');
                $(obj).addClass('open2');
                $(obj).parent().removeClass('close1');
                $(obj).parent().addClass('open1');
                option=1;
            }
            else{
                $(obj).removeClass('open2');
                $(obj).addClass('close2');
                $(obj).parent().removeClass('open1');
                $(obj).parent().addClass('close1');
            }
            var id=$(obj).attr('detailid');
            $.ajax({
                type: "POST",
                url: "{{helper.createUrl(['p':'pointreport/upstat'])}}",
                data: {"id":id,"option":option},
                dataType: "json",
                success: function(res){
                   console.log('success');
                }
           });
        }
        
    </script>
{% endif %}