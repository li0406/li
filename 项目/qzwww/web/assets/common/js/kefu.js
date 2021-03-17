$('.loginin').click(function () {
    window.location.href = http + account + '/login?redirect=' + window.location.href;
});
 $(document).scroll(function () {
    var scrollTop = $(document).scrollTop();
    var height = $(window).height();
    if (scrollTop > height) {
        $(".kefu_right_enter").css("top","260px")  
        $(".kefu_right_enter").css("bottom", "auto")
        $(".kefu_right").css("top","260px")
        $(".logo_top").css("display", "block")
    } else {
        $(".kefu_right_enter").css("top", "auto")
        $(".kefu_right_enter").css("bottom", "21px")
        $(".kefu_right").css("top","444px")
        $(".logo_top").css("display", "none")
    }
});
$(".fix_warp").on('click','#s-zxzb',function (event) {
    var _this = $(this);
    $.ajax({
        url: '/dispatcher/',
        type: 'POST',
        dataType: 'JSON',
        data: {
            type: "bj",
            source: 159,
            action: "load"
        }
    })
        .done(function (data) {
            if (data.status == 1) {
                $("body").append(data.data);
                if (navigator.appName == "Microsoft Internet Explorer") {
                    $(".zxfb").show();
                    _this.hide();
                } else {
                    $(".zxfb").fadeIn(400, function () {
                        $(this).find("input[name='bj-xiaoqu']").focus();
                    });
                    $(".zxbj_content").removeClass('smaller');
                }
                $(".win_box .win-box-bj-mianji").addClass('focus').focus();
            }
        });
});
//鼠标移入到小挂件图标时，小挂件隐藏，大挂件显示
$(".fix_warp").on('mouseenter','.kefu_right ul li', function () {
    $(".kefu_right_enter").show();
    $(".kefu_right").hide();
});
//鼠标移入大挂件图标时
$(".fix_warp").on('mouseenter','.kefu_right_enter ul .bigimg_list',function () {
    var index = $(this).index()
    $(".right-content").show();
    $(".guanzhubj").hide();
    $(".kefu_right_enter ul li").removeClass("active");
    $(".kefu_right_enter ul li").find(".big_img").css({ "height": "0", "overflow": "hidden" });
    $(this).addClass("active");
    $(this).find(".big_img").css("height", "100px");
    $(".touxiang_logo").html('<img src="/assets/common/img/' + (index + 1) + (index + 1) + '.png" />');
    $(".kefu_logo").html('<img src="/assets/common/img/' + (index + 1) + '.png" />');
    if (index == 0) {
        $(".right-content-tit").html('设计报价在线咨询');
        $(".right-content-des p").eq(0).html('设计报价在线咨询');
        $(".right-content-des p").eq(1).html('专业设计报价团队，历时三年的数据分析，根据上百万条案例数据分析，为您打造最适宜的设计报价方案。');
        $(".kefu_talk").html('您好，您已进入设计报价专线，请问有什么可以帮助您？')
    } else if (index == 1) {
        $(".right-content-tit").html('装修服务在线咨询');
        $(".right-content-des p").eq(0).html('装修服务在线咨询');
        $(".right-content-des p").eq(1).html('小白用户不用怕，从收房到入住一站式装修类问题，专业客服为您来解答，让您全面了解齐装网装修服务。');
        $(".kefu_talk").html('您好，您已进入装修服务专线，请问有什么可以帮助您？')
    } else if (index == 2) {
        $(".right-content-tit").html('家居建材在线咨询');
        $(".right-content-des p").eq(0).html('家居建材在线咨询');
        $(".right-content-des p").eq(1).html('买品牌家居建材，就上齐装网，合作商家遍布全国，给你更多选择。');
        $(".kefu_talk").html('您好，您已进入家居建材专线，请问有什么可以帮助您？')
    } else if (index == 3) {
        $(".right-content-tit").html('工程质检在线咨询');
        $(".right-content-des p").eq(0).html('工程质检在线咨询');
        $(".right-content-des p").eq(1).html('审核报价、质量监管、装修材料检查，一站式解决施工烦恼，为您提供专业的装修全过程质检服务。');
        $(".kefu_talk").html('您好，您已进入工程质检专线，请问有什么可以帮助您？')
    } else if (index == 4) {
        $(".right-content-tit").html('商务合作在线咨询');
        $(".right-content-des p").eq(0).html('商务合作在线咨询');
        $(".right-content-des p").eq(1).html('全方位介绍公司，增加公司曝光，获得更多订单，推广您的产品，更多合作，请点击下方联系');
        $(".kefu_talk").html('您好，您已进入商务合作专线，请问您的公司在哪个城市呢？')
    } else if (index == 5) {
        $(".right-content-tit").html('投诉反馈在线咨询');
        $(".right-content-des p").eq(0).html('投诉反馈在线咨询');
        $(".right-content-des p").eq(1).html('装修、设计遇到问题？请在线联系我们客服，将第一时间给您解答。');
        $(".kefu_talk").html('您好，您已进入投诉反馈专线，请问有什么可以帮到您？')
    }
});
$(".fix_warp").on('mouseenter','.saoma_list',function () {
    $(".kefu_right_enter ul li").removeClass("active");
    $(".kefu_right_enter ul li").find(".big_img").css({ "height": "0", "overflow": "hidden" });
    $(".right-content").hide();
    $(".guanzhubj").show();
}).mouseleave(function () {
    $(".guanzhubj").hide();
});
//鼠标移出挂件区域
$(".fix_warp").on('mouseleave','.kefu_right_enter',function () {
    $(".kefu_right_enter").hide();
    $(".kefu_right").show();
});
//回到顶部
$(".fix_warp").on('click','.goback_list',function () {
    $('body,html').animate({ scrollTop: 0 }, 1000);
    return false;
});
$(".loginout").click(function (event) {
    var _this = $(this);
    $.ajax({
        url: '/loginout/',
        type: 'GET',
        dataType: 'JSON',
        data: {
            ssid: "{$ssid}"
        }
    })
        .done(function (data) {
            if (data.status == 1) {
                window.location.href = window.location.href;
            } else {
                $.pt({
                    target: _this,
                    content: data.info,
                    width: 'auto'
                });
            }
        })
        .fail(function (xhr) {
            $.pt({
                target: _this,
                content: '操作失败,请稍后再试！',
                width: 'auto'
            });
        });
});