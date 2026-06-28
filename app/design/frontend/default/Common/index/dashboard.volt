        <!--第三方插件-->
    <!--滚动条-->
    <script src="/skin/adminhtml/default/libs/nicescroll/jquery.nicescroll.js"></script>
    <!--cookie-->
    <script src="/skin/adminhtml/default/libs/cookie/cookie.min.js"></script>
    <!--日历插件-->
    <script src="/skin/adminhtml/default/libs/laydate/laydate.js"></script>
    <!--表单验证-->
    <script src="/skin/adminhtml/default/libs/Validform/Validform_v5.3.2_min.js" type="text/javascript" charset="utf-8"></script>
    <!--日期插件-->
    <script type="text/javascript" src="/skin/adminhtml/default/libs/laydate/laydate.js" ></script>
    <!--图表-->
    <script type="text/javascript" src="/skin/adminhtml/default/libs/Echarts/echarts.common.min.js" ></script>
        <!--含有full_box这个类的盒子表示整个内容块，暂时无样式，只做标识用-->
        <div class="full_box base_bg table_chart">
            <!--控制台页面布局-->
            <!--table_box这个类来特指这样一种布局：在同一行有两个或两个以上的table表格，表格与表格之间的间距为固定值（默认15px），他们的高度相同。
                那么就将这一行的table盒子都装在table_box这个类当中，子级高度主要由copy_tr_box这个盒子的高度决定，如果要对整个table_box的高度
                进行限制，请注意计算。而他的子级table先要装在一个div盒子中，这个盒子用table_item类，table表格之间的间距是由table_item的
                padding-left指定。而真正的table表格是用含有copy_table类的div盒子进行模拟。结构如下：-->
           
        </div>
            <!--联动菜单-->
    <script src="/skin/adminhtml/default/js/linkage.js" type="text/javascript" charset="utf-8"></script>
    <script src="/skin/adminhtml/default/js/ljk.js"></script>
    <!--图表JS-->
    <script src="/skin/adminhtml/default/js/charts.js" type="text/javascript" charset="utf-8"></script>
    <script>
        window.onload = function(){
            //tab切换
            listFun.tabSwitch({btn:".tab_btn",e:".copy_tab_item"});
            //表格单双行背景色初始化
            listFun.tableBackgroundSpace();
            
            //滚动条优化
            $(".copy_tr_box,.explain_list").niceScroll({cursorcolor:"#ccc"});
            charts.options({
                ele:'main',  //需要生成图表的ID,必填 
//              type:0,//图表类型,默认曲线图，1为柱状图，
                color:['#ffba9f','#86cefa'],//图表颜色
                name:['充值数'],//图例名字
                xAxis:["12-1","12-2","12-3","12-4","12-5","12-6","12-7","12-8","12-9","12-10","12-11","12-12","12-13","12-14","12-15","12-16","12-17","12-18","12-19","12-20","12-21","12-22","12-23","12-24","12-25","12-26","12-27","12-28","12-29","12-30",], //X轴参数
                data:[
                	[5, 20, 36, 10, 10,5, 20, 36, 10, 10,5, 20, 36, 10, 10,5, 20, 36, 10, 10,5, 20, 36, 10, 10,5, 20, 36, 10, 10]//第一组数据
                ]//图表数据
            });
            charts.options({
                ele:'main2',  //需要生成图表的ID,必填 
//              type:0,//图表类型,默认曲线图，1为柱状图，
                color:['#ffba9f','#86cefa'],//图表颜色
                name:['会员注册数'],//图例名字
                xAxis:["12-1","12-2","12-3","12-4","12-5","12-6","12-7","12-8","12-9","12-10","12-11","12-12","12-13"], //X轴参数
                data:[
                [5, 20, 36, 10, 10,5, 20, 36, 10, 10,5, 20, 36]//第一组数据
                ]//图表数据
            }); 
        }
        
    </script>