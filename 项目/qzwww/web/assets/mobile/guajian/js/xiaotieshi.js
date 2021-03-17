var mySwiper = new Swiper('.swiper-container', {
    //移动端轮播
    pagination: '.swiper-pagination',
    autoplayDisableOnInteraction: false,
    loop: true,
    // autoplay: 4000,//可选选项，自动滑动
    initialSlide: 0,
    observer: true,
    //修改swiper自己或子元素时，自动初始化swiper
    observeParents: true,
    //修改swiper的父元素时，自动初始化swiper
    nextButton: '.swiper-button-next',
    prevButton: '.swiper-button-prev',
});


$('.xiaotieskz3 .baojiaye .logobaojia').click(function() {
    $('.toumyingy').show();
    $('.biaodanys2').hide();
    $('.xiaotieskz3').hide();
    $('.biaodanys3').show();
});

// 获取随机数的方法
function GetRandomNum(Min, Max) {
    var Range = Max - Min;
    var Rand = Math.random();
    return (Min + Math.round(Rand * Range));
}
// 随机数
var timer = setInterval(function() {
    var num = GetRandomNum(30000, 120000) + '';
    if (num < 99999) {
        var num1 = 'num num-gray',
        num2 = 'num num-' + num.charAt(0),
        num3 = 'num num-' + num.charAt(1),
        num4 = 'num num-' + num.charAt(2),
        num5 = 'num num-' + num.charAt(3),
        num6 = 'num num-' + num.charAt(4);
    } else {
        var num1 = 'num num-' + num.charAt(0),
        num2 = 'num num-' + num.charAt(1),
        num3 = 'num num-' + num.charAt(2),
        num4 = 'num num-' + num.charAt(3),
        num5 = 'num num-' + num.charAt(4),
        num6 = 'num num-' + num.charAt(5);
    }
    $('#num-1').removeClass().addClass(num1);
    $('#num-2').removeClass().addClass(num2);
    $('#num-3').removeClass().addClass(num3);
    $('#num-4').removeClass().addClass(num4);
    $('#num-5').removeClass().addClass(num5);
    $('#num-6').removeClass().addClass(num6);

},
400);

$('body').on('click', '.bouzou',
function() {
    var dangqianli = $(this).parent('li');
    var index = $(this).parent("li").index();
    var length = $(".small_bg").length;
    $(this).addClass('active');
    $(this).removeClass('active2');
    dangqianli.siblings().children('.bouzou').removeClass('active');
    dangqianli.siblings().children('.bouzou').addClass('active2');
    dangqianli.children('.gongyongms').addClass('active2');
    dangqianli.siblings().children('.gongyongms').removeClass('active2');
    dangqianli.children('.yuanneir').show();
    dangqianli.siblings().children('.yuanneir').hide();
    for (var i = 0; i < length; i++) {
        if (i != index) {
            $($(".small_bg")[i]).css("background", "url(/assets/mobile/guajian/img/" + i + "_2.png) no-repeat");
        } else {
            $($(".small_bg")[i]).css("background", "url(/assets/mobile/guajian/img/" + i + "_1.png) no-repeat");
        }

    }
})

$('.zxfs').mouseover(function() {
    $('.zxfsms').html('清包、半包、全包有哪些区别');
});
$('.zxfs').mouseout(function() {
    $('.zxfsms').html('装修方式');
})

$('.zxfg').mouseover(function() {
    $('.zxfgms').html('近期火热装修风格有哪些');
});
$('.zxfg').mouseout(function() {
    $('.zxfgms').html('装修风格');
})

$('.zxjc').mouseover(function() {
    $('.zxjcms').html('装修要用到哪些建材材料');
});
$('.zxjc').mouseout(function() {
    $('.zxjcms').html('装修建材')
})

$('.zxzq').mouseover(function() {
    $('.zxzqms').html('完整的装修要多长时间')
});
$('.zxzq').mouseout(function() {
    $('.zxzqms').html('装修周期')
})

$('.zxfy').mouseover(function() {
    $('.zxfyms').html('装修的钱花在哪里了')
});
$('.zxfy').mouseout(function() {
    $('.zxfyms').html('装修费用')
})

