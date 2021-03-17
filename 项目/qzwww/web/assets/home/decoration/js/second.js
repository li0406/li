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
                source: 19121125,
            },
            success: function (data, status, xhr) {
                if (data.status == 1) {
                    $('.mask').fadeIn(0);
                    $('.success-box').fadeIn(0);
                    fixIEColor();
                    $.ajax({
                        url: '/poplayer/pop',
                        type: 'GET',
                        dataType: 'JSON',
                        data: {
                            type: 'thankapply'
                        }
                    })
                        .done(function (data) {
                            if (data.status == 0) {
                                $("body").append(data.tmp);
                            }
                        });
                } else {
                    alert(data.info);
                }
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
                    $('.tel-warn').show().html('请输入正确的手机号〜');
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
// 案例切换
$(".al-box ul li").hover(function () {
    $(this).find('.shupai').hide();
    $(this).siblings().find('p').hide()
    $(this).stop(true).animate({width: "480px"}, 1000, function () {
        $(this).find('.shupai').siblings('.p-bg').find('p').show();
    }).siblings().stop(true).animate({width: "175px"}, 1000);
    $(this).siblings().find('.shupai').show();
}, function () {
    $(this).siblings().find('p').hide();
    $(this).find('p').show();
});
//新房装修美图
$(".meitu ul li").click(function () {

    var index = $(this).index();
    $(this).parent().siblings().children().eq(index).show().siblings().hide();
})
function beginrotate() {
    var angle = 0;
    function rotateloop() {
        if (angle < 360) {
            angle++;
            setTimeout(rotateloop, 100);
        }
    }
    setTimeout(rotateloop, 100);
}
$(".p-bg .bg-btn").on("click",function(){
    var _this = $(this);
    $.ajax({
        url: '/dispatcher/',
        type: 'POST',
        dataType: 'JSON',
        data: {
            type:"sj",
            source: 19121148,
            action:"load"
        }
    })
        .done(function(data) {
            if (data.status == 1) {
                $("body").append(data.data);
                if(navigator.appName == "Microsoft Internet Explorer"){
                    $(".zxfb").show();
                }else{
                    $(".zxfb").fadeIn(400,function(){
                        $(this).find("input[name='bj-xiaoqu']").focus();
                    });
                    $(".zxbj_content").removeClass('smaller');
                }
                $(".win_box .win-box-bj-mianji").addClass('focus').focus();
            }
        });
});