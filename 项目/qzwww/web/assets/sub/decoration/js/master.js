$(function () {
    $(function () {
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
            var mySwiper1 = '';
            if (IEVersion == 8 || IEVersion == 9) {
                loadAllSource('//assets.qizuang.com/plugins/Swiper-2.7.6/css/idangerous.swiper.css', '//assets.qizuang.com/plugins/Swiper-2.7.6/js/idangerous.swiper.min.js', function () {
                    console.log('swiper 2 onload');
                    var mySwiper = new Swiper('.swiper-container1', {
                        //speed:300,
                        autoplay: 4000,//可选选项，自动滑动
                        loop: true,
                        //loopedSlides:6,
                        //onlyExternal:true,
                        autoplayDisableOnInteraction: false,
                    })
                    mySwiper1 = new Swiper('.swiper-container2', {
                        loop: true,
                        autoplay: 4000,
                        paginationClickable: true,
                        // 如果需要分页器
                        pagination: '.pagination',
                    });
                    $(".duibi-btn a").mouseenter(function () {
                        var index = $(this).index();
                        //var index = index + 1;
                        mySwiper.swipeTo(index, 300, false);
                    })
                })
            } else {
                loadAllSource('//assets.qizuang.com/plugins/Swiper-3.4.2/css/swiper.min.css', '//assets.qizuang.com/plugins/Swiper-3.4.2/js/swiper.min.js', function () {
                    console.log('swiper 3 onload');
                    var mySwiper = new Swiper('.swiper-container1', {
                        autoplay: 3000,
                        effect: 'fade',
                        autoplayDisableOnInteraction: false,
                        loop: true,
                        onSlideChangeEnd: function (swiper) {
                            var index = swiper.activeIndex - 1;
                            if (index < 0) {
                                index = 3;
                            } else if (index > 3) {
                                index = 0;
                            }
                        }
                    });
                    mySwiper1 = new Swiper('.swiper-container2', {
                        loop: true,
                        autoplay: 4000,
                        paginationClickable: true,
                        autoplayDisableOnInteraction: false,
                        // 如果需要分页器
                        pagination: '.pagination',

                    });
                    $(".duibi-btn a").mouseenter(function () {
                        var index = $(this).index();
                        var index = index + 1;
                        mySwiper.slideTo(index, 300, false);
                    })
                })
            }

            //推荐设计师
            $(".design-content ul li").mouseenter(function () {
                $(this).addClass('active');
                if(IEVersion == 8 || IEVersion == 9){
                    mySwiper1.autoplay.stop();
                }else{
                    mySwiper1.stopAutoplay();
                }
            }).mouseleave(function () {
                $('.design-content ul li').removeClass('active');
                if(IEVersion == 8 || IEVersion == 9){
                    mySwiper1.autoplay.start();
                }else{
                    mySwiper1.startAutoplay();
                }
            });
            //推荐设计师预约设计
            $("body").on("click",".yuyue-btn",function (event) {
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
            })
            //装修公司
            $(".company-content ul li").mouseenter(function () {
                $(this).addClass('active');
            }).mouseleave(function () {
                $(".company-content ul li").removeClass('active');
            })
        })

        $(function () {
            // 浏览器判断
            function checkBrowser() {
                var u = window.navigator.userAgent.toLocaleLowerCase(),
                    msie = /(msie) ([\d.]+)/,
                    chrome = /(chrome)\/([\d.]+)/,
                    firefox = /(firefox)\/([\d.]+)/,
                    safari = /(safari)\/([\d.]+)/,
                    opera = /(opera)\/([\d.]+)/,
                    ie11 = /(trident)\/([\d.]+)/,
                    b = u.match(msie) || u.match(chrome) || u.match(firefox) || u.match(safari) || u.match(opera) || u.match(ie11);
                return {name: b[1], version: parseInt(b[2])};
            }

            function unBindScroll(delay) {
                if (browerObj.name == "firefox") {
                    $("body div.gaizao-liucheng").off('DOMMouseScroll')
                    $("body div.gaizao-liucheng").on('DOMMouseScroll', function (evt) {
                        evt.preventDefault();
                        return false
                    })
                } else {
                    $("body div.gaizao-liucheng").off('mousewheel')
                    $("body div.gaizao-liucheng").on('mousewheel', function (evt) {
                        evt.preventDefault();
                        return false
                    })
                }
                setTimeout(function () {
                    if (browerObj.name == "firefox") {
                        $("body div.gaizao-liucheng").off('DOMMouseScroll')
                        $("body div.gaizao-liucheng").on('DOMMouseScroll', function (evt) {
                            wheel()
                            evt.preventDefault();
                            return false
                        })
                    } else {
                        $("body div.gaizao-liucheng").off('mousewheel')
                        $("body div.gaizao-liucheng").on('mousewheel', function (evt) {
                            wheel()
                            evt.preventDefault();
                            return false
                        })
                    }
                }, delay);
            }

            //scroll
            function handle(delta) {
                var scrlDiv = $("body > div.gaizao-liucheng"),
                    divObj = scrlDiv.children("div"),
                    onObj = divObj.filter(".on"),
                    len = divObj.length,
                    index = divObj.index(onObj),
                    sH = scrlDiv.scrollTop(),
                    cH = scrlDiv.height(),
                    onM = $("body > .nav > span.on");

                // obObj就是需要滚动的区域
                onObj = $("body div.gaizao-liucheng");
                if (delta < 0) {
                    //down
                    if (step_item < 6) {
                        onObj.find("div.cicle-img" + step_item).show();
                        onObj.find('.item-circle' + (step_item + 1)).addClass('switch-circle');
                        onObj.find('.liucheng_dec > p').hide()
                        onObj.find('.liucheng_dec > .liucheng_dec_' + (step_item + 1)).show()
                        onObj.find('.circle-des').removeClass('des-active')
                        onObj.find('.circle-des' + (step_item + 1)).addClass('des-active')
                        step_item++;
                        unBindScroll(400);
                        return;
                    }else{
                        $("body div.gaizao-liucheng").off('mousewheel')
                        $("body div.gaizao-liucheng").off('DOMMouseScroll')

                    }
                } else {
                    //up
                    if (step_item > 1) {
                        step_item--;
                    }
                    onObj.find("div.cicle-img" + step_item).hide();
                    if (step_item > 0) {
                        onObj.find('.item-circle' + (step_item + 1)).removeClass('switch-circle');
                        onObj.find('.liucheng_dec > p').hide()
                        onObj.find('.liucheng_dec > .liucheng_dec_' + (step_item)).show()
                        onObj.find('.circle-des').removeClass('des-active')
                        onObj.find('.circle-des' + (step_item)).addClass('des-active')
                    }

                    unBindScroll(400);
                    return;
                }
            }

            //ensure direction
            function wheel(event) {
                var delta = 0;
                if (!event) event = window.event;
                if (event.wheelDelta) {
                    delta = event.wheelDelta / 120;
                    if (window.opera) delta = -delta;
                } else if (event.detail) {
                    delta = -event.detail / 3;
                }
                if (delta) {
                    handle(delta);
                }
            }

            function init(step_item){
                var delta = 0;
                if (!event) event = window.event;
                if (event.wheelDelta) {
                    delta = event.wheelDelta / 120;
                    if (window.opera) delta = -delta;
                } else if (event.detail) {
                    delta = -event.detail / 3;
                }
                if (delta) {
                    handle(delta);
                }
            }
            var animateTime = 700,
                step_item = 1;
            var browerObj = checkBrowser();
            var scrlObj = $("body  div.gaizao-liucheng");

            scrlObj.scrollTop(0);
            if (browerObj.name == "firefox") {
                document.getElementById("div").addEventListener("DOMMouseScroll", function(evt){
                    if(step_item == 6 || step_item == 1){
                        init(step_item)
                    }else{
                        init(step_item)
                        evt.preventDefault();
                        return false
                    }
                })
            } else {
                document.getElementById("div").addEventListener("mousewheel", function(evt){
                    if(step_item == 6 || step_item == 1){
                        init(step_item)
                    }else{
                        init(step_item)
                        evt.preventDefault();
                        return false
                    }
                })
            }

            // if (browerObj.name == "firefox") {
            //     $("body div.gaizao-liucheng").on('DOMMouseScroll', function (evt) {
            //         wheel()
            //         evt.preventDefault();
            //         return false
            //     })
            // } else {
            //     $("body div.gaizao-liucheng").on('mousewheel', function (evt) {
            //         wheel()
            //         evt.preventDefault();
            //         return false
            //     })
            // }
            // $("body div.gaizao-liucheng").on('mousewheel', function (evt) {
            //     evt.preventDefault();
            //     return false;
            // })
            $(".item-circle").click(function (evt, index) {
                var index = $(".item-circle").index(this);
                scrlObj.find('.circle-des').removeClass('des-active');
                scrlObj.find('.liucheng_dec > p').hide();
                scrlObj.find('.circle-des' + (index + 1)).addClass('des-active');
                scrlObj.find('.liucheng_dec > .liucheng_dec_' + (index+1)).show();
                step_item = index + 1
                scrlObj.find(".item-circle").each(function (index, item) {
                    if (step_item >= (index + 1)) {
                        scrlObj.find("div.cicle-img" + (index)).show();
                        if (index > 0) {
                            scrlObj.find('.item-circle' + (index + 1)).addClass('switch-circle');
                        }
                    } else {
                        scrlObj.find("div.cicle-img" + (index)).hide();
                        scrlObj.find('.item-circle' + (index + 1)).removeClass('switch-circle');
                        if (index > 0) {
                            scrlObj.find('.item-circle' + (index + 1)).removeClass('switch-circle');
                        }
                    }
                });
            });
        })
    })

})
