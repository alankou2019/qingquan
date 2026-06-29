<script src="/skin/adminhtml/default/js/form.js"></script>
<script src="/skin/adminhtml/default/libs/nicescroll/jquery.nicescroll.js"></script>
<div class="full_box">
    <div class="full_title">
        <span style="float: right; cursor: pointer;" onclick="window.location=window.location;"><i class="iconfont icon-shuaxin"></i></span>
        <span class="name">{% if item %}编辑{% else %}新增{% endif %}工资项目</span>
        <a class="go_back" onclick="window.location='{{helper.createUrl(['p':'salaryproject/index'])}}';">
            <i class="iconfont icon-fanhui"></i> <span>返回工资项目列表</span>
        </a>
    </div>

    <div class="full_cont">
        <form action="{{helper.createUrl(['p':'salaryproject/save'])}}" method="post" class="form_full" id="dataForm" name="dataForm">
            <input type="hidden" name="id" value="">
            <div class="sub_title">工资项目信息</div>
            <ul class="list_form_full">
                <li class="posi_lm">
                    <div class="left posi_l must">所属企业:</div>
                    <div class="right posi_m">
                        <select name="company_id" class="select_name" datatype="n" errormsg="请选择企业">
                            <option value="0">请选择企业</option>
                            {% for company in companies %}
                            <option value="{{company.id}}">{{company.name}}</option>
                            {% endfor %}
                        </select>
                    </div>
                </li>
                <li class="posi_lm">
                    <div class="left posi_l must">项目名称:</div>
                    <div class="right posi_m">
                        <div class="input_clear">
                            <input type="text" name="name" datatype="*1-80" value="" maxlength="80" autocomplete="off"/>
                        </div>
                    </div>
                </li>
                <li class="posi_lm">
                    <div class="left posi_l">项目类型:</div>
                    <div class="right posi_m">
                        <select name="source_type" class="select_name">
                            {% for key,label in sourceTypes %}
                            <option value="{{key}}">{{label}}</option>
                            {% endfor %}
                        </select>
                    </div>
                </li>
                <li class="posi_lm">
                    <div class="left posi_l">加减方向:</div>
                    <div class="right posi_m">
                        <select name="direction" class="select_name">
                            {% for key,label in directions %}
                            <option value="{{key}}">{{label}}</option>
                            {% endfor %}
                        </select>
                    </div>
                </li>
                <li class="posi_lm">
                    <div class="left posi_l">计算方式:</div>
                    <div class="right posi_m">
                        <select name="calculation_mode" class="select_name">
                            {% for key,label in calculationModes %}
                            <option value="{{key}}">{{label}}</option>
                            {% endfor %}
                        </select>
                    </div>
                </li>
                <li class="posi_lm">
                    <div class="left posi_l">关联模块:</div>
                    <div class="right posi_m">
                        <div class="input_clear">
                            <input type="text" name="linked_module" value="none" maxlength="30" autocomplete="off"/>
                            <small class="help-block prompt_box"><i class="fa fa-times-circle-o"></i>没有关联模块时填写 none，例如 performance、commission 可留作后续扩展。</small>
                        </div>
                    </div>
                </li>
                <li class="posi_lm">
                    <div class="left posi_l">公式说明:</div>
                    <div class="right posi_m">
                        <div class="input_clear">
                            <textarea name="formula_text" maxlength="500"></textarea>
                        </div>
                    </div>
                </li>
                <li class="posi_lm">
                    <div class="left posi_l">计入应发:</div>
                    <div class="right">
                        <div class="txt">
                            <input type="radio" name="include_earning" value="1">是&nbsp;&nbsp;
                            <input type="radio" name="include_earning" value="0" checked="checked">否
                        </div>
                    </div>
                </li>
                <li class="posi_lm">
                    <div class="left posi_l">计入应扣:</div>
                    <div class="right">
                        <div class="txt">
                            <input type="radio" name="include_deduction" value="1">是&nbsp;&nbsp;
                            <input type="radio" name="include_deduction" value="0" checked="checked">否
                        </div>
                    </div>
                </li>
                <li class="posi_lm">
                    <div class="left posi_l">计入实发:</div>
                    <div class="right">
                        <div class="txt">
                            <input type="radio" name="include_net" value="1" checked="checked">是&nbsp;&nbsp;
                            <input type="radio" name="include_net" value="0">否
                        </div>
                    </div>
                </li>
                <li class="posi_lm">
                    <div class="left posi_l">排序:</div>
                    <div class="right posi_m">
                        <div class="input_clear">
                            <input type="text" name="sort_order" value="0" maxlength="10" autocomplete="off"/>
                        </div>
                    </div>
                </li>
                <li class="posi_lm">
                    <div class="left posi_l">状态:</div>
                    <div class="right posi_m">
                        <select name="status" class="select_name">
                            {% for key,label in statusLabels %}
                            <option value="{{key}}">{{label}}</option>
                            {% endfor %}
                        </select>
                    </div>
                </li>
            </ul>

            <div class="online_btn_box">
                <button type="reset" class="f_btn">重置</button>
                <button type="button" class="f_btn active" id="btnSubmit">确认</button>
            </div>
        </form>
    </div>
</div>
<script src="/skin/adminhtml/default/js/common.js" type="text/javascript" charset="utf-8"></script>
<script src="/skin/adminhtml/default/js/check.js" type="text/javascript" charset="utf-8"></script>
<script src="/skin/adminhtml/default/js/ljk.js"></script>
<script src="/skin/adminhtml/default/js/form.js"></script>
<script>
    $(function() {
        $("html").niceScroll({
            cursorcolor : "#ccc"
        });
        Utils.validate("#dataForm","#btnSubmit",function(curform){
            $("#btnSubmit").attr('disabled','disabled');
            $("#btnSubmit").html('处理中...');
            return true;
        });

        {% if item %}
        var formObj = new Form('dataForm');
        formObj.init({{item|json_encode}});
        {% endif %}
    });
</script>
