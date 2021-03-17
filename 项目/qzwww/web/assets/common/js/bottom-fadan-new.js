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
                'bottom':'-131px'
            }, 300);
            $(".bottom-main-arrow").attr("src",'/assets/common/img/bon-arrow-down.png');
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
                'bottom':'-373px'
            }, 300);
            $(".bottom-fadan-mask").animate({
                'bottom':'-503px'
            }, 300);
            $(".bottom-main-arrow").attr("src",'/assets/common/img/bon-arrow.png');
        }
        $(".bottom-main-arrow").data("dir",!dir)
    }

});