<script src="/skin/adminhtml/default/js/form.js"></script>
<div class="full_box">
    <div class="full_title">
        <span class="name">企业微信部署设置</span>
        <a class="go_back" onclick="window.location='{{helper.createUrl(['p':'company/index'])}}';">
            <i class="iconfont icon-fanhui"></i><span>返回公司列表</span>
        </a>
    </div>
    <div class="full_cont">
        <form action="{{helper.createUrl(['p':'wecom/save'])}}" method="post" class="form_full" id="wecomForm">
            <div class="sub_title">选择企业</div>
            <ul class="list_form_full">
                <li class="posi_lm">
                    <div class="left posi_l must">企业:</div>
                    <div class="right posi_m">
                        <select name="company_id" onchange="window.location='{{helper.createUrl(['p':'wecom/index'])}}?company_id='+this.value">
                            <option value="">请选择企业</option>
                            {% for company in companies %}
                                <option value="{{company.id}}" {% if company_id == company.id %}selected="selected"{% endif %}>{{company.name}}</option>
                            {% endfor %}
                        </select>
                    </div>
                </li>
            </ul>

            {% if company_id %}
            <div class="sub_title">自建应用参数</div>
            <ul class="list_form_full">
                <li class="posi_lm"><div class="left posi_l must">CorpID:</div><div class="right posi_m"><div class="input_clear"><input type="text" name="corp_id" value="{% if integration %}{{integration.corp_id}}{% endif %}" maxlength="128"/></div></div></li>
                <li class="posi_lm"><div class="left posi_l must">AgentID:</div><div class="right posi_m"><div class="input_clear"><input type="text" name="agent_id" value="{% if integration %}{{integration.agent_id}}{% endif %}" maxlength="64"/></div></div></li>
                <li class="posi_lm"><div class="left posi_l must">Secret:</div><div class="right posi_m"><div class="input_clear"><input type="password" name="secret" value="" autocomplete="new-password"/><small class="help-block prompt_box">{% if integration %}已保存；留空表示不修改{% else %}首次配置必须填写{% endif %}</small></div></div></li>
                <li class="posi_lm"><div class="left posi_l">回调Token:</div><div class="right posi_m"><div class="input_clear"><input type="text" name="callback_token" value="{% if integration %}{{integration.callback_token}}{% endif %}" maxlength="255"/></div></div></li>
                <li class="posi_lm"><div class="left posi_l">EncodingAESKey:</div><div class="right posi_m"><div class="input_clear"><input type="text" name="encoding_aes_key" value="{% if integration %}{{integration.encoding_aes_key}}{% endif %}" maxlength="255"/></div></div></li>
                <li class="posi_lm">
                    <div class="left posi_l">启用状态:</div>
                    <div class="right"><input type="radio" name="enabled" value="0" {% if not integration or integration.enabled == 0 %}checked="checked"{% endif %}/>关闭&nbsp;&nbsp;<input type="radio" name="enabled" value="1" {% if integration and integration.enabled == 1 %}checked="checked"{% endif %}/>启用</div>
                </li>
                <li class="posi_lm"><div class="left posi_l">企业识别码:</div><div class="right posi_m"><div class="input_clear">{{company_hash_key}}</div></div></li>
                <li class="posi_lm"><div class="left posi_l">工作台主页:</div><div class="right posi_m"><div class="input_clear"><input type="text" readonly="readonly" value="{{wecom_base_url}}/wecom/entry/{{company_hash_key}}" onclick="this.select()"/></div></div></li>
                <li class="posi_lm"><div class="left posi_l">事件回调:</div><div class="right posi_m"><div class="input_clear"><input type="text" readonly="readonly" value="{{wecom_base_url}}/wecom/event/{{company_hash_key}}" onclick="this.select()"/></div></div></li>
                {% if integration %}
                <li class="posi_lm"><div class="left posi_l">最近同步:</div><div class="right posi_m">{% if integration.last_sync_at %}{{helper.formatDateTime(integration.last_sync_at)}}{% else %}尚未同步{% endif %}</div></li>
                <li class="posi_lm"><div class="left posi_l">最近错误:</div><div class="right posi_m">{{integration.last_error}}</div></li>
                {% endif %}
            </ul>
            <div class="form_btn">
                <button type="submit" class="btn1">保存配置</button>
                {% if integration and integration.enabled == 1 %}
                    <button type="button" class="btn1" onclick="testWecom()">测试连接</button>
                    <button type="button" class="btn1" onclick="syncWecom()">同步通讯录</button>
                {% endif %}
            </div>
            {% endif %}
        </form>
    </div>
</div>

<script>
function callWecom(action, successText) {
    $.ajax({
        type: 'POST',
        url: action,
        data: {company_id: '{{company_id}}'},
        dataType: 'json',
        success: function(res) {
            if (res.status === 'y') {
                alert(successText + (res.data ? '\n' + JSON.stringify(res.data) : ''));
                window.location.reload();
            } else {
                alert(res.error || '操作失败');
            }
        },
        error: function() { alert('网络请求失败'); }
    });
}
function testWecom() { callWecom('{{helper.createUrl(['p':'wecom/test'])}}', '连接成功'); }
function syncWecom() { callWecom('{{helper.createUrl(['p':'wecom/sync'])}}', '同步完成'); }
</script>
