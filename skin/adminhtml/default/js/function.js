/**
 * 该函数库需要在之前引入layer.js,http://layer.layui.com/
 */

//导出进度条蒙版索引
var model_index = null;
//--------------------------------------------导出数据start
/*
 * 使用方法
 * data-key 唯一标识，用于生成文件
 * data-url 请求地址
 *<button class="operate" onclick="exportDatat(1,this)" data-key="" data-url=""><i class="iconfont icon-daochuexcel"></i>导出</button>
 */
function exportDatat(page,item)
{
	page = typeof(page) == 'undefined'?1:page;
	var url = $(item).attr('data-url');
	var key = $(item).attr('data-key');
	
	//获取查询参数
	var name = listTable.filter.name ;
	var department_id = listTable.filter.department_id ;
	var start_time = listTable.filter.start_time ;
	var end_time = listTable.filter.end_time ;
	
	//重置进度条
	if(model_index === null){
		resetProgress();
	}

	$.ajax({
		url:url,
		type:'POST',
		data:{'page':page,'key':key,"name":name,"department_id":department_id,"start_time":start_time,"end_time":end_time},
		dataType:'json',
		cache:false,
		timeout:1000*10*6,
		beforeSend:function(){

			if(model_index === null){
				model_index = createProgress();
			}
		},
		success:function(res){
			if(res.status == 'y'){
				if(res.data.stop == 'y'){
					closeIndex(model_index);
					resetProgress();
					//下载
					download(res.data.key,res.data.type,res.data.name);

					layer.alert(res.data.info);
				}else{
					if(res.data.key != key){
						 $(item).attr('data-key',res.data.key);
					}

					startProgress(res.data.progress);
					exportDatat(res.data.page,item);
				}
			}else{
				closeIndex(model_index);
				layer.alert(res.error);
			}
		},
		error:function(){
			closeIndex(model_index);

			layer.alert('网络故障，请稍后再试！');
		}
	});
}



function exportDatattt(page,item,actionname,type)
{
	page = typeof(page) == 'undefined'?1:page;
	var url = $(item).attr('data-url');
	var key = $(item).attr('data-key');

	//重置进度条
	if(model_index === null){
		resetProgress();
	}

	$.ajax({
		url:url,
		type:'POST',
		data:{'page':page,'key':key,'actionname':actionname,'type':type},
		dataType:'json',
		cache:false,
		timeout:1000*10*6,
		beforeSend:function(){

			if(model_index === null){
				model_index = createProgress();
			}
		},
		success:function(res){
			if(res.status == 'y'){
				if(res.data.stop == 'y'){
					closeIndex(model_index);
					resetProgress();
					//下载
					download(res.data.key,res.data.type,res.data.name);

					layer.alert(res.data.info);
				}else{
					if(res.data.key != key){
						 $(item).attr('data-key',res.data.key);
					}

					startProgress(res.data.progress);
					exportDatattt(res.data.page,item,actionname,type);
				}
			}else{
				layer.alert(res.error);
			}
		},
		error:function(){
			closeIndex(model_index);

			layer.alert('网络故障，请稍后再试！');
		}
	});
}



//关闭蒙版
function closeIndex(index)
{
	layer.close(index);

	model_index = null;
}
//创建进度条
function createProgress()
{
	var index = layer.open({
			  type: 1,
			  title:'',
			  closeBtn:0,
			  area:['600px','50px'],
			  content: '<div class="progress"><div class="progress-finishd"></div></div><div class="progress-notice">正在导出数据，请勿关闭！</div>'
		});

		$('.progress').css({
			'height':'3px',
			'background-color':'#ccc',
			'position':'relative',
			'margin':'10px 20px'
		});
		$('.progress .progress-finishd').css({
			'height':'inherit',
			'background-color':'blue',
			'width':'0%',
			'position':'absolute',
			'top':'0px',
			'left':'0px'
		});
		$('.progress-notice').css({
			'text-align':'center',
			'color':'red'
		});

	return index;
}
//执行进度
function startProgress(number)
{
	$('.progress .progress-finishd').css('width',number+'%');
}
//重置进度条
function resetProgress()
{
	$('.progress .progress-finishd').css('width','0%');
}
//下载文件
function download(key,type,name)
{
	window.top.location.href = '/download/list/file?key='+key+'&type='+type+'&name='+name;
}
//--------------------------------------------导出数据end

//ajax加载提示蒙版索引
var load_index  = null;
/*
 * ajax操作
 *
 * 参数说明
 * options = {
 * 		url:'',
 * 		data:'',
 * 		callback:function(res){
 *
 * 		},
 * 		handle:function(){
 * 			//额外处理
 * 		}
 * }
 */
function ajaxHandle(options)
{
	if(typeof(options) != 'object'){
		return;
	}

	$.ajax({
		url:options.url,
		type:'POST',
		data:options.data,
		dataType:'json',
		cache:false,
		timeout:1000*60,
		beforeSend:function(){

			if(load_index === null){
				load_index = createLoading();
			}
		},
		complete:function(){
			closeIndex(load_index);
		},
		success:function(res){

			if(typeof(options.handle) != 'undefined'){
				options.handle();
			}

			if(res.status == 'y'){
				options.callback(res);
			}else{
				layer.alert(res.error);
			}
		},
		error:function(){
			layer.alert('网络故障，请稍后再试！');
		}
	});
}
//创建加载层
function createLoading()
{
	var index = layer.load(0, {shade: [0.3,'#fff']});

	return index;
}