$('.zxlc').mouseover(function() {
    $('.zxlcms').html('装修要经历哪些环节')
});
$('.zxlc').mouseout(function() {
    $('.zxlcms').html('装修流程')
})

$('.leavelout').click(function() {
    $('.toumyingy').hide();
    $('.xiaotieskz').hide();
})

$('.xiaotieskz .kuangxs .zxfs').click(function() {
    $('.toumyingy').show();
    $('.xiaotieskz').hide();
    $('.xiaotieskz2').show();
})

$('.xiaotieskz .kuangxs .footms .mianfeifa').click(function() {
    $('.toumyingy').show();
    $('.xiaotieskz').hide();
    $('.xiaotieskz3').show();

});

$('.topguanbi .likai').click(function() {
    $('.toumyingy').hide();
    $('.xiaotieskz2').hide();
});

$('.topguanbi .fanhui').click(function() {
    $('.xiaotieskz2').hide();
    $('.xiaotieskz').show();
});

$('.xiaotieskz2 .zxfskz .wenxints .mianfeihuode').click(function() {
    el2 = $(this).parent().parent().parent();
    $('.toumyingy').show();
    $('.xiaotieskz2').hide();
    $('.xiaotieskz3').show();
});

$('.xiaotieskz3 .zxfsbjfoot .zxfsbjlk').click(function() {
    $('.toumyingy').hide();
    $('.xiaotieskz3').hide();
});

$('.xiaotieskz3 .zxfsbjfoot .zxfsbjfh').click(function() {
    el2.show();
    $('.toumyingy').show();
    $('.xiaotieskz3').hide();
});

$('.baojiawk .zxfsbd .tijiao').click(function() {
    $('.toumyingy').show();
    $('.xiaotieskz3').hide();
    $('.xiaotieskz4').show();
});

$('.biaodanys2 .baojiawk .zxfsbd .tijiao2').click(function() {
    $('.toumyingy').show();
    $('.biaodanys2').hide();
    $('.biaodanys3').show();
});

$('.xiaotieskz4 .jieguofoot .likai').click(function() {
    $('.toumyingy').hide();
    $('.xiaotieskz4').hide();
});

$('.xiaotieskz4 .jieguofoot .fanhui').click(function() {
    $('.toumyingy').show();
    $('.xiaotieskz4').hide();
    $('.xiaotieskz3').show();
});

$('.xiaotieskz .kuangxs .zxfy').click(function() {
    $('.toumyingy').show();
    $('.xiaotieskz').hide();
    $('.zxfytanchuan2').show();
});

$('.zxfytanchuan2 .zxfyfoot .fanhui').click(function() {
    $('.toumyingy').show();
    $('.zxfytanchuan2').hide();
    $('.xiaotieskz').show();

});

$('.zxfytanchuan2 .zxfyfoot .likai').click(function() {
    $('.toumyingy').hide();
    $('.zxfytanchuan2').hide();
});
$('.zxfytanchuan2 .zxfsneirong .zxfywxts .zxfyfangan').click(function() {
    el2 = $(this).parent().parent().parent();
    $('.toumyingy').show();
    $('.zxfytanchuan2').hide();
    $('.xiaotieskz3').show();
});

$('.zxjctanchuan2 .zxjcfoot .likai').click(function() {
    $('.toumyingy').hide();
    $('.zxjctanchuan2').hide();
});

$('.zxjctanchuan2 .zxjcfoot .fanhui').click(function() {
    $('.toumyingy').show();
    $('.zxjctanchuan2').hide();
    $('.xiaotieskz').show();
});

$('.zxjctanchuan2 .zxjcneirong .zxjcwxts .zxjcfangan').click(function() {
    el2 = $(this).parent().parent().parent();
    $('.toumyingy').show();
    $('.zxjctanchuan2').hide();
    $('.xiaotieskz3').show();
});

$('.xiaotieskz .kuangxs .zxjc').click(function() {
    $('.toumyingy').show();
    $('.xiaotieskz').hide();
    $('.zxjctanchuan2').show();
});

