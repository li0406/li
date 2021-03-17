$(function(){
    if(IEVersion() != -1){
        $('.c-name').css('background','transparent');
    }
    $('.style-tabs').on('click','li',function(){
        $(this).addClass('cur').siblings('li').removeClass('cur');
        var index = $(this).index();
        if(IEVersion() == 8 || IEVersion() == 9){
            mySwiper.swipeTo(index, 500, false);
        }else{
            mySwiper.slideTo(index, 500, false);
        };
    });
    $('.yysj').click(function(){
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
    // 底部发单
    +function () {
        $(".apply-now").click(function (event) {
            var cs = $('#city').val();
            var qx = $('#area').val();
            var tel = $('.tel').val();
            $('.warn').html('');
            window.order({
                extra: {
                    cs: cs,
                    qx: qx,
                    tel: tel,
                    source: '19121111'
                },
                success: function (data, status, xhr) {
                    if (data.status == 1) {
                        $.ajax({
                            url: '/poplayer/pop',
                            type: 'GET',
                            dataType: 'JSON',
                            data: {
                                type: 'luxury_success'
                            }
                        })
                        .done(function (data) {
                            if (data.status == 0) {
                                $('.tel').val('');
                                $("body").append(data.tmp);
                            }
                        });
                    }
                },
                validate: function (item, value, method, info) {
                    if ('cs' == item && 'notempty' == method) {
                        $('#city').siblings('.warn').show().html("请选择城市");
                        return false;
                    };
                    if ('qx' == item && 'notempty' == method) {
                        $('#city').siblings('.warn').show().html("请选择区域");
                        return false;
                    };
                    if ('tel' == item && 'notempty' == method) {
                        $('.tel').siblings('.warn').show().html('请输入正确的手机号');
                        return false;
                    };
                    if ('tel' == item && 'ismobile' == method) {
                        $('.tel').siblings('.warn').show().html(info);
                        return false;
                    };
                    return true;
                }
            })
        });
    }()
    var mySwiper = new Swiper('.swiper-container',{
        autoplayDisableOnInteraction : false,
        loop:false,
        initialSlide :0,
        observer:true,
        observeParents:true,
        onSlideChangeEnd: function(swiper){
            var index = swiper.activeIndex;
            $('.style-tabs li').eq(index).addClass('cur').siblings('li').removeClass('cur');
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