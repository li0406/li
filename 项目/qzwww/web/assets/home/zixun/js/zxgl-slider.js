(function($){
	$.fn.zxglSlide=function(opts){
		opts=$.extend({},$.fn.zxglSlide.opts,opts);
		this.each(function(){
			var slidewrap=$(this).find('.slide-wrapper');
			var slide=slidewrap.find('li');
			var count=slide.length;
			var that=this;
			var index=0;
			var time=null;
			$(this).data('opts',opts);
			var bgColor=slide.attr("data-value");
			slidewrap.parents(".zxgl-slide").css("background-color",bgColor);
			$(this).find('.next').on('click',function(){
				if(opts['isAnimate']==true){return;}
			var old=index;if(index>=count- 1){index=0;}else{index++;}
			change.call(that,index,old);});
			$(this).find('.prev').on('click',function(){
				if(opts['isAnimate']==true){
					return;
				}
			var old=index;
			if(index<=0){index=count- 1;}else{index--;}
			change.call(that,index,old);
			});
			$(this).find('.btns ul li').each(function(cindex){
				$(this).on('click.slidebox',function(){
					change.call(that,cindex,index);
					index=cindex;
				});
			});
			startAtuoPlay();
			function startAtuoPlay(){
				if(opts.autoPlay){
					time=setInterval(function(){
						var old=index;
						if(index>=count- 1){
							index=0;
						}else{
							index++;
						}
						change.call(that,index,old);
					},4000);
				}
			}
			var box = $(this).find('.btns');

			box.css({'margin-left':-(box.width()/ 2)})
			switch(opts.dir){
				case"x":opts['width']=$(this).width();
				slidewrap.css({'width':count*opts['width']});
				slide.css({'float':'left','position':'relative'});
				slidewrap.wrap('<div class="ck-slide-dir"></div>');
				slide.show();
				break;
				}
			});
		};
		function change(show,hide){
			var opts=$(this).data('opts');
			if(opts.dir=='x'){
				var x=show*opts['width'];
				$(this).find('.slide-wrapper').stop().animate({'margin-left':-x},
					function(){
						opts['isAnimate']=false;
					});
				opts['isAnimate']=true
			}else{

				$(this).find('.slide-wrapper li').eq(hide).stop().animate({opacity:0},function(){
					$(this).hide()
				});
				$(this).find('.slide-wrapper li').eq(show).show().css({opacity:0})
				.stop().animate({opacity:1}).parents(".home-slider");
			}
				$(this).find('.btns ul li').removeClass('on');
				$(this).find('.btns ul li').eq(show).addClass('on');
		}
$.fn.zxglSlide.opts={autoPlay:true,dir:null,isAnimate:false};})(jQuery);