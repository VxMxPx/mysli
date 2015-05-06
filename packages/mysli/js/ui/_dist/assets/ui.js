var mysli;!function(mysli){var js;!function(js){var ui;!function(ui){var Cell=function(){function Cell(parent,$cell,options){void 0===options&&(options={}),this.parent=parent,this.$cell=$cell,this.prop=new js.common.Prop({visible:!0,padding:!1},this),this.prop.push(options,["visible","padding"])}return Cell.prototype.animate=function(what,duration,callback){void 0===duration&&(duration=500),void 0===callback&&(callback=!1),this.$cell.animate(what,duration,callback)},Object.defineProperty(Cell.prototype,"padding",{get:function(){return this.prop.padding},set:function(value){var positions=["top","right","bottom","left"];this.$cell.css("padding",""),"boolean"==typeof value&&(value=[value,value,value,value]);for(var i=0;i<positions.length;i++)"number"==typeof value[i]?this.$cell.css("padding-"+positions[i],value[i]):this.$cell[value[i]?"addClass":"removeClass"]("pad"+positions[i])},enumerable:!0,configurable:!0}),Object.defineProperty(Cell.prototype,"visible",{get:function(){return this.prop.visible},set:function(status){status!==this.prop.visible&&(this.prop.visible=status,this.$cell[status?"show":"hide"]())},enumerable:!0,configurable:!0}),Cell.prototype.remove=function(){this.$cell.remove()},Cell}();ui.Cell=Cell}(ui=js.ui||(js.ui={}))}(js=mysli.js||(mysli.js={}))}(mysli||(mysli={}));var mysli;!function(mysli){var js;!function(js){var ui;!function(ui){var Widget=function(){function Widget(options){if(void 0===options&&(options={}),this.events_count=0,this.events_count_native={},this.events={click:{},destroyed:{}},this.prop=new js.common.Prop({disabled:!1,style:"default",flat:!1,uid:null},this),"undefined"==typeof options.uid)options.uid=Widget.next_uid();else if("string"!=typeof options.uid)throw new Error("UID needs to be a valid string, got: "+uid);this.$element=$(this.constructor.template),this.prop.push(options,["style!","flat!","disabled"])}return Widget.next_uid=function(){return"mju-id-"+ ++Widget.uid_count},Object.defineProperty(Widget.prototype,"element",{get:function(){return this.$element},enumerable:!0,configurable:!0}),Object.defineProperty(Widget.prototype,"uid",{get:function(){return this.prop.uid},enumerable:!0,configurable:!0}),Object.defineProperty(Widget.prototype,"disabled",{get:function(){return this.prop.disabled},set:function(status){this.prop.disabled=status,this.element.prop("disabled",status)},enumerable:!0,configurable:!0}),Object.defineProperty(Widget.prototype,"flat",{get:function(){return this.prop.flat},set:function(value){this.element[value?"addClass":"removeClass"]("style-flat")},enumerable:!0,configurable:!0}),Object.defineProperty(Widget.prototype,"style",{get:function(){return this.prop.style},set:function(style){if(!(this.constructor.allowed_styles.indexOf(style)>-1))throw new Error("Invalid style: "+style+", please use one of the following: "+this.constructor.allowed_styles.join(", "));this.element.removeClass("style-"+this.prop.style),this.prop.style=style,this.element.addClass("style-"+style)},enumerable:!0,configurable:!0}),Widget.prototype.destroy=function(){this.trigger("destroyed"),this.$element.remove(),this.prop.uid=-1},Widget.prototype.connect=function(event,callback){var id,_this=this,_ref=Widget.event_extract_name(event);if(event=_ref[0],id=_ref[1],"undefined"==typeof this.events[event])throw new Error("No such event available: "+event);return id=""+id+event+"--"+ ++this.events_count,this.events[event][id]=callback,Widget.events_native.indexOf(event)>-1&&(this.events_count_native[event]="undefined"==typeof this.events_count_native[event]?1:this.events_count_native[event]+1,1===this.events_count_native[event]&&this.element.on(event.replace("-",""),function(e){_this.trigger(event,[e])})),id},Widget.prototype.trigger=function(event,params){void 0===params&&(params=[]);var call,_results=[];if("undefined"==typeof this.events[event])throw new Error("Invalid event: "+event);if("function"!=typeof params.push)throw new Error("Params must be an array!");params.push(this);for(var id in this.events[event])if(this.events[event].hasOwnProperty(id)){if(call=this.events[event][id],"function"!=typeof call)throw new Error("Invalid type of callback: "+id);_results.push(call.apply(this,params))}return _results},Widget.prototype.disconnect=function(id){var event,eid;if("string"==typeof id&&"*"===id.substr(0,1)){id+="*";for(event in this.events)if(this.events.hasOwnProperty(event))for(eid in this.events[event])this.events[event].hasOwnProperty(eid)&&eid.substr(0,id.length)===id&&(this.event_disconnect_native(event),delete this.events[event][eid]);return!0}return"string"==typeof id?event=id.split("--",2)[0]:(event=id[0],id=id[1]),"undefined"!=typeof this.events[event]?(this.event_disconnect_native(event),delete this.events[event][id]):!1},Widget.prototype.event_disconnect_native=function(event){"undefined"!=typeof Widget.events_native[event]&&(this.events_count_native[event]="undefined"==typeof this.events_count_native[event]?0:this.events_count_native[event]-1,0===this.events_count_native[event]&&this.$element.off(event.replace("-","")))},Widget.event_extract_name=function(event){var id,idr="";return event.indexOf("*")>0&&(id=event.split("*",2),event=id[0],idr="*"+id[1]+"*"),[event,idr]},Widget.events_native=["click","mouse-enter","mouse-leave","mouse-move","mouse-out","mouse-over","mouse-up"],Widget.uid_count=0,Widget.template='<div class="ui-widget" />',Widget.allowed_styles=["default","alt","primary","confirm","attention"],Widget}();ui.Widget=Widget}(ui=js.ui||(js.ui={}))}(js=mysli.js||(mysli.js={}))}(mysli||(mysli={}));var __extends=this.__extends||function(d,b){function __(){this.constructor=d}for(var p in b)b.hasOwnProperty(p)&&(d[p]=b[p]);__.prototype=b.prototype,d.prototype=new __},mysli;!function(mysli){var js;!function(js){var ui;!function(ui){var Container=function(_super){function Container(options){void 0===options&&(options={}),_super.call(this,options),this.Cell_constructor=ui.Cell,this.collection=new js.common.Arr,this.element_wrapper='<div class="ui-cell container-target"></div>',this.element.addClass("ui-container"),this.$target=this.element}return __extends(Container,_super),Container.prototype.push=function(widgets,options){return void 0===options&&(options=null),this.insert(widgets,-1,options)},Container.prototype.insert=function(widgets,at,options){var at_index,class_id,pushable,widget,cell=null;if(!(widgets instanceof ui.Widget)){if(widgets.constructor===Array){for(var i=0;i<widgets.length;i++)this.insert(widgets[i],at,options);return widgets}throw new Error("Instance of widget|widgets[] is required!")}if(widget=widgets,options)if("string"==typeof options)options={uid:options};else{if("object"!=typeof options)throw new Error("Invalid options provided. Null, string or {} allowed.");"undefined"==typeof options.uid&&(options.uid=widget.uid)}else options={uid:widget.uid};if(this.collection.has(options.uid))throw new Error("Element with such ID already exists: "+options.id);if(class_id="coll-euid-"+widget.uid+" coll-uid-"+options.uid,this.element_wrapper){if(pushable=$(this.element_wrapper),pushable.addClass(class_id),pushable.filter(".container-target").length)pushable.filter(".container-target").append(widget.element);else{if(!pushable.find(".container-target").length)throw new Error("Cannot find .container-target!");pushable.find(".container-target").append(widget.element)}cell=new this.Cell_constructor(this,pushable,options)}else widget.element.addClass(class_id),pushable=widget.element;return at_index=at>-1?this.collection.push_after(at,options.uid,[widget,cell]):this.collection.push(options.uid,[widget,cell]),at>-1?this.$target.find(".coll-euid-"+this.collection.get_from(at_index,-1).uid).after(pushable):this.$target.append(pushable),widget},Container.prototype.get=function(uid,cell){void 0===cell&&(cell=!1);var index_at;if("string"==typeof uid&&(index_at=uid.indexOf(">"))>-1){var uidq=uid.substr(0,index_at).trim(),ccontainer=this.collection.get(uidq)[0];if(ccontainer instanceof Container)return ccontainer.get(uid.substr(index_at+1).trim(),cell);throw new Error("Failed to acquire an element. Container needed: "+uidq)}return cell?this.collection.get(uid)[1]:this.collection.get(uid)[0]},Container.prototype.pull=function(uid){var element=this.get(uid,!1);return this.remove(uid),element},Container.prototype.has=function(uid){return this.collection.has(uid)},Container.prototype.remove=function(uid){uid=this.collection.get(uid).uid,this.collection.remove(uid),this.$target.find(".coll-euid-"+uid).remove()},Container}(ui.Widget);ui.Container=Container}(ui=js.ui||(js.ui={}))}(js=mysli.js||(mysli.js={}))}(mysli||(mysli={}));var __extends=this.__extends||function(d,b){function __(){this.constructor=d}for(var p in b)b.hasOwnProperty(p)&&(d[p]=b[p]);__.prototype=b.prototype,d.prototype=new __},mysli;!function(mysli){var js;!function(js){var ui;!function(ui){var Box=function(_super){function Box(options){if(void 0===options&&(options={}),_super.call(this,options),this.Cell_constructor=BoxCell,this.prop.def({orientation:Box.HORIZONTAL}),this.prop.push(options),this.element.addClass("ui-box"),this.element_wrapper_original=this.element_wrapper,this.prop.orientation===Box.VERTICAL){var row=$('<div class="ui-row" />');this.element.append(row),this.$target=row}}return __extends(Box,_super),Object.defineProperty(Box,"HORIZONTAL",{get:function(){return 1},enumerable:!0,configurable:!0}),Object.defineProperty(Box,"VERTICAL",{get:function(){return 2},enumerable:!0,configurable:!0}),Box.prototype.insert=function(){for(var args=[],_i=0;_i<arguments.length;_i++)args[_i-0]=arguments[_i];return this.prop.orientation===Box.HORIZONTAL?this.element_wrapper='<div class="ui-row"><div class="ui-cell container-target" /></div>':this.element_wrapper=this.element_wrapper_original,_super.prototype.insert.apply(this,args)},Box}(ui.Container);ui.Box=Box;var BoxCell=function(_super){function BoxCell(parent,$cell,options){void 0===options&&(options={}),_super.call(this,parent,$cell,options),this.prop.def({expanded:!1}),this.prop.push(options,["expanded"])}return __extends(BoxCell,_super),Object.defineProperty(BoxCell.prototype,"expanded",{get:function(){return this.prop.expanded},set:function(value){this.$cell[value?"addClass":"removeClass"]("expanded")},enumerable:!0,configurable:!0}),BoxCell}(ui.Cell)}(ui=js.ui||(js.ui={}))}(js=mysli.js||(mysli.js={}))}(mysli||(mysli={}));var __extends=this.__extends||function(d,b){function __(){this.constructor=d}for(var p in b)b.hasOwnProperty(p)&&(d[p]=b[p]);__.prototype=b.prototype,d.prototype=new __},mysli;!function(mysli){var js;!function(js){var ui;!function(ui){var Button=function(_super){function Button(options){void 0===options&&(options={}),_super.call(this,options),this.prop.def({label:null,toggle:!1,pressed:!1,icon:{name:null,position:"left",spin:!1}}),this.prop.push(options,["icon!","label!","toggle","pressed"])}return __extends(Button,_super),Object.defineProperty(Button.prototype,"toggle",{get:function(){return this.prop.toggle},set:function(value){var _this=this;this.prop.toggle=value,value?this.connect("click*self-toggle",function(){_this.pressed=!_this.pressed}):this.disconnect("click*self-toggle")},enumerable:!0,configurable:!0}),Object.defineProperty(Button.prototype,"pressed",{get:function(){return this.prop.pressed},set:function(value){this.prop.pressed=value,this.element[value?"addClass":"removeClass"]("pressed")},enumerable:!0,configurable:!0}),Object.defineProperty(Button.prototype,"label",{get:function(){return this.prop.label},set:function(value){var method,$label=this.element.find("span.label");return this.prop.label=value,value?($label.length||($label=$('<span class="label" />'),method="right"===this.icon.position?"prepend":"append",this.element[method]($label)),void $label.text(value)):void $label.remove()},enumerable:!0,configurable:!0}),Object.defineProperty(Button.prototype,"icon",{get:function(){return this.prop.icon},set:function(options){var $icon,method,spin;return $icon=this.element.find("i.fa"),$icon.remove(),"string"==typeof options&&(options={name:options}),options.name?(this.prop.icon=js.common.mix(this.prop.icon,options),method="right"===this.prop.icon.position?"append":"prepend",spin=this.prop.icon.spin?" fa-spin":"",void this.element[method]($('<i class="fa fa-'+this.prop.icon.name+spin+'" />'))):void(this.prop.icon.name=null)},enumerable:!0,configurable:!0}),Button.template='<button class="ui-widget ui-button"></button>',Button.allowed_styles=["default","alt","primary","confirm","attention"],Button}(ui.Widget);ui.Button=Button}(ui=js.ui||(js.ui={}))}(js=mysli.js||(mysli.js={}))}(mysli||(mysli={}));var __extends=this.__extends||function(d,b){function __(){this.constructor=d}for(var p in b)b.hasOwnProperty(p)&&(d[p]=b[p]);__.prototype=b.prototype,d.prototype=new __},mysli;!function(mysli){var js;!function(js){var ui;!function(ui){var HTML=function(_super){function HTML(text,options){void 0===text&&(text={}),void 0===options&&(options={}),null!==text&&"object"==typeof text&&(options=text),_super.call(this,options),this.element.addClass("ui-html"),"string"==typeof text&&this.push(text)}return __extends(HTML,_super),HTML.prototype.push=function(html){var element;return html='<div class="ui-html-element">'+html+"</div>",element=$(html),this.element.append(element),element},HTML.prototype.remove=function(selector){this.element.filter(selector).remove()},HTML}(ui.Widget);ui.HTML=HTML}(ui=js.ui||(js.ui={}))}(js=mysli.js||(mysli.js={}))}(mysli||(mysli={}));var __extends=this.__extends||function(d,b){function __(){this.constructor=d}for(var p in b)b.hasOwnProperty(p)&&(d[p]=b[p]);__.prototype=b.prototype,d.prototype=new __},mysli;!function(mysli){var js;!function(js){var ui;!function(ui){var Overlay=function(_super){function Overlay(options){void 0===options&&(options={}),_super.call(this,options),this.prop.def({busy:!1,visible:!0}),this.prop.push(options,["busy","visible"])}return __extends(Overlay,_super),Object.defineProperty(Overlay.prototype,"busy",{get:function(){return this.prop.busy},set:function(status){this.prop.busy=status,this.element[status?"addClass":"removeClass"]("status-busy")},enumerable:!0,configurable:!0}),Object.defineProperty(Overlay.prototype,"visible",{get:function(){return this.element.is(":visible")},set:function(status){this.prop.visible=status,this.element[status?"show":"hide"]()},enumerable:!0,configurable:!0}),Overlay.template='<div class="ui-overlay ui-widget"><div class="ui-overlay-busy"><i class="fa fa-cog fa-spin"></i></div></div>',Overlay}(ui.Widget);ui.Overlay=Overlay}(ui=js.ui||(js.ui={}))}(js=mysli.js||(mysli.js={}))}(mysli||(mysli={}));var __extends=this.__extends||function(d,b){function __(){this.constructor=d}for(var p in b)b.hasOwnProperty(p)&&(d[p]=b[p]);__.prototype=b.prototype,d.prototype=new __},mysli;!function(mysli){var js;!function(js){var ui;!function(ui){var Label=function(_super){function Label(options){void 0===options&&(options={}),_super.call(this,options),this.prop.def({text:"",type:Label.DEFAULT,input:null}),this.prop.push(options,["type!","text!","input"])}return __extends(Label,_super),Object.defineProperty(Label,"DEFAULT",{get:function(){return 1},enumerable:!0,configurable:!0}),Object.defineProperty(Label,"TITLE",{get:function(){return 2},enumerable:!0,configurable:!0}),Object.defineProperty(Label,"INPUT",{get:function(){return 3},enumerable:!0,configurable:!0}),Object.defineProperty(Label.prototype,"type",{get:function(){return this.prop.type},set:function(type){var element;switch(type){case Label.DEFAULT:this.input=null,element=$("<span />");break;case Label.TITLE:this.input=null,element=$("<h1 />");break;case Label.INPUT:element=$("<label />");break;default:throw new Error("Invalid type provided: "+type)}this.element.empty(),this.prop.type=type,element.text(this.text),this.element.append(element)},enumerable:!0,configurable:!0}),Object.defineProperty(Label.prototype,"text",{get:function(){return this.prop.text},set:function(value){this.prop.text=value,this.element.find(":first-child").text(value)},enumerable:!0,configurable:!0}),Object.defineProperty(Label.prototype,"input",{get:function(){return this.prop.input},set:function(widget){widget?(this.prop.input=widget,widget.element.prop("id")||widget.element.prop("id",widget.uid),this.type=Label.INPUT,this.element.find("label").prop("for",widget.uid)):this.input&&(this.element.find("label").prop("for",!1),this.prop.input=null,this.prop.input.destroy())},enumerable:!0,configurable:!0}),Label.template='<span class="ui-widget ui-title" />',Label}(ui.Widget);ui.Label=Label}(ui=js.ui||(js.ui={}))}(js=mysli.js||(mysli.js={}))}(mysli||(mysli={}));var __extends=this.__extends||function(d,b){function __(){this.constructor=d}for(var p in b)b.hasOwnProperty(p)&&(d[p]=b[p]);__.prototype=b.prototype,d.prototype=new __},mysli;!function(mysli){var js;!function(js){var ui;!function(ui){var Tabbar=function(_super){function Tabbar(items,options){void 0===options&&(options={}),_super.call(this,options),this.prop.def({active:null}),this.prop.push(options),options.orientation=ui.Box.VERTICAL,this.container=new ui.Box(options),this.$element=this.container.element,this.element.addClass("ui-tabbar"),this.events=js.common.mix({action:{}});for(var item in items)items.hasOwnProperty(item)&&this.container.push(this.produce(items[item],item),item)}return __extends(Tabbar,_super),Object.defineProperty(Tabbar.prototype,"active",{get:function(){return this.prop.active},set:function(value){this.container.has(value)&&(this.prop.active&&(this.container.get(this.prop.active).pressed=!1),this.prop.active=value,this.container.get(value).pressed=!0)},enumerable:!0,configurable:!0}),Tabbar.prototype.produce=function(title,id){var _this=this,button=new ui.Button({uid:id,toggle:!0,label:title,flat:!0,style:this.style});return this.prop.active===id&&(button.pressed=!0),button.connect("click",function(e){_this.active=button.uid,_this.trigger("action",[id,e])}),button},Tabbar}(ui.Widget);ui.Tabbar=Tabbar}(ui=js.ui||(js.ui={}))}(js=mysli.js||(mysli.js={}))}(mysli||(mysli={}));var __extends=this.__extends||function(d,b){function __(){this.constructor=d}for(var p in b)b.hasOwnProperty(p)&&(d[p]=b[p]);__.prototype=b.prototype,d.prototype=new __},mysli;!function(mysli){var js;!function(js){var ui;!function(ui){var Titlebar=function(_super){function Titlebar(options){void 0===options&&(options={}),options.orientation=ui.Box.VERTICAL,_super.call(this,options),this.element.addClass("ui-titlebar")}return __extends(Titlebar,_super),Titlebar.prototype.insert=function(widgets,at,options){if(widgets.constructor===Array){for(var i=0;i<widgets.length;i++)widgets[i].flat=!0,_super.prototype.insert.call(this,widgets[i],at,options);return widgets}return widgets.flat=!0,_super.prototype.insert.call(this,widgets,at,options)},Titlebar}(ui.Box);ui.Titlebar=Titlebar}(ui=js.ui||(js.ui={}))}(js=mysli.js||(mysli.js={}))}(mysli||(mysli={}));var __extends=this.__extends||function(d,b){function __(){this.constructor=d}for(var p in b)b.hasOwnProperty(p)&&(d[p]=b[p]);__.prototype=b.prototype,d.prototype=new __},mysli;!function(mysli){var js;!function(js){var ui;!function(ui){var Navigation=function(_super){function Navigation(items,options){void 0===options&&(options={}),_super.call(this,options),this.container=new ui.Box(options),this.$element=this.container.element,this.element.addClass("ui-navigation"),this.events=js.common.mix({action:{}},this.events);for(var item in items)items.hasOwnProperty(item)&&this.container.push(this.produce(items[item],item),item)}return __extends(Navigation,_super),Navigation.prototype.produce=function(title,id){var _this=this,button=new ui.Button({flat:!0,label:title,style:this.style});return button.connect("click",function(e){_this.trigger("action",[id,e])}),button},Navigation}(ui.Widget);ui.Navigation=Navigation}(ui=js.ui||(js.ui={}))}(js=mysli.js||(mysli.js={}))}(mysli||(mysli={}));var __extends=this.__extends||function(d,b){function __(){this.constructor=d}for(var p in b)b.hasOwnProperty(p)&&(d[p]=b[p]);__.prototype=b.prototype,d.prototype=new __},mysli;!function(mysli){var js;!function(js){var ui;!function(ui){var Panel=function(_super){function Panel(options){var _this=this;void 0===options&&(options={}),_super.call(this,options),this.closing=!1,this.old_zindex=0,this.old_width=0,this.element.addClass("ui-panel"),this.element.append('<div class="ui-panel-sides" />'),this.events=js.common.mix({close:{},"set-away":{},"set-width":{},"set-popout":{},"set-insensitive":{},"set-min-size":{},"set-focus":{},"set-expandable":{}},this.events),this.prop.def({position:0,offset:0,locked:!1,expandable:!1,expanded_for:0,min_size:0,width:Panel.SIZE_NORMAL,away:!1,away_on_blur:!1,away_width:10,insensitive:!1,popout:!1,popout_size:Panel.SIZE_HUGE,focus:!1,flippable:!1,side:Panel.SIDE_FRONT}),this.prop.push(options),this.element.width(this.prop.width),this.element.on("click",function(){_this.prop.closing||_this.locked||(_this.focus=!0)}),this.front=new ui.PanelSide,this.element.find(".ui-panel-sides").append(this.front.element),this.prop.flippable&&(this.element.addClass("multi"),this.back=new ui.PanelSide({style:"alt"}),this.element.find(".ui-panel-sides").append(this.back.element),this.side=this.prop.side)}return __extends(Panel,_super),Object.defineProperty(Panel,"SIZE_TINY",{get:function(){return 160},enumerable:!0,configurable:!0}),Object.defineProperty(Panel,"SIZE_SMALL",{get:function(){return 260},enumerable:!0,configurable:!0}),Object.defineProperty(Panel,"SIZE_NORMAL",{get:function(){return 340},enumerable:!0,configurable:!0}),Object.defineProperty(Panel,"SIZE_BIG",{get:function(){return 500},enumerable:!0,configurable:!0}),Object.defineProperty(Panel,"SIZE_HUGE",{get:function(){return 800},enumerable:!0,configurable:!0}),Object.defineProperty(Panel,"SIDE_FRONT",{get:function(){return"front"},enumerable:!0,configurable:!0}),Object.defineProperty(Panel,"SIDE_BACK",{get:function(){return"back"},enumerable:!0,configurable:!0}),Panel.prototype.animate=function(callback){this.prop.closing||this.element.stop(!0,!1).animate({left:this.position+this.offset,width:this.width+this.expand,opacity:1},{duration:400,queue:!1,always:function(){callback&&callback.call(this)}}).css({overflow:"visible"})},Object.defineProperty(Panel.prototype,"side",{get:function(){return this.prop.side},set:function(value){if(-1===Panel.valid_sides.indexOf(value))throw new Error("Trying to set invalid side: "+value);if(!this.prop.flippable)throw new Error("Trying to flip a panel which is not flippable.");this.element[value===Panel.SIDE_BACK?"addClass":"removeClass"]("flipped")},enumerable:!0,configurable:!0}),Object.defineProperty(Panel.prototype,"width",{get:function(){return this.prop.width},set:function(value){var diff;value!==this.width&&(diff=-(this.width-value),this.prop.width=value,this.trigger("set-width",[value,diff]))},enumerable:!0,configurable:!0}),Object.defineProperty(Panel.prototype,"away",{get:function(){return this.prop.away},set:function(status){var width;if(status!==this.away){if(status){if(this.focus||this.away)return void(this.prop.away_on_blur=!0);this.prop.away=!0,width=-(this.width-this.away_width)}else{if(!this.away)return void(this.prop.away_on_blur=!1);this.prop.away=!1,this.prop.away_on_blur=!1,width=this.width-this.away_width}this.trigger("set-away",[status,width])}},enumerable:!0,configurable:!0}),Object.defineProperty(Panel.prototype,"popout",{get:function(){return this.prop.popout},set:function(status){status!==this.popout&&(status?(this.prop.popout=!0,this.focus=!0,this.old_zindex=+this.element.css("z-index"),this.old_width=this.width,this.element.css("z-index",10005),this.width=this.prop.popout_size):(this.prop.popout=!1,this.element.css("z-index",this.old_zindex),this.width=this.old_width),this.trigger("set-popout",[status]))},enumerable:!0,configurable:!0}),Object.defineProperty(Panel.prototype,"insensitive",{get:function(){return this.prop.insensitive},set:function(value){value!==this.insensitive&&(value?(this.focus&&(this.focus=!1),this.prop.insensitive=!0):this.prop.insensitive=!1,this.trigger("set-insensitive",[value]))},enumerable:!0,configurable:!0}),Object.defineProperty(Panel.prototype,"min_size",{get:function(){return this.prop.min_size},set:function(size){this.min_size!==size&&(this.prop.min_size=size,this.trigger("set-min-size",[size]))},enumerable:!0,configurable:!0}),Object.defineProperty(Panel.prototype,"focus",{get:function(){return this.prop.focus},set:function(value){value!==this.focus&&(value?(this.prop.focus=!0,this.element.addClass("focused"),this.away&&(this.away=!1,this.prop.away_on_blur=!0)):(this.prop.focus=!1,this.element.removeClass("focused"),this.prop.away_on_blur&&(this.away=!0)),this.trigger("set-focus",[value]))},enumerable:!0,configurable:!0}),Object.defineProperty(Panel.prototype,"expandable",{get:function(){return this.prop.expandable},set:function(value){value!==this.expandable&&(this.prop.expandable=value,this.trigger("set-expandable",[value]))},enumerable:!0,configurable:!0}),Object.defineProperty(Panel.prototype,"position",{get:function(){return this.prop.position},set:function(value){this.prop.position=value},enumerable:!0,configurable:!0}),Object.defineProperty(Panel.prototype,"offset",{get:function(){return this.prop.offset},set:function(value){this.prop.offset=value},enumerable:!0,configurable:!0}),Object.defineProperty(Panel.prototype,"locked",{get:function(){return this.prop.locked},set:function(value){this.prop.locked=value},enumerable:!0,configurable:!0}),Object.defineProperty(Panel.prototype,"expand",{get:function(){return this.prop.expanded_for},set:function(value){this.prop.expanded_for=value},enumerable:!0,configurable:!0}),Object.defineProperty(Panel.prototype,"away_width",{get:function(){return this.prop.away_width},enumerable:!0,configurable:!0}),Panel.prototype.close=function(){var _this=this;this.locked||(this.insensitive=!0,this.prop.closing=!0,this.trigger("close"),this.element.stop(!0,!1).animate({left:this.position+this.offset-(this.width+this.expand)-10,opacity:0},{done:function(){_this.destroy()}}))},Panel.valid_sides=["front","back"],Panel}(ui.Widget);ui.Panel=Panel}(ui=js.ui||(js.ui={}))}(js=mysli.js||(mysli.js={}))}(mysli||(mysli={}));var __extends=this.__extends||function(d,b){function __(){this.constructor=d}for(var p in b)b.hasOwnProperty(p)&&(d[p]=b[p]);__.prototype=b.prototype,d.prototype=new __},mysli;!function(mysli){var js;!function(js){var ui;!function(ui){var PanelSide=function(_super){function PanelSide(options){void 0===options&&(options={}),_super.call(this,options),this.element.addClass("ui-panel-side")}return __extends(PanelSide,_super),PanelSide}(ui.Container);ui.PanelSide=PanelSide}(ui=js.ui||(js.ui={}))}(js=mysli.js||(mysli.js={}))}(mysli||(mysli={}));var __extends=this.__extends||function(d,b){function __(){this.constructor=d}for(var p in b)b.hasOwnProperty(p)&&(d[p]=b[p]);__.prototype=b.prototype,d.prototype=new __},mysli;!function(mysli){var js;!function(js){var ui;!function(ui){var PanelContainer=function(_super){function PanelContainer(options){void 0===options&&(options={}),_super.call(this,options),this.sum_size=0,this.expandable_count=0,this.active_id=null,this.offseted=!1,this.container_width=0,this.resize_timer=null,this.collection=new js.common.Arr,this.element.addClass("ui-panel-container"),this.set_resize_with_window(!0),this.set_size_from_dom_element(window)}return __extends(PanelContainer,_super),PanelContainer.prototype.update_sum=function(value,modify_before_id){this.sum_size=this.sum_size+value,"undefined"!=typeof modify_before_id&&this.collection.each_after(modify_before_id,function(index,panel){panel.element.css("z-index",1e4-index),panel.position=panel.position+value,panel.animate()})},PanelContainer.prototype.update_view=function(){var active_panel,overflow,overflow_part,overflow_percent,screen_left,offset_so_far,panel_calculated,diff;this.active_id&&(active_panel=this.get(this.active_id),active_panel.width>this.container_width&&(this.sum_size=this.sum_size+(this.container_width-active_panel.width),active_panel.width=this.container_width),overflow=this.container_width-this.sum_size,overflow_part=this.expandable_count>0?Math.floor(overflow/this.expandable_count):0,screen_left=this.container_width-active_panel.width,overflow_percent=100-js.common.Num.get_percent(screen_left,this.sum_size-active_panel.width),offset_so_far=0,panel_calculated=0,0>=overflow_part&&(overflow_part=overflow),overflow>0?(overflow_percent=0,this.offseted=!1):this.offseted=!0,this.collection.each(function(index,panel){if(panel.away&&!panel.focus)return panel.expand=0,panel.offset=-(panel.width-panel.away_width+offset_so_far),panel.animate(),void(offset_so_far=offset_so_far+panel.width-panel.away_width);if(panel.expandable){if(overflow>0)return panel.offset=-offset_so_far,panel.expand=overflow_part,panel.animate(),void(offset_so_far+=-overflow_part);panel.expand=0,panel.animate()}return panel.focus?(panel.expand=0,panel.offset=-offset_so_far,void panel.animate()):(panel_calculated=js.common.Num.set_percent(overflow_percent,panel.width),panel.min_size&&panel.width+panel.expand>panel.min_size?panel.min_size>panel.width-panel_calculated?(diff=panel_calculated-(panel.width-panel.min_size),panel.expand=-diff,panel.offset=-(panel_calculated-diff+offset_so_far),panel.animate(),void(offset_so_far+=panel_calculated)):(panel.expand=-panel_calculated,panel.offset=-offset_so_far,panel.animate(),void(offset_so_far+=panel_calculated)):(panel.expand=0,panel.offset=-(panel_calculated+offset_so_far),panel.animate(),void(offset_so_far+=panel_calculated)))}))},PanelContainer.prototype.insert=function(panel,after_id){var index,_this=this,size=0;return"string"==typeof after_id?(size=this.get(after_id).width,size="number"==typeof size?size:0,this.collection.each_before(after_id,function(index,ipanel){ipanel.element.css("z-index",1e4-index),size+=ipanel.width}),panel.position=size):panel.position=this.sum_size,this.update_sum(panel.width),panel.element.css({opacity:0,left:panel.position+panel.offset-(panel.width+panel.expand)}),after_id?(this.collection.push_after(after_id,panel.uid,panel),panel.uid!==this.collection.get_last().uid&&this.collection.each_after(panel.uid,function(index,ipanel){ipanel.element.css("z-index",1e4-index),ipanel.position=ipanel.position+panel.width,ipanel.animate()})):this.collection.push(panel.uid,panel),this.element.append(panel.element),index=this.collection.get_index(panel.uid),panel.element.css("z-index",1e4-index),panel.connect("set-focus",this.switch_focus.bind(this)),panel.connect("set-expandable",function(status){_this.expandable_count=_this.expandable_count+(status?1:-1)}),panel.connect("close",function(){_this.remove(panel.uid)}),panel.focus=!0,panel.expandable&&this.expandable_count++,panel},PanelContainer.prototype.push=function(panel){return this.insert(panel,null)},PanelContainer.prototype.get=function(id){return this.collection.get(id)},PanelContainer.prototype.remove=function(id){var next_panel,panel=this.get(id),width=panel.width;panel.expandable&&this.expandable_count--,this.update_sum(-width,id),id==this.active_id?(this.active_id=null,next_panel=this.collection.get_from(id,-1),this.collection.remove(id),next_panel.focus=!0):(this.collection.remove(id),this.update_view())},PanelContainer.prototype.set_resize_with_window=function(status,timeout){void 0===timeout&&(timeout=500),status?$(window).on("resize",function(){this.resize_timer&&clearTimeout(this.resize_timer),this.resize_timer=setTimeout(this.set_size_from_dom_element.bind(this,window),timeout)}.bind(this)):this.resize_timer&&clearTimeout(this.resize_timer)},PanelContainer.prototype.set_size_from_dom_element=function(selector){var width=$(selector).outerWidth(),height=$(selector).outerHeight();this.element.css({width:width,height:height}),this.container_width=width,this.update_view()},PanelContainer.prototype.switch_focus=function(status,panel){status&&(this.active_id&&(this.get(this.active_id).focus=!1),
this.active_id=panel.uid,this.update_view())},PanelContainer}(ui.Widget);ui.PanelContainer=PanelContainer}(ui=js.ui||(js.ui={}))}(js=mysli.js||(mysli.js={}))}(mysli||(mysli={}));