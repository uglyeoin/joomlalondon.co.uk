!function(t,i,e){function s(i,e,s){this.size=48,this.sidePadding=25,this.delay=s,this.slider=window[i],this.slider.started(t.proxy(this.start,this,i,e,s))}s.prototype.start=function(i,e,s){if(this.slider.sliderElement.data("arrow"))return!1;this.previous=t("#"+i+"-arrow-previous").on("click",t.proxy(function(t){t.stopPropagation(),this.slider.previous()},this));var o=this.previous.find(".nextend-arrow-title");this.previous.on({mouseenter:t.proxy(this.mouseEnter,this,this.previous,o),mouseleave:t.proxy(this.mouseLeave,this,this.previous,o)}),this.next=t("#"+i+"-arrow-next").on("click",t.proxy(function(t){t.stopPropagation(),this.slider.next()},this));var n=this.next.find(".nextend-arrow-title");this.next.on({mouseenter:t.proxy(this.mouseEnter,this,this.next,n),mouseleave:t.proxy(this.mouseLeave,this,this.next,n)});var r=e.length;this.slider.sliderElement.data("arrow",this).on("sliderSwitchTo",t.proxy(function(t,i){0==i?o.html(e[r-1]):o.html(e[i-1]),this.previous.width()!=this.size&&this.previous.width(this.size+this.sidePadding+o.width()),i==r-1?n.html(e[0]):n.html(e[i+1]),this.next.width()!=this.size&&this.next.width(this.size+this.sidePadding+n.width())},this))},s.prototype.mouseEnter=function(t,i,e){var s=t.data("ssTween");s&&s.pause(),t.data("ssTween",NextendTween.to(t,.4,{width:this.size+this.sidePadding+i.width(),delay:this.delay}))},s.prototype.mouseLeave=function(t,i,e){var s=t.data("ssTween");s&&s.pause(),t.data("ssTween",NextendTween.to(t,.4,{width:this.size}))},i.NextendSmartSliderWidgetArrowGrow=s}(n2,window);
!function(t,i,s){"use strict";function h(i,s){this.slider=window[i],this.slider.started(t.proxy(this.start,this,i,s))}h.prototype.start=function(i,s){if(this.slider.sliderElement.data("indicator"))return!1;if(this.slider.sliderElement.data("indicator",this),this.pie=this.slider.sliderElement.find(".nextend-indicator-pie"),this.slider.controls.autoplay._disabled)this.destroy();else{switch(this.slider.controls.autoplay.enableProgress(),this.input=t('<input type="hidden" value="0">').appendTo(this.pie),s.skin){case"tron":s.draw=function(){var t,i=this.angle(this.cv),s=this.startAngle,h=this.startAngle,e=h+i,n=!0;return this.g.lineWidth=this.lineWidth,this.o.cursor&&(h=e-.3)&&(e+=.3),this.o.displayPrevious&&(t=this.startAngle+this.angle(this.value),this.o.cursor&&(s=t-.3)&&(t+=.3),this.g.beginPath(),this.g.strokeStyle=this.previousColor,this.g.arc(this.xy,this.xy,Math.abs(this.radius-this.lineWidth),s,t,!1),this.g.stroke()),this.g.beginPath(),this.g.strokeStyle=n?this.o.fgColor:this.fgColor,this.g.arc(this.xy,this.xy,Math.abs(this.radius-this.lineWidth),h,e,!1),this.g.stroke(),this.g.lineWidth=2,this.g.beginPath(),this.g.strokeStyle=this.o.bgColor,this.g.arc(this.xy,this.xy,this.radius-this.lineWidth+1+2*this.lineWidth/3,0,2*Math.PI,!1),this.g.stroke(),!1}}delete s.skin,this.input.n2knob(s),this.slider.sliderElement.on("autoplayDisabled",t.proxy(this.destroy,this)).on("autoplay",t.proxy(this.onProgress,this))}},h.prototype.onProgress=function(t,i){this.input.val(100*i).trigger("change")},h.prototype.destroy=function(){this.pie.remove()},i.NextendSmartSliderWidgetIndicatorPie=h,function(i){i(t)}(function(t){var i={},h=Math.max,e=Math.min;i.c={},i.c.d=t(document),i.c.t=function(t){return t.originalEvent.touches.length-1},i.o=function(){var i=this;this.o=null,this.$=null,this.i=null,this.g=null,this.v=null,this.cv=null,this.x=0,this.y=0,this.w=0,this.h=0,this.$c=null,this.c=null,this.t=0,this.isInit=!1,this.fgColor=null,this.pColor=null,this.dH=null,this.cH=null,this.eH=null,this.rH=null,this.scale=1,this.relative=!1,this.relativeWidth=!1,this.relativeHeight=!1,this.$div=null,this.run=function(){var h=function(t,s){var h;for(h in s)i.o[h]=s[h];i._carve().init(),i._configure()._draw()};if(!this.$.data("kontroled")){if(this.$.data("kontroled",!0),this.extend(),this.o=t.extend({min:this.$.data("min")!==s?this.$.data("min"):0,max:this.$.data("max")!==s?this.$.data("max"):100,stopper:!0,readOnly:this.$.data("readonly")||"readonly"===this.$.attr("readonly"),cursor:this.$.data("cursor")===!0&&30||this.$.data("cursor")||0,thickness:this.$.data("thickness")&&Math.max(Math.min(this.$.data("thickness"),1),.01)||.35,lineCap:this.$.data("linecap")||"butt",width:this.$.data("width")||200,height:this.$.data("height")||200,displayInput:null==this.$.data("displayinput")||this.$.data("displayinput"),displayPrevious:this.$.data("displayprevious"),fgColor:this.$.data("fgcolor")||"#87CEEB",inputColor:this.$.data("inputcolor"),font:this.$.data("font")||"Arial",fontWeight:this.$.data("font-weight")||"bold",inline:!1,step:this.$.data("step")||1,rotation:this.$.data("rotation"),draw:null,change:null,cancel:null,release:null,format:function(t){return t},parse:function(t){return parseFloat(t)}},this.o),this.o.flip="anticlockwise"===this.o.rotation||"acw"===this.o.rotation,this.o.inputColor||(this.o.inputColor=this.o.fgColor),this.$.is("fieldset")?(this.v={},this.i=this.$.find("input"),this.i.each(function(s){var h=t(this);i.i[s]=h,i.v[s]=i.o.parse(h.val()),h.bind("change blur",function(){var t={};t[s]=h.val(),i.val(i._validate(t))})}),this.$.find("legend").remove()):(this.i=this.$,this.v=this.o.parse(this.$.val()),""===this.v&&(this.v=this.o.min),this.$.bind("change blur",function(){i.val(i._validate(i.o.parse(i.$.val())))})),!this.o.displayInput&&this.$.hide(),this.$c=t(document.createElement("canvas")).attr({width:this.o.width,height:this.o.height}),this.$div=t('<div style="'+(this.o.inline?"display:inline;":"")+"width:"+this.o.width+"px;height:"+this.o.height+'px;"></div>'),this.$.wrap(this.$div).before(this.$c),this.$div=this.$.parent(),"undefined"!=typeof G_vmlCanvasManager&&G_vmlCanvasManager.initElement(this.$c[0]),this.c=this.$c[0].getContext?this.$c[0].getContext("2d"):null,!this.c)throw{name:"CanvasNotSupportedException",message:"Canvas not supported. Please use excanvas on IE8.0.",toString:function(){return this.name+": "+this.message}};return this.scale=(window.devicePixelRatio||1)/(this.c.webkitBackingStorePixelRatio||this.c.mozBackingStorePixelRatio||this.c.msBackingStorePixelRatio||this.c.oBackingStorePixelRatio||this.c.backingStorePixelRatio||1),this.relativeWidth=this.o.width%1!==0&&this.o.width.indexOf("%"),this.relativeHeight=this.o.height%1!==0&&this.o.height.indexOf("%"),this.relative=this.relativeWidth||this.relativeHeight,this._carve(),this.v instanceof Object?(this.cv={},this.copy(this.v,this.cv)):this.cv=this.v,this.$.bind("configure",h).parent().bind("configure",h),this._listen()._configure()._xy().init(),this.isInit=!0,this.$.val(this.o.format(this.v)),this._draw(),this}},this._carve=function(){if(this.relative){var t=this.relativeWidth?this.$div.parent().width()*parseInt(this.o.width)/100:this.$div.parent().width(),i=this.relativeHeight?this.$div.parent().height()*parseInt(this.o.height)/100:this.$div.parent().height();this.w=this.h=Math.min(t,i)}else this.w=this.o.width,this.h=this.o.height;return this.$div.css({width:this.w+"px",height:this.h+"px"}),this.$c.attr({width:this.w,height:this.h}),1!==this.scale&&(this.$c[0].width=this.$c[0].width*this.scale,this.$c[0].height=this.$c[0].height*this.scale,this.$c.width(this.w),this.$c.height(this.h)),this},this._draw=function(){var t=!0;i.g=i.c,i.clear(),i.dH&&(t=i.dH()),t!==!1&&i.draw()},this._xy=function(){var t=this.$c.offset();return this.x=t.left,this.y=t.top,this},this._listen=function(){return this.$.attr("readonly","readonly"),this.relative&&t(window).resize(function(){i._carve().init(),i._draw()}),this},this._configure=function(){return this.o.draw&&(this.dH=this.o.draw),this.o.change&&(this.cH=this.o.change),this.o.cancel&&(this.eH=this.o.cancel),this.o.release&&(this.rH=this.o.release),this.o.displayPrevious?(this.pColor=this.h2rgba(this.o.fgColor,"0.4"),this.fgColor=this.h2rgba(this.o.fgColor,"0.6")):this.fgColor=this.o.fgColor,this},this._clear=function(){this.$c[0].width=this.$c[0].width},this._validate=function(t){var i=~~((0>t?-.5:.5)+t/this.o.step)*this.o.step;return Math.round(100*i)/100},this.listen=function(){},this.extend=function(){},this.init=function(){},this.change=function(t){},this.val=function(t){},this.xy2val=function(t,i){},this.draw=function(){},this.clear=function(){this._clear()},this.h2rgba=function(t,i){var s;return t=t.substring(1,7),s=[parseInt(t.substring(0,2),16),parseInt(t.substring(2,4),16),parseInt(t.substring(4,6),16)],"rgba("+s[0]+","+s[1]+","+s[2]+","+i+")"},this.copy=function(t,i){for(var s in t)i[s]=t[s]}},i.Dial=function(){i.o.call(this),this.startAngle=null,this.xy=null,this.radius=null,this.lineWidth=null,this.cursorExt=null,this.w2=null,this.PI2=2*Math.PI,this.extend=function(){this.o=t.extend({bgColor:this.$.data("bgcolor")||"#EEEEEE",angleOffset:this.$.data("angleoffset")||0,angleArc:this.$.data("anglearc")||360,inline:!0},this.o)},this.val=function(t,i){return null==t?this.v:(t=this.o.parse(t),void(i!==!1&&t!=this.v&&this.rH&&this.rH(t)===!1||(this.cv=this.o.stopper?h(e(t,this.o.max),this.o.min):t,this.v=this.cv,this.$.val(this.o.format(this.v)),this._draw())))},this.xy2val=function(t,i){var s,n;return s=Math.atan2(t-(this.x+this.w2),-(i-this.y-this.w2))-this.angleOffset,this.o.flip&&(s=this.angleArc-s-this.PI2),this.angleArc!=this.PI2&&0>s&&s>-.5?s=0:0>s&&(s+=this.PI2),n=s*(this.o.max-this.o.min)/this.angleArc+this.o.min,this.o.stopper&&(n=h(e(n,this.o.max),this.o.min)),n},this.init=function(){(this.v<this.o.min||this.v>this.o.max)&&(this.v=this.o.min),this.$.val(this.v),this.w2=this.w/2,this.cursorExt=this.o.cursor/100,this.xy=this.w2*this.scale,this.lineWidth=this.xy*this.o.thickness,this.lineCap=this.o.lineCap,this.radius=this.xy-this.lineWidth/2,this.o.angleOffset&&(this.o.angleOffset=isNaN(this.o.angleOffset)?0:this.o.angleOffset),this.o.angleArc&&(this.o.angleArc=isNaN(this.o.angleArc)?this.PI2:this.o.angleArc),this.angleOffset=this.o.angleOffset*Math.PI/180,this.angleArc=this.o.angleArc*Math.PI/180,this.startAngle=1.5*Math.PI+this.angleOffset,this.endAngle=1.5*Math.PI+this.angleOffset+this.angleArc;var t=h(String(Math.abs(this.o.max)).length,String(Math.abs(this.o.min)).length,2)+2;this.o.displayInput&&this.i.css({width:(this.w/2+4>>0)+"px",height:(this.w/3>>0)+"px",position:"absolute","vertical-align":"middle","margin-top":(this.w/3>>0)+"px","margin-left":"-"+(3*this.w/4+2>>0)+"px",border:0,background:"none",font:this.o.fontWeight+" "+(this.w/t>>0)+"px "+this.o.font,"text-align":"center",color:this.o.inputColor||this.o.fgColor,padding:"0px","-webkit-appearance":"none"})||this.i.css({width:"0px",visibility:"hidden"})},this.change=function(t){this.cv=t,this.$.val(this.o.format(t))},this.angle=function(t){return(t-this.o.min)*this.angleArc/(this.o.max-this.o.min)},this.arc=function(t){var i,s;return t=this.angle(t),this.o.flip?(i=this.endAngle+1e-5,s=i-t-1e-5):(i=this.startAngle-1e-5,s=i+t+1e-5),this.o.cursor&&(i=s-this.cursorExt)&&(s+=this.cursorExt),{s:i,e:s,d:this.o.flip&&!this.o.cursor}},this.draw=function(){var t,i=this.g,s=this.arc(this.cv),h=1;i.lineWidth=this.lineWidth,i.lineCap=this.lineCap,"none"!==this.o.bgColor&&(i.beginPath(),i.strokeStyle=this.o.bgColor,i.arc(this.xy,this.xy,this.radius,this.endAngle-1e-5,this.startAngle+1e-5,!0),i.stroke()),this.o.displayPrevious&&(t=this.arc(this.v),i.beginPath(),i.strokeStyle=this.pColor,i.arc(this.xy,this.xy,this.radius,t.s,t.e,t.d),i.stroke(),h=this.cv==this.v),i.beginPath(),i.strokeStyle=h?this.o.fgColor:this.fgColor,i.arc(this.xy,this.xy,this.radius,s.s,s.e,s.d),i.stroke()},this.cancel=function(){this.val(this.v)}},t.fn.n2knob=function(s){return this.each(function(){var h=new i.Dial;h.o=s,h.$=t(this),h.run()}).parent()}})}(n2,window);