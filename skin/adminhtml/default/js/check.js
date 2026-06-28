(function($){
	$.fn.CheckBox =function  (call) {
		$(this).each(function(){
			var hasOn = $(this).hasClass('on');				
			if(hasOn){
				$(this).find('input').prop("checked",true); 
			}
		})
		$(this).click(function (event) {
			var type = $(this).find('input').attr("type");
			var isCheck = $(this).find('input').prop("checked");
			if (type=="radio") {
				if($(this).hasClass('radio_check')){
					$(this).addClass('on').siblings().removeClass('on');
				}else{
					$(this).find(".radio_check").addClass('on');
					$(this).siblings().find(".radio_check").removeClass('on');
				}
				$(this).find('input').prop("checked",true).siblings().prop("checked",false);
			} else{	
				if(!isCheck){
					$(this).addClass('on');
					$(this).find('input').prop("checked",true);
				}
				else{
					$(this).removeClass('on');
					$(this).find('input').prop("checked",false);
				}
			}
			if(typeof(call) === "function"){
				call($(this));
			}
//			event.stopPropagation();
		});
		
		$(this).find("input").click(function(event){
//			event.stopPropagation();
		})

	}
})(jQuery);
