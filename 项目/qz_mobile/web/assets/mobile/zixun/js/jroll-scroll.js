/*! JRoll v2.3.2 ~ (c) 2015-2016 Author:BarZu Git:https://github.com/chjtx/JRoll Website:http://www.chjtx.com/JRoll/ */
;!function(o,r,l){"use strict";function e(o){var r=v.findScroller(o.target);r?(c.jrollActive=r,r.moving&&(o.preventDefault(),r.moving=!1),r._start(o)):c.jrollActive=null}function t(o){if(c.jrollActive){var l=r.activeElement;c.jrollActive.options.preventDefault&&o.preventDefault(),!v.isMobile||"INPUT"!==l.tagName&&"TEXTAREA"!==l.tagName||l.blur(),c.jrollActive._move(o)}}function s(){c.jrollActive&&c.jrollActive._end()}function n(){setTimeout(function(){for(var o in m)m[o].refresh().scrollTo(m[o].x,m[o].y,200)},600)}function i(o){var r=v.findScroller(o.target);r&&r._wheel(o)}function a(o,l){r.addEventListener(o,l,!1)}var c,p="2.3.2",d=o.requestAnimationFrame||o.webkitRequestAnimationFrame||function(o){setTimeout(o,17)},u=r.createElement("div").style,m={},f=navigator.userAgent.toLowerCase(),_=function(){for(var o,r=["t","webkitT","MozT","msT","OT"],l=r.length;l--;)if(o=r[l]+"ransform",o in u)return r[l]}(),v={TSF:_+"ransform",TSD:_+"ransitionDuration",TFO:_+"ransformOrigin",isAndroid:/android/.test(f),isIOS:/iphone|ipad/.test(f),isMobile:/mobile|phone|android|pad/.test(f),translateZ:function(o){var r;return r=o?o+"Perspective"in u:"perspective"in u,r?" translateZ(0px)":""}(_.substr(0,_.length-1)),computePosition:function(o,r){for(var l=0,e=0;o;)l+=o.offsetLeft,e+=o.offsetTop,o=o.offsetParent,o===r&&(o=null);return{left:l,top:e}},moveTo:function(o,r,l,e,t){function s(){p-=17,p<=0?(n=r,i=l):(n=parseInt(n+a,10),i=parseInt(i+c,10)),o.style[v.TSF]="translate("+n+"px, "+i+"px)"+v.translateZ+" scale("+_+")",p>0&&(n!==r||i!==l)?d(s):"function"==typeof t&&t()}var n,i,a,c,p,u,m=0,f=0,_=1;u=/translate\(([\-\d\.]+)px,\s+([\-\d\.]+)px\)\s+(?:translateZ\(0px\)\s+)?scale\(([\d\.]+)\)/.exec(o.style[v.TSF]),u&&(m=Number(u[1]),f=Number(u[2]),_=Number(u[3])),p=e||17,a=(r-m)/(p/17),c=(l-f)/(p/17),n=m,i=f,s()},findScroller:function(o,l){var e;if(l||!("TEXTAREA"===o.tagName&&o.scrollHeight>o.offsetHeight))for(;o!==r;){if(e=o.getAttribute("jroll-id"))return m[e];o=o.parentNode}return null}};a(v.isMobile?"touchstart":"mousedown",e),a(v.isMobile?"touchmove":"mousemove",t),a(v.isMobile?"touchend":"mouseup",s),v.isMobile?a("touchcancel",s):a(/firefox/.test(f)?"DOMMouseScroll":"mousewheel",i),o.addEventListener("resize",n),o.addEventListener("orientationchange",n),c=function(o,r){this._init(o,r)},c.version=p,c.utils=v,c.jrollMap=m,c.prototype={_init:function(e,t){var s=this;if(s.wrapper="string"==typeof e?r.querySelector(e):e,s.scroller=t&&t.scroller?"string"==typeof t.scroller?r.querySelector(t.scroller):t.scroller:s.wrapper.children[0],s.scroller.jroll)return s.scroller.jroll.refresh(),s.scroller.jroll;s.scroller.jroll=s,s.wrapperOffset=v.computePosition(s.wrapper,r.body),s.id=t&&t.id||s.scroller.getAttribute("jroll-id")||"jroll_"+l.random().toString().substr(2,8),s.scroller.setAttribute("jroll-id",s.id),m[s.id]=s,s.options={scrollX:!1,scrollY:!0,scrollFree:!1,minX:null,maxX:null,minY:null,maxY:null,zoom:!1,zoomMin:1,zoomMax:4,bounce:!0,scrollBarX:!1,scrollBarY:!1,scrollBarFade:!1,preventDefault:!0,momentum:!0,autoStyle:!0};for(var n in t)"scroller"!==n&&(s.options[n]=t[n]);s.options.autoStyle&&("static"===o.getComputedStyle(s.wrapper).position&&(s.wrapper.style.position="relative",s.wrapper.style.top="0",s.wrapper.style.left="0"),s.wrapper.style.overflow="hidden",s.scroller.style.minHeight="100%"),s.x=0,s.y=0,s.s=null,s.scrollBarX=null,s.scrollBarY=null,s._s={startX:0,startY:0,lastX:0,lastY:0,endX:0,endY:0},s._z={spacing:0,scale:1,startScale:1},s._event={scrollStart:[],scroll:[],scrollEnd:[],zoomStart:[],zoom:[],zoomEnd:[],refresh:[],touchEnd:[]},s.refresh(!0)},enable:function(){var o=this;return o.scroller.setAttribute("jroll-id",o.id),o},disable:function(){var o=this;return o.scroller.removeAttribute("jroll-id"),o},destroy:function(){var o=this;delete m[o.id],delete o.scroller.jroll,o.scrollBarX&&o.wrapper.removeChild(o.scrollBarX),o.scrollBarY&&o.wrapper.removeChild(o.scrollBarY),o.disable(),o.scroller.style[v.tSF]="",o.scroller.style[v.tSD]="",o.prototype=null;for(var r in o)o.hasOwnProperty(r)&&delete o[r]},call:function(o,r){var l=this;return l._s.lockX=!1,l._s.lockY=!1,l.scrollTo(l.x,l.y),c.jrollActive=o,r&&o._start(r),o},refresh:function(o){var r,e,t,s,n,i,a=this,c=getComputedStyle(a.wrapper),p=getComputedStyle(a.scroller);return a.wrapperWidth=a.wrapper.clientWidth,a.wrapperHeight=a.wrapper.clientHeight,a.scrollerWidth=l.round(a.scroller.offsetWidth*a._z.scale),a.scrollerHeight=l.round(a.scroller.offsetHeight*a._z.scale),r=parseInt(c["padding-left"])+parseInt(c["padding-right"]),e=parseInt(c["padding-top"])+parseInt(c["padding-bottom"]),t=parseInt(p["margin-left"])+parseInt(p["margin-right"]),s=parseInt(p["margin-top"])+parseInt(p["margin-bottom"]),a.minScrollX=null===a.options.minX?0:a.options.minX,a.maxScrollX=null===a.options.maxX?a.wrapperWidth-a.scrollerWidth-r-t:a.options.maxX,a.minScrollY=null===a.options.minY?0:a.options.minY,a.maxScrollY=null===a.options.maxY?a.wrapperHeight-a.scrollerHeight-e-s:a.options.maxY,a.minScrollX<0&&(a.minScrollX=0),a.minScrollY<0&&(a.minScrollY=0),a.maxScrollX>0&&(a.maxScrollX=0),a.maxScrollY>0&&(a.maxScrollY=0),a._s.endX=a.x,a._s.endY=a.y,a.options.scrollBarX?(a.scrollBarX||(n=a._createScrollBar("jroll-xbar","jroll-xbtn",!1),a.scrollBarX=n[0],a.scrollBtnX=n[1]),a.scrollBarScaleX=a.wrapper.clientWidth/a.scrollerWidth,i=l.round(a.scrollBarX.clientWidth*a.scrollBarScaleX),a.scrollBtnX.style.width=(i>8?i:8)+"px",a._runScrollBarX()):a.scrollBarX&&(a.wrapper.removeChild(a.scrollBarX),a.scrollBarX=null),a.options.scrollBarY?(a.scrollBarY||(n=a._createScrollBar("jroll-ybar","jroll-ybtn",!0),a.scrollBarY=n[0],a.scrollBtnY=n[1]),a.scrollBarScaleY=a.wrapper.clientHeight/a.scrollerHeight,i=l.round(a.scrollBarY.clientHeight*a.scrollBarScaleY),a.scrollBtnY.style.height=(i>8?i:8)+"px",a._runScrollBarY()):a.scrollBarY&&(a.wrapper.removeChild(a.scrollBarY),a.scrollBarY=null),o||a._execEvent("refresh"),a},scale:function(o){var r=this,l=parseFloat(o);return isNaN(l)||(r.scroller.style[v.TFO]="0 0",r._z.scale=l,r.refresh()._scrollTo(r.x,r.y),r.scrollTo(r.x,r.y,400)),r},_wheel:function(o){var r=this,l=o.wheelDelta||120*-(o.detail/3);(r.options.scrollY||r.options.scrollFree)&&r.scrollTo(r.x,r._compute(r.y+l,r.minScrollY,r.maxScrollY))},_runScrollBarX:function(){var o=this,r=l.round(-1*o.x*o.scrollBarScaleX);o._scrollTo.call({scroller:o.scrollBtnX,_z:{scale:1}},r,0)},_runScrollBarY:function(){var o=this,r=l.round(-1*o.y*o.scrollBarScaleY);o._scrollTo.call({scroller:o.scrollBtnY,_z:{scale:1}},0,r)},_createScrollBar:function(o,l,e){var t,s,n=this;return t=r.createElement("div"),s=r.createElement("div"),t.className=o,s.className=l,this.options.scrollBarX!==!0&&this.options.scrollBarY!==!0||(e?(t.style.cssText="position:absolute;top:2px;right:2px;bottom:2px;width:6px;overflow:hidden;border-radius:2px;-webkit-transform: scaleX(.5);transform: scaleX(.5);",s.style.cssText="background:rgba(0,0,0,.4);position:absolute;top:0;left:0;right:0;border-radius:2px;"):(t.style.cssText="position:absolute;left:2px;bottom:2px;right:2px;height:6px;overflow:hidden;border-radius:2px;-webkit-transform: scaleY(.5);transform: scaleY(.5);",s.style.cssText="background:rgba(0,0,0,.4);height:100%;position:absolute;left:0;top:0;bottom:0;border-radius:2px;")),n.options.scrollBarFade&&(t.style.opacity=0),t.appendChild(s),n.wrapper.appendChild(t),[t,s]},_fade:function(o,r){var l=this;l.fading&&r>0&&(r-=25,r%100===0&&(o.style.opacity=r/1e3),d(l._fade.bind(l,o,r)))},on:function(o,r){var l=this;switch(o){case"scrollStart":l._event.scrollStart.push(r);break;case"scroll":l._event.scroll.push(r);break;case"scrollEnd":l._event.scrollEnd.push(r);break;case"zoomStart":l._event.zoomStart.push(r);break;case"zoom":l._event.zoom.push(r);break;case"zoomEnd":l._event.zoomEnd.push(r);break;case"refresh":l._event.refresh.push(r);break;case"touchEnd":l._event.touchEnd.push(r)}},_execEvent:function(o,r){for(var l=this,e=l._event[o].length-1;e>=0;e--)l._event[o][e].call(l,r)},_compute:function(o,r,e){var t=this;return o>r?t.options.bounce&&o>r+10?l.round(r+(o-r)/4):r:o<e?t.options.bounce&&o<e-10?l.round(e+(o-e)/4):e:o},_scrollTo:function(o,r){this.scroller.style[v.TSF]="translate("+o+"px, "+r+"px)"+v.translateZ+" scale("+this._z.scale+")"},scrollTo:function(o,r,l,e,t,s,n){var i=this;return e?(i.x=o,i.y=r):(o>=i.minScrollX?(i.x=i.minScrollX,n&&(i._s.startX=n[0].pageX,i._s.endX=i.minScrollX)):o<=i.maxScrollX?(i.x=i.maxScrollX,n&&(i._s.startX=n[0].pageX,i._s.endX=i.maxScrollX)):i.x=o,r>=i.minScrollY?(i.y=i.minScrollY,n&&(i._s.startY=n[0].pageY,i._s.endY=i.minScrollY)):r<=i.maxScrollY?(i.y=i.maxScrollY,n&&(i._s.startY=n[0].pageY,i._s.endY=i.maxScrollY)):i.y=r),s||(i._s.endX=i.x,i._s.endY=i.y),l?v.moveTo(i.scroller,i.x,i.y,l,t):(i._scrollTo(i.x,i.y),"function"==typeof t&&t()),i.scrollBtnX&&i._runScrollBarX(),i.scrollBtnY&&i._runScrollBarY(),i},_endAction:function(){var o=this;o._s.endX=o.x,o._s.endY=o.y,o.moving=!1,o.options.scrollBarFade&&!o.fading&&(o.fading=!0,o.scrollBarX&&o._fade(o.scrollBarX,2e3),o.scrollBarY&&o._fade(o.scrollBarY,2e3)),o._execEvent("scrollEnd")},_stepBounce:function(){function o(){r.scrollTo(r.x,r.y,100)}var r=this;r.bouncing=!1,"scrollY"===r.s?1===r.directionY?(r.scrollTo(r.x,r.minScrollY+20,100,!0,o),r.y=r.minScrollY):(r.scrollTo(r.x,r.maxScrollY-20,100,!0,o),r.y=r.maxScrollY):"scrollX"===r.s&&(1===r.directionX?(r.scrollTo(r.minScrollX+20,r.y,100,!0,o),r.x=r.minScrollX):(r.scrollTo(r.maxScrollX-20,r.y,100,!0,o),r.x=r.maxScrollX))},_x:function(o){var r=this,l=r.directionX*o;isNaN(l)||(r.x=r.x+l,(r.x>=r.minScrollX||r.x<=r.maxScrollX)&&(r.moving=!1,r.options.bounce&&(r.bouncing=!0)))},_y:function(o){var r=this,l=r.directionY*o;isNaN(l)||(r.y=r.y+l,(r.y>=r.minScrollY||r.y<=r.maxScrollY)&&(r.moving=!1,r.options.bounce&&(r.bouncing=!0)))},_xy:function(o){var r=this,e=l.round(r.cosX*o),t=l.round(r.cosY*o);isNaN(e)||isNaN(t)||(r.x=r.x+e,r.y=r.y+t,(r.x>=r.minScrollX||r.x<=r.maxScrollX)&&(r.y>=r.minScrollY||r.y<=r.maxScrollY)&&(r.moving=!1))},_step:function(o){var r=this,e=Date.now(),t=e-o,s=0;if(r.bouncing&&r._stepBounce(),!r.moving)return void r._endAction();if(t>10){if(r.speed=r.speed-t*(r.speed>1.2?.001:r.speed>.6?8e-4:6e-4),s=l.round(r.speed*t),r.speed<=0||s<=0)return void r._endAction();o=e,r._do(s),r.scrollTo(r.x,r.y,0,!1,null,!0),r._execEvent("scroll")}d(r._step.bind(r,o))},_doScroll:function(r,l){var e,t=this;t.distance=r,t.options.bounce&&(t.x=t._compute(t.x,t.minScrollX,t.maxScrollX),t.y=t._compute(t.y,t.minScrollY,t.maxScrollY)),t.scrollTo(t.x,t.y,0,t.options.bounce,null,!0,l.touches||[l]),t._execEvent("scroll",l),l&&l.touches&&(e=l.touches[0].pageY,(e<=10||e>=o.innerHeight-10)&&t._end())},_start:function(o){var r=this,e=o.touches||[o];if((r.options.scrollX||r.options.scrollY||r.options.scrollFree)&&(1===e.length||!r.options.zoom))return r.s="preScroll",r.distance=0,r.lastMoveTime=r.startTime=Date.now(),r._s.lastX=r.startPositionX=r._s.startX=e[0].pageX,r._s.lastY=r.startPositionY=r._s.startY=e[0].pageY,void r._execEvent("scrollStart",o);if(r.s=null,r.options.zoom&&e.length>1){r.s="preZoom",r.scroller.style[v.TFO]="0 0";var t=l.abs(e[0].pageX-e[1].pageX),s=l.abs(e[0].pageY-e[1].pageY);return r._z.spacing=l.sqrt(t*t+s*s),r._z.startScale=r._z.scale,r.originX=l.abs(e[0].pageX+e[1].pageX)/2-r.wrapperOffset.left-r.x,r.originY=l.abs(e[0].pageY+e[1].pageY)/2-r.wrapperOffset.top-r.y,void r._execEvent("zoomStart",o)}},_move:function(o){var r,e,t,s,n,i,a,c,p=this,d=o.touches||[o],u=1,m=1;if(e=d[0].pageX,t=d[0].pageY,s=e-p._s.lastX,n=t-p._s.lastY,p._s.lastX=e,p._s.lastY=t,u=s>=0?1:-1,m=n>=0?1:-1,r=Date.now(),(r-p.lastMoveTime>200||p.directionX!==u||p.directionY!==m)&&(p.startTime=r,p.startPositionX=e,p.startPositionY=t,p.directionX=u,p.directionY=m),p.lastMoveTime=r,i=e-p.startPositionX,a=t-p.startPositionY,"preScroll"===p.s){if(p.options.scrollBarFade&&(p.fading=!1,p.scrollBarX&&(p.scrollBarX.style.opacity=1),p.scrollBarY&&(p.scrollBarY.style.opacity=1)),!p.options.scrollFree&&p.options.scrollY&&(!p.options.scrollX||l.abs(t-p._s.startY)>=l.abs(e-p._s.startX)))return p._do=p._y,void(p.s="scrollY");if(!p.options.scrollFree&&p.options.scrollX&&(!p.options.scrollY||l.abs(t-p._s.startY)<l.abs(e-p._s.startX)))return p._do=p._x,void(p.s="scrollX");if(p.options.scrollFree)return p._do=p._xy,void(p.s="scrollFree")}if("scrollY"===p.s)return p.y=t-p._s.startY+p._s.endY,void p._doScroll(a,o);if("scrollX"===p.s)return p.x=e-p._s.startX+p._s.endX,void p._doScroll(i,o);if("scrollFree"===p.s)return p.x=e-p._s.startX+p._s.endX,p.y=t-p._s.startY+p._s.endY,c=l.sqrt(i*i+a*a),p.cosX=i/c,p.cosY=a/c,void p._doScroll(l.sqrt(i*i+a*a),o);if("preZoom"===p.s){var f,_=l.abs(d[0].pageX-d[1].pageX),v=l.abs(d[0].pageY-d[1].pageY),x=l.sqrt(_*_+v*v),h=x/p._z.spacing*p._z.startScale;return h<p.options.zoomMin?h=p.options.zoomMin:h>p.options.zoomMax&&(h=p.options.zoomMax),f=h/p._z.startScale,p.x=l.round(p.originX-p.originX*f+p._s.endX),p.y=l.round(p.originY-p.originY*f+p._s.endY),p._z.scale=h,p._scrollTo(p.x,p.y),void p._execEvent("zoom",o)}},_end:function(){var o,r,e=this,t=Date.now(),s="scrollY"===e.s,n="scrollX"===e.s,i="scrollFree"===e.s;return c.jrollActive=null,e._execEvent("touchEnd"),s||n||i?(e.duration=t-e.startTime,o=e.y>e.minScrollY||e.y<e.maxScrollY,r=e.x>e.minScrollX||e.x<e.maxScrollX,void(s&&o||n&&r||i&&(o||r)?e.scrollTo(e.x,e.y,300)._endAction():e.options.momentum&&e.duration<200&&e.distance?(e.speed=l.abs(e.distance/e.duration),e.speed=e.speed>2?2:e.speed,e.moving=!0,d(e._step.bind(e,t))):e._endAction())):"preZoom"===e.s?(e._z.scale>e.options.zoomMax?e._z.scale=e.options.zoomMax:e._z.scale<e.options.zoomMin&&(e._z.scale=e.options.zoomMin),e.refresh(),e.scrollTo(e.x,e.y,400),void e._execEvent("zoomEnd")):void("preScroll"!==e.s&&"preZoom"!==e.s||!e.options.scrollBarFade||e.fading||(e.fading=!0,e.scrollBarX&&e._fade(e.scrollBarX,2e3),e.scrollBarY&&e._fade(e.scrollBarY,2e3)))}},"undefined"!=typeof module&&module.exports&&(module.exports=c),"function"==typeof define&&define(function(){return c}),o.JRoll=c}(window,document,Math);