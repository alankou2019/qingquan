			<script src="/skin/adminhtml/default/js/check.js" type="text/javascript" charset="utf-8"></script>
		<script src="/skin/adminhtml/default/libs/layer/layer.js" type="text/javascript" charset="utf-8"></script>
    <!--权限分配-->  <form action="{{helper.createUrl(['p':'adminrole/save'])}}" method="post" class="form_full" id="dataForm" name="dataForm" enctype="multipart/form-data">
    	<input type="hidden" value="" name="id" />
		<div class="allocation full_box">
      
			<div class="full_title">

		<span style="float: right; cursor: pointer;"
			onclick="window.location=window.location;"><i
			class="iconfont icon-shuaxin"></i></span> <span class="name">{% if item %}编辑{% else %}新增{% endif %}角色</span> <a
			class="go_back" onclick="window.location='{{helper.createUrl(['p':'adminrole/index'])}}';"> <i
			class="iconfont icon-fanhui"></i> <span>返回角色列表</span>
		</a>

	</div>
			<div class="role_name">
				<label>角色名称：</label>
				<input type="text" class="name"  name="name" datatype="*2-16"  value="" errormsg="角色名称至少2个字符,最多16个字符！" maxlength="16"/>
			</div>
			
			<div class="list_box">
				<div class="head">
					<span class="name">会员管理：</span>
					<label class="radio_check check_all"><input type="checkbox" name="radio_check"/>全选</label>
				</div>
				<div class="list">
					<ul>
						<li><label class="radio_check"><input type="checkbox" name="role[]" value="c"/>[会员]会员列表</label></li>
						<li><label class="radio_check"><input type="checkbox" name="role[]" value="d"/>[会员]会员删除</label></li>
					</ul>
				</div>
			</div>
			
			<div class="list_box">
				<div class="head">
					<span class="name">商品管理：</span>
					<label class="radio_check check_all"><input type="checkbox" name="radio_check"/>全选</label>
				</div>
				<div class="list">
					<ul>
						<li><label class="radio_check"><input type="checkbox" name="role[]" value="a"/>[会员]会员列表</label></li>
						<li><label class="radio_check"><input type="checkbox" name="role[]" value="b"/>[会员]会员删除</label></li>
				
					</ul>
				</div>
			</div>
            
            <div class="online_btn_box">
				<button type="reset" class="f_btn">重置</button>
				<button type="button" class="f_btn active" id="btnSubmit">确认</button>
			</div>
            
          
		</div>  </form>
		<!--权限分配-->
        <script src="/skin/adminhtml/default/js/form.js"></script>
       	<script>
		(function() {
			
		Utils.validate("#dataForm","#btnSubmit",function(curform){
			   $("#btnSubmit").attr('disabled','disabled');
		       $("#btnSubmit").html('处理中..');       
		       return true;
		});
		
		{% if item %}
		var formObj = new Form('dataForm');
		formObj.init({{item|json_encode}});
		{% endif %}
			
			//单选框美化
		$(".radio_check").CheckBox();
		//全选
		$(".check_all").click(function () {
			var labels = $(this).parent().parent().find('.radio_check');
				//因为选框美化的时候点击会加上on,所以这里是有on就选中
				if($(this).hasClass("on")){
					labels.addClass('on');
					labels.children().prop("checked",true);					
				}
				else{
					labels.removeClass('on');
					labels.children().prop("checked",false);
				}
		})
		//如果一个未选中，就取消全选
		$(".list .radio_check").on('click',function () {			
			var ck_length = $(this).parent().parent().find('.radio_check').length;//选框个数
			var on_ck = $(this).parent().parent().find('.radio_check.on').length;//选中个数
			var all_ck = $(this).parent().parent().parent().parent().find('.check_all');
			if (ck_length==on_ck) {//如果选框个数等于选中个数，就把全选选中，反之就把全选取消
				all_ck.addClass('on');
				all_ck.prop("checked",true);
			} else{
				all_ck.removeClass('on');
				all_ck.prop("checked",false);
			}
		})
		}())
	</script>