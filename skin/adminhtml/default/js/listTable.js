if (typeof Utils != 'object')
{
  alert('Utils object doesn\'t exists.');
}

var listTable = new Object;

listTable.callback   = null;
listTable.isclose    = true;
listTable.closeIndex = '';
listTable.query = "query";
listTable.filter = new Object;
listTable.url = location.href.lastIndexOf("?") == -1 ? location.href.substring((location.href.lastIndexOf("/")) + 1) : location.href.substring((location.href.lastIndexOf("/")) + 1, location.href.lastIndexOf("?"));
listTable.url += "?is_ajax=1";


listTable.init  = function(callback)
{
	 listTable.createPage();
}


listTable.createPage = function()
{
	if(listTable.pageCount>0){
		 $("#recordCount").html(listTable.recordCount);
		 $("#recordPageCount").html(listTable.pageCount);
		 $("#listtable_page").empty();		 
		 $("#listtable_page").pagination(listTable.recordCount, {
			callback: function(page,jq){
				if(page == listTable.currentPage-1)return false;
				listTable.filter.page = page+1;
				listTable.loadList();
			},
			current_page:listTable.currentPage-1,
			items_per_page:listTable.pageSize,
			prev_text:"上一页",
			next_text:"下一页",
			num_edge_entries: 2,
            num_display_entries: 4
		 });

	}else{
		$("#listtable_page").html("");
	}
	if(listTable.recordCount<1)
	{
		var length = $("#listTable .table_box .table_head th").length;
		length = length +1;
		var html = '<tr><td colspan="'+length+'" class="name"><span class="txt" style="text-align:center">暂无数据!</span></td></tr>';
		$("#listTable .table_box .table_head").after(html);
	}
}

/**
 * 创建一个可编辑区
 */
listTable.edit = function(obj, act, id)
{
  var tag = obj.firstChild.tagName;

  if (typeof(tag) != "undefined" && tag.toLowerCase() == "input")
  {
    return;
  }

  /* 保存原始的内容 */
  var org = obj.innerHTML;
  var val = Browser.isIE ? obj.innerText : obj.textContent;

  /* 创建一个输入框 */
  var txt = document.createElement("INPUT");
  txt.value = (val == 'N/A' || val == 'undefined') ? '' : val;
  txt.style.width = (obj.offsetWidth + 12) + "px" ;

  /* 隐藏对象中的内容，并将输入框加入到对象中 */
  obj.innerHTML = "";
  obj.appendChild(txt);
  txt.focus();

  /* 编辑区输入事件处理函数 */
  txt.onkeypress = function(e)
  {
    var evt = Utils.fixEvent(e);
    var obj = Utils.srcElement(e);

    if (evt.keyCode == 13)
    {
      obj.blur();

      return false;
    }

    if (evt.keyCode == 27)
    {
      obj.parentNode.innerHTML = org;
    }
  }

  /* 编辑区失去焦点的处理函数 */
  txt.onblur = function(e)
  {
    if (Utils.trim(txt.value).length > 0)
    {
    	var value = encodeURIComponent(Utils.trim(txt.value));
		$.ajax({
			  type: 'POST',
			  url:listTable.url,
			  data: "act="+act+"&val=" + (value==undefined?'':value) + "&id=" +id,
			  success: function(res){
				  if (res.status=='n')
				  {
					alert(res.msg);
				  }
				  obj.innerHTML = (res.status == 'y') ? res.data : org;
			  },
			  dataType: 'JSON'
		});
    }
    else
    {
      obj.innerHTML = org;
    }
  }
}

/**
 * 切换状态
 */
listTable.toggle = function(obj, act, id)
{
    var val = (obj.src.match(/yes.png/i)) ? 0 : 1;
	var baseUrl = obj.src.replace('yes.png','');
	 baseUrl = baseUrl.replace('no.png','');
	$.ajax({
		  type: 'POST',
		  url: this.url,
		  data: "act="+act+"&val=" + val + "&id=" +id,
		  success: function(res){
			  if (res.msg)
			  {
				alert(res.msg);
			  }

			  if (res.status == 'y')
			  {
				baseUrl +=  (res.data > 0) ? 'yes.png' : 'no.png';
				obj.src = baseUrl;
			  }
		  },
		  dataType: 'JSON'
	});
}

/**
 * 切换排序方式
 */
