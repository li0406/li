$(function() {
	$(".piclistshow li").hover(function() {
		$(this).css("background", "#FAFAFA")
	},
	function() {
		$(this).css("background", "white")
	});

	$(".piclistshow li").last().css("border-bottom", "none");

	$(document).keydown(function(event) {
		var key = event.keyCode;
			//Left Arrow 37
			if (key == 37) {
				preclick()
			} else {
				//Right Arrow	39
				if (key == 39) {
					nextclick()
				}
			}

	});

	var firstpic = $(".defaultImg");
	var firstsrc = firstpic.attr("bigimg");

	$("#pic1").attr("src", firstsrc);

	firstpic.addClass("selectpic");
	firstpic.parent().parent().addClass('selectli');

	$("#preArrow").hover(function() {
		$("#preArrow").css("cursor", "url('/assets/home/meitu/img/cursorleft.ico'),auto");
		//$("#preArrow_A").css("display", "block")
	},
	function() {
		$("#preArrow_A").css("display", "none")
	});
	$("#nextArrow").hover(function() {
		$("#nextArrow").css("cursor", "url('/assets/home/meitu/img/cursorright.ico'),auto");
		//$("#nextArrow_A").css("display", "block")
	},

	function() {
		$("#nextArrow_A").css("display", "none")
	});
	var getli = $(".picmidmid ul li");

	function nextclick() {
		var currrentindex = parseFloat($(".picmidmid .selectpic").attr("curindex"));
		var length = getli.length;
		if (currrentindex != (length - 1)) {
			var picshowtop = $(".picshowtop");
			var curli = getli.eq(currrentindex);
			var curnextli = getli.eq(currrentindex + 1);
			var curnextsrc = curnextli.find("img").attr("bigimg");
			var curnexttxt = curnextli.find("img").attr("text");
			curli.find("img").removeClass("selectpic");
			curli.removeClass("selectli");
			curnextli.find("img").addClass("selectpic");
			curnextli.addClass('selectli');
			$("#pic1").attr("src", curnextsrc);
			$("#pic1").attr("curindex", currrentindex + 1);
			picshowtop.find(".bigshow").hide().eq(currrentindex+1).show();
			var id = picshowtop.find(".bigshow").eq(currrentindex+1).find('img').attr("data-id");
			var title = picshowtop.find(".bigshow").eq(currrentindex).find('img').attr("alt");
			document.title = title;
			history.pushState( null,null, '/meitu/p'+id+'.html');
		} else {
			if($("#nexturl").attr("href") != 'http://www.qizuang.com/meitu/p.html'){
				window.location.href = $("#nexturl").attr("href");
			}
		}
	}

	function preclick() {
		var currrentindex = parseFloat($(".picmidmid .selectpic").attr("curindex"));
		if (currrentindex != 0) {
			var curli = getli.eq(currrentindex);
			var length = getli.length;
			var picshowtop = $(".picshowtop");
			$(".picmidmid .selectpic").removeClass('selectpic');
			curli.removeClass('selectli');
			getli.eq(currrentindex-1).find("img").addClass("selectpic");
			getli.eq(currrentindex-1).addClass('selectli');
			var curnextsrc = getli.eq(currrentindex-1).find("img").attr("bigimg");
			$("#pic1").attr("src", curnextsrc);
			picshowtop.find(".bigshow").hide().eq(currrentindex-1).show();
			$(".picshowtxt_left span").text(currrentindex);
			var id = picshowtop.find(".bigshow").eq(currrentindex-1).find('img').attr("data-id");
			var title = picshowtop.find(".bigshow").eq(currrentindex-1).find('img').attr("alt");
			document.title = title;
			history.pushState( null,null, '/meitu/p'+id+'.html');
		} else {
			if($("#preurl").attr("href") == 'http://www.qizuang.com/meitu/p0.html'){

			}else{
				window.location.href = $("#preurl").attr("href");
			}
		}
	}

	$("#nextArrow_B").click(function() {
		nextclick()
	});
	$("#nextArrow").click(function() {
		nextclick()
	});
	$("#preArrow_B").click(function() {
		preclick()
	});
	$("#preArrow").click(function() {
		preclick()
	});

	getli.click(function() {
		var currentliindex = $(this).index(".picmidmid ul li");
		$(".picmidmid ul li .selectpic").removeClass("selectpic");
		var currentli = getli.eq(currentliindex);
		currentli.find("img").addClass("selectpic");
		var bigimgsrc = currentli.find("img").attr("bigimg");
		var curnexttxt = currentli.find("img").attr("text");
		$("#pic1").attr("src", bigimgsrc);
		$("#pic1").attr("curindex", currentliindex);
		$(".picshowtop").find(".bigshow").hide().eq(currentliindex).show();
		$(".picshowtxt_right").text(curnexttxt);
		$(".picshowtxt_left span").text(currentliindex + 1);
		var id = $(".picshowtop").find(".bigshow").eq(currentliindex).find('img').attr("data-id");
		var title = $(".picshowtop").find(".bigshow").eq(currentliindex).find('img').attr("alt");
		document.title = title;
		history.pushState( null,null, '/meitu/p'+id+'.html');
	});

	setblock();

	$(".imgbox a:first ").show();

	function reSetImg(){
		var ratio = 0;
		var Img = $(".imgbox a:first ").find("img");
		var width = Img.width();
		var height = Img.height();
		var theImage = new Image();
		theImage.src = Img.attr("src");
		var w = theImage.width;
		var h = theImage.height;
	    if(width > w){
	        ratio = w / width;
	        Img.css("width", w);
	        height = height * ratio;
	        Img.css("height", height * ratio);
	    }
	    if(height > h){
	        ratio = h / height;
	        Img.css("height", h);
	        width = width * ratio;
	        Img.css("width", width * ratio);
	    }
	}

	function setblock() {
		var left = $(window).width() / 2 - 300;
		$(".firsttop").css("left", left);
		$(".endtop").css("left", left);
		$(".picshowtop").css("width",$(".picshow").width());
		$(".picshowtop").css("height",$(".picshow").height());
		reSetImg();
		var h = $(".right-sider").height() + $('.wlmain').height();
    	$(".meitu-wrap").height(h);
	}

	$(window).resize(function() {
		setblock();
	});

	function setwh(){
		var preArroww = parseInt($("#preArrow").css("width"),0);
		var SelectImg = 0;
		$(".imgbox").find("a").each(function(){
			if($(this).css("display") == "inline"){
				SelectImg = parseInt($(this).css("width"),0)/2;
			}
		})
		if(SelectImg == 0.5 || SelectImg == 0){
			$("#preArrow_A").css("left",0);
			$("#nextArrow_A").css("right",0);
		}else{
			$("#preArrow_A").css("left",(preArroww-SelectImg));
			$("#nextArrow_A").css("right",(preArroww-SelectImg));
		}
	}

});