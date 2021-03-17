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
        if (IEVersion() == 8 || IEVersion() == 9) {
            loadAllSource('//assets.qizuang.com/plugins/Swiper-2.7.6/css/idangerous.swiper.css', '//assets.qizuang.com/plugins/Swiper-2.7.6/js/idangerous.swiper.min.js', function () {
                var mySwiper = new Swiper('.swiper-container', {
                    loop: true,
                    autoplay: 3000,
                    paginationClickable: true,
                    // 如果需要分页器
                    pagination: '.swiper-pagination',
                });
            })
        } else {
            loadAllSource('//assets.qizuang.com/plugins/Swiper-3.4.2/css/swiper.min.css', '//assets.qizuang.com/plugins/Swiper-3.4.2/js/swiper.min.js', function () {
                var mySwiper = new Swiper('.swiper-container', {
                    loop: true,
                    autoplay: 3000,
                    paginationClickable: true,
                    autoplayDisableOnInteraction: false,
                    // 如果需要分页器
                    pagination: '.swiper-pagination',
                    onSlideChangeEnd: function (swiper) {
                        var index = swiper.activeIndex - 1;
                        if (index < 0) {
                            index = 3;
                        } else if (index > 3) {
                            index = 0;
                        }
                    }
                });
            })
        }
    })

    function IEVersion() {
        var userAgent = navigator.userAgent; //取得浏览器的userAgent字符串
        var isIE = userAgent.indexOf("compatible") > -1 && userAgent.indexOf("MSIE") > -1; //判断是否IE<11浏览器
        var isEdge = userAgent.indexOf("Edge") > -1 && !isIE; //判断是否IE的Edge浏览器
        var isIE11 = userAgent.indexOf('Trident') > -1 && userAgent.indexOf("rv:11.0") > -1;
        if (isIE) {
            var reIE = new RegExp("MSIE (\\d+\\.\\d+);");
            reIE.test(userAgent);
            var fIEVersion = parseFloat(RegExp["$1"]);
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