listTable.sort = function(sort_by, sort_order,obj)
{
  var args = "act="+this.query+"&sort_by="+sort_by+"&sort_order=";
  var sort_order = 'DESC';
  
  if (this.filter.sort_by == sort_by)
  {
     sort_order =  this.filter.sort_order == "DESC" ? "ASC" : "DESC";
  }
  
  args += sort_order;
  for (var i in this.filter)
  {
    if (typeof(this.filter[i]) != "function" &&
      i != "sort_order" && i != "sort_by" && !Utils.isEmpty(this.filter[i]))
    {
      args += "&" + i + "=" + this.filter[i];
    }
  }

  this.filter['page_size'] = this.getPageSize(); 
  $.ajax({
	  type: 'POST',
	  url: this.url,
	  data: args,
	  success: this.listCallback,
	  dataType: 'JSON'
  });
}

/**
 * 翻页
 */
listTable.gotoPage = function(page)
{
  if (page != null) this.filter['page'] = page;

  if (this.filter['page'] > this.pageCount) this.filter['page'] = 1;

  this.filter['page_size'] = this.getPageSize();

  this.loadList();
}

/**
 * 载入列表
 */
listTable.loadList = function()
{
  var args = "act="+this.query+"" + this.compileFilter();
  listTable.closeIndex = Utils.load();
  $.ajax({
	  type: 'POST',
	  url: this.url,
	  data: args,
	  success: this.listCallback,
	  dataType: 'JSON'
  });
}

/**
 * 删除列表中的一个记录
 */
listTable.remove = function(id, cfm, opt,isclose)
{
	listTable.isclose = isclose == undefined ? true :isclose;	
	
  if (opt == null)
  {
    opt = "remove";
  }

  if(cfm){
	  if (confirm(cfm))
	  {
			var args = "act=" + opt + "&id=" + id + this.compileFilter();
			$.ajax({
				  type: 'GET',
				  url: this.url,
				  data: args,
				  success: this.listCallback,
				  dataType: 'JSON'
			});
	  }
  }else{
		var args = "act=" + opt + "&id=" + id + this.compileFilter();
		$.ajax({
			  type: 'GET',
			  url: this.url,
			  data: args,
			  success: this.listCallback,
			  dataType: 'JSON'
		});
  }
  
}

/**
 * 修改列表中的一个记录的状态
 */
listTable.update = function(id, cfm, opt,isclose)
{
  listTable.isclose = isclose == undefined ? true :isclose; 
  
  if (opt == null)
  {
    opt = "update";
  }

  if(cfm){
    if (confirm(cfm))
    {
      var args = "act=" + opt + "&id=" + id + this.compileFilter();
      $.ajax({
          type: 'GET',
          url: this.url,
          data: args,
          success: this.listCallback,
          dataType: 'JSON'
      });
    }
  }else{
    var args = "act=" + opt + "&id=" + id + this.compileFilter();
    $.ajax({
        type: 'GET',
        url: this.url,
        data: args,
        success: this.listCallback,
        dataType: 'JSON'
    });
  }
  
}

listTable.gotoPageFirst = function()
{
  if (this.filter.page > 1)
  {
    listTable.gotoPage(1);
  }
}

listTable.gotoPagePrev = function()
{
  if (this.filter.page > 1)
  {
    listTable.gotoPage(this.filter.page - 1);
  }
}

listTable.gotoPageNext = function()
{
  if (this.filter.page < listTable.pageCount)
  {
    listTable.gotoPage(parseInt(this.filter.page) + 1);
  }
}

listTable.gotoPageLast = function()
{
  if (this.filter.page < listTable.pageCount)
  {
    listTable.gotoPage(listTable.pageCount);
  }
}

listTable.changePageSize = function(e)
{
    var evt = Utils.fixEvent(e);
    if (evt.keyCode == 13)
    {
        listTable.gotoPage();
        return false;
    };
}

