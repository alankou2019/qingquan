 <script type="text/javascript" src="http://g.alicdn.com/dingding/open-develop/1.5.1/dingtalk.js"></script>
 <script type="text/javascript">
   function closePage()
   {
	   dd.biz.navigation.close({
  		  onSuccess : function(result) {
   		 },
   			 onFail : function(err) {}
		});
   }
   
   function showMsg(msg,type)
   {
	   if(typeof(type)=='undefined')
	   {
		   type = '';
	   }else if(type==true)
	   {
		  type = 'success';  
	   }else
	   {
		   type = 'error';  
	   }
	   
	   dd.device.notification.toast({
		icon: type, //icon样式，有success和error，默认为空 0.0.2
		text: msg, //提示信息
		onSuccess : function(result) {
			/*{}*/
		},
         onFail : function(err) {}
      });
   }
   
  </script>
  
 <script type="text/javascript">

 {% if jsconfig['is_new']==0 %}
   dd.config({
    agentId: '{{jsconfig["agentId"]}}', // 必填，微应用ID
    corpId: '{{jsconfig["corpId"]}}',//必填，企业ID
    timeStamp:'{{jsconfig["timeStamp"]}}' , // 必填，生成签名的时间戳
    nonceStr: '{{jsconfig["nonceStr"]}}', // 必填，生成签名的随机串
    signature: '{{jsconfig["signature"]}}', // 必填，签名
    type:0,   //选填。0表示微应用的jsapi,1表示服务窗的jsapi。不填默认为0。该参数从dingtalk.js的0.8.3版本开始支持
    jsApiList : [ 'runtime.info', 'biz.contact.choose',
        'device.notification.confirm', 'device.notification.alert',
        'device.notification.prompt', 'biz.ding.post','device.base.getUUID',
        'biz.util.openLink','biz.contact.choose','biz.contact.departmentsPicker','biz.contact.complexPicker'] // 必填，需要使用的jsapi列表，注意：不要带dd。
	});
 {% endif %}
	dd.ready(function(){
		 layer.open({
			type: 2
			,content: '登录中.....'
 		 });
		dd.runtime.permission.requestAuthCode({
			corpId: "{{jsconfig["true_corpId"]}}",
			onSuccess: function(result) {
				 $.ajax({
					 url:"{{helper.createUrl(['p':'dding/info'])}}",
					 data:"code="+result.code,
					 type:"POST",
					 dataType:"json",
					 success: function(res){
						 if(res.status=='y')
						 {
							 if(res.data.user_id<1)
							 {
								showMsg('账号未初始化,请联系管理员操作!');
								closePage();

							 }else
							 {
								//window.location="{{helper.createUrl(['p':'bs/newindex'])}}";
								window.location='{{callbackUrl}}';
							 }
						 }else
						 {
						 	 showMsg('授权失败!!!!');
							 closePage();
						 }
					 },
					 error: function(res){
						 	showMsg(JSON.stringify(err));
							closePage();
					 }
				 });
		    },
		    onFail : function(err) {
		    	showMsg(JSON.stringify(err));
				layer.closeAll();
				closePage();
			}
		});
	});

	dd.error(function(error){
		showMsg('授权失败!');
		closePage();
	});
	
  </script>