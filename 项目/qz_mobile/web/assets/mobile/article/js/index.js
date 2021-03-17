// 装修攻略模块选项卡切换
function listClick(){
    var $zxGongLue = $("#zx-gonglue");
    var $tab = $zxGongLue.find(".recommend-tab-list")
    var $zxgl = $zxGongLue.find(".zxgl");
    $tab.on("click", function () {
        var index = $(this).index()
        $tab.removeClass("active");
        $(this).addClass("active");
        $zxgl.removeClass("block");
        $zxgl.eq(index).addClass("block");
    });
}

$.ajax({
    url: '/recommend/article_recommend',
    type: 'POST',
    dataType: 'json',
}).done(function (result) {
    $('#zx-gonglue').html(result.data.template);
    listClick()
})
.fail(function (xhr) {
    alert("加载失败,请刷新再试！");
});

var scrollTop = 0;
var layerOpt = {
    type: 2,
    title: false,
    shadeClose: true,
    resize: false,
    shade: [0.95, '#fff'],
    offset: '30px',
    area: [$(window).width() - 10 + 'px', $(window).height() - 30 + 'px'],
    cancel: function () {
        $('html').removeClass('alpha');
        $(document).scrollTop(scrollTop);
    }
};
$('.float-menu').GooeyMenu({
    success: function () {
        scrollTop = $(document).scrollTop();
    },
    menuItems: {
        protection: {
            style: 0,
            title: '装修保障',
            icon: 'icon-protection',
            click: function () {
                window.location  = '/zxfw.html'
            }
        },
        listen: {
            style: 1,
            title: '免费设计',
            icon: 'icon-listen',
            click: function () {
                window.location  = '/sheji/'
            }
        },
        price: {
            style: 2,
            title: '装修报价',
            icon: 'icon-price',
            click: function () {
               window.location  = '/sz/baojia.html'
            }
        }
    }
});

//切换免责对勾
$("#new-shenming").click(function () {
    $(this).find("#new-check").toggleClass('fa-check');
})