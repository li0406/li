$(function(){
    $(".loginout").click(function(event) {
        $.ajax({
            url: '/loginout/',
            type: 'GET',
            dataType: 'JSON',
            data: {
              ssid:"{$ssid}"
            }
        })
        .done(function(data) {
            if(data.status == 1){
                window.location.href = window.location.href;
            }else{
              $.pt({
                  target: _this,
                  content: data.info,
                  width: 'auto'
              });
            }
        })
        .fail(function(xhr) {
            $.pt({
                target: _this,
                content: '操作失败,请稍后再试！',
                width: 'auto'
            });
        });
    });

    $(".search-nav ul li").click(function(event) {
        $(".options em").html($(this).html());
        $(".search-nav input[type=text]").attr("placeholder", $(this).attr("placeholder"));
        var url = $(this).attr("target");
        $(".search-form").attr("action",url);
        $(".search-nav .first-search").html($(this).html()+'<i class="fa fa-sort-asc"></i>').find('i').css('line-height','30px');
    });
    $(".search-nav button").click(function(event) {
        var url = $(".search-form").attr("action");
        var keyword = $(".search-nav input[name=keyword]").val();
        url += "?keyword="+keyword;
        window.open(url);
    });

    $(".pub-head-nav ul li").removeClass('active');
    $(".pub-head-nav ul li").eq("{$tabIndex}").addClass('active');

    $(".pub-city").hover(function() {
        $(this).addClass('active');
    }, function() {
        $(this).removeClass('active');
    });
    var timer = null;
    $('.first-search').mouseenter(function() {
        $(this).css('color','#ff5353').find('i').removeClass('fa-sort-desc').addClass('fa-sort-asc').css('line-height','30px')
        clearTimeout(timer);
        $('.search-nav ul').show();
    });
    $('.first-search').mouseleave(function() {
        timer = setTimeout(function(){
            $('.search-form ul').hide();
            $(this).css('color','#333').find('i').removeClass('fa-sort-asc').addClass('fa-sort-desc').css('line-height','22px')
        },500);
    });
    $('.search-nav ul').mouseenter(function() {
        $('.first-search').css('color','#ff5353').find('i').removeClass('fa-sort-desc').addClass('fa-sort-asc').css('line-height','30px')
        clearTimeout(timer);
        $(this).show();
    });
    $('.search-nav ul').mouseleave(function() {
        $('.first-search').css('color','#333').find('i').removeClass('fa-sort-asc').addClass('fa-sort-desc').css('line-height','22px')
        clearTimeout(timer);
        $(this).hide();
    });

    var timer1 = null;
    var timer2 = null;
    $(".pub-head-nav li").mouseenter(function(event) {
        clearTimeout(timer2);
        clearTimeout(timer1);
        $(this).addClass('on').find('i').removeClass('fa-sort-desc').addClass('fa-sort-asc').css('line-height','56px');
        $(this).siblings().removeClass('on').find('i').removeClass('fa-sort-asc').addClass('fa-sort-desc').css('line-height','47px');
        var pub_text=$(this).data("pub");
        if(pub_text){
            $('.pub-nav-hover').show().attr('state',pub_text);
            $("."+pub_text).show();
            $("."+pub_text).siblings().hide();
        }else{
            $('.pub-nav-hover').hide();
        }
    });

     $(".pub-head-nav li").mouseleave(function(event) {
        var that=$(this);
        timer1 = setTimeout(function(){
            that.removeClass('on').find('i').removeClass('fa-sort-asc').addClass('fa-sort-desc').css('line-height','47px');
            $('.pub-nav-hover').hide().removeAttr('state');
            $(".zxgl-nav , .zxmt-nav, .more-nav").hide();
            clearTimeout(timer1);
        },500);
    });
    $('.pub-nav-hover').mouseenter(function() {
        clearTimeout(timer1);
        clearTimeout(timer2);
        $(this).show();
        if($(this).attr('state') == 'zxmt-nav'){
            $('.nav-list-meitu').addClass('on').find('i').removeClass('fa-sort-desc').addClass('fa-sort-asc').css('line-height','56px');
            $('.nav-list-gonglue').removeClass('on').find('i').removeClass('fa-sort-asc').addClass('fa-sort-desc').css('line-height','47px');
            $('.nav-list-more').removeClass('on').find('i').removeClass('fa-sort-asc').addClass('fa-sort-desc').css('line-height','47px');
            $(this).find('.zxmt-nav').show().siblings().hide();
        }else if($(this).attr('state') == 'zxgl-nav'){
            $('.nav-list-gonglue').addClass('on').find('i').removeClass('fa-sort-desc').addClass('fa-sort-asc').css('line-height','56px');
            $('.nav-list-meitu').removeClass('on').find('i').removeClass('fa-sort-asc').addClass('fa-sort-desc').css('line-height','47px');
            $('.nav-list-more').removeClass('on').find('i').removeClass('fa-sort-asc').addClass('fa-sort-desc').css('line-height','47px');
            $(this).find('.zxgl-nav').show().siblings().hide();
        }else if($(this).attr('state') == 'more-nav'){
            $('.nav-list-more').addClass('on').find('i').removeClass('fa-sort-desc').addClass('fa-sort-asc').css('line-height','56px');
            $('.nav-list-meitu').removeClass('on').find('i').removeClass('fa-sort-asc').addClass('fa-sort-desc').css('line-height','47px');
            $('.nav-list-gonglue').removeClass('on').find('i').removeClass('fa-sort-asc').addClass('fa-sort-desc').css('line-height','47px');
            $(this).find('.more-nav').show().siblings().hide();
        }
    });
    $('.pub-nav-hover').mouseleave(function() {
        var that=$(this);

        timer2=setTimeout(function(){
            that.removeAttr('state');
        $('.nav-list-meitu,.nav-list-gonglue,.nav-list-more').removeClass('on').find('i').removeClass('fa-sort-asc').addClass('fa-sort-desc').css('line-height','47px');
            that.hide().children().hide();
        },500);
    });

    $('.pub-nav .nav li').hover(function() {
        $(this).find('a i').removeClass('fa-sort-desc').addClass('fa-sort-asc').css('line-height','55px');
    }, function() {
        $(this).find('a i').removeClass('fa-sort-asc').addClass('fa-sort-desc').css('line-height','45px');
    });

    // 搜索框模块切换
    $('.search-box').on('click',function(){
        $('.pub-head-nav').stop().animate({width: 0}, 250,function(){
            $('.pub-head-nav').hide();
            $('.pub-search').show();
        });

    });
    // 关闭搜索框模块
    $('.search-close').on('click',function() {
        $('.pub-search').hide();
        $('.pub-head-nav').show().stop().animate({width: 754}, 250)
    });


});