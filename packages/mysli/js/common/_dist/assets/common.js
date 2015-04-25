mysli="object"==typeof mysli?mysli:{},mysli.js="object"==typeof mysli.js?mysli.js:{},mysli.js.ui="object"==typeof mysli.js.ui?mysli.js.ui:{},mysli.js.common=function($){"use strict";if("undefined"==typeof $)throw new Error("mysli.js.common requires jQuery.");return{merge:function(defaults,options){return"object"!=typeof options&&(options={}),$.extend({},defaults,options)},has_own_property:{}.hasOwnProperty,number_format:function(number,decimals,dec_point,thousands_sep){var n,prec,s,to_fixed_fix;return null===dec_point&&(dec_point="."),null===thousands_sep&&(thousands_sep=","),number=(number+"").replace(/[^0-9+\-Ee.]/g,""),n=isFinite(+number)?+number:0,prec=isFinite(+decimals)?Math.abs(decimals):0,s="",to_fixed_fix=function(n,prec){var k;return k=Math.pow(10,prec),""+Math.round(n*k)/k},s=prec?to_fixed_fix(n,prec):""+Math.round(n),s=s.split("."),s[0].length>3&&(s[0]=s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g,thousands_sep)),(s[1]||"").length<prec&&(s[1]=s[1]||"",s[1]+=new Array(prec-s[1].length+1).join("0")),s.join(dec_point)},get_percent:function(amount,total,percision){var count;return"number"==typeof amount&&"number"==typeof total?(percision=percision||2,amount&&total?(count=amount/total,count=100*count,count=parseFloat(this.number_format(count,percision))):amount):!1},set_percent:function(percent,total,percision){var result;return"number"==typeof percent&&"number"==typeof total?(percision=percision||2,percent&&total?result=parseFloat(this.number_format(total/100*percent,percision)):0):!1}}}(jQuery),mysli.js.common.arr=function(){"use strict";var arr=function(){this.stack={},this.ids=[]};return arr.prototype={construct:arr,push:function(id,element){return this.stack[id]=element,this.ids.push(id),this.ids.length-1},push_after:function(after_id,id,element){var index_to=this.get_index(after_id)+1;return this.stack[id]=element,this.ids.splice(index_to,0,id),index_to},remove:function(id){delete this.stack[id],this.ids.splice(this.get_index(id),1)},get_index:function(id){if("function"==typeof this.ids.indexOf)return this.ids.indexOf(id);for(var i=this.ids.length-1;i>=0;i--)if(this.ids[i]===id)return i},get_index_from:function(id,step){return id=this.get_index(id),id!==!1&&id>0?id+step:void 0},get:function(id){return"undefined"!=typeof this.stack[id]?this.stack[id]:!1},get_from:function(id,step){return id=this.get_index_from(id,step),id!==!1?this.get(this.ids[id]):!1},count:function(){return this.ids.length},get_last:function(){return this.stack[this.ids[this.ids.length-1]]},each:function(callback){for(var r,i=0;i<this.ids.length;i++)if(r=callback(i,this.stack[this.ids[i]]),"undefined"!=typeof r)return r},each_after:function(id,callback){for(var r,i=this.get_index(id)+1;i<this.ids.length;i++)if(r=callback(i,this.stack[this.ids[i]]),"undefined"!=typeof r)return r},each_before:function(id,callback){for(var r,i=0;i<this.get_index(id);i++)if(r=callback(i,this.stack[this.ids[i]]),"undefined"!=typeof r)return r}},arr}();