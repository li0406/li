$(function () {
    // 头部发单
    +function () {
        $(".dz-btn").click(function (event) {
            var cs = $('#city').val();
            var qy = $('#area').val();
            var tel = $('.tel').val();
            $('.warn').html('');
            window.order({
                extra: {
                    cs: cs,
                    qx: qy,
                    tel: tel,
                    source: '19121156'
                },
                success: function (data, status, xhr) {
                    if (data.status == 1) {
                        $.ajax({
                            url: '/poplayer/pop',
                            type: 'GET',
                            dataType: 'JSON',
                            data: {
                                type: "sqsuccess",
                            }
                          })
                          .done(function(data) {
                              if (data.status == 0) {
                                $("body").append(data.tmp);
                              }
                          });
                    }
                },
                validate: function (item, value, method, info) {
                    if ('cs' == item && 'notempty' == method) {
                        $('#city').siblings('.warn').show().html("请选择城市");
                        return false;
                    }
                    ;
                    if ('qx' == item && 'notempty' == method) {
                        $('#city').siblings('.warn').show().html("请选择区域");
                        return false;
                    }
                    ;
                    if ('tel' == item && 'notempty' == method) {
                        $('.tel').siblings('.warn').show().html('请输入您的手机号码');
                        return false;
                    }
                    ;
                    if ('tel' == item && 'ismobile' == method) {
                        $('.tel').siblings('.warn').show().html('请输入正确的手机号');
                        return false;
                    }
                    ;
                    if (!checkDisclamer(".form-box")) {
                        return false;
                    }
                    ;
                    return true;
                }
            })
        });
    }()
    $('.box-ul .img1').on('mouseover', function () {
        $(this).css('background', 'url(/assets/home/decoration/img/gl-bg.jpg)');
        $(this).children('img').attr('src', '/assets/home/decoration/img/icon-right.png');
        $(this).find('.gt').css('color', '#fff');
    });
    $('.box-ul .img1').on('mouseleave', function () {
        $(this).css('background', '#F7F7F7');
        $(this).children('img').attr('src', '/assets/home/decoration/img/icon-gl.png');
        $(this).find('.gt').css('color', '#333');
    });
    $('.box-ul .img2').on('mouseover', function () {
        $(this).css('background', 'url(/assets/home/decoration/img/wd-bg.jpg)');
        $(this).children('img').attr('src', '/assets/home/decoration/img/icon-right.png');
        $(this).find('.gt').css('color', '#fff');
    });
    $('.box-ul .img2').on('mouseleave', function () {
        $(this).css('background', '#F7F7F7');
        $(this).children('img').attr('src', '/assets/home/decoration/img/icon-question.png');
        $(this).find('.gt').css('color', '#333');
    });
    $('.box-ul .img3').on('mouseover', function () {
        $(this).css('background', 'url(/assets/home/decoration/img/bk-bg.jpg)');
        $(this).children('img').attr('src', '/assets/home/decoration/img/icon-right.png');
        $(this).find('.gt').css('color', '#fff');
    });
    $('.box-ul .img3').on('mouseleave', function () {
        $(this).css('background', '#F7F7F7');
        $(this).children('img').attr('src', '/assets/home/decoration/img/icon-baike.png');
        $(this).find('.gt').css('color', '#333');
    });
    $('.box-ul .img4').on('mouseover', function () {
        $(this).css('background', 'url(/assets/home/decoration/img/sp-bg.jpg)');
        $(this).children('img').attr('src', '/assets/home/decoration/img/icon-right.png');
        $(this).find('.gt').css('color', '#fff');
    });
    $('.box-ul .img4').on('mouseleave', function () {
        $(this).css('background', '#F7F7F7');
        $(this).children('img').attr('src', '/assets/home/decoration/img/icon-video.png');
        $(this).find('.gt').css('color', '#333');
    });
    $('.newsbanner ul').bxSlider({
        mode:'vertical',
        slideWidth: 1220,
        minSlides: 1,
        maxSlides: 1,
        auto: true
    });
})