YUI.add("moodle-calendar-eventmanager",function(e,t){var n="Calendar event",r="eventId",i="node",s="title",o="content",u="delay",a="showTimeout",f="hideTimeout",l=function(){l.superclass.constructor.apply(this,arguments)},c;e.extend(l,e.Base,{initpanelcalled:!1,initializer:function(){this.get(r);var e=this.get(i),t;return e?(t=e.ancestor("td"),this.publish("showevent"),this.publish("hideevent"),t.on("mouseenter",this.startShow,this),t.on("mouseleave",this.startHide,this),t.on("focus",this.startShow,this),t.on("blur",this.startHide,this),!0):!1},initPanel:function(){if(!this.initpanelcalled){this.initpanelcalled=!0;var t=this.get(i),n=t.ancestor("td"),u=n.ancestor("div"),a;a=new e.Overlay({constrain:u,align:{node:n,points:[e.WidgetPositionAlign.TL,e.WidgetPositionAlign.BC]},headerContent:e.Node.create('<h2 class="eventtitle">'+this.get(s)+"</h2>"),bodyContent:e.Node.create('<div class="eventcontent">'+this.get(o)+"</div>"),visible:!1,id:this.get(r)+"_panel",width:Math.floor(u.get("offsetWidth")*.9)+"px"}),a.render(n),a.get("boundingBox").addClass("calendar-event-panel"),a.get("boundingBox").setAttribute("aria-live","off"),this.on("showevent",a.show,a),this.on("showevent",this.setAriashow,a),this.on("hideevent",this.setAriahide,a),this.on("hideevent",a.hide,a)}},startShow:function(){this.get(a)!==null&&this.cancelShow();var e=this;this.set(a,setTimeout(function(){e.show()},this.get(u)))},cancelShow:function(){clearTimeout(this.get(a))},setAriashow:function(){this.get("boundingBox").setAttribute("aria-live","assertive")},setAriahide:function(){this.get("boundingBox").setAttribute("aria-live","off")},show:function(){this.initPanel(),this.fire("showevent")},startHide:function(){this.get(f)!==null&&this.cancelHide();var e=this;this.set(f,setTimeout(function(){e.hide()},this.get(u)))},hide:function(){this.fire("hideevent")},cancelHide:function(){clearTimeout(this.get(f))}},{NAME:n,ATTRS:{eventId:{setter:function(t){return this.set(i,e.one("#"+t)),t},validator:e.Lang.isString},node:{setter:function(t){return typeof t=="string"&&(t=e.one("#"+t)),t}},title:{validator:e.Lang.isString},content:{validator:e.Lang.isString},delay:{value:300,validator:e.Lang.isNumber},showTimeout:{value:null},hideTimeout:{value:null}}}),e.augment(l,e.EventTarget),c={add_event:function(e){new l(e)}},M.core_calendar=M.core_calendar||{},e.mix(M.core_calendar,c)},"@VERSION@",{requires:["base","node","event-mouseenter","overlay","moodle-calendar-eventmanager-skin"]});
