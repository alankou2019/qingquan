var listFun = {
	/*********************************
	 * 
	 * 点击按钮，实现对应输入框的加减（输入数字的输入框和加减按钮的组件）
	 * 
	 * *********************************/
	minusPlus:function(parameter,call){
		/*******************************
		 * 参数说明：
		 * 
		 * 传入参数：
		 * parameter = {
		 * 		ele为按钮的类;
		 * 		input_num为输入框的类;
		 * 		flag为计算方法，即判断是做加法还是减法
		 * 			1为减，2为加
		 * }
		 * 
		 * 备注:在输入框上，默认写有最小值（data-min）和最大值(data-max)，如果没有这两个属性，那么最小值默认为0，最大值为999999。
		 * ***************************/
		var e = $(parameter.ele);
		var inp = parameter.input_num;
		var f = parameter.flag;
		var min = $(inp).attr("data-min") || 0;
		var max = $(inp).attr("data-max") || 999999;
		
		e.on("click",function(){
			var parBox = $(this).parent().parent();
			var inpE = parBox.find(inp);
			var inpNum = Number(inpE.val());
			//点击获得焦点时，添加类
			parBox.addClass("active");
			//减法逻辑
			if(f == 1){
				if(inpNum > min){
					inpE.val(inpNum-1);
				}else{
					inpE.val(min);
				}
				
			}else if(f == 2){
				//加法逻辑
				if(inpNum < max){
					inpE.val(inpNum+1);
				}else{
					inpE.val(max);
				}
			}
			$(this).addClass("active").siblings().removeClass("active");
			listFun.shopPriceInput(inp);
		})
		
		//点击获得焦点时，添加类
		$(inp).on("click",function(){
			var parBox = $(this).parent();
			parBox.addClass("active");
		})
		
		//失去获得焦点时，删除类
		$(inp).blur("click",function(){
			var parBox = $(this).parent();
			parBox.removeClass("active");
		})
		
		//失去焦点时，盒子无状态
		e.blur(function(){
			var parBox = $(this).parent().parent();
			parBox.removeClass("active");
		})
	},
	/***********************************
	 * 
	 * 监听输入框，其输入必须是数字，最多可以输入小数，而且必须在限定范围内
	 * 
	 * 备注:在输入框上，默认写有最小值（data-min）和最大值(data-max)，如果没有这两个属性，那么最小值默认为0，最大值为999999。
	 * **************************************/
	shopPriceInput:function(e){
		/*******************************
		 * 参数说明：
		 * 
		 * 传入参数：
		 * e 为输入框的类
		 * ***************************/


		var reg = /^[1-9]\d{0,5}(\.\d{1,2})?$/;
		var oldNum = 1;
		var inpNum = 1;
		var min = $(e).attr("data-min") || 0;
		var max = $(e).attr("data-max") || 999999;

		
		$(e).on("blur",function(){
			inpNum = Number($(this).val());
			if($(this).val() == ""){
				$(this).val(1);
			}else if(reg.test($(this).val())){
				if(inpNum < min){
					$(this).val(min);
				}
				if(inpNum > max){
					$(this).val(max);
				}
				oldNum = $(this).val();
			}else{
				$(this).val(oldNum);
			}
			if(inpNum < min){
				$(this).val(min);
			}
			if(inpNum > max){
				$(this).val(max);
			}
		})
		
	},
	/*********************************
	 * tab切换：
	 * parameter = {
	 * 		btn:点击的类,
	 * 		e:切换的块。
	 * }
	 * call为回调函数
	 * 
	 * *********************************/
	tabSwitch:function(parameter,call){
		var btn = parameter.btn;
		var e = parameter.e;
		$(btn).click(function(){
			var num = $(this).index();
			$(this).addClass("active").siblings().removeClass("active");
			$(e).eq(num).addClass("active").siblings().removeClass("active");
			if(typeof(call) === "function"){
				call();
			}
		})
	},
	/*********************************
	 * 
	 * 表格的单双行背景色显示不同，为兼容ie8，因此用js实现
	 * 
	 * *********************************/
	tableBackgroundSpace:function(){
		
		//默认表格模拟的类为copy_table;
		$(".copy_table").each(function(i,item){
			var tr = $(item).find(".copy_tr");
			for(var i = 0;i < tr.length;i ++){
				if(i % 2 == 0){
					tr.eq(i).addClass("one");
				}else{
					tr.eq(i).addClass("two");
				}
			}
		})
	}
}