$('.xiaotieskz .kuangxs .zxlc').click(function() {
    $('.toumyingy').show();
    $('.xiaotieskz').hide();
    $('.zxlctanchuan2').show();
})

$('.zxlctanchuan2 .zxlcneirong .zxlcwxts .zxlcfangan').click(function() {
    el = $(this).parent().parent().parent();
    $('.toumyingy').show();
    $('.zxlctanchuan2').hide();
    $('.biaodanys2').show();
});

$('.zxlctanchuan2 .zxlcfoot .fanhui').click(function() {
    $('.toumyingy').show();
    $('.zxlctanchuan2').hide();
    $('.xiaotieskz').show();
});

$('.zxlctanchuan2 .zxlcfoot .likai').click(function() {
    $('.toumyingy').hide();
    $('.zxlctanchuan2').hide();
});

$('.biaodanys2 .biaodanys2foot .likai').click(function() {
    $('.toumyingy').hide();
    $('.biaodanys2').hide();
});

$('.biaodanys2 .biaodanys2foot .fanhui').click(function() {
    el.show();
    $('.toumyingy').show();
    $('.biaodanys2').hide();
});

$('.biaodanys3 .biaodanys3foot .likai').click(function() {
    $('.toumyingy').hide();
    $('.biaodanys3').hide();
});

$('.biaodanys3 .biaodanys3foot .fanhui').click(function() {
    $('.toumyingy').show();
    $('.biaodanys3').hide();
    $('.biaodanys2').show();
});

$('.biaodanys3 .baojiawk .zxfsbd .tijiao3').click(function() {
    $('.toumyingy').show();
    $('.biaodanys3').hide();
    $('.successweix').show();
});

$('.successweix .successweixfoot .likai').click(function() {
    $('.toumyingy').hide();
    $('.successweix').hide();
});

$('.successweix .successweixfoot .fanhui').click(function() {
    $('.toumyingy').show();
    $('.successweix').hide();
    $('.biaodanys3').show();
});
$('.xiaotieskz .kuangxs .zxzq').click(function() {
    $('.toumyingy').show();
    $('.xiaotieskz').hide();
    $('.zxzqtanchuang2').show();
});

$('.zxzqtanchuang2 .zxzqtanchuang2foot .likai').click(function() {
    $('.toumyingy').hide();
    $('.zxzqtanchuang2').hide();
});

$('.zxzqtanchuang2 .zxzqtanchuang2foot .fanhui').click(function() {
    $('.toumyingy').show();
    $('.zxzqtanchuang2').hide();
    $('.xiaotieskz').show();
});
var el = null;
var el2 = null;
$(' .zxzqwxts .zxzqfangan').click(function() {
    el = $(this).parent().parent().parent();
    $('.toumyingy').show();
    $('.zxzqtanchuang2').hide();
    $('.biaodanys2').show();
});

$('.xiaotieskz .kuangxs .zxfg').click(function() {
    $('.toumyingy').show();
    $('.xiaotieskz').hide();
    $('.zxfgtanchuang2').show();
});

$('.zxfgtanchuang2 .zxfgtanchuang2foot .likai').click(function() {
    $('.toumyingy').hide();
    $('.zxfgtanchuang2').hide();
});

$('.zxfgtanchuang2 .zxfgtanchuang2foot .fanhui').click(function() {
    $('.toumyingy').show();
    $('.zxfgtanchuang2').hide();
    $('.xiaotieskz').show();
});

$('.zxfgtanchuang2 .zxfgtctop .zxfgwxts .zxfgfangan').click(function() {
    el = $(this).parent().parent().parent();
    $('.toumyingy').show();
    $('.zxfgtanchuang2').hide();
    $('.biaodanys2').show();
});

$('.zxfgtanchuang2 .zxfgtctop .oushijianyue img').click(function() {
    $('.toumyingy').show();
    $('.zxfgtanchuang2').hide();
    $('.zxfglunbotc').show();
});
$('.zxfgtanchuang2 .zxfgtctop .dizhonghaifg img').click(function() {
    $('.toumyingy').show();
    $('.zxfgtanchuang2').hide();
    $('.zxfglunbotc2').show();
});
$('.zxfgtanchuang2 .zxfgtctop .jianyuerishi img').click(function() {
    $('.toumyingy').show();
    $('.zxfgtanchuang2').hide();
    $('.zxfglunbotc3').show();
});
$('.zxfgtanchuang2 .zxfgtctop .xiandaizsfg img').click(function() {
    $('.toumyingy').show();
    $('.zxfgtanchuang2').hide();
    $('.zxfglunbotc4').show();
});

$('.zxfglunbotc .zxfglunbofoot .likai').click(function() {
    $('.toumyingy').hide();
    $('.zxfglunbotc').hide();
});

$('.zxfglunbotc2 .zxfglunbofoot .likai').click(function() {
    $('.toumyingy').hide();
    $('.zxfglunbotc2').hide();
});

$('.zxfglunbotc3 .zxfglunbofoot .likai').click(function() {
    $('.toumyingy').hide();
    $('.zxfglunbotc3').hide();
});

$('.zxfglunbotc4 .zxfglunbofoot .likai').click(function() {
    $('.toumyingy').hide();
    $('.zxfglunbotc4').hide();
});

$('.zxfglunbotc .zxfglunbofoot .fanhui').click(function() {
    $('.toumyingy').show();
    $('.zxfglunbotc').hide();
    $('.zxfgtanchuang2').show();
});

$('.zxfglunbotc2 .zxfglunbofoot .fanhui').click(function() {
    $('.toumyingy').show();
    $('.zxfglunbotc2').hide();
    $('.zxfgtanchuang2').show();
});

$('.zxfglunbotc3 .zxfglunbofoot .fanhui').click(function() {
    $('.toumyingy').show();
    $('.zxfglunbotc3').hide();
    $('.zxfgtanchuang2').show();
});

$('.zxfglunbotc4 .zxfglunbofoot .fanhui').click(function() {
    $('.toumyingy').show();
    $('.zxfglunbotc4').hide();
    $('.zxfgtanchuang2').show();
});

$('.zxfglunbotc .zxfglbtctop .zxfglunbowxts .zxfglbfangan').click(function() {
    el = $(this).parent().parent().parent();
    $('.toumyingy').show();
    $('.zxfglunbotc').hide();
    $('.biaodanys2').show();
});

$('.zxfglunbotc2 .zxfglbtctop .zxfglunbowxts .zxfglbfangan').click(function() {
    el = $(this).parent().parent().parent();
    $('.toumyingy').show();
    $('.zxfglunbotc2').hide();
    $('.biaodanys2').show();
});

$('.zxfglunbotc3 .zxfglbtctop .zxfglunbowxts .zxfglbfangan').click(function() {
    el = $(this).parent().parent().parent();
    $('.toumyingy').show();
    $('.zxfglunbotc3').hide();
    $('.biaodanys2').show();
});

$('.zxfglunbotc4 .zxfglbtctop .zxfglunbowxts .zxfglbfangan').click(function() {
    el = $(this).parent().parent().parent();
    $('.toumyingy').show();
    $('.zxfglunbotc4').hide();
    $('.biaodanys2').show();
});

$('.zxfglbtctop .zxfglbwk .zxfgfbt .geijiazx').click(function() {
    el = $(this).parent().parent().parent().parent();
    $('.toumyingy').show();
    $('.zxfglunbotc').hide();
    $('.biaodanys2').show();
});

$('.zxfglbtctop .zxfglbwk .zxfgfbt .geijiazx').click(function() {
    el = $(this).parent().parent().parent().parent();
    $('.toumyingy').show();
    $('.zxfglunbotc2').hide();
    $('.biaodanys2').show();
});

$('.zxfglbtctop .zxfglbwk .zxfgfbt .geijiazx').click(function() {
    el = $(this).parent().parent().parent().parent();
    $('.toumyingy').show();
    $('.zxfglunbotc3').hide();
    $('.biaodanys2').show();
});

$('.zxfglbtctop .zxfglbwk .zxfgfbt .geijiazx').click(function() {
    el = $(this).parent().parent().parent().parent();
    $('.toumyingy').show();
    $('.zxfglunbotc4').hide();
    $('.biaodanys2').show();
});