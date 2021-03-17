$(function () {
    mui.previewImage();
    var ahref=document.getElementsByTagName("a");
    for(var i=0;i<ahref.length;i++){
        ahref[i].addEventListener("tap",function(){
            mui.openWindow({
                url: this.href
            })
        })
    }
    mui.plusReady(function() {});
    if($("#pics").children('li').length!=0){
        var w = $("#pics").children('li').length*130;
        $("#pics").css('width',w);
        new JRoll("#wrapper", {scrollX:true,scrollY:false});
    }

    var str = $('.desc').text();
    if (str.length > 300) {
        var strend = str.substring(300, str.length);
        var newstr = str.replace(strend, "...");
        $('.desc').html(newstr);
        $('.more').show();
    } else {
        $('.more').hide();
    }

    $('.more').click(function () {
        if ($('.more').text() == '全文') {
            $('.desc').html(str);
            $('.more').text('收起');
        } else {
            var strend = str.substring(300, str.length);
            var newstr = str.replace(strend, "...");
            $('.desc').html(newstr);
            $('.more').text('全文');
        }
    })
    var leader = 0;
    var timer = null;
    $('.tab .menu').click(function () {
        $(this).addClass('cur').siblings('div').removeClass('cur');
        var honorTop = $('.company-honor').offset().top;
        var introTop = $('.company-intro').offset().top;

        if ($(this).index() == 0) {
            window.scrollTo(0, 0);
        } else if ($(this).index() == 1) {
            clearInterval(timer);
            timer = setInterval(function () {
                var step = (honorTop - leader) / 10;
                step = step > 0 ? Math.ceil(step) : Math.floor(step);
                leader = leader + step;
                window.scrollTo(0, leader);
                if (Math.abs(honorTop - leader) <= Math.abs(step)) {
                    window.scrollTo(0, honorTop);
                    clearInterval(timer);
                }
            }, 25);
        } else {
            clearInterval(timer);
            timer = setInterval(function () {
                var step = (introTop - leader) / 10;
                step = step > 0 ? Math.ceil(step) : Math.floor(step);
                leader = leader + step;
                window.scrollTo(0, leader);
                if (Math.abs(introTop - leader) <= Math.abs(step)) {
                    window.scrollTo(0, introTop);
                    clearInterval(timer);
                }
            }, 25);
        }
    })
})
