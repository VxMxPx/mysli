var Mysli=Mysli||{};Mysli.UI={},function($,MU){"use strict";var Button=function(element){this.element="object"==typeof element?element:$(element),this.label=this.element.find("span"),this.label.length||(this.element.html("<span>"+this.element.text()+"</span>"),this.label=this.element.find("span")),this.properties={originalContent:{content:"",disabled:!1}}};Button.prototype={constructor:Button,on:function(evnt,call){return this.element.on(evnt,call)},off:function(evnt){return this.element.off(evnt)},disabled:function(state){return"undefined"==typeof state?(state=this.element.attr("disabled"),"true"===state||"disabled"===state):void(state?this.element.attr("disabled",!0):this.element.removeAttr("disabled"))},pressed:function(state){return"undefined"==typeof state?this.element.hasClass("pressed"):void(state?this.element.addClass("pressed"):this.element.removeClass("pressed"))},style:function(variant){var classes="alt primary attention";if("undefined"==typeof variant){for(var i=classes.split(" ").length-1;i>=0;i--)if(this.element.hasClass(classes[i]))return classes[i];return"default"}this.element.removeClass(classes),"default"!==variant&&this.element.addClass(variant)},busy:function(state,label){if("undefined"==typeof state)return this.element.hasClass("busy");if(state){if(this.busy())return;this.element.addClass("busy"),this.properties.originalContent.content=this.element.html(),this.properties.originalContent.disabled=this.disabled(),this.element.html(" "+(label?label:this.label.text())),this.icon("spinner","left",!0),this.disabled(!0)}else{if(!this.busy())return;this.element.removeClass("busy"),this.element.html(this.properties.originalContent.content),this.disabled(this.properties.originalContent.disabled)}},icon:function(name,position,spin){var icon=this.element.find("i");return"undefined"==typeof name?icon.length?icon.attr("class").match(/fa-([a-z\-]+)/)[1]:!1:(icon.remove(),void(name&&this.element["right"===position?"append":"prepend"]("<i></i>").find("i").removeClass().addClass("fa fa-"+name+(spin?" fa-spin":""))))}},MU.Button=Button}(Zepto,Mysli.UI),function($,MU){"use strict";function getGeometery(parent,padding){return{top:parent.offset().top-padding,left:parent.offset().left-padding,width:parent.width()+2*padding,height:parent.height()+2*padding}}var count=1,Overlay=function(parent,options){var that=this;this.options=$.extend({},{loading:!1,text:null,padding:0,canClose:!1,onClick:!1,id:null,classes:[]},options),this.element=$('<div class="overlay" />'),parent?this.parent=$(parent):(this.options.classes.push("expanded"),this.parent=!1),this.options.text&&this.element.append("<div class=text>"+this.options.text+"</div>"),this.options.loading&&(this.options.classes.push("loading"),this.element.append('<i class="fa spinner fa-spinner fa-spin"></i>')),this.options.id||(this.options.id="mu-overlay-"+count),this.element.attr("id",this.options.id),this.options.classes.length&&this.element.addClass(this.options.classes.join(" ")),this.options.canClose&&this.element.on("click",that.hide),this.isVisible=!1,this.isAppended=!1,this.resize={timer:!1,event:!1},count+=1};Overlay.prototype={constructor:Overlay,onClick:function(callback){return"function"==typeof callback?this.element.on("click.callback",callback):this.element.unbind("click.callback"),this},update:function(animated,geometry){!geometry&&this.parent&&(geometry=getGeometery(this.parent,this.options.padding)),"object"==typeof geometry&&this.element[animated?"animate":"css"]({top:geometry.top,left:geometry.left,width:geometry.width,height:geometry.height})},show:function(geometry){var that=this;(!this.parent||this.parent.is(":visible"))&&(this.isVisible||(!geometry&&this.parent&&(geometry=getGeometery(this.parent,this.options.padding)),"object"==typeof geometry&&this.element.css({display:"none",top:geometry.top,left:geometry.left,width:geometry.width,height:geometry.height,position:"absolute"}),this.parent&&(this.resize.event=$(window).on("resize",function(){that.resize.timer&&clearTimeout(that.resize.timer),that.resize.timer=setTimeout(function(){that.update(!1),console.log("Fire!!")},200)})),"function"==typeof this.options.onClick&&(this.onClick=this.options.onClick),this.isVisible=!0,this.isAppended||(this.element.appendTo("body"),this.isAppended=!0),this.element.fadeIn()))},hide:function(){this.isVisible=!1,this.element.fadeOut(),this.resize.event&&($(window).off(this.resize.event),this.resize.event=!1)}},MU.Overlay=Overlay}(Zepto,Mysli.UI),function(MU){"use strict";var Aarray=function(){this.stack={},this.idsStack=[]};Aarray.prototype={construct:Aarray,push:function(id,element){return this.stack[id]=element,this.idsStack.push(id),this.idsStack.length-1},pushAfter:function(afterId,id,element){var indexTo=this.getIndex(afterId)+1;return this.stack[id]=element,this.idsStack.splice(indexTo,0,id),indexTo},remove:function(id){delete this.stack[id],this.idsStack.splice(this.getIndex(id),1)},getIndex:function(id){if("function"==typeof this.idsStack.indexOf)return this.idsStack.indexOf(id);for(var i=this.idsStack.length-1;i>=0;i--)if(this.idsStack[i]===id)return i},getIndexFrom:function(id,step){return id=this.getIndex(id),id!==!1&&id>0?id+step:void 0},get:function(id){return void 0!==typeof this.stack[id]?this.stack[id]:!1},getFrom:function(id,step){return id=this.getIndexFrom(id,step),id!==!1?this.get(this.idsStack[id]):!1},count:function(){return this.idsStack.length},getLast:function(){return this.stack[this.idsStack[this.idsStack.length-1]]},each:function(callback){for(var r,i=0;i<this.idsStack.length;i++)if(r=callback(i,this.stack[this.idsStack[i]]),void 0!==r)return r},eachAfter:function(id,callback){for(var r,i=this.getIndex(id)+1;i<this.idsStack.length;i++)if(r=callback(i,this.stack[this.idsStack[i]]),void 0!==r)return r},eachBefore:function(id,callback){for(var r,i=0;i<this.getIndex(id);i++)if(r=callback(i,this.stack[this.idsStack[i]]),void 0!==r)return r}},MU.Aarray=Aarray}(Mysli.UI),function(MU){"use strict";MU.Calc={numberFormat:function(number,decimals,dec_point,thousands_sep){number=(number+"").replace(/[^0-9+\-Ee.]/g,"");var n=isFinite(+number)?+number:0,prec=isFinite(+decimals)?Math.abs(decimals):0,sep="undefined"==typeof thousands_sep?",":thousands_sep,dec="undefined"==typeof dec_point?".":dec_point,s="",toFixedFix=function(n,prec){var k=Math.pow(10,prec);return""+Math.round(n*k)/k};return s=(prec?toFixedFix(n,prec):""+Math.round(n)).split("."),s[0].length>3&&(s[0]=s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g,sep)),(s[1]||"").length<prec&&(s[1]=s[1]||"",s[1]+=new Array(prec-s[1].length+1).join("0")),s.join(dec)},getPercent:function(amount,total,percision){var count;return"number"==typeof amount&&"number"==typeof total?(percision=percision||2,amount&&total?(count=amount/total,count=100*count,count=parseFloat(this.numberFormat(count,percision))):amount):!1},setPercent:function(percent,total,percision){return"number"==typeof percent&&"number"==typeof total?(percision=percision||2,percent&&total?parseFloat(this.numberFormat(total/100*percent,percision)):0):!1}}}(Mysli.UI),function($,MU){"use strict";function calculateTargetPosition(target){var result={top:{},left:{}};if("undefined"!=typeof target.pageX)result={top:{min:target.pageY,max:target.pageY},left:{min:target.pageX,max:target.pageX}};else if("function"==typeof target.offset)result={top:{min:target.offset().top,max:target.offset().top+target.height()},left:{min:target.offset().left,max:target.offset().left+target.width()}};else if("number"==typeof target.top)result={top:{min:target.top,max:target.top},left:{min:target.left,max:target.left}};else{if("object"!=typeof target.top)throw new Error("Invalid target!");result=target}return result.top.mid=result.top.min+(result.top.max-result.top.min)/2,result.left.mid=result.left.min+(result.left.max-result.left.min)/2,result}function getMidPosition(result,where,targetPosition,elementDimension,elementDimensionHalf,parentDimension,spacing){var diff={min:0,max:0}.result={};return diff.min=targetPosition-elementDimensionHalf,diff.max=parentDimension-(targetPosition+elementDimensionHalf),-diff.min>elementDimensionHalf-8||-diff.max>elementDimensionHalf-8?!1:diff.min>=spacing?(diff.max<=spacing?(result.pointerDiff=-diff.max,result[where]=parentDimension-spacing-elementDimension):result[where]=diff.min,!0):targetPosition+elementDimensionHalf-diff.min<parentDimension?(result[where]=spacing,result.pointerDiff=diff.min,!0):void 0}function getElementPlacement(placementOrder,targetPosition,parentDimension,elementDimension){for(var result={pointerDiff:0},targetSpacing=10,i=0,len=placementOrder.length;len>i;i++)switch(placementOrder[i]){case"top":if(result.position="down",targetPosition.top.min-elementDimension.height-2*targetSpacing>=0&&(result.top=targetPosition.top.min-elementDimension.height-targetSpacing,getMidPosition(result,"left",targetPosition.left.mid,elementDimension.width,Math.round(elementDimension.width/2),parentDimension.width,targetSpacing)))return result;continue;case"bottom":if(result.position="up",targetPosition.top.max+elementDimension.height+2*targetSpacing<parentDimension.height&&(result.top=targetPosition.top.max+targetSpacing,getMidPosition(result,"left",targetPosition.left.mid,elementDimension.width,Math.round(elementDimension.width/2),parentDimension.width,targetSpacing)))return result;continue;case"left":if(result.position="right",targetPosition.left.min-elementDimension.width-2*targetSpacing>=0&&(result.left=targetPosition.left.min-elementDimension.width-targetSpacing,getMidPosition(result,"top",targetPosition.top.mid,elementDimension.height,Math.round(elementDimension.height/2),parentDimension.height,targetSpacing)))return result;continue;case"right":if(result.position="left",targetPosition.left.max+elementDimension.width+2*targetSpacing<parentDimension.width&&(result.left=targetPosition.left.max+targetSpacing,getMidPosition(result,"top",targetPosition.top.mid,elementDimension.height,Math.round(elementDimension.height/2),parentDimension.height,targetSpacing)))return result;continue}}var Popup=function(options){options=options||{},"string"==typeof options.position&&(options.position=[options.position,"bottom","top","left","right"]),this.properties=$.extend({},{spaced:!0,position:["bottom","top","left","right"],content:null,pointerSpace:-8,visible:!1,style:null,trigger:null,event:"click",toggle:!0,delay:0,selfClose:!0,sticky:!1},options);var delayTimer=null;if(this.element=$('<div class="popup point" style="display:none;"><div class="pointer" /><div class="contents" /></div>'),this.element.appendTo("body"),this.properties.content&&this.content(this.properties.content),this.properties.style&&this.style(this.properties.style),this.spaced(this.properties.spaced),this.properties.visible&&this.show(),this.properties.selfClose?this.element.on("click",this.hide.bind(this)):this.element.on("click",function(e){e.stopPropagation()}),!this.sticky&&this.properties.trigger&&$(document).on("click",function(){this.properties.visible&&this.hide()}.bind(this)),this.properties.trigger&&this.properties.event)if("click"===this.properties.event)this.properties.trigger.on("click",function(e){return e.preventDefault(),e.stopPropagation(),this.properties.visible?void(this.properties.toggle&&this.hide()):void(this.properties.delay?(clearTimeout(delayTimer),delayTimer=setTimeout(function(){this.show()}.bind(this),this.properties.delay)):this.show())}.bind(this));else{if("mouseover"!==this.properties.event)throw new Error('Unsupported event. Allowed are: "click" and "mouseover".');this.properties.trigger.on("mouseover",function(){this.properties.visible||(this.properties.delay?(clearTimeout(delayTimer),delayTimer=setTimeout(function(){this.show()}.bind(this),this.properties.delay)):this.show())}.bind(this)),this.properties.toggle&&this.properties.trigger.on("mouseout",function(){this.properties.delay?(clearTimeout(delayTimer),delayTimer=setTimeout(function(){this.hide()}.bind(this),this.properties.delay)):this.hide()}.bind(this))}};Popup.prototype={constructor:Popup,updatePosition:function(targetPosition){var elementPlacement,pointerPosition,elementDimension={width:this.element.width(),height:this.element.height()},windowDimension={width:$(window).width(),height:$(window).height()+$(window).scrollTop()};elementPlacement=getElementPlacement(this.properties.position,calculateTargetPosition(targetPosition),windowDimension,elementDimension),elementPlacement&&(this.element.css({top:elementPlacement.top,left:elementPlacement.left}),this.element.removeClass("up down left right"),this.element.addClass(elementPlacement.position),pointerPosition="left"===elementPlacement.position||"right"===elementPlacement.position?{marginTop:elementPlacement.pointerDiff+this.properties.pointerSpace+"px",marginLeft:"0px"}:{marginTop:"0px",marginLeft:elementPlacement.pointerDiff+this.properties.pointerSpace+"px"},this.element.find(".pointer").css(pointerPosition))},show:function(targetPosition){this.properties.trigger&&(targetPosition=this.properties.trigger),this.element.fadeIn(200),this.updatePosition(targetPosition),this.properties.visible=!0},hide:function(){this.element.fadeOut(200),this.properties.visible=!1},toggle:function(e){this[this.properties.visible?"hide":"show"](e)},content:function(value){this.element.find(".contents").html(value)},spaced:function(value){this.element[value?"addClass":"removeClass"]("spaced")},style:function(variant){var classes=["alt"];if("undefined"==typeof variant){for(var i=classes.length-1;i>=0;i--)if(this.element.hasClass(classes[i]))return classes[i];return"default"}this.element.removeClass(classes.join(" ")),"default"!==variant&&this.element.addClass(variant)}},MU.Popup=Popup}(Zepto,Mysli.UI),function($,MU){"use strict";function createHeaderElement(parent,options){var element,action=options.action,preventDefault=options.preventDefault;switch(options.type){case"costume":element=$(options.element);break;default:element=$('<a href="#" />')}return options.id&&element.prop("id",id),action&&element.on("click",function(e){preventDefault&&(e.preventDefault(),e.stopPropagation()),parent.element.trigger(action)}),options.icon&&element.append('<i class="fa fa-'+options.icon+'" />'),options.label&&element.append("<span>"+options.label+"</span>"),element}var count=1,PanelSide=function(container,options){options=$.extend({},{id:null,header:!0,title:!1,style:null,content:null,footer:!1},options),this.container=container,this.properties={id:options.id?options.id:"panel-side"+count,headerItems:{left:0,right:0}},this.headerElements=[],this.element=$('<div class="side panel" id="'+this.properties.id+'" />'),this.contentContainer=$('<div class="body"><div class="inner" /></div>').appendTo(this.element),options.header&&this.header(!0),options.title&&this.title(options.title),options.style&&this.style(options.style),options.content&&this.content(options.content),count++};PanelSide.prototype={constructor:PanelSide,header:function(value){return"undefined"==typeof value?this.element.find("header.main"):void(value?this.header().length||(this.contentContainer.addClass("has-header"),this.element.prepend('<header class="main"><h2></h2></header>')):(this.contentContainer.removeClass("has-header"),this.element.remove("header.main")))},title:function(value){return"undefined"==typeof value?this.header().find("h2").text():void this.header().find("h2").text(value)},headerAppend:function(options){var pos=null,position=null,element=createHeaderElement(this,options);"costume"!==options.type&&(position="right",pos=++this.properties.headerItems[position],pos=1===pos?20*pos:25*pos,element.css(position,pos+"px")),element.appendTo(this.header()),this.headerElements.push(element)},headerPrepend:function(options){var pos=null,position=null,element=createHeaderElement(this,options);"costume"!==options.type&&(position="left",pos=++this.properties.headerItems[position],pos=1===pos?20*pos:25*pos,element.css(position,pos+"px")),element.prependTo(this.header()),this.headerElements.unshift(element)},content:function(content){return"undefined"==typeof content?this.contentContainer.find(".inner").html():void this.contentContainer.find(".inner").html(content)},footer:function(value){return"undefined"==typeof value?this.element.find("footer.main"):void(value?this.footer().length||this.element.append('<footer class="main"><h2></h2></footer>'):this.element.remove("footer.main"))},style:function(style){return"undefined"==typeof style?this.element.prop("class"):void this.element.addClass(style)}},MU.PanelSide=PanelSide}(Zepto,Mysli.UI),function($,MU){"use strict";var count=1,dimensions={tiny:160,small:260,medium:500,big:800},Panel=function(container,options){options=$.extend({},{size:"small",front:{},back:{},expand:!1,flippable:!1,closable:!0,shrink:!1,id:!1,position:0},options),this.properties={position:options.position,offset:0,locked:!1,expand:options.expand,expandFor:0,shrink:0,size:"undefined"==typeof dimensions[options.size]?"small":options.size,width:0,children:[],away:!1,awayOnBlur:!1,awayWidth:10,insensitive:!1,busy:!1,full:!1,oldZIndex:0,id:options.id?options.id:"mu-panel-"+count,closing:!1},this.container=container,this.front=!1,this.back=!1,this.shrink(options.shrink?options.shrink:!1),this.properties.width=dimensions[this.properties.size],this.element=$('<div class="panel multi" id="'+this.properties.id+'" />'),this.element.width(this.properties.width+"px"),this.sides=$('<div class="sides" />').appendTo(this.element),options.front.id=this.properties.id+"-side-front",this.front=new MU.PanelSide(this,options.front),this.front.style("front"+(options.front.style?" "+options.front.style:"")),options.closable&&this.front.headerPrepend({icon:"times",type:"link",action:"self/close",preventDefault:!0}),this.sides.append(this.front.element),options.flippable?(options.back.id=this.properties.id+"-side-back",this.back=new MU.PanelSide(this,options.back),this.back.style("back"+(options.back.style?" "+options.back.style:" alt")),this.back.headerPrepend({icon:"arrow-left",type:"link",action:"self/flip"}),this.sides.append(this.back.element)):this.sides.append('<div class="no-side"/>'),count++};Panel.prototype={constructor:Panel,flip:function(){this.back&&this.element.toggleClass("flipped")},away:function(value){var width;if(value){if(this.hasFocus()||this.properties.away)return void(this.properties.awayOnBlur=!0);this.properties.away=!0,width=-(this.properties.width-this.properties.awayWidth)}else{if(!this.properties.away)return void(this.properties.awayOnBlur=!1);this.properties.away=!1,this.properties.awayOnBlur=!1,width=this.properties.width-this.properties.awayWidth}this.container.updateSum(width),this.container.refresh()},full:function(value){value!==this.properties.full&&(value?(this.properties.full=!0,this.container.focus(this.properties.id),this.properties.oldZIndex=this.element.css("z-index"),this.element.css("z-index",1e4),this.element.animate({left:0,width:"100%"})):(this.properties.full=!1,this.animate(function(){this.element.css("z-index",this.properties.oldZIndex)})))},insensitive:function(value){value?this.hasFocus()?(this.setFocus(!1),this.properties.insensitive=!0,this.container.focusNext(this.properties.id)):this.properties.insensitive=!0:this.properties.insensitive=!1},busy:function(value){if(this.properties.busy!==value)if(value){var busy=$('<div class="loading panel-busy" style="opacity:0;" />').prependTo(this.element);busy.animate({opacity:.75}),this.properties.busy=!0}else this.element.find("div.panel-busy").fadeOut(400,function(){this.remove()}),this.properties.busy=!1},animate:function(callback){if(!this.properties.closing){var _this=this;this.element.animate({left:this.properties.position+this.properties.offset,width:this.properties.width+this.properties.expandFor,opacity:1},400,"ease",function(){"function"==typeof callback&&callback.call(_this)})}},expand:function(value){this.properties.expand=!!value,this.container.refresh()},shrink:function(value){this.properties.shrink=value?dimensions[value]?dimensions[value]:dimensions.tiny:!1},size:function(value){var sizeDiff=0;this.properties.size="undefined"==typeof dimensions[value]?"small":value,sizeDiff=-(this.properties.width-dimensions[this.properties.size]),this.properties.width=dimensions[this.properties.size],this.animate(),this.container.updateSum(sizeDiff,this.properties.id),this.container.refresh()},addChild:function(child){this.properties.children.push(child)},getChildren:function(){return this.properties.children},zIndex:function(value){return"undefined"==typeof value?this.element.css("z-index"):void this.element.css({"z-index":value})},hasFocus:function(){return this.element.hasClass("selected")},setFocus:function(value){value?(this.element.addClass("selected"),this.properties.away&&(this.away(!1),this.properties.awayOnBlur=!0)):(this.element.removeClass("selected"),this.properties.awayOnBlur&&this.away(!0))}},MU.Panel=Panel}(Zepto,Mysli.UI),function($,MU){"use strict";var Panels=function(container){var _this=this,resizeTimer=null;this.properties={sumSize:0,fillCount:0,activeId:!1,offseted:0,containerWidth:0},this.container=$(container),this.panelsStack=new MU.Aarray,this.updateContainerWidth(),this.container.on("click","div.panel.multi",function(){_this.focus(this.id,!0)}),this.container.on("self/close","div.panel.multi",function(){_this.remove(this.id)}),$(window).on("resize",function(){resizeTimer&&clearTimeout(resizeTimer),resizeTimer=setTimeout(function(){_this.updateContainerWidth(),_this.refreshView()},300)})};Panels.prototype={constructor:Panels,updateContainerWidth:function(){this.properties.containerWidth=this.container.width()},updateSum:function(value,modifyBeforeId){this.properties.sumSize=this.properties.sumSize+value,modifyBeforeId&&this.panelsStack.eachAfter(modifyBeforeId,function(index,panelInside){panelInside.zIndex(1e4-index),panelInside.properties.position=panelInside.properties.position+value,panelInside.animate()})},refreshView:function(){if(this.properties.activeId){var overflow=this.properties.containerWidth-this.properties.sumSize,overflowPart=this.properties.fillCount>0?Math.ceil(overflow/this.properties.fillCount):0,activePanel=this.panelsStack.get(this.properties.activeId),screenLeft=this.properties.containerWidth-activePanel.properties.width,overflowPercent=100-MU.Calc.getPercent(screenLeft,this.properties.sumSize-activePanel.properties.width),offsetSoFar=0,panelCalculated=0;0>=overflowPart&&(overflowPart=overflow),overflow>0?(overflowPercent=0,this.properties.offseted=!1):this.properties.offseted=!0,this.panelsStack.each(function(index,panel){if(panel.properties.away&&!panel.hasFocus())return panel.properties.expandFor=0,panel.properties.offset=-(panel.properties.width-panel.properties.awayWidth+offsetSoFar),panel.animate(),void(offsetSoFar+=panel.properties.width-panel.properties.awayWidth);if(panel.properties.expand){if(overflow>0)return panel.properties.offset=-offsetSoFar,panel.properties.expandFor=overflowPart,panel.animate(),void(offsetSoFar+=-overflowPart);panel.properties.expandFor=0,panel.animate()}if(panel.hasFocus())return panel.properties.expandFor=0,panel.properties.offset=-offsetSoFar,void panel.animate();if(panelCalculated=MU.Calc.setPercent(overflowPercent,panel.properties.width),panel.properties.shrink&&panel.properties.width+panel.properties.expandFor>panel.properties.shrink){if(panel.properties.shrink>panel.properties.width-panelCalculated){var diff=panelCalculated-(panel.properties.width-panel.properties.shrink);return panel.properties.expandFor=-diff,panel.properties.offset=-(panelCalculated-diff+offsetSoFar),panel.animate(),void(offsetSoFar+=panelCalculated)}return panel.properties.expandFor=-panelCalculated,panel.properties.offset=-offsetSoFar,panel.animate(),void(offsetSoFar+=panelCalculated)}panel.properties.expandFor=0,panel.properties.offset=-(panelCalculated+offsetSoFar),panel.animate(),offsetSoFar+=panelCalculated})}},add:function(options,afterId){"object"!=typeof options&&(options={});var panel=new MU.Panel(this,options),beforeSize=0,stackIndex=0;if(this.panelsStack.get(panel.properties.id))throw new Error("Duplicated ID: "+panel.properties.id);return afterId?(beforeSize=this.panelsStack.get(afterId).properties.width,this.panelsStack.eachBefore(afterId,function(index,panelInside){panelInside.zIndex(1e4-index),beforeSize+=panelInside.properties.width})):beforeSize=this.properties.sumSize,panel.properties.position=beforeSize,this.updateSum(panel.properties.width,afterId),afterId?(this.panelsStack.get(afterId).zIndex(1e4-this.panelsStack.getIndex(afterId)),stackIndex=this.panelsStack.pushAfter(afterId,panel.properties.id,panel)):stackIndex=this.panelsStack.push(panel.properties.id,panel),panel.zIndex(1e4-stackIndex),this.properties.activeId&&this.panelsStack.get(this.properties.activeId).properties.full||this.focus(panel.properties.id,!1),panel.properties.expand&&this.properties.fillCount++,this.refreshView(),panel.element.css({opacity:0,left:panel.properties.position+panel.properties.offset-(panel.properties.width+panel.properties.expandFor)}),this.container.append(panel.element),panel.animate(),panel},remove:function(id){if(this.panelsStack.get(id)){var panel=this.panelsStack.get(id),width=panel.properties.width;if(!panel.properties.locked){if(panel.properties.insensitive=!0,panel.properties.closing=!0,id===this.properties.activeId&&(this.properties.activeId=!1),panel.getChildren().length)for(var i=panel.getChildren().length-1;i>=0;i--)this.remove(panel.getChildren()[i].properties.id,!1);panel.properties.expand&&this.properties.fillCount--,this.updateSum(-width,id),panel.element.animate({left:panel.properties.position+panel.properties.offset-(width+panel.properties.expandFor)-10,opacity:0},"normal",function(){panel.element.remove()}),this.focusNext(id),this.panelsStack.remove(id),this.refreshView()}}},get:function(id){return this.panelsStack.get(id)?this.panelsStack.get(id):void 0},focusNext:function(lastId){var focusTo=this.panelsStack.getFrom(lastId,-1);(focusTo&&!focusTo.properties.insensitive||(focusTo=this.panelsStack.each(function(id,panel){return panel.properties.insensitive?void 0:panel})))&&this.focus(focusTo.properties.id,!1)},focus:function(id,refresh){if(id===this.properties.activeId)return!0;var panel=this.panelsStack.get(id);panel.properties.insensitive||(this.properties.activeId&&this.panelsStack.get(this.properties.activeId).setFocus(!1),panel.setFocus(!0),this.properties.activeId=id,refresh===!0&&this.refreshView())}},MU.Panels=Panels}(Zepto,Mysli.UI);