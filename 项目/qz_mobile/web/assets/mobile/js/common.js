$(document).ready(function() {

	/*菜单弹出*/
	var flag=0;
	$(".switch").click(function(event){
		if(flag===0){
			$(this).addClass("active");
			$(this).children("ul").show();
			flag=1;
		}
		else{
			$(this).removeClass("active");
			$(this).children("ul").hide();
			flag=0;
		}
	});

	/*美图展开*/
	$(".flexible").click(function(){
		if(flag===0){
			$(this).parent().addClass("active");
			$(this).html("收起<i class='icon-double-angle-up'></i>");
			flag=1;
		}
		else{
			$(this).parent().removeAttr("class");
			$(this).html("展开<i class='icon-double-angle-down'></i>");
			flag=0;
		}
	});

	if (typeof TouchSlide !== "undefined") {
		/*焦点图轮播*/
		TouchSlide({
			slideCell:"#focus",
			titCell:".hd ul", //开启自动分页 autoPage:true ，此时设置 titCell 为导航元素包裹层
			mainCell:".bd ul",
			effect:"leftLoop",
			autoPlay:true,//自动播放
			autoPage:true //自动分页
		});
	}

	if (typeof smint !== "undefined") {
		/*菜单悬浮(后续代码插入它前面)*/
	    $('.subMenu').smint({
	    	'scrollSpeed' : 1000
	    });
	}
});