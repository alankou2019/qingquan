{% for item in dataList.items %}
   <li>
       <a href="{{helper.createUrl(['p':'bs/storesdetail','id':item.reportId,'uid':item.id,'sid':item.sid])}}" class="clear">
           <img src="{{item.avatar}}" class="header_img fl" alt="{{item.name}}" onerror="this.src='/favicon.ico'"/>
           <div class="fl user_msg">
               <div class="name">
                   {{item.name}}
               </div>  
               <div>
                 	{{item.dname}}
               </div>
           </div>
           <div class="fr time">
               {{helper.formatDateTime(item.createdtime,'Y.m.d')}}
           </div>
       </a>
   </li>
{% endfor %}                   