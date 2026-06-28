$(function() {
	settingHandle();
})

function settingHandle()
{
	//表单操作隐藏显示
	$(".table_box .handle").hover(function () {
		$(this).animate({'width':'175px'},0,function () {
			$(this).addClass('on');
			$(this).find('.list').show();
		});
		$(this).find('.sanjiao').removeClass('icon-sanjiao');
		$(this).find('.sanjiao').addClass('icon-yousanjiao');
		
	},function () {
		$(this).removeClass('on');
		$(this).find('.list').hide();
		$(this).animate({'width':'80px'},0);
		$(this).find('.sanjiao').removeClass('icon-yousanjiao');
		$(this).find('.sanjiao').addClass('icon-sanjiao');
	});
	
	//全选
	$(".ck_all").click(function () {
		//因为选框美化的时候点击会加上on,所以这里是有on就选中
		if($(this).hasClass("on")){
			$('.radio_check').addClass('on');
			$(".radio_check").children().prop("checked",true);					
		}
		else{
			$('.radio_check').removeClass('on');
			$(".radio_check").children().prop("checked",false);
		}
	})
	//高级搜索隐藏显示
	$(".advanced_btn").click(function () {
		$(".advanced_search").toggle(500);
	})
	$(".advanced_search .close").click(function () {
		$(".advanced_search").hide(500);
	})
}