//参数绑定
function setMeituSelectParams() {
    if ($.cookie) {
        $.cookie('meitu_terminal_params', $('#meitu_terminal_params').val(), {expires: 7, path: '/'});
    }
    ;
}

$(function () {
    imgOldSize();

    /*加载大图大小重置*/
    function imgOldSize() {
        $("#pic1").get(0).onload = function () {
            var picW = this.naturalWidth;
            var naturH = this.naturalHeight;
            var picH = 700;
            var imgwrapHeight = $(".imgwrap").height(),
                imgwrapWidth = $(".imgwrap").width()
            // 图片的尺寸超出范围

            if (picH > imgwrapHeight && picW > imgwrapWidth) {
                $('.imgsize').width((imgwrapHeight / naturH) * picW);
                $('.imgsize').css({'max-width': '1200px'});
                $('#pic1').css({'width': '100%', 'height': 'auto'});
                $('.imgsize').height(imgwrapHeight);

            } else if (picW > imgwrapWidth && picH <= imgwrapHeight) {
                $('.imgsize').width((imgwrapHeight / naturH) * picW);
                $('.imgsize').css({'max-width': '1200px'});
                $('.i-box').css({'height': '100%'});
                $('#pic1').css({'width': '100%', 'height': 'auto'});
                $('.imgsize').height(imgwrapHeight);

            } else if (picH > imgwrapHeight && picW <= imgwrapWidth) {
                $('.imgsize').height(imgwrapHeight);
                $('#pic1').css({'width': '100%', 'height': '100%'});
                $('.imgsize').width((imgwrapHeight / naturH) * picW);
                $('.i-box').css({'height': '100%'});
                $('.imgsize').css({'max-width': '1200px'});

            } else {
                $('.imgsize').width((imgwrapHeight / naturH) * picW);
                $('.imgsize').css({'max-width': '1200px'});
                $('.i-box').css({'height': '100%'});
                $('.imgsize').height(picH);
                $('#pic1').css({'width': '100%', 'height': '100%'});
            }
        }
    }

    window.onresize = function () {
        imgOldSize();
    }

    /*底部图片列表*/
    var firstpic = $(".picmidmid ul li").eq(1).find("img"); //第一张图片
    var firstsrc = firstpic.attr("bigimg"); // 大图地址
    var isSingle = $('#isSingle').val();
    var isPublicMeitu = $('#isPublicMeitu').val();
    var emptyMUrl = 'http://www.qizuang.com/qwdz/tu/t0.html';
    var emptyGUrl = 'http://www.qizuang.com/qwdz/tu/t0.html';
    var baseUrl = 'http://www.qizuang.com/qwdz/t';

    $("#pic1").attr("src", firstsrc);
    $("#pic1").attr("curindex", 1);
    firstpic.addClass("selectpic");
    /*底部图片列表*/
    var getli = $(".picmidmid ul li");

    function nextclick() {
        imgOldSize();
        var currrentindex = parseFloat($("#pic1").attr("curindex"));
        if(currrentindex === 10){
            var thatId = getli.eq(currrentindex + 1).find("img").attr("data-id");
            goNewHtml(thatId)
            return false
        }else if(currrentindex > 10){
            return false
        }
        console.log(currrentindex)
        var length = getli.length;
        if (currrentindex != (length - 1)) {
            var curli = getli.eq(currrentindex);
            var curnextli = getli.eq(currrentindex + 1);
            var curnextsrc = curnextli.find("img").attr("bigimg");
            var time = curnextli.find("img").data("time");
            $("#created-time").html(time)

            curli.find("img").removeClass("selectpic");
            curnextli.find("img").addClass("selectpic");
            $("#pic1").attr("src", curnextsrc);
            $("#pic1").attr("curindex", currrentindex + 1);
            $(".picshowtxt_left span").text(currrentindex + 2);
            $("a.sc-img").attr("href", curnextsrc);
            var theId = curnextli.find("img").attr("data-id");
            var theTitle = curnextli.find("img").attr("data-meitutitle");
            var fullTitle = curnextli.find("img").attr("data-fulltitle");
            var id = curnextli.find("img").attr("data-id");
            getRelevantimgs(id);
            setfullValue(theId, theTitle, fullTitle);
        } else {
            console.log(isSingle)
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

    $("#nextArrow_B").click(function () {
        nextclick()
    });
    $("#nextArrow").click(function () {
        nextclick()
    });

    function preclick() {
        imgOldSize();
        var currrentindex = parseFloat($("#pic1").attr("curindex"));
        if(currrentindex === 1){
            var thatId = getli.eq(currrentindex - 1).find("img").attr("data-id");
            goNewHtml(thatId)
            return false
        }else if(currrentindex < 1){
            return false
        }
        if (currrentindex != 0) {
            var curli = getli.eq(currrentindex);
            var curnextli = getli.eq(currrentindex - 1);
            var curnextsrc = curnextli.find("img").attr("bigimg");
            var time = curnextli.find("img").data("time");
            $("#created-time").html(time)
            
            //将美图的 标签对应到页面上

            curli.find("img").removeClass("selectpic");
            curnextli.find("img").addClass("selectpic");
            $("#pic1").attr("src", curnextsrc);
            $("#pic1").attr("curindex", currrentindex - 1);
            $(".picshowtxt_left span").text(currrentindex);
            $("a.sc-img").attr("href", curnextsrc);
            var theId = curnextli.find("img").attr("data-id");
            var theTitle = curnextli.find("img").attr("data-meitutitle");
            var id = curnextli.find("img").attr("data-id");
            getRelevantimgs(id);
            setValue(theId, theTitle);
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

    $("#preArrow_B").click(function () {
        preclick()
    });
    $("#preArrow").click(function () {
        preclick()
    });

    function setValue(id, title) {
        var id = id;
        var title = title;
        document.title = title;
        history.pushState(null, null, 'http://www.qizuang.com/qwdz/t' + id + '.html');
        var url = 'http://www.qizuang.com/qwdz/t' + id + '.html'
        $('#smallTit').text(title);
        $('.picshowtop img').attr("alt", title);
        $('.picshowtop img').attr("data-fulltitle", title);
        $('.picshowtop img').parent().attr("title", title).attr("href", url);
    }

    function setfullValue(id, title, fulltitle) {
        var id = id;
        var title = title;
        var fulltitle = fulltitle;
        document.title = fulltitle;
        history.pushState(null, null, 'http://www.qizuang.com/qwdz/t' + id + '.html');
        var url = 'http://www.qizuang.com/qwdz/t' + id + '.html'
        $('#smallTit').text(title);
        $('.picshowtop img').attr("alt", title);
        $('.picshowtop img').attr("data-fulltitle", title);
        $('.picshowtop img').parent().attr("title", title).attr("href", url);
    }

    function goNewHtml(id){
        window.location.href = baseUrl+id+'.html'
    }
    
    getli.click(function () {
        imgOldSize();
        var currentliindex = $(this).index(".picmidmid ul li");
        $(".picmidmid ul li img[class='selectpic']").removeClass("selectpic");
        var currentli = getli.eq(currentliindex);
        currentli.find("img").addClass("selectpic");
        var bigimgsrc = currentli.find("img").attr("bigimg");
        var time = currentli.find("img").data("time");
        $("#created-time").html(time)
        //将美图的 标签对应到页面上
        // setMeituTagsHtml(currentli.find("img").attr("data-id"),currentli.find("img").attr("data-type"))

        $("#pic1").attr("src", bigimgsrc);
        $("#pic1").attr("curindex", currentliindex);
        $("a.sc-img").attr("href", bigimgsrc);
        var theId = currentli.find("img").attr("data-id");
        var theTitle = currentli.find("img").attr("data-meitutitle");
        getRelevantimgs(theId);
        setValue(theId, theTitle);
    });

    // 获取右边相关图片
    function getRelevantimgs(id) {
        $.ajax({
            url: '/qwdz/tu/getTuDetailRelative',
            type: 'GET',
            dataType: 'JSON',
            data: {id: id}
        })
            .done(function (data) {
                if (data.code == 0) {
                    if (data.data.length == 0) {
                        $('#xg-img').hide();
                    } else {
                        $('#xg-img').show();
                        $('.more-xgt').html('');
                        var tpl = '';
                        var scheme_host = $('#scheme_host').val();
                        for (var i = 0; i < data.data.length; i++) {
                            tpl += '<li>' +
                                '<a target="_blank" href="' + scheme_host + data.data[i].id + '.html">' +
                                '<img src="' + data.data[i].image_src_total + '"-w140h105.jpg width="' + data.data[i].img_width + '" height="' + data.data[i].img_height + '" alt="' + data.data[i].image_title + '"/><p>' + data.data[i].title + '</p></a>' +
                                '</li>'
                        }
                        $('.more-xgt').html(tpl);
                    }
                }
            })
    }
});
