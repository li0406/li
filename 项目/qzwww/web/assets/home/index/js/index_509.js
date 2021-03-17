$(function(){
    $('.f-tabs li').on('mouseover',function(){
        var index = $(this).index();
        $(this).addClass('current').siblings('li').removeClass('current');
        $('.tab-main').find('ul').eq(index).show().siblings('ul').hide();
    });
    $('.banner').on('mouseover',function(){
        $('.swiper-button-prev').show();
        $('.swiper-button-next').show();
    });
    $('.banner').on('mouseleave',function(){
        $('.swiper-button-prev').hide();
        $('.swiper-button-next').hide();
    });
    $('.btn-apply').click(function(){
        var cs = $('#y_cs').val();
        var qy = $('#y_qy').val();
        var tel = $('.tel').val();
        var source = $(this).attr('data-source');
        $('.b-warn').html('');
        window.order({
            extra: {
                cs: cs,
                qx: qy,
                tel: tel,
                source: source
            },
            success: function (data, status, xhr) {
                if (data.status == 1) {
                    $.ajax({
                        url: '/poplayer/pop',
                        type: 'GET',
                        dataType: 'JSON',
                        data: {
                            type: "sqsuccess",
                        }
                        })
                        .done(function(data) {
                            if (data.status == 0) {
                                $("body").append(data.tmp);
                            }
                        });
                }
            },
            validate: function (item, value, method, info) {
                if ('cs' == item && 'notempty' == method) {
                    $('#y_cs').siblings('.city-warn').show().html("请选择城市");
                    return false;
                }
                ;
                if ('qx' == item && 'notempty' == method) {
                    $('#y_qy').siblings('.city-warn').show().html("请选择区域");
                    return false;
                }
                ;
                if ('tel' == item && 'notempty' == method) {
                    $('.tel').siblings('.tel-warn').show().html('请输入您的手机号码');
                    return false;
                }
                ;
                if ('tel' == item && 'ismobile' == method) {
                    $('.tel').siblings('.tel-warn').show().html('请输入正确的手机号');
                    return false;
                }
                ;
                if (!checkDisclamer(".order-box")) {
                    return false;
                }
                ;
                return true;
            }
        })
    })
    init();

    $('.company .r-tabs div').on('mouseover',function(){
        var _this = $(this);
        _this.addClass('r-cur').siblings('div').removeClass('r-cur');
        if(_this.index() == 0){
            $('.koubei-list').show();
            $('.huoyue-list').hide();
        }else{
            $('.koubei-list').hide();
            $('.huoyue-list').show();
        }
    });

    $('.xgt .c-tabs li').on('mouseover',function(){
        var index = $(this).index();
        if(index > 4){
            return false;
        }else{
            $(this).addClass('active').siblings('li').removeClass('active');
            if(IEVersion() == 8 || IEVersion() == 9){
                mySwiper2.swipeTo(index, 1000, false);
            }else{
                mySwiper2.slideTo(index, 1000, false);
            }
        }
    });
    // 立即预约发单弹窗
    $('.order-btn').click(function(){
        $.ajax({
            url: '/poplayer/pop',
            type: 'GET',
            dataType: 'JSON',
            data: {
                type: "yuyuebox",
            }
          })
          .done(function(data) {
              if (data.status == 0) {
                $("body").append(data.tmp);
              }
          });
    });
    // 预约设计弹窗
    $('#d1-swiper').on('click','.yysj',function(event){
        event.preventDefault();
        event.stopPropagation();
        var cs = $(this).attr('data-cs');
        var id = $(this).attr('data-id');
        var cost = $(this).attr('data-cost');
        var src = $(this).attr('data-src');
        var name = $(this).attr('data-name');
        var jobtime = $(this).attr('data-jobtime');
        var zw = $(this).attr('data-zw');
        $.ajax({
            url: '/poplayer/pop',
            type: 'GET',
            dataType: 'JSON',
            data: {
                type: "designer_order",
                cs: cs,
                id: id,
                cost: cost,
                src: src,
                name: name,
                jobtime: jobtime,
                zw: zw
            }
        })
        .done(function(data) {
            if (data.status == 0) {
                $("body").append(data.tmp);
            }
        });
    });
    // 立即申请发单弹窗
    $('.apply-btn').click(function(){
        var _this = $(this);
        _this.siblings('.warn').html('');
        var tel = $('#phone').val();
        var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
        var source = _this.attr('data-source');
        if(tel == ''){
            _this.siblings('.warn').show().html('请输入手机号');
            return false;
        }else{
            if(!reg.test(tel)){
                _this.siblings('.warn').show().html('请输入正确的手机号');
                return false;
            }
        };
        window.order({
            extra:{
                tel: tel,
                source: source
            },
            error:function(){
                console.log('获取报价失败,请刷新页面');
            },
            success:function(data, status, xhr){
                if(data.status == 1){
                    $.ajax({
                        url: '/poplayer/pop',
                        type: 'GET',
                        dataType: 'JSON',
                        data: {
                            type: "thankapply",
                        }
                      })
                      .done(function(data) {
                          if (data.status == 0) {
                            $("body").append(data.tmp);
                          }
                      });
                }
            }
        })
    });
    // 找设计弹窗
    $('.find-sheji').click(function(event){
        event.preventDefault();
        event.stopPropagation();
        var src = $(this).attr('data-src');
        var title = $(this).attr('data-title');
        $.ajax({
            url: '/poplayer/pop',
            type: 'GET',
            dataType: 'JSON',
            data: {
                type: "find-sj",
                src: src,
                title: title
            }
          })
          .done(function(data) {
              if (data.status == 0) {
                $("body").append(data.tmp);
              }
          });
    });
    $('.classroom .c-tabs li').on('mouseover',function(){
        var index = $(this).index();
        $(this).addClass('active').siblings('li').removeClass('active');
        if(IEVersion() == 8 || IEVersion() == 9){
            mySwiper3.swipeTo(index, 1000, false);
        }else{
            mySwiper3.slideTo(index, 1000, false);
        }
    });
    $(".al-box ul li").hover(function(){
        $(this).find('.shupai').hide();
        $(this).siblings().find('p').hide()
        $(this).stop(true).animate({width:"623px"},1000,function(){
            $(this).find('.shupai').siblings('.p-bg').find('p').show();
        }).siblings().stop(true).animate({width:"53px"},1000);
        $(this).siblings().find('.shupai').show();
    },function(){
        $(this).siblings().find('p').hide();
        $(this).find('p').show();
    });
    $('.zixun .z-left').find('li').eq(1).css('float','right');
    $('.zixun .z-left').find('li').eq(3).css('float','right');
    $('.zixun .z-right').find('li').eq(1).css('float','right');
    $('.zixun .z-right').find('li').eq(3).css('float','right');
    $('.box-ul .img1').on('mouseover', function () {
        $(this).css('background', 'url(/assets/home/decoration/img/gl-bg.jpg)');
        $(this).find('img').attr('src', '/assets/home/decoration/img/icon-right.png');
        $(this).find('.gt').css('color', '#fff');
    });
    $('.box-ul .img1').on('mouseleave', function () {
        $(this).css('background', '#F7F7F7');
        $(this).find('img').attr('src', '/assets/home/index/pic/liuc.png');
        $(this).find('.gt').css('color', '#333');
    });
    $('.box-ul .img2').on('mouseover', function () {
        $(this).css('background', 'url(/assets/home/decoration/img/wd-bg.jpg)');
        $(this).find('img').attr('src', '/assets/home/decoration/img/icon-right.png');
        $(this).find('.gt').css('color', '#fff');
    });
    $('.box-ul .img2').on('mouseleave', function () {
        $(this).css('background', '#F7F7F7');
        $(this).find('img').attr('src', '/assets/home/decoration/img/icon-question.png');
        $(this).find('.gt').css('color', '#333');
    });
    $('.box-ul .img3').on('mouseover', function () {
        $(this).css('background', 'url(/assets/home/decoration/img/bk-bg.jpg)');
        $(this).find('img').attr('src', '/assets/home/decoration/img/icon-right.png');
        $(this).find('.gt').css('color', '#fff');
    });
    $('.box-ul .img3').on('mouseleave', function () {
        $(this).css('background', '#F7F7F7');
        $(this).find('img').attr('src', '/assets/home/decoration/img/icon-baike.png');
        $(this).find('.gt').css('color', '#333');
    });
    $('.box-ul .img4').on('mouseover', function () {
        $(this).css('background', 'url(/assets/home/decoration/img/sp-bg.jpg)');
        $(this).find('img').attr('src', '/assets/home/decoration/img/icon-right.png');
        $(this).find('.gt').css('color', '#fff');
    });
    $('.box-ul .img4').on('mouseleave', function () {
        $(this).css('background', '#F7F7F7');
        $(this).find('img').attr('src', '/assets/home/index/pic/zxgl.png');
        $(this).find('.gt').css('color', '#333');
    });
    // 顶部banner轮播
    var swiper = new Swiper('#banner-swiper',{
        pagination : '.swiper-pagination',
        autoplayDisableOnInteraction : true,
        loop:true,
        autoplay: 5000,
        speed: 700,
        initialSlide :0,
        paginationClickable :true,
        prevButton:'.swiper-button-prev',
        nextButton:'.swiper-button-next',
    });
    // 初始化
    function init() {
        var length = $('#c1-swiper').find('.swiper-slide').length;
        if(length > 1){
            var cSwiper = new Swiper('#c1-swiper',{
                pagination : '.swiper-pagination1',
                loop:true,
                autoplay: 3000,
                speed: 700,
                autoplayDisableOnInteraction : true,
                initialSlide :0,
                observer:true,
                observeParents:true,
                paginationClickable :true,
            });
        }else{
            var cSwiper = new Swiper('#c1-swiper',{
                pagination : '.swiper-pagination1',
                loop:true,
                autoplay: false,
                autoplayDisableOnInteraction : true,
                initialSlide :0,
                observer:true,
                observeParents:true,
                paginationClickable :true,
            });
        }

        var comtainer1 = document.getElementById('c1-swiper');
        comtainer1.onmouseenter = function () {
            cSwiper.stopAutoplay();
        };
        comtainer1.onmouseleave = function () {
            cSwiper.startAutoplay();
        };
        // var dSwiper = new Swiper('#d1-swiper',{
        //     pagination : '.swiper-pagination2',
        //     autoplayDisableOnInteraction : false,
        //     loop:true,
        //     autoplay: 3000,
        //     speed: 700,
        //     initialSlide :0,
        //     observer:true,
        //     observeParents:true,
        //     paginationClickable :true,
        // });
        // var comtainer2 = document.getElementById('d1-swiper');
        // comtainer2.onmouseenter = function () {
        //     dSwiper.stopAutoplay();
        // };
        // comtainer2.onmouseleave = function () {
        //     dSwiper.startAutoplay();
        // };
        // cSwiper.startAutoplay();
        // dSwiper.stopAutoplay();

        $('.company .c-tabs li').on('mouseover',function(){
            var index = $(this).index();
            if(index == 0){
                cSwiper.startAutoplay();
                // dSwiper.stopAutoplay();
            }else if(index == 1){
                cSwiper.stopAutoplay();
                // dSwiper.startAutoplay();
            }else if(index == 2){
                cSwiper.stopAutoplay();
                // dSwiper.stopAutoplay();
            }
            if(index > 2){
                return false;
            }else{
                $(this).addClass('active').siblings('li').removeClass('active');
                if(IEVersion() == 8 || IEVersion() == 9){
                    mySwiper.swipeTo(index, 1000, false);
                }else{
                    mySwiper.slideTo(index, 1000, false);
                }
            }
        });
    }

    // 装修公司轮播
    var mySwiper = new Swiper('#c-swiper',{
        autoplayDisableOnInteraction : true,
        loop:false,
        initialSlide :0,
        observer:true,
        observeParents:true,
        onSlideChangeEnd: function(swiper){
            var index = swiper.activeIndex
            $('.company .c-tabs li').eq(index).addClass('active').siblings('li').removeClass('active');
        }
    });
    $('.newsbanner ul').bxSlider({
        mode:'vertical',
        slideWidth: 1220,
        minSlides: 1,
        maxSlides: 4,
        auto: true
    });


    // 效果图轮播
    var mySwiper2 = new Swiper('#x-swiper',{
        autoplayDisableOnInteraction : true,
        loop:false,
        initialSlide :0,
        // observer:true,
        // observeParents:true,
        onSlideChangeEnd: function(swiper){
            var index = swiper.activeIndex
            $('.xgt .c-tabs li').eq(index).addClass('active').siblings('li').removeClass('active');
        }
    });
    // 装修课堂
    var mySwiper3 = new Swiper('#room-swiper',{
        autoplayDisableOnInteraction : true,
        loop:false,
        initialSlide :0,
        // observer:true,
        // observeParents:true,
        onSlideChangeEnd: function(swiper){
            var index = swiper.activeIndex
            $('.classroom .c-tabs li').eq(index).addClass('active').siblings('li').removeClass('active');
        }
    });
    function IEVersion() {
		var userAgent = navigator.userAgent; //取得浏览器的userAgent字符串
		var isIE = userAgent.indexOf ("compatible") > -1 && userAgent.indexOf ("MSIE") > -1; //判断是否IE<11浏览器
		var isEdge = userAgent.indexOf ("Edge") > -1 && !isIE; //判断是否IE的Edge浏览器
        var isIE11 = userAgent.indexOf ('Trident') > -1 && userAgent.indexOf ("rv:11.0") > -1;
		if (isIE) {
			var reIE = new RegExp ("MSIE (\\d+\\.\\d+);");
			reIE.test (userAgent);
			var fIEVersion = parseFloat (RegExp["$1"]);
			if (fIEVersion == 7) {
				return 7;
			} else if (fIEVersion == 8) {
				return 8;
			} else if (fIEVersion == 9) {
				return 9;
			} else if (fIEVersion == 10) {
				return 10;
			} else {
				return 6;//IE版本<=7
			}
		} else if (isEdge) {
			return 'edge';//edge
		} else if (isIE11) {
			return 11; //IE11
		} else {
			return -1;//不是ie浏览器
		}
	}
})
