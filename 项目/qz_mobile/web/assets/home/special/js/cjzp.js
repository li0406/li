// 列表滚动库

// JavaScript Document
;(function($){var l=location.href.replace(/#.*/,'');var g=$.localScroll=function(a){$('body').localScroll(a)};g.defaults={duration:1e3,axis:'y',event:'click',stop:true,target:window,reset:true};g.hash=function(a){if(location.hash){a=$.extend({},g.defaults,a);a.hash=false;if(a.reset){var e=a.duration;delete a.duration;$(a.target).scrollTo(0,a);a.duration=e}i(0,location,a)}};$.fn.localScroll=function(b){b=$.extend({},g.defaults,b);return b.lazy?this.bind(b.event,function(a){var e=$([a.target,a.target.parentNode]).filter(d)[0];if(e)i(a,e,b)}):this.find('a,area').filter(d).bind(b.event,function(a){i(a,this,b)}).end().end();function d(){return!!this.href&&!!this.hash&&this.href.replace(this.hash,'')==l&&(!b.filter||$(this).is(b.filter))}};function i(a,e,b){var d=e.hash.slice(1),f=document.getElementById(d)||document.getElementsByName(d)[0];if(!f)return;if(a)a.preventDefault();var h=$(b.target);if(b.lock&&h.is(':animated')||b.onBefore&&b.onBefore.call(b,a,f,h)===false)return;if(b.stop)h.stop(true);if(b.hash){var j=f.id==d?'id':'name',k=$('<a> </a>').attr(j,d).css({position:'absolute',top:$(window).scrollTop(),left:$(window).scrollLeft()});f[j]='';$('body').prepend(k);location=e.hash;k.remove();f[j]=d}h.scrollTo(f,b).trigger('notify.serialScroll',[f])}})(jQuery);

// 填写个人信息弹窗
$(document).ready(function() {
	$(".userinfo-shut").click(function(event) {
		$(".userinfo").fadeOut(100);
	});
});
// 转盘抽奖

$(function (){

	var rotateTimeOut = function (){
		$('#rotate').rotate({
			angle:0,
			animateTo:2160,
			duration:8000,
			callback:function (){
				alert('网络超时，请检查您的网络设置！');
			}
		});
	};
	var bRotate = true;//抽奖开关

	var rotateFn = function (awards, angles, txt){
		bRotate = !bRotate;

		$('#rotate').stopRotate();
		$('#rotate').rotate({
			angle:0,
			animateTo:angles+1800,
			duration:5000,
			callback:function (){
				document.body.onmousewheel = function(){return false;};
				if(awards == 1){
					message("很遗憾，您本次<strong>"+txt+"</strong>,感谢您的参与。");
				}
				else{
					message("恭喜您！您获得了<strong>"+txt+"</strong>,奖品会在11月30日统一发放给您。");
				};
			}
		})
	};

	$('.pointer').click(function (){
		if(bRotate){
			var _this = $(this);
			$.ajax({
				url: '/prize/',
				type: 'POST',
				dataType: 'JSON',
				data: {
					type:"dazhuanpan"
				}
			})
			.done(function(data) {
				if(data.status == 1){
					rotateFn(data.data.fixed,parseInt(data.data.angle),data.data.prize);
				}else if(data.status == 4){
					$(".userinfo").show();
				}else if(data.status == 5){
					message("亲,每天只能参加一次活动哟！");
				}else{
					message(data.info);
				}
			})
			.fail(function() {
               	message("转盘活动发生了未知的错误,请稍后再试！");
			});
		}else{
			message("亲,每天只能参加一次活动哟！");
		}
	});

	$(".hjmes-sub").click(function(){
	   $(".hjmes").animate({top:'40%',opacity:'0'},200,function(){
	   		$(".hjmes").hide(1000);
	   });

	   document.body.onmousewheel = function(){return true;};
	});
});

function message(txt){
	$(".hjmes").show();
	$(".hjmes").animate({top:'50%',opacity:'1'},200);
	$(".hjinfo").html(txt);
}



