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

// 头部发单
+function () {
    $("#btnSave").click(function (event) {
        var cs = $('#y_cs').val();
        var qy = $('#y_qy').val();
        var name = $('input[name=name]').val();
        var tel = $('#tel').val();
        $('.city-warn').hide();
        $('.name-warn').hide();
        $('.tel-warn').hide();
        window.order({
            extra: {
                cs: cs,
                qx: qy,
                name: name,
                tel: tel,
                source: 19121130,
            },
            success: function (data, status, xhr) {
                if (data.status == 1) {
                    $(".order-box")[0].reset();
                    $('.mask').fadeIn(0);
                    $('.success-box').fadeIn(0);
                    fixIEColor();
                    $.ajax({
                        url: '/poplayer/pop',
                        type: 'GET',
                        dataType: 'JSON',
                        data: {
                            type: 'yysuccess'
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
            },
            validate: function (item, value, method, info) {
                if ('cs' == item && 'notempty' == method) {
                    $('.city-warn').show().html("请选择您所在的省份，城市，区域〜");
                    return false;
                }
                ;
                if ('qx' == item && 'notempty' == method) {
                    $('.city-warn').show().html("请选择您所在的省份，城市，区域〜");
                    return false;
                }
                ;
                if ('tel' == item && 'notempty' == method) {
                    $('.tel-warn').show().html('手机号别忘了哦〜');
                    return false;
                }
                ;
                if ('tel' == item && 'ismobile' == method) {
                    $('.tel-warn').show().html('请输入正确的手机号');
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
    });
    $('.success-box').find(".close").on("click", function () {
        $('.mask').fadeOut(0);
        $('.success-box').fadeOut(0);
    })
}()
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
            var mySwiper = new Swiper('.swiper-container', {
                autoplay: 2000,//可选选项，自动滑动
                loop: false,
                autoplayDisableOnInteraction: false,
            })
            var mySwiper1 = new Swiper('.swiper-container1', {
                autoplay: 2000,//可选选项，自动滑动
                loop: false,
                autoplayDisableOnInteraction: false,
            })
            var mySwiper2 = new Swiper('.swiper-container2', {
                autoplay: 2000,//可选选项，自动滑动
                loop: false,
                autoplayDisableOnInteraction: false,
            })
            var mySwiper3 = new Swiper('.swiper-container3', {
                autoplay: 2000,//可选选项，自动滑动
                loop: false,
                autoplayDisableOnInteraction: false,
            })
            var mySwiper4 = new Swiper('.swiper-container4', {
                autoplay: 2000,//可选选项，自动滑动
                loop: false,
                autoplayDisableOnInteraction: false,
            })
            var mySwiper5 = new Swiper('.swiper-container5', {
                autoplay: 2000,//可选选项，自动滑动
                loop: false,
                autoplayDisableOnInteraction: false,
            })
        })
    } else {
        loadAllSource('//assets.qizuang.com/plugins/Swiper-3.4.2/css/swiper.min.css', '//assets.qizuang.com/plugins/Swiper-3.4.2/js/swiper.min.js', function () {
            var mySwiper = new Swiper('.swiper-container', {
                autoplay: 3000,
                effect: 'fade',
                autoplayDisableOnInteraction: false,
                loop: true
            });
            var mySwiper1 = new Swiper('.swiper-container1', {
                autoplay: 3000,
                effect: 'fade',
                autoplayDisableOnInteraction: false,
                loop: true
            });
            var mySwiper2 = new Swiper('.swiper-container2', {
                autoplay: 3000,
                effect: 'fade',
                autoplayDisableOnInteraction: false,
                loop: true
            });
            var mySwiper3 = new Swiper('.swiper-container3', {
                autoplay: 3000,
                effect: 'fade',
                autoplayDisableOnInteraction: false,
                loop: true
            });
            var mySwiper4 = new Swiper('.swiper-container4', {
                autoplay: 3000,
                effect: 'fade',
                autoplayDisableOnInteraction: false,
                loop: true
            });
            var mySwiper5 = new Swiper('.swiper-container5', {
                autoplay: 3000,
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
$(function(){
    $('#dowebok').fullpage({
        sectionsColor: ['#F7F7F7', '#F7F7F7', '#F7F7F7', '#F7F7F7','#F7F7F7', '#F7F7F7','#F7F7F7', '#F7F7F7'],
        scrollOverflow: true,
        scrollingSpeed: 500,
    });
    $(".wrap-box .tourl").click(function(e){
        $.fn.fullpage.moveTo(1,0);
    });
});
