!function($,Mysli){"use strict";function getToken(){return null}{var token=getToken();$("#dashboard")}token||Mysli.Dashboard.Login.show(),Mysli.Dashboard.start=function(token){$.get("navigation?token="+token,function(response){console.log(response)})}}(Zepto,Mysli);