//预约定制
$(".order-btn").click(function () {
    var cs = $("#order-cs").val();
    var qy = $('#order-qy').val();
    var tele = $(".telephone").val();
    var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
    if(cs == ''){
        $(".error-city").html('请选择城市');
        return false
    }else{
        $(".error-city").html('');
    }
    if(qy == ''){
        $(".error-area").html('请选择区域');
        return false
    }else{
        $(".error-area").html('');
    }
    if(tele == ''){
        $(".error-tel").html('请输入您的手机号码');
        return false
    }else if(!reg.test(tele)){
        $(".error-tel").html('请输入正确的手机号');
        return false
    }else{
        $(".error-tel").html('');
    }
    window.order({
        extra:{
            tel:tele,
            cs:cs,
            qx:qy,
            source: 19121150
        },
        success:function (data, status, xhr) {
            if(data.status == 1){
                $.ajax({
                    url: '/poplayer/pop',
                    type: 'GET',
                    dataType: 'JSON',
                    data: {
                        type: 'thankapply'
                    }
                })
                    .done(function (data) {
                        if (data.status == 0) {
                            $("body").append(data.tmp);
                        }
                    });
            }
        },
        error:function (data) {
            alert(data.info)
        }
    })
});
function fixIEColor() {
    var $inputs = $("input");
    var $select = $("select");
    // input框文本颜色监控修改
    for (var i = 0; i < $inputs.length; i++) {
        $inputs.eq(i).css("color", "#999");
    }
    $("html,body").on("keyup", "input", function () {
        if ($(this).val().length > 0) {
            $(this).css('color', "#333");
        } else {
            $(this).css('color', "#999");
        }
    });
    // select文本颜色监听修改
    $("html,body").on("change", "select", function () {
        var $parentEle = $(this).closest("table").length > 0 ? $(this).closest("table") : $(this).closest("div");
        if ($(this).val()) {
            $parentEle.find("select").css('color', "#333");
        } else {
            $parentEle.find("select").css('color', "#999");
        }
    })
}
function loadScript(def, src, callback) {
    setTimeout(function () { // setTimeout本来是测试用的，但注释掉IE8就无效了
        var script = document.createElement('script'),
            head = document.getElementsByTagName('head')[0];
        script.type = 'text/javascript';
        script.src = src;
        if (script.addEventListener) {
            script.addEventListener('load', function () {
                def.resolve()
                callback && callback();
            }, false);
        } else if (script.attachEvent) {
            script.attachEvent('onreadystatechange', function () {
                var target = window.event.srcElement;
                if (target.readyState == 'complete') {
                    callback && callback();
                    def.resolve()
                }
            });
        }
        head.appendChild(script);
    }, 0)
    return def.promise();
}
function loadCss(def, src, callback) {
    setTimeout(function () {
        var link = document.createElement('link'),
            head = document.getElementsByTagName('head')[0];
        link.type = 'text/css';
        link.rel = 'stylesheet'
        link.href = src;
        if (link.addEventListener) {
            link.addEventListener('load', function () {
                def.resolve()
                callback && callback();
            }, false);
        } else if (link.attachEvent) {
            link.attachEvent('onreadystatechange', function () {
                var target = window.event.srcElement;
                if (target.readyState == 'complete') {
                    callback && callback();
                    def.resolve()
                }
            });
        }
        head.appendChild(link);
    }, 0)
    return def.promise();
}
function loadAllSource(cssLink, scriptLink, cb) {
    $.when(loadCss($.Deferred(), cssLink), loadScript($.Deferred(), scriptLink)).done(function () {
        cb && cb.call()
    })
}
$(function () {
    if (IEVersion == 8 || IEVersion == 9) {
        loadAllSource('//assets.qizuang.com/plugins/Swiper-2.7.6/css/idangerous.swiper.css', '//assets.qizuang.com/plugins/Swiper-2.7.6/js/idangerous.swiper.min.js', function () {
            console.log('swiper 2 onload');
            var mySwiper = new Swiper('.swiper-container', {
                autoplay: 2000,//可选选项，自动滑动
                loop: true,
                autoplayDisableOnInteraction: false,
            })
        })
    } else {
        loadAllSource('//assets.qizuang.com/plugins/Swiper-3.4.2/css/swiper.min.css', '//assets.qizuang.com/plugins/Swiper-3.4.2/js/swiper.min.js', function () {
            var mySwiper = new Swiper('.swiper-container', {
                autoplay: 2000,
                effect: 'fade',
                autoplayDisableOnInteraction: false,
                loop: true
            });
        })
    }

    //推荐设计师
    $(".design-content ul li").mouseenter(function () {
        $(this).addClass('active')
    }).mouseleave(function () {
        $('.design-content ul li').removeClass('active');
    });
    //装修公司
    $(".company-content ul li").mouseenter(function () {
        $(this).addClass('active');
    }).mouseleave(function () {
        $(".company-content ul li").removeClass('active');
    })
})
//切换
$(".xgfl>.jbbox ul li").click(function () {
    var index = $(this).index();
    var moreText = $(this).find(".jb-class").html();
    var category = $(this).find(".jb-class").attr('data-category');
    var url = '//' + QZ_YUMINGWWW
    $(this).addClass('actived').siblings().removeClass("actived");
    $(this).parent().siblings().children().eq(index).show().siblings().hide();
    $(".jb-more").html('更多' + moreText + '美图')
    $(".jb-more").attr('href', url + '/search/esfzx/tu/?category=' + category);
})
//云朵
move()
function move(){
    $(".xgt .jbyunz").animate({
        top:-46
    },1000,function () {
        $(".xgt .jbyunz").animate({
            top:-36
        },1000,function () {
            move()
        })
    })
}
moveyun()
function moveyun(){
    $(".xgt .jbyuny").animate({
        right:-310
    },1000,function () {
        $(".xgt .jbyuny").animate({
            right:-290
        },1000,function () {
            moveyun()
        })
    })
}