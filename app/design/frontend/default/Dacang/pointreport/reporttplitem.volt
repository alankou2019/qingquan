<style>
.table_box td,.table_box th{
	text-align:center
}
</style>
<table border="1"  width="100%" class="table_box">
    <!--head部分-->
   	<tr>
    	<th width="20%">指标名称</th>
    	<th>评分标准</th>
    	<th>权重</th>
    	<th width="30%">评分人</th>
  	</tr>
   	<!--head部分-->
   	<!--列表部分-->
   	<tbody class="reporttr">
   	{% for item in dataList.items %}
   		<tr>
	    	<td>
	    		<span>{{helper.substr(item.quota_name,0,15)}}</span> 
	    		<!--指标id-->
	    		<input type="hidden" name='quotaids[]' value='{{item.quota_id}}'/>
	    		
	    	</td>
	    	<td>
	    		<!--评分标准-->
	    		<span  class="txt" title="{{item.point_desc}}">{{helper.substr(item.point_desc,0,15)}}</span>
	    	</td>
	    	<td>
                {% if item.quota_type == helper.getExtratype() %}
                    <span>加减分不参与总权重的计算</span>
                    <input type='hidden' name='quotavalue[]'  value='{{item.quota_value}}' readonly='readonly'/>
                {% else %}
                    <!--权重-->
                    <input type="text" name='quotavalue[]' value='{{item.quota_value}}' class="avgpoint" readonly='readonly' />
                {% endif %}
            </td>
	    	<td>
	    		<span>{{item.report_user_name}}</span> 
	    		<!--指标评分人-->
	    		<input type="hidden" name='reportuserid[]' value='{{item.report_user_id}}'/>
	    		<!--指标权重-->
	    		<input type="hidden" name='reportuserweight[]' value='{{item.quota_total}}'/>
	    		
	    	</td>
	  	</tr>
   	{% endfor %}
   	</tbody>
   	<!--列表部分-->
</table>
