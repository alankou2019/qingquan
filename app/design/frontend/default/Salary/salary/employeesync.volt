<style>
.salary_panel{padding:18px;}
.salary_notice{border:1px solid #d9e2ef;background:#fbfdff;padding:14px 16px;margin-bottom:14px;line-height:24px;color:#475569;}
.salary_grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;}
.salary_sync_item{border:1px solid #d9e2ef;background:#fff;padding:14px;min-height:118px;}
.salary_sync_item.active{border-color:#4560e6;background:#f8fbff;}
.salary_sync_item h3{font-size:15px;color:#1f2937;margin:0 0 8px 0;}
.salary_sync_item p{color:#64748b;line-height:22px;margin:0 0 12px 0;}
.salary_tag{display:inline-block;background:#eef2ff;color:#3949ab;padding:0 8px;line-height:22px;margin-bottom:8px;}
.salary_btn{display:inline-block;background:#4560e6;color:#fff;padding:0 16px;line-height:30px;height:30px;text-decoration:none;}
.salary_block{margin-top:18px;}
.salary_block h3{font-size:15px;color:#1f2937;margin:0 0 10px 0;}
.salary_table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #d9e2ef;}
.salary_table th{background:#f8fafc;color:#334155;font-weight:normal;text-align:left;padding:9px;border-bottom:1px solid #d9e2ef;}
.salary_table td{padding:9px;border-bottom:1px solid #edf2f7;color:#475569;}
.salary_empty{padding:18px;color:#94a3b8;text-align:center;}
</style>
<div class="full_box">
	<div class="head_tab clear">
		<ul>
			<li class="on"><a href="#">员工同步/导入</a></li>
			<li style="float:right;width:140px;border-left:1px solid #efefef;border-right:0;"><a href="{{helper.createUrl(['p':'salary/index'])}}">返回薪酬首页</a></li>
		</ul>
	</div>
	<div class="salary_panel">
		<div class="salary_notice">
			当前企业通讯平台：{{platformName}}
		</div>
		<div class="salary_grid">
			{% for code,item in syncItems %}
			<div class="salary_sync_item {% if code==platform %}active{% endif %}">
				<h3>{{item['name']}}</h3>
				<span class="salary_tag">{{item['status']}}</span>
				<p>{{item['desc']}}</p>
				<a class="salary_btn" href="{{item['url']}}">{{item['action']}}</a>
			</div>
			{% endfor %}
		</div>
		<div class="salary_block">
			<h3>当前员工</h3>
			<table class="salary_table">
				<tr>
					<th width="80">ID</th>
					<th width="180">姓名</th>
					<th>部门</th>
					<th width="100">管理员</th>
					<th width="100">负责人</th>
				</tr>
				{% for item in userItems %}
				<tr>
					<td>{{item['id']}}</td>
					<td>{{item['name']}}</td>
					<td>{{item['departmentname']}}</td>
					<td>{% if item['is_admin'] %}是{% else %}否{% endif %}</td>
					<td>{% if item['is_leader'] %}是{% else %}否{% endif %}</td>
				</tr>
				{% elsefor %}
				<tr><td colspan="5" class="salary_empty">暂无员工数据</td></tr>
				{% endfor %}
			</table>
		</div>
	</div>
</div>
