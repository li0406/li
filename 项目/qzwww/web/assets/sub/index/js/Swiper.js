$(function () {
    var Swiper = function (selector, width) {
        var _this = this;
        var width = width || 1;
        var picWidth = width * selector.children(0).outerWidth(true);
        var picLength = selector.children().size() / width;
        var _pagationList = selector.next(".pagation-list");
        _this.index = 0;
        _this.timer = null;

        selector.hover(
            function () {
                clearInterval(_this.timer);
            },
            function () {
                _this.autoPlay();
            })
        //自动播放
        _this.autoPlay = function () {
            clearInterval(_this.timer);
            _this.timer = setInterval(function () {
                _this.index++;
                if (_this.index >= picLength) {
                    _this.index = 0;
                }
                selector.stop().animate({
                    left: -_this.index * picWidth
                }, 800)
                _pagationList.find("span").removeClass("current-dot").eq(_this.index).addClass("current-dot");
            }, 5000)
        }
        //下一张
        _this.swiperNext = function () {
            clearInterval(_this.timer);
            _this.index++;
            if (_this.index >= picLength) {
                _this.index = 0;
            }
            selector.stop().animate({
                left: -_this.index * picWidth
            }, 800)
            _pagationList.find("span").removeClass("current-dot").eq(_this.index).addClass("current-dot");
            _this.autoPlay();
        }
        //上一张
        _this.swiperPrev = function () {
            clearInterval(_this.timer);
            _this.index--;
            if (_this.index < 0) {
                _this.index = picLength - 1;
            }
            selector.stop().animate({
                left: -_this.index * picWidth
            }, 800)
            _pagationList.find("span").removeClass("current-dot").eq(_this.index).addClass("current-dot");
            _this.autoPlay();
        }
        //到第几张
        _this.swiperTo = function (index) {
            clearInterval(_this.timer);
            _this.index = index;
            selector.stop().animate({
                left: -_this.index * picWidth
            }, 800)
            _pagationList.find("span").removeClass("current-dot").eq(_this.index).addClass("current-dot");
            _this.autoPlay();
        }

        if (picLength > 1) {
            for (var i = 0; i < picLength; i++) {
                if (i == 0) {
                    $("<span/>", {
                        "class": "current-dot",
                        "click": function () {
                            _this.swiperTo($(this).index());
                        }
                    }).appendTo(_pagationList);
                } else {
                    $("<span/>", {
                        "click": function () {
                            _this.swiperTo($(this).index());
                        }
                    }).appendTo(_pagationList)
                }
            }
        }

        _this.autoPlay();

        if (selector.parent().children("a").hasClass("left-arrow")) {
            selector.parent().find(".right-arrow").on("click", function () {
                _this.swiperNext();
            });
            selector.parent().find(".left-arrow").on("click", function () {
                _this.swiperPrev();
            })
        }
    }


    //总站首页装修效果图切换
    var _Timer = null;
    var i = Math.ceil(Math.random(0, 10) * 5) - 1;
    $(".xgt-list li:eq(0)").find("a.tag").eq(i).addClass("hover")
    $(".xgt-list li:eq(0)").find("a.tag").eq(i + 1).addClass("hover")
    $(".xgt-list li:eq(1)").find("a.tag").eq(i).addClass("hover")
    $(".xgt-list li:eq(1)").find("a.tag").eq(i + 1).addClass("hover")
    autoPlay = function () {
        _Timer = setInterval(function () {
            var i = Math.ceil(Math.random(0, 10) * 5) - 1;
            $(".xgt-list li:eq(0)").find("a.tag").removeClass("hover");
            $(".xgt-list li:eq(0)").find("a.tag").eq(i).addClass("hover")
            $(".xgt-list li:eq(0)").find("a.tag").eq(i + 1).addClass("hover")
            $(".xgt-list li:eq(1)").find("a.tag").removeClass("hover");
            $(".xgt-list li:eq(1)").find("a.tag").eq(i).addClass("hover")
            $(".xgt-list li:eq(1)").find("a.tag").eq(i + 1).addClass("hover")

        }, 3000)
    }
    autoPlay();

    $(".xgt-info>a").click(function () {
        i = $(".xgt-list").position().left;
        if (i == "0") {
            $(".xgt-list").stop().animate({ left: -1210 }, 800)
        }
        else {
            $(".xgt-list").stop().animate({ left: 0 }, 800)
        }
    });

})