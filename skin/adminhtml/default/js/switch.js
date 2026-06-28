(function($){
	$.fn.Switch =function  (call) {
		//对页面上的开关组件初始化，如果是开启按钮高亮，则在隐藏域中赋值1，否则赋值0；
		$(this).each(function(){
		var haveOn = $(this).find(".switch_off").hasClass("active");
		if(haveOn){
			$(this).find("input[type=hidden]").val(1);
			$(this).addClass('on');
		}else{
			$(this).find("input[type=hidden]").val(0);
			
		}
		});
		//点击开关时，逻辑处理
		$(".switch_btn").on("click",function(){
			var switchBox = $(this).parent();
			//选中时，修改样式
			switchBox.find(".switch_btn").removeClass("active");
			$(this).addClass("active");
			//点击开启时，用1表示；关闭时用0表示。并将状态值保存在隐藏域中
			if($(this).hasClass("switch_off")){
				switchBox.find("input[type=hidden]").val(1);
			}else{
				switchBox.find("input[type=hidden]").val(0);
			}
			//给开关更换背景颜色
			if(switchBox.find(".switch_off").hasClass("active")){
				switchBox.addClass('on');							
			}else{
				switchBox.removeClass('on');
			}
		})

	}
})(jQuery);