listTable.listCallback = function(result, txt,isclose)
{
	
  if(listTable.closeIndex != ''){
	  if(listTable.isclose) Utils.closeIndex(listTable.closeIndex);
  }else{
	  if(listTable.isclose) Utils.close();
  }
  if (result.status !='y')
  {
    alert(result.msg);
  }
  else
  {
    try
    {
      document.getElementById('listTable').innerHTML = result.data.content;

      if (typeof result.data.filter == "object")
      {
        listTable.filter = result.data.filter;
      }

      listTable.pageCount =  result.data.pageCount;
	  listTable.recordCount = result.data.count;
	  listTable.currentPage = result.data.currentPage;
	  if(typeof result.data.extended == 'object'){
		  for(var key  in result.data.extended){
			  $("#listtable_"+key).html(result.data.extended[key]);
		  }
	  }
	  if(listTable.callback != null){
		  eval(listTable.callback+'()');
	  }
	  $(".table_box").colResizable();
	  listTable.createPage();
	  
	  settingHandle();
	  if($(".table_box",listTable.frameObj).length > 0){
		  $(".table_box",listTable.frameObj).colResizable();
	  }
	  if($(".radio_check",listTable.frameObj).length > 0){
		  $(".radio_check",listTable.frameObj).CheckBox();
	  }

	  
    }
    catch (e)
    {
      alert(e.message);
    }
  }
  changecolor();
}

listTable.selectAll = function(obj, chk,callback)
{
  if (chk == null)
  {
    chk = 'checkboxes';
  }

  var elems = obj.form.getElementsByTagName("INPUT");

  for (var i=0; i < elems.length; i++)
  {
    if (elems[i].name == chk || elems[i].name == chk + "[]")
    {
      elems[i].checked = obj.checked;
    }
  }
  if(callback != undefined){
	  eval(callback+'("'+chk+'")');
  }
}

listTable.compileFilter = function()
{
  var args = '';
  for (var i in this.filter)
  {
    if (typeof(this.filter[i]) != "function" && typeof(this.filter[i]) != "undefined")
    {
      args += "&" + i + "=" + encodeURIComponent(this.filter[i]);
    }
  }

  return args;
}

listTable.getPageSize = function()
{
  var ps = 15;

  pageSize = document.getElementById("pageSize");

  if (pageSize)
  {
    ps = Utils.isInt(pageSize.value) ? pageSize.value : 15;
    document.cookie = "ECSCP[page_size]=" + ps + ";";
  }
}

listTable.addRow = function(checkFunc)
{
  cleanWhitespace(document.getElementById("listDiv"));
  var table = document.getElementById("listDiv").childNodes[0];
  var firstRow = table.rows[0];
  var newRow = table.insertRow(-1);
  newRow.align = "center";
  var items = new Object();
  for(var i=0; i < firstRow.cells.length;i++) {
    var cel = firstRow.cells[i];
    var celName = cel.getAttribute("name");
    var newCel = newRow.insertCell(-1);
    if (!cel.getAttribute("ReadOnly") && cel.getAttribute("Type")=="TextBox")
    {
      items[celName] = document.createElement("input");
      items[celName].type  = "text";
      items[celName].style.width = "50px";
      items[celName].onkeypress = function(e)
      {
        var evt = Utils.fixEvent(e);
        var obj = Utils.srcElement(e);

        if (evt.keyCode == 13)
        {
          listTable.saveFunc();
        }
      }
      newCel.appendChild(items[celName]);
    }
    if (cel.getAttribute("Type") == "Button")
    {
      var saveBtn   = document.createElement("input");
      saveBtn.type  = "image";
      saveBtn.src = "./images/icon_add.gif";
      saveBtn.value = save;
      newCel.appendChild(saveBtn);
      this.saveFunc = function()
      {
        if (checkFunc)
        {
          if (!checkFunc(items))
          {
            return false;
          }
        }
        var str = "act=add";
        for(var key in items)
        {
          if (typeof(items[key]) != "function")
          {
            str += "&" + key + "=" + items[key].value;
          }
        }
        res = Ajax.call(listTable.url, str, null, "POST", "JSON", false);
        if (res.error)
        {
          alert(res.message);
          table.deleteRow(table.rows.length-1);
          items = null;
        }
        else
        {
          document.getElementById("listDiv").innerHTML = res.content;
          if (document.getElementById("listDiv").childNodes[0].rows.length < 6)
          {
             listTable.addRow(checkFunc);
          }
          items = null;
        }
      }
      saveBtn.onclick = this.saveFunc;

      //var delBtn   = document.createElement("input");
      //delBtn.type  = "image";
      //delBtn.src = "./images/no.gif";
      //delBtn.value = cancel;
      //newCel.appendChild(delBtn);
    }
  }

}
