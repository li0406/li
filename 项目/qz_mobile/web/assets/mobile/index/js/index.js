

'use strict';
$(function(){
	/*苏州页面特效开始*/
	/*banner轮播*/
	TouchSlide({
		slideCell:"#focus",
		titCell:".hd ul", //开启自动分页 autoPage:true ，此时设置 titCell 为导航元素包裹层
		mainCell:".bd ul",
		effect:"leftLoop",
		autoPlay:true,//自动播放
		autoPage:true //自动分页
	});
	/*关闭广告*/
	$(".advert .adv-del").click(function(event) {
		$(this).parent().hide();
	});
	/*打开导航*/
	var flag=0;
	$(".hd-nav").click(function(event){
		if(flag==0){
			$(this).addClass("active");
			$(this).children(".subnav").css("display","block");
			flag=1;
		}else{
			$(this).removeClass("active");
			$(this).children(".subnav").css("display","none");
			flag=0;
		}
	});
	/*苏州页面特效结束*/
});