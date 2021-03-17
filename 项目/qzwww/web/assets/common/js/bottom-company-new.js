;$(function(){
    // 展开/关闭 效果
    var hasResult = true;
    $(".bottom-main-header").click(function(){
        setAnimate();
    });
    $(".bottom-fadan-mask").click(function(){
        if(!hasResult){
            return
        }
        setAnimate();
    });
    function setAnimate () {
        var dir = $(".bottom-main-arrow").data("dir");
        if (!dir) {
            $(".bottom-dibu-radius").animate({
                'bottom':'0px'
            }, 300);
            $(".bottom-fadan-sideradius").animate({
                'bottom':'25px'
            }, 300);
            $(".bottom-main-raidus").animate({
                'bottom':'0px'
            }, 300);
            $(".bottom-fadan-mask").animate({
                'bottom':'-92px'
            }, 300);
            $(".bottom-mice-bottom").animate({
                'bottom':'0px'
            }, 300);
            $(".bottom-main-arrow").attr("src",'/assets/common/img/mice-xh.png');
            // 重置发单页面状态
            $('#step2-box1').fadeIn(0)
            $('#step2-box2').fadeOut(0)
            $('#step2-box3').fadeOut(0)
            $('.bottom-main-raidus input[type="text"]').val('')
            $('.bottom-main-raidus input[type="number"]').val('')
        } else {
            $(".bottom-dibu-radius").animate({
                'bottom':'-45px'
            }, 300);
            $(".bottom-fadan-sideradius").animate({
                'bottom':'-180px'
            }, 300);
            $(".bottom-main-raidus").animate({
                'bottom':'-400px'
            }, 300);
            $(".bottom-fadan-mask").animate({
                'bottom':'-492px'
            }, 300);
            $(".bottom-mice-bottom").animate({
                'bottom':'-133px'
            }, 300);
            $(".bottom-main-arrow").attr("src",'/assets/common/img/mice-sh.png');
        }
        $(".bottom-main-arrow").data("dir",!dir)
    }

//  随机
    function getRandom() {
        var num = Math.random() * 5;
        num = Math.floor(num)
        return num;
    }
    var moneyArr = [];
    $.ajax({
        url: '/get_city_baojia/',
        type: 'POST',
        dataType: 'JSON',
        data: {
            cs: $('#bon-cs').val()
        }
    }).done(function (data) {
        if (data.error_code == 0) {
            setInterval(function () {
                var num = getRandom();
                moneyArr = data.data
                for (var i = 0; i < moneyArr.length; i++) {
                    if (num == i) {
                        $('.moneys1').html(moneyArr[i].wsj);
                        $('.moneys2').html(moneyArr[i].cf);
                        $('.moneys3').html(moneyArr[i].kt);
                        $('.moneys4').html(moneyArr[i].sd);
                        $('.moneys5').html(moneyArr[i].zw);
                        $('.moneys6').html(moneyArr[i].other);
                        $('.total-moneys').html(moneyArr[i].total);
                    }
                }
            }, 400)
        }
    });
});

