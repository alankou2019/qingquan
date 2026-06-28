/**
 * 页面加载完成过后运行的js
 */
//设置完成状态的字体颜色
var len = $('.statustext').length ;
for(var i=0;i<len;i++)
{
    var statustext = $('.statustext').eq(i).html() ;
    
    if(statustext == '未完成' || statustext=='审核中' || statustext=='未审核'){
        $('.statustext').eq(i).addClass('red') ;
    }
    if (statustext == '完成' || statustext == '已完成' || statustext=='已通过' || statustext=='已审') {
    	$('.statustext').eq(i).addClass('blue');
	}
}

//改变颜色
function changecolor()
{
	var len = $('.statustext').length ;
	for(var i=0;i<len;i++)
	{
	    var statustext = $('.statustext').eq(i).html() ;
	    if(statustext == '未完成' || statustext=='审核中' || statustext=='未审核'){
	        $('.statustext').eq(i).addClass('red') ;
	    }
	    if (statustext == '完成' || statustext == '已完成' || statustext=='已通过' || statustext=='已审') {
	    	$('.statustext').eq(i).addClass('blue');
		}
	}
}