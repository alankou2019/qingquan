<script src="/skin/adminhtml/default/js/form.js"></script>
<script type="text/javascript" src="/skin/adminhtml/default/libs/laydate/laydate.js" ></script>
<!--滚动条-->
<script
    src="/skin/adminhtml/default/libs/nicescroll/jquery.nicescroll.js"></script>
<div class="full_box">
    <div class="full_title">

        <span style="float: right; cursor: pointer;"
            onclick="window.location=window.location;"><i
            class="iconfont icon-shuaxin"></i></span> <span class= "name">查看权限</span> <a
            class="go_back" onclick="window.location='{{helper.createUrl(['p':'user/index'])}}';"> <i
            class="iconfont icon-fanhui"></i> <span>返回用户列表</span>
        </a>
    </div>

    <div class="full_cont">
        <!--表单-->
        <form action="{{helper.createUrl(['p':'user/userviewrolesave'])}}" method="post" class="form_full" id="dataForm" name="dataForm" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="{{user_id}}">
            <div class="sub_title">管理权限</div>
            <ul class="list_form_full">

                <li class="posi_lm">
                    <div class="left posi_l ">设置权限:</div>
                    <div class="right posi_m">
                        {% for depart in departList %}
                            <div>
                                {{depart.delimiter}}
                                <input type="checkbox" name="role[]" level="{{depart.level}}"  class="checkboxinput" value="{{depart.id}}"  
                                path="{{depart.path}}"
                                {% if depart.isChecked==1 %}checked="checked"{% endif %} >
                                {{depart.name}}
                            </div>
                        {% endfor %}
                    </div>
                </li>
            </ul>

            <div class="online_btn_box">
                <button type="reset" class="f_btn">取消</button>
                <button type="button" class="f_btn active" id="btnSubmit">保存</button>
            </div>
        </form>

    </div>

    <div class="panel-body mt-10">
       
    </div>
           
</div>
<script src="/skin/adminhtml/default/js/common.js"></script>
<script src="/skin/adminhtml/default/js/ljk.js"></script>
<script src="/skin/adminhtml/default/js/form.js"></script>
<script>
$(function() {
    //滚动条优化
    $("html").niceScroll({
        cursorcolor : "#ccc"
    });
    Utils.validate("#dataForm","#btnSubmit",function(curform){
           $("#btnSubmit").attr('disabled','disabled');
           $("#btnSubmit").html('处理中..');       
           return true;
    });     
    {% if item %}
        var formObj = new Form('dataForm');
        formObj.init({{item|json_encode}});
    {% endif %}
});
$('.checkboxinput').click(function(){
	var  level=$(this).attr('level');
	
	console.log(level);
})

//多选框点击事件
$('.checkboxinput').click(function(){
    var inputlen=$('.checkboxinput').length;
    var current=$(this).attr('path').split('_').pop();
    var isChecked=$(this).is(':checked');
    if(isChecked){
        //下级所有的都选中
        for(var i=0;i<inputlen;i++){
            var obj=$('.checkboxinput').eq(i);
            var path=obj.attr('path').split('_');
            if($.inArray(current,path) >= 0){
                obj.prop('checked',true);
            }
        }
    }else{
        //下级所有的都取消
        for(var i=0;i<inputlen;i++){
            var obj=$('.checkboxinput').eq(i);
            var path=obj.attr('path').split('_');
            if($.inArray(current,path) >= 0){
                obj.attr('checked',false);
            }
        }
    }
})
</script>