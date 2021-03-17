//参数绑定
function setMeituSelectParams(){
    if ($.cookie) {
        $.cookie('meitu_terminal_params', $('#meitu_terminal_params').val(), { expires: 7, path: '/' });
    };
}
$(function() {
    imgOldSize();
    $(".piclistshow li").hover(function() {
        $(this).css("background", "#FAFAFA")
    },
    function() {
        $(this).css("background", "white")
    });
    $(".piclistshow li").last().css("border-bottom", "none");
    $(".piclistshow li").each(function() {
        var curindex = $(this).index(".piclistshow li") + 1;
        if (curindex % 4 == 0) {
            $(this).css({
                "border-right": "none",
                "width": "251px"
            })
        }
    });
    function imgOldSize() {
        $("#pic1").get(0).onload = function() {
            var picW = this.naturalWidth;
            var naturH = this.naturalHeight;
            var picH = 700;
            $(".img-size-info").text("图片尺寸:" + picW + "×" + picH);
            var imgwrapHeight = $(".imgwrap").height(),
                imgwrapWidth = $(".imgwrap").width(),
                changeSize;
            // 图片的尺寸超出范围

            if(picH > imgwrapHeight && picW > imgwrapWidth){
                $('.imgsize').width((imgwrapHeight/naturH)*picW);
                $('.imgsize').css({'max-width':'1200px'});
                $('#pic1').css({'width':'100%','height':'auto'});
                $('.imgsize').height(imgwrapHeight);
                changeSize = parseInt((imgwrapWidth / picW) * 100);

            }else if(picW > imgwrapWidth && picH <= imgwrapHeight){
                $('.imgsize').width((imgwrapHeight/naturH)*picW);
                $('.imgsize').css({'max-width':'1200px'});
                $('.i-box').css({'height':'100%'});
                $('#pic1').css({'width':'100%','height':'auto'});
                $('.imgsize').height(imgwrapHeight);
                changeSize = parseInt((imgwrapWidth / picW) * 100);

            }else if(picH > imgwrapHeight && picW <= imgwrapWidth){
                $('.imgsize').height(imgwrapHeight);
                $('#pic1').css({'width':'100%','height':'100%'});
                $('.imgsize').width((imgwrapHeight/naturH)*picW);
                $('.i-box').css({'height':'100%'});
                $('.imgsize').css({'max-width':'1200px'});
                changeSize = parseInt((imgwrapHeight / picH) * 100);

            }else{
                $('.imgsize').width((imgwrapHeight/naturH)*picW);
                $('.imgsize').css({'max-width':'1200px'});
                $('.i-box').css({'height':'100%'});
                $('.imgsize').height(picH);
                $('#pic1').css({'width':'100%','height':'100%'});
                changeSize = parseInt((picW / picW) * 100);
            }
            $("#imgsize").text(changeSize + "%");
        }
    }
    window.onresize = function() {
        imgOldSize();
    }

    $(document).keydown(function(event) {
        var key = event.keyCode;
        var firstdisplay = $(".firsttop").css("display");
        var enddisplay = $(".endtop").css("display");
        if (firstdisplay == "none" && enddisplay == "none") {
            if (key == 37) {
                preclick()
            } else {
                if (key == 39) {
                    nextclick()
                }
            }
        } else {
            if (key == 27) {
                $(".firsttop").css("display", "none");
                $(".bodymodal").css("display", "none");
                $(".endtop").css("display", "none")
            }
        }
    });
    var firstpic = $(".picmidmid ul li").first().find("img");
    var firstsrc = firstpic.attr("bigimg");
    var firsttxt = firstpic.attr("text");
    var isSingle = $('#isSingle').val();
    var isPublicMeitu = $('#isPublicMeitu').val();
    var emptyMUrl = 'http://www.qizuang.com/tu/j0.html';
	var emptyGUrl = 'http://www.qizuang.com/tu/g0.html';
    if (isPublicMeitu == 'on') {
        var baseUrl = 'http://www.qizuang.com/tu/g';
    } else {
        var baseUrl = 'http://www.qizuang.com/tu/j';
    }
    $("#pic1").attr("src", firstsrc);
    firstpic.addClass("selectpic");
    $(".picshowtxt_right").text(firsttxt);
    var getli = $(".picmidmid ul li");
    function nextclick() {
        imgOldSize();
        var currrentindex = parseFloat($("#pic1").attr("curindex"));
        var length = getli.length;
        if (currrentindex != (length - 1)) {
            var curli = getli.eq(currrentindex);
            if (currrentindex > 3) {
                // getli.eq(currrentindex - 5).css("display", "none");
                getli.eq(currrentindex + 1).css("display", "block")
            }
            var curnextli = getli.eq(currrentindex + 1);
            var curnextsrc = curnextli.find("img").attr("bigimg");
            var curnexttxt = curnextli.find("img").attr("text");
            var curnextalt= curnextli.find(".img_desc").html();
            //将美图的 标签对应到页面上

            // setMeituTagsHtml(curnextli.find("img").attr("data-id"),curnextli.find("img").attr("data-type"))
            if (!curnextalt) {
                $("#imgsize-info").hide();
            }else {
                $(".text-p").html(curnextalt);
                $("#imgsize-info").show();
            }
            curli.find("img").removeClass("selectpic");
            curnextli.find("img").addClass("selectpic");
            $("#pic1").attr("src", curnextsrc);
            $("#pic1").attr("curindex", currrentindex + 1);
            $(".picshowtxt_right").text(curnexttxt);
            $(".picshowtxt_left span").text(currrentindex + 2);
            $("a.sc-img").attr("href", curnextsrc);
            $(".source_left span").text(currrentindex + 2);
            if (isSingle == 'on') {
                var theId = curnextli.find("img").attr("data-id");
                var theTitle = curnextli.find("img").attr("data-meitutitle");
                var fullTitle = curnextli.find("img").attr("data-fulltitle");
                var likes = curnextli.find("img").attr("data-likes");
                var collect = curnextli.find("img").attr("data-collect") ? true: false;
                var id = curnextli.find("img").attr("data-id");
                getRelevantimgs(id);
                setfullValue(theId, theTitle, likes, collect, fullTitle);
            }
        } else {
            if (isSingle == 'on') {
                if ($("#nexturl").val() == 0) {
                    return false;
                } else {
                    setMeituSelectParams();
                    window.location.href = baseUrl + $("#nexturl").val() + '.html';
                }
            } else {
                setMeituSelectParams();
                window.location.href = $(".picshowlist_right a").attr("href");
            }
        }
    }
    $("#nextArrow_B").click(function() {
        nextclick()
    });
    $("#nextArrow").click(function() {
        nextclick()
    });

    // 监听鼠标滚轮事件
    function autoRoll() {
        $(document).on("mousewheel DOMMouseScroll", function(e) {
            var delta = (e.originalEvent.wheelDelta && (e.originalEvent.wheelDelta > 0 ? 1 : -1)) || // chrome & ie
                (e.originalEvent.detail && (e.originalEvent.detail > 0 ? -1 : 1)); // firefox
            $(document).off("mousewheel DOMMouseScroll");
            if (delta > 0) {
                // 向上滚
                preclick()
            } else if (delta < 0) {
                // 向下滚
                nextclick()
            }
            //使用setTimeout方法产生一个延时效果，是的每次滑动鼠标滑轮，只执行一次事件方法
            setTimeout(autoRoll, 500);
        });
    }
    autoRoll();

    function preclick() {
        imgOldSize();
        var currrentindex = parseFloat($("#pic1").attr("curindex"));
        if (currrentindex != 0) {
            var curli = getli.eq(currrentindex);
            var length = getli.length;
            if (currrentindex <= (length - 5)) {
                getli.eq(currrentindex + 4).css("display", "block");
                getli.eq(currrentindex - 3).css("display", "block")
            }
            var curnextli = getli.eq(currrentindex - 1);
            var curnextsrc = curnextli.find("img").attr("bigimg");
            var curnexttxt = curnextli.find("img").attr("text");
            var curnextalt= curnextli.find(".img_desc").html();
            //将美图的 标签对应到页面上
            // setMeituTagsHtml(curnextli.find("img").attr("data-id"),curnextli.find("img").attr("data-type"))
            if (!curnextalt) {
                $("#imgsize-info").hide();
            }else {
                $(".text-p").html(curnextalt);
                $("#imgsize-info").show();
            }
            curli.find("img").removeClass("selectpic");
            curnextli.find("img").addClass("selectpic");
            $("#pic1").attr("src", curnextsrc);
            $(".picshowtxt_right").text(curnexttxt);
            $("#pic1").attr("curindex", currrentindex - 1);
            $(".picshowtxt_left span").text(currrentindex);
            $("a.sc-img").attr("href", curnextsrc);
            $(".source_left span").text(currrentindex);
            if (isSingle == 'on') {
                var theId = curnextli.find("img").attr("data-id");
                var theTitle = curnextli.find("img").attr("data-meitutitle");
                var likes = curnextli.find("img").attr("data-likes");
                var collect = curnextli.find("img").attr("data-collect") ? true: false;
                var id = curnextli.find("img").attr("data-id");
                getRelevantimgs(id);
                setValue(theId, theTitle, likes, collect);
            }
        } else {
            if (isSingle == 'on') {
                if ($("#preurl").val() == 0) {
                    // alert('该条件下没有更多的美图啦！');
                    return false;
                } else {
                    setMeituSelectParams();
                    window.location.href = baseUrl + $("#preurl").val() + '.html';
                }
            } else {
                if ($(".picshowlist_left a").attr("href") == emptyMUrl || $(".picshowlist_left a").attr("href") == emptyGUrl) {
                    // alert('该条件下没有更多的美图啦！');
                    return false;
                } else {
                    setMeituSelectParams();
                    window.location.href = $(".picshowlist_left a").attr("href");
                }
            }
        }
    }
    if($('.fl-ul li').children().length == 0){
        $('.fl-ul').hide();
    }   
    // 获取右边相关图片
    function getRelevantimgs(id) {
        $.ajax({
            url: '/getrelevantimgs',
            type: 'GET',
            dataType: 'JSON',
            data: {id:id}
        })
            .done(function(data) {
                if(data.error_code == 1){
                    // 分类渲染
                    if(data.categoryinfo.type == 1){
                        $('.gz-name').html(data.categoryinfo.first_name);
                    }else{
                        if(data.categoryinfo.first_name == ''){
                            $('#s-nav').hide();
                            $('#s-nav').next('dd').hide();
                        } else {
                            if($('#s-nav').length > 0){
                                $('#s-nav').show();
                                $('#s-nav').next('dd').show();
                                var tpl1 = '<a class="dt-tit font-s" href="'+ data.categoryinfo.first_url +'">'+ data.categoryinfo.first_name +'<i class="fa fa-angle-down"></i></a><ul class="fl-ul"><li></li></ul>';
                                var tpl2 = '';
                                for(var j = 0;j < data.categoryinfo.other.length;j++){
                                    tpl2 +=  '<a href="'+ data.categoryinfo.other[j].url +'" title="'+ data.categoryinfo.other[j].name +'装修效果图">'+ data.categoryinfo.other[j].name +'</a>'
                                };
                                $('#s-nav').html(tpl1);
                                $('.fl-ul li').html(tpl2);
                            }else{
                                $('#nextnav').after('<dt class="bread-nav" id="s-nav"></dt><dd><i class="angle-right"></i></dd>');
                                $('#s-nav').show();
                                $('#s-nav').next('dd').show();
                                var tpl1 = '<a class="dt-tit font-s" href="'+ data.categoryinfo.first_url +'">'+ data.categoryinfo.first_name +'<i class="fa fa-angle-down"></i></a><ul class="fl-ul"><li></li></ul>';
                                var tpl2 = '';
                                for(var j = 0;j < data.categoryinfo.other.length;j++){
                                    tpl2 +=  '<a href="'+ data.categoryinfo.other[j].url +'" title="'+ data.categoryinfo.other[j].name +'装修效果图">'+ data.categoryinfo.other[j].name +'</a>'
                                };
                                $('#s-nav').html(tpl1);
                                $('.fl-ul li').html(tpl2);
                            }
                            if(data.categoryinfo.other.length == 0){
                                $('.fl-ul').hide();
                            }
                        }
                    }
                    
                    
                    if(data.data.length == 0){
                        $('#xg-img').hide();
                    } else {
                        $('#xg-img').show();
                        $('.more-xgt').html('');
                        var tpl = '';
                        var scheme_host = $('#scheme_host').val();
                        for(var i = 0;i < data.data.length;i++){
                            tpl +=  '<li>'+
                                        '<a target="_blank" href="'+ scheme_host +data.data[i].meitu_type+data.data[i].id+'.html">'+
                                        '<img src="'+ data.data[i].image_src +'" width="'+ data.data[i].img_width +'" height="'+ data.data[i].img_height +'" alt="'+ data.data[i].image_title +'"/></a>'+
                                    '</li>'
                        }
                        $('#pubTime').html(data.publish_time);
                        $('.more-xgt').html(tpl);
                    } 
                }
            })
    }
    $("#preArrow_B").click(function() {
        preclick()
    });
    $("#preArrow").click(function() {
        preclick()
    });
    function setValue(id, title, likes, collect) {
        var id = id;
        var title = title;
        document.title = title;
        $('.source_left-tit').text(title);
        if (isPublicMeitu == 'on') {
            history.pushState(null, null, 'http://www.qizuang.com/tu/g' + id + '.html');
            var url = 'http://www.qizuang.com/tu/g' + id + '.html'
        } else {
            history.pushState(null, null, 'http://www.qizuang.com/tu/j' + id + '.html');
            var url = 'http://www.qizuang.com/tu/j' + id + '.html'
        }
        $('#smallTit').text(title);
        $('.likeit span').text(likes);
        if (collect) {
            $('.collect').attr("data-on", 1).html('<i class="on fa fa-star"></i><span class="font-limit">已收藏<i class="line">|</i></span>');
        } else {
            $('.collect').attr("data-on", 0).html('<i class="star fa fa-star-o"></i><span class="font-limit">收藏<i class="line">|</i></span>');
        }
        $('.picshowtop img').attr("alt", title);
        $('.picshowtop img').attr("data-fulltitle", title);
        $('.picshowtop img').parent().attr("title", title).attr("href", url);
    }
    function setfullValue(id, title, likes, collect, fulltitle) {
        var id = id;
        var title = title;
        var fulltitle = fulltitle;
        document.title = fulltitle;
        $('.source_left-tit').text(title);
        if (isPublicMeitu == 'on') {
            history.pushState(null, null, 'http://www.qizuang.com/tu/g' + id + '.html');
            var url = 'http://www.qizuang.com/tu/g' + id + '.html'
        } else {
            history.pushState(null, null, 'http://www.qizuang.com/tu/j' + id + '.html');
            var url = 'http://www.qizuang.com/tu/j' + id + '.html'
        }
        $('#smallTit').text(title);
        $('.likeit span').text(likes);
        if (collect) {
            $('.collect').attr("data-on", 1).html('<i class="on fa fa-star"></i><span class="font-limit">已收藏<i class="line">|</i></span>');
        } else {
            $('.collect').attr("data-on", 0).html('<i class="star fa fa-star-o"></i><span class="font-limit">收藏<i class="line">|</i></span>');
        }
        $('.picshowtop img').attr("alt", title);
        $('.picshowtop img').attr("data-fulltitle", title);
        $('.picshowtop img').parent().attr("title", title).attr("href", url);
    }
    getli.click(function() {
        imgOldSize();
        var currentliindex = $(this).index(".picmidmid ul li");
        $(".picmidmid ul li img[class='selectpic']").removeClass("selectpic");
        var currentli = getli.eq(currentliindex);
        currentli.find("img").addClass("selectpic");
        var bigimgsrc = currentli.find("img").attr("bigimg");
        var curnexttxt = currentli.find("img").attr("text");
        var curnextalt= currentli.find(".img_desc").html();
        //将美图的 标签对应到页面上
        // setMeituTagsHtml(currentli.find("img").attr("data-id"),currentli.find("img").attr("data-type"))
        if (!curnextalt) {
            $("#imgsize-info").hide();
        }else {
            $(".text-p").html(curnextalt);
            $("#imgsize-info").show();
        }
        $("#pic1").attr("src", bigimgsrc);
        $("#pic1").attr("curindex", currentliindex);
        $(".picshowtxt_right").text(curnexttxt);
        $(".picshowtxt_left span").text(currentliindex + 1);
        $('.source_left-tit').find('span').text(currentliindex+1);
        $("a.sc-img").attr("href", bigimgsrc);
        if (isSingle == 'on') {
            var theId = currentli.find("img").attr("data-id");
            var theTitle = currentli.find("img").attr("data-meitutitle");
            var likes = currentli.find("img").attr("data-likes");
            var collect = currentli.find("img").attr("data-collect") ? true: false;
            getRelevantimgs(theId);
            setValue(theId, theTitle, likes, collect);
        }
    });
    $(".piclistshow li").click(function() {
        imgOldSize();
        $(".imgwrap").css("transform", "scale(1)");
        var curli = $(this).index(".piclistshow li");
        showgaoqing();
        $(".picmidmid ul li img[class='selectpic']").removeClass("selectpic");
        var currentli = getli.eq(curli);
        currentli.find("img").addClass("selectpic");
        var bigimgsrc = currentli.find("img").attr("bigimg");
        var curnexttxt = currentli.find("img").attr("text");
        $("#pic1").attr("src", bigimgsrc);
        $("#pic1").attr("curindex", curli);
        $(".picshowtxt_right").text(curnexttxt);
        $(".picshowtxt_left span").text(curli + 1);
        $(".picmidmid li").css("display", "block");
        if (curli >= 5) {
            var cha = curli - 5;
            for (var i = 0; i <= cha; i++) {
                getli.eq(i).css("display", "none")
            }
        }
    });
    setblock();
    function setblock() {
        var left = $(window).width() / 2 - 300;
        $(".firsttop").css("left", left);
        $(".endtop").css("left", left)
    }
    $(window).resize(function() {
        setblock()
    });
    $(".closebtn1").click(function() {
        $(".firsttop").css("display", "none");
        $(".bodymodal").css("display", "none")
    });
    $(".closebtn2").click(function() {
        $(".endtop").css("display", "none");
        $(".bodymodal").css("display", "none")
    });
    $(".replaybtn1").click(function() {
        $(".firsttop").css("display", "none");
        $(".bodymodal").css("display", "none")
    });
    $(".replaybtn2").click(function() {
        $(".endtop").css("display", "none");
        $(".bodymodal").css("display", "none");
        $(".detail_picbot_mid ul li img[class='selectpic']").removeClass("selectpic");
        $(".detail_picbot_mid ul li").eq(0).find("img").addClass("selectpic");
        var bigimgsrc = $(".detail_picbot_mid ul li").eq(0).find("img").attr("bigimg");
        $("#pic1").attr("src", bigimgsrc);
        $("#pic1").attr("curindex", 0)
    });
    $(".list").click(function() {
        $(".picshow").css("display", "none");
        $(".piclistshow").css("display", "block");
        $(".source_right").css("display", "none");
        $(".source_right1").css("display", "block")
    });
    $(".gaoqing").click(function() {
        showgaoqing();
    });
    function showgaoqing() {
        $(".picshow").css("display", "block");
        $(".piclistshow").css("display", "none");
        $(".source_right").css("display", "block");
        $(".source_right1").css("display", "none")
    }
    $(".rank ul").first().css("display", "block");
    $(".ranknext").click(function() {
        var showindex = $(this).attr("show");
        var show = parseInt(showindex) + 1;
        var length = $(".rank ul").length;
        if (show < length) {
            $(".rank ul").eq(showindex).css("display", "none");
            $(".rank ul").eq(show).css("display", "block");
            $(this).attr("show", show);
            $(".rank ul").eq(show).find("img").lazyload()
        } else {
            $(".rank ul").css("display", "none");
            $(".rank ul").first().css("display", "block");
            $(this).attr("show", 0);
            $(".rank ul").first().find("img").lazyload()
        }
    });
    $(".tuijian").click(function() {
        var showindex = $(this).attr("show");
        var show = parseInt(showindex) + 1;
        var length = $(".rank1 ul").length;
        if (show < length) {
            $(".rank1 ul").eq(showindex).css("display", "none");
            $(".rank1 ul").eq(show).css("display", "block");
            $(this).attr("show", show)
        } else {
            $(".rank1 ul").css("display", "none");
            $(".rank1 ul").first().css("display", "block");
            $(this).attr("show", 0)
        }
    });
    $(".checkimg").find("#smaller").on("click",function() {

        var imgwrapHeight = $(".imgwrap").height(),
        	imgwrapWidth = $(".imgwrap").width(),
            imgsizeH = $(".imgsize").height(),
            imgsizeW = $(".imgsize").width(),
        	redSize,
            changeSize;
            // debugger
            if(imgsizeW<=200){
                return
            }
            redSize=(imgsizeH > imgwrapHeight && imgsizeW < imgwrapWidth)?imgsizeH:imgsizeW;
            redSize -= 50;
            $('.imgsize').width(redSize);
            $('.imgsize').height((redSize*imgsizeH)/imgsizeW);
            $('#pic1').css({'width':'100%','height':'auto'});
            changeSize = parseInt((redSize / $("#pic1").get(0).naturalWidth) * 100);
            $("#imgsize").text(changeSize + "%");
    });
    $(".checkimg").find("#bigger").on("click",function() {

        var imgwrapHeight = $(".imgwrap").height(),
        	imgwrapWidth = $(".imgwrap").width(),
            imgsizeH = $(".imgsize").height(),
            imgsizeW = $(".imgsize").width(),
        	addSize,
        	changeSize,
            natureHeight= $('#pic1').get(0).naturalHeight;
		    // 图片的尺寸超出范围
            if(imgsizeH > natureHeight){
                return
            }
            addSize=(imgsizeH > natureHeight&& imgsizeW < imgwrapWidth)?imgsizeH:imgsizeW;
            addSize+=50;
            $('.imgsize').width(addSize);
            $('.imgsize').height((addSize*imgsizeH)/imgsizeW);
            $('#pic1').css({'width':'100%','height':'auto'});
            changeSize = parseInt(($(".imgsize").width() / natureHeight * 100));
            $("#imgsize").text(changeSize + "%");
    });
    var toggle;
    var flag = 1;

    if ($('.picpointer .picshowtoggle i').hasClass('fa-angle-double-up')) {
        toggle = 1;
        $(".picshow").css("bottom", "30px");
        $(".picpointer").css("top", "-32px");
    }

    $(".tagimg").on('click',function() {

    	var imgwrapWidth = $('.imgwrap').width(),
    		imgwrapHeight = $('.imgwrap').height(),
            picW = $('#pic1').get(0).naturalWidth,
            picH = $('#pic1').get(0).naturalHeight,
            changeSize;
            $('.imgsize').height(picH);
            $('.imgsize').width(picW);

        if (flag == 1) {
            changeSize = 100;
            $(this).html('合适尺寸<i class="fa fa-object-group"></i>');
            $("#imgsize").text(changeSize + "%");
            flag = 0;

        } else {

        	if(picW > imgwrapWidth && picH > imgwrapHeight){

        		$('.imgsize').width(imgwrapWidth);
        		$('.imgsize').height((picH*imgwrapWidth)/picW);
                changeSize = parseInt((imgwrapWidth / picW) * 100);

        	}else if(picH > imgwrapHeight && picW < imgwrapWidth){

        		$('.imgsize').height(imgwrapHeight);
        		$('#pic1').css({'width':'auto','height':'100%'});
        		$('.imgsize').width((imgwrapHeight*picW)/picH);
                changeSize = parseInt((imgwrapHeight / picH) * 100);
        	}else{
        		$('.imgsize').height($('#pic1').height());
                changeSize = 100;
        	}
        	$("#imgsize").text(changeSize+"%");
            $(this).html('原始尺寸<i class="fa fa-object-ungroup"></i>');
            flag = 1;
        }

    });
    $(".img-size-info").text("图片尺寸:" + $("#pic1").width() + "x" + $("#pic1").height());
    $("a.sc-img").attr("href", $(".picmidmid ul li").find("img").attr("bigimg"));
    $(".picshowtoggle").click(function() {
        imgOldSize();
        if (toggle != 1) {
            $(".picshow").css("bottom", "30px");
            $(".picpointer").css("top", "-32px");
            $(this).html('图片列表<i class="fa fa-angle-double-up"></i>');
            toggle = 1;
        } else {
            $(".picpointer").css("top", "-22px");
            $(".picshow").css("bottom", "140px");
            $(this).html('图片列表<i class="fa fa-angle-double-down"></i>');
            toggle = 0;
        }
    });
    $(".sc-img").click(function() {})
});
function DownLoadReportIMG(imgPathURL) {
    if (!document.getElementById("IframeReportImg")) $('<iframe style="display:none;" id="IframeReportImg" name="IframeReportImg" onload="DoSaveAsIMG();" width="0" height="0" src="about:blank"></iframe>').appendTo("body");
    if (document.all.IframeReportImg.src != imgPathURL) {
        document.all.IframeReportImg.src = imgPathURL;
    } else {
        DoSaveAsIMG();
    }
}
function DoSaveAsIMG() {
    if (document.all.IframeReportImg.src != "about:blank") document.frames("IframeReportImg").document.execCommand("SaveAs");
}

