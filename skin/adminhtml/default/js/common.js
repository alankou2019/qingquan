var tool = {
	/****************************
	 * 输入框在输入内容后，出现清除按钮，点击清空输入框中的内容
	 * 
	 * *****************************/
	inputClear:function(){
		//监听具有input_clear类的输入框，一旦输入内容，就显示清除按钮
		$(".input_clear").each(function(i,item){
			var num = 0;
			var inp = $(item).children("input");
			inp.on("keyup",function(){
				if($(this).val().length > 0){
					//判断num的值，只让按钮添加一次，
					num++;
					if(num <= 1){
						$(this).parent().append('<i class="iconfont icon-unie61a"></i>');
					}
				}
			})
			$(item).delegate('.iconfont','click',function(){
				$(this).parent().find("input").val("");
				$(this).remove();
				num = 0;
			})
		})
	}(),
	/*****************************
	 * 开关组件
	 * 要求页面上结构必须是
	 * <div class="switch_box">
			<input type="hidden" name="" id="" value="" />
			<button type="button" class="switch_btn switch_on active">开启</button>
			<button type="button" class="switch_btn switch_off">关闭</button>
		</div>
		
		如果开启，input中value的值为1；关闭为0；
	 * **************************/
	switchBox:function(){
		//对页面上的开关组件初始化，如果是开启按钮高亮，则在隐藏域中赋值1，否则赋值0；
		var switchBoxes = $(".switch_box");
		if(switchBoxes.find(".switch_on").hasClass("active")){
			switchBoxes.find("input[type=hidden]").val(1);			
		}else{
			switchBoxes.find("input[type=hidden]").val(0);
			switchBoxes.addClass('on');
		}
		//点击开关时，逻辑处理
		$(".switch_btn").on("click",function(){
			var switchBox = $(this).parent();
			//选中时，修改样式
			switchBox.find(".switch_btn").removeClass("active");
			$(this).addClass("active");
			//点击开启时，用1表示；关闭时用0表示。并将状态值保存在隐藏域中
			if($(this).hasClass("switch_on")){
				switchBox.find("input[type=hidden]").val(1);
			}else{
				switchBox.find("input[type=hidden]").val(0);
			}
			//给开关更换背景颜色
			if(switchBoxes.find(".switch_on").hasClass("active")){
				switchBoxes.removeClass('on');			
			}else{
				switchBoxes.addClass('on');
			}
		})
	}
	
}


//tip提示
//使用方法：title提示，只需在元素上加上title就可以了
//图片放大提示，只需在图片加上data-src="放大图片的路径"
$(document).ready(function(){ 

$('[title]').mouseover(function(e) {
	var titles = $(this).attr('title'); //获取title的值
	var pointX = e.pageX;
	var pointY = e.pageY;

		layer.msg(titles,{
		time: 1000,
		offset: [pointY, pointX]
	});

	

});


$('[data-src]').hover(function(e){
	var pointX = e.pageX;
	var pointY = e.pageY;
	var data = $(this).attr('data-src');
	var hoverimg = '<img src="' +data+ '" class="hoverimg"/>';
	 $(document.body).append(hoverimg);
	 $('.hoverimg').css({'position':'absolute','top':pointY,'left':pointX,});
},function () {
	$('img').remove('.hoverimg');
});

});


//监听iframe点击事件，调用方式：
//IframeOnClick.track(document.getElementById("content_iframe"), function() { //代码}); 
var IframeOnClick = {  
    resolution: 200,  
    iframes: [],  
    interval: null,  
    Iframe: function() {  
        this.element = arguments[0];  
        this.cb = arguments[1];   
        this.hasTracked = false;  
    },  
    track: function(element, cb) {  
        this.iframes.push(new this.Iframe(element, cb));  
        if (!this.interval) {  
            var _this = this;  
            this.interval = setInterval(function() { _this.checkClick();}, this.resolution);  
        }  
    },  
    checkClick: function() {  
        if (document.activeElement) {  
            var activeElement = document.activeElement;  
            for (var i in this.iframes) {  
                if (activeElement === this.iframes[i].element) { // user is in this Iframe  
                    if (this.iframes[i].hasTracked == false) {   
                        this.iframes[i].cb.apply(window, []);   
                        this.iframes[i].hasTracked = true;  
                    }  
                } else {  
                    this.iframes[i].hasTracked = false;  
                }  
            }  
        }  
    }  
};  
