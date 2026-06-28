var charts = {

	options: function(params) {
		
		//实例化对象
		var ele = params.ele;//需要生成图表的ID
		var type = params.type ? params.type:0;//图表类型,默认曲线图，1为柱状图
		var color = params.color;//图表颜色
		var title = params.title;//图表标题
		var xAxis = params.xAxis;//X轴参数
		var data = params.data;//图表数据
		var name = params.name;//图例名字
		//初始化参数
		types = ['line','bar'];	
		params.title = null;//默认标题为空
		
		// 基于准备好的dom，初始化echarts实例
        var myChart = echarts.init(document.getElementById(ele));

        // 指定图表的配置项和数据
        var option = {
            tooltip: {//提示框组件
            	type: 'showTip',
		        trigger: 'axis'
		    },
            legend: {//图例组件
                data:name
            },
            color:color,
            grid: {
            	show:true,
		        left: '20px',
		        right: '20px',
		        containLabel: true
		    },
            xAxis: {
            	boundaryGap: false,
                data: xAxis,
            },
            yAxis: {},
            series: [
            {
                name: name[0],
                type:types[type],
                splitArea: {
                	show:true
                },
                data: data[0]
            },
            {
                name: name[1],
                type: types[type],
                data: data[1]
            },
            ]
        };

        // 使用刚指定的配置项和数据显示图表。
        myChart.setOption(option);

	}
}