/**
 * 获取对应美图的tag
 * @param id
 * @param type 请求类型 家装:meitu 公装:pubmeitu
 */
// function setMeituTagsHtml(id,type) {
//     $.ajax({
//         url: '/meitutag/',
//         type: 'POST',
//         dataType: 'JSON',
//         data: {
//             id:id,
//             type:type,
//         }
//     })
//         .done(function(data) {
//             if (data.status == 1) {
//                 var html = '';
//                 if(type == 'meitu'){
//                     html +='<li><a rel="nofollow" href="http://meitu.qizuang.com'+data.info.wz.href+'/" title="" target="_blank">'+data.info.wz.name+'</a></li>' +
//                         '<li><a rel="nofollow" href="http://meitu.qizuang.com'+data.info.fg.href+'/" title="" target="_blank">'+data.info.fg.name+'</a></li>'+
//                         '<li><a rel="nofollow" href="http://meitu.qizuang.com'+data.info.hx.href+'/" title="" target="_blank">'+data.info.hx.name+'</a></li>'+
//                         '<li><a rel="nofollow" href="http://meitu.qizuang.com'+data.info.ys.href+'/" title="" target="_blank">'+data.info.ys.name+'</a></li>';
//                 }
//                 if(type == 'pubmeitu'){
//                     $('.likeit span').text(data.info.likes);
//                     html +='<li><a rel="nofollow" href="http://meitu.qizuang.com'+data.info.wz.href+'/" title="" target="_blank">'+data.info.wz.name+'</a></li>' +
//                         '<li><a rel="nofollow" href="http://meitu.qizuang.com'+data.info.fg.href+'/" title="" target="_blank">'+data.info.fg.name+'</a></li>'+
//                         '<li><a rel="nofollow" href="http://meitu.qizuang.com'+data.info.mj.href+'/" title="" target="_blank">'+data.info.mj.name+'</a></li>';
//                 }
//                 $(".meitu_tag").html(html);
//             }
//         });
// }
