/********************************
 * 多级联动菜单
 * 
 * *****************************/

(function ($) {
    $.fn.extend({
        "more": function (params,call) {
        	//数据对象
			var def = {
				selects: [],            // 菜单的各级盒子元素，依次为一级，二级...
			    str: [],				// 各级菜单标签字符串，要将数据放在哪个标签里，这里可以通过传字符串来实现，依次为一级，二级...
			    data: null,             // 所有数据
			    defaultData: [],		// 默认数据，即页面呈现的数据。当存有cookie时，展示cookie的值，否则默认第一个值。
			    storageData:[],			// 需要存储的数据，数组里的每个元素都是一个数组，分别对应各级菜单。
			    jsonName: 'n',          // 数据标题字段名称
			    jsonSub: 's',           // 子集数据字段名称
			    on:'on',				// 高亮显示的类
			    replace:""				// 字符串替换变量
			};
			//回调函数对象
			var call_def = {
				begin:null,				// 页面数据渲染完成时的回调函数
				on:null					// 点击菜单选项时的回调函数
			}
			
			var option = $.extend(def,params);
			
			var selects = option.selects;
			var strs = option.str;
			var data = option.data;
			var de = option.defaultData;
			var sData = option.storageData;
			var _n = option.jsonName;
			var _s = option.jsonSub;
			var on = option.on;
			var rp = option.replace;
			
			//菜单层级记录变量
			var rend_num = 0;
			//菜单深度，即共有几级菜单
			var num = selects.length;
			
			//回调函数
			var calls = $.extend(call_def,call);
			var begin = calls.begin;
			var oncall = calls.on;
			
			//判断是否传入了各级菜单盒子元素，各级标签字符串，以及数据，如果有任何一个值没有传，函数无法工作，将终止函数。
			if(selects.length <=0){
				console.log("请输入各级菜单盒子的id！");
				return ;
			}else if(strs.length <= 0){
				console.log("请输入各级菜单标签字符串！");
				return ;
			}else if(data == null ){
				console.log("请输入数据！");
				return ;
			}
			
			//判断浏览器是否存有cookie，当存有cookie时，展示cookie的值，否则默认第一个值。
			if(de.length == 0){
				for(var i = 0;i < selects.length;i ++){
					de[i] = 0;
				}
			}
			
			//调用函数，将内容呈现在页面上
			rendering(data);
			//调用函数，显示高亮
			highlight();
			//调用函数，绑定点击切换事件
			bindSelect();
			//回调函数
			if(typeof begin === "function"){
				console.log(de);
				begin(de);
			}
			
			
			//渲染菜单函数
			function rendering(arr){
				//首次传进来的json数据，是最原始的数据，调用菜单内容渲染函数，将渲染出第一级菜单
				getStr(arr,rend_num);
				//菜单层级增加，进入第二层，即第二级菜单
				rend_num ++;
				//在默认显示数据de数组中取出第一级默认的菜单:de[rend_num]。然后在arr中取出数据，arr[de[rend_num]]。由于这里要一层一层往里去读取数据，
				//所以这里用了递归函数。当arr[de[rend_num]]存在时才读取数据，否则就终止。
				if(_s in arr[de[rend_num-1]]){
					//这里用de[rend_num-1]而不是de[rend_num]是因为这里的调用是在渲染第二级菜单了。那么要获取的数据是一级菜单高亮的内容，即de数组中保存
					//的默认值，而数组de中一级菜单高亮的序号是保存在第一个的，因此这里应该是de[rend_num-1]而不是de[rend_num]，否则的话，取到的就是第二
					//级菜单的高亮序号了。
					rendering(arr[de[rend_num-1]][_s]);
				}
			}
			
			//添加高亮显示的类
			function highlight(){
				for(var i = 0;i < num;i ++){
					//各级菜单元素selects[i]盒子，找到是第几个（de[i]）元素为高亮显示，然后添加类即可
					$(selects[i]).children().eq(de[i]).addClass(on);
				}
			}
			
			//绑定事件函数(给每个菜单项绑定一个切换菜单的点击事件)
			function bindSelect(){
				//给每个菜单项添加一个绑定事件
				for(var i = 0; i < num;i ++){
					//记录菜单盒子的序号，即此菜单是第几级菜单
					$(selects[i]).data("_i_",i);
					//找到菜单盒子的子级，即菜单项的标签名称。
					var tagName = $(selects[i]).children()[0].tagName;
					
					//绑定事件
					$(selects[i]).delegate(tagName,"click",function(){
						//获取层级
						var id = $(this).parent().data("_i_");
						//获取当前菜单序号
						var index = $(this).index();
						//将当前点击的项的序号放入默认数组de中
						de[id] = index;
						//将序号为id之后的de数组里的值都变为0，即默认第一项
						for(var j = 0;j < de.length;j ++){
							if(j > id){
								de[j] = 0;
							}
						}
						//将菜单层级记录变量重置为0，重新渲染整个菜单体系
						rend_num = 0;
						rendering(data);
						highlight();

						//回调函数
						if(typeof oncall === "function"){
							console.log(de);
							oncall(de);
						}
					});
				}
			}
			
			/********************
			 *菜单内容渲染函数
			 * 
			 *c表示层级数，即表示第几级菜单（为方便与数组序号对应，一级菜单序号为0，二级菜单序号为1，依次类推），
			 * arr表示当前层的标题数组
			***********************/
			var d;
			function getStr(arr,c){
				//当前层标题数组长度
				var num = arr.length;
				//存放标题内容的数组，
				var ns = [];
				//得到当前层标签字符串
				var str = strs[c];
				//存放标签字符串，最后将放到菜单盒子元素中进行渲染呈现在页面上
				var s = "";
				//存放标签字符串，表示一个标签元素的内容
				var strings = "";
				//需要存储的对应属性数组
				var sd = sData[c];
				
				//将数据放入标签字符串。
				for(var i = 0;i < num;i++){
					//取出每个标题的数据内容
					ns = arr[i][_n];
					//计数变量，从标题数据的第一个数据开始
					var m = 0;
					
					//调用替换函数，将数据存放到标签字符串中
					strings = re(str,m,ns.length,ns);
					//strings表示一个标签元素的内容，通过循环，将所有的数据加起来存放到s中。
					s += strings;
				}
				//将数据插入当前级元素盒子中
				$(selects[c]).html(s);
				
				//将一些需要用到但不是文本显示的数据放到标签上，存放在data-*里
//				console.log(sd);
				if(!sd){
					return ;
				}
				$(selects[c]).children().each(function(i,item){
					for(var j = 0;j < sd.length;j ++){
						$(item).attr("data-"+sd[j],arr[i][sd[j]]);
					}
				})
			}
			
			/********************
			 * 替换函数（依次将变量用数据替换，变量默认为str）
			 * 
			 * s表示要替换的字符串，
			 * m表示要替换的内容的数组的数据序号
			 * num表示总共要替换的数据的个数
			 * ns表示要替换的内容的数组
			***********************/
			function re(s,m,num,ns){
				//用数据替换变量
				s = s.replace(rp,ns[m]);
				//m增大，直到将数据完全替换完成为止。
				m ++;
				//m < num则表示数据还没有替换完，故继续调用
				if(m < num){
					//为使得能够返回最终调用的结果，这里的调用直接写在return 里，否则无法得到数据。
					return re(s,m,num,ns);
				}else{
					//数据替换完成，返回最终结果
					return s;
				}
			}
        }
    });
})(jQuery);
