<div class="warp" id="screen">
	<form action="{{helper.createUrl(['p':'bs/searchlist'])}}" id="searchfrom" method="post">
		<input type="hidden"name="departId" id="hiddendepartId"/>
		<input type="hidden"name="stime" id="hiddenstime"/>
		<input type="hidden"name="etime" id="hiddenetime"/>
	  	<div class="mui-content has_footer">
			<div class="input_box">
				<input type="text" name="name" placeholder="请输入姓名" id="name"/>
			</div>
			<ul class="screen_ul">
				<li class="clear">
					<div class="fl">
						部门
					</div>
					<div class="fr">
						<span id="department">请选择</span>
						<i class="mui-icon mui-icon-arrowright"></i>
					</div>
				</li>
			</ul>
			<ul class="screen_ul">
				<li class="clear">
					<div class="fl">
						开始时间
					</div>
					<div class="fr">
						<span data-options='{"type":"date","beginYear":{{year-2}},"endYear":{{year}}}' class="btns" id="stime">{{searchtime}}</span>
						<i class="mui-icon mui-icon-arrowright"></i>
					</div>
				</li>
				<li class="clear">
					<div class="fl">
						结束时间
					</div>
					<div class="fr">
						<span data-options='{"type":"date","beginYear":{{year-2}},"endYear":{{year}}}' class="btns" id="etime">{{searchtime}}</span>
						<i class="mui-icon mui-icon-arrowright"></i>
					</div>
				</li>
			</ul>
		</div>
		<footer class="footer_sub_btn">
			<input  class="sub_btn subbutton" value="提交" readonly="readonly" onclick="searchsubmit()"/>
		</footer>
	</form>
</div>
<script type="text/javascript">
	(function($) {
		$.init();
		//部门选择
		if({{departjson}} != ''){
			var userPicker = new $.PopPicker();
			userPicker.setData({{departjson}});
			var department = document.getElementById('department');
			department.addEventListener('tap', function(event) {
				userPicker.show(function(items) {
					department.innerText = items[0].text;
				});
			}, false);
		}else{
			var parent = document.getElementById('department').parentNode.parentNode.parentNode.remove();
		}
		
		//日期选择
		var btns = $('.btns');
		btns.each(function(i, btn) {
			btn.addEventListener('tap', function() {
				var optionsJson = this.getAttribute('data-options') || '{}';
				var options = JSON.parse(optionsJson);
				var id = this.getAttribute('id');
				var that = this;
				/*
				 * 首次显示时实例化组件
				 * 示例为了简洁，将 options 放在了按钮的 dom 上
				 * 也可以直接通过代码声明 optinos 用于实例化 DtPicker
				 */
				var picker = new $.DtPicker(options);
				picker.show(function(rs) {
					/*
					 * rs.value 拼合后的 value
					 * rs.text 拼合后的 text
					 * rs.y 年，可以通过 rs.y.vaue 和 rs.y.text 获取值和文本
					 * rs.m 月，用法同年
					 * rs.d 日，用法同年
					 * rs.h 时，用法同年
					 * rs.i 分（minutes 的第二个字母），用法同年
					 */
					that.innerText = rs.text;
					/* 
					 * 返回 false 可以阻止选择框的关闭
					 * return false;
					 */
					/*
					 * 释放组件资源，释放后将将不能再操作组件
					 * 通常情况下，不需要示放组件，new DtPicker(options) 后，可以一直使用。
					 * 当前示例，因为内容较多，如不进行资原释放，在某些设备上会较慢。
					 * 所以每次用完便立即调用 dispose 进行释放，下次用时再创建新实例。
					 */
					picker.dispose();
				});
			}, false);
		});
	})(mui);
	
	
	//提交查询
	function searchsubmit()
	{
		var name     = $.trim($('#name').val());
		var departId = $.trim($('#department').html());
		var stime    = $.trim($('#stime').html());
		var etime    = $.trim($('#etime').html());
		
		if(departId==undefined || departId=='请选择'){
			tanceng('请选择部门'); return false ;
		}
		if(stime=='' || stime==undefined){
			tanceng('请选择开始时间'); return false ;
		}
		if(etime=='' || etime==undefined){
			tanceng('请选择结束时间'); return false ;
		}
		
		$('#hiddendepartId').val(departId);
		$('#hiddenstime').val(stime);
		$('#hiddenetime').val(etime);
		$('#searchfrom').submit() ;
		
	}
</script>

