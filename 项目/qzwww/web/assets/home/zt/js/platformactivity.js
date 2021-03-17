$(function () {
    var qccPhone = $("#jzj-phone");
    var qccCode = $("#jzj-code");
    var receiveBtn = $("#jzj-lq-btn");
    var getCodeBtn = $("#jzj-getcode");
    var AppEnv = 'platform=WEB&user-agent=' + navigator.userAgent;
    var inputTel = $("#tel-input")
    var popupBox = $("#jzj-popup")
    var popType = '' // 领取齐装保1 领取齐津贴2 抽奖3
    var btnOff = true // 抽奖防多次点击

    function swiper(){
        var mySwiper = new Swiper("#swiper", {
            loop: true,       //允许从第一张到最后一张，或者从最后一张到第一张  循环属性
            slidesPerView: 4,   // 设置显示几张
            centeredSlidesBounds: true,    //使左右两边的图片始终贴合边缘
            calculateHeight: true,
            slidesOffsetBefore: 0,
            speed: 500,//设置过度时间
            nextButton: '.swiper-button-next',
            prevButton: '.swiper-button-prev',
        });
        var DEFAULT_VERSION = 9;
        var ua = navigator.userAgent.toLowerCase();
        var isIE = ua.indexOf("msie") > -1;
        var safariVersion;
        if (isIE) {
            safariVersion = ua.match(/msie ([\d.]+)/)[1];
            var sa = parseInt(safariVersion);
            if (safariVersion <= DEFAULT_VERSION) {      
                $(".swiper-button-prev").click(function(){
                    mySwiper.swipePrev();
                })
                $(".swiper-button-next").click(function(){
                    mySwiper.swipeNext();
                })
            }
        }
    }
    swiper()

    $(".jzj-city-name").click(function(){
        $(".jzj-city-select").toggle()
    })
    $(".jzj-city-select li").click(function(){
        $(".jzj-city-select").fadeOut()
        $(this).addClass('jzj-city-acitve').siblings().removeClass('jzj-city-acitve')
        var cityId = $(this).data('id')
        var url = '/v1/platformactivity/select_activity'
        var data = {}
        data.cs = cityId
        data.type = 2
        
        $.ajax({
            type: 'GET',
            url: apiUrl + url,
            headers: {
                'App-env': AppEnv
            },
            data: data,
            dataType: 'json',
            success: function (res) {
                if (res.error_code == 0) {
                    var html = ''
                    var text = []
                    if(res.data.length>4){
                        $(".swiper-button-prev").show()
                        $(".swiper-button-next").show()
                    }else{
                        $(".swiper-button-prev").hide()
                        $(".swiper-button-next").hide()
                    }
                    res.data.forEach(function(item,index) {
                        html = ''
                        html += '<div class="swiper-slide">'
                        html += '<div class="jzj-activity-item">'
                        html += '<div class="jzj-activity-item">'
                        if(item.activity_type === 4){
                            html += '<div class="horn-icon icon-cxhd">促销活动</div>'
                        }else {
                            html += '<div class="horn-icon icon-cxhd">到店有礼</div>'
                        }
                        html += '<div class="top-pic">'
                        html += '<img class="img" src="'+item.img_url+'">'
                        html += '<div class="ljyy-btn"><div class="ljyy-btn-name">立即预约</div></div><div class="bottom-text">'
                        if(index%2 === 0){
                            html += '<img class="bottom-text-pic" src="/assets/home/zt/images/platformactivity/jzj-lw-icon.png" alt="">'
                        }else{
                            html += '<img class="bottom-text-pic2" src="/assets/home/zt/images/platformactivity/jzj-lw-icon2.png" alt="" width="37" height="37">'
                        }
                        html += '<h5>'+ item.activity_content.slice(0,30) +'</h5>'
                        html += '</div></div><div class="bom-name"><div class="comy-logo">'   
                        html += '<img src="'+ item.logo + '" alt="">'
                        html += '</div>'   
                        html += '<h4>'+ item.company_jc + '</h4>'
                        html += '</div></div></div>'
                        text.push(html)
                    });
                    $("#swiper-wrapper").html(text)
                    swiper()
                } else {
                    alert(res.error_msg)
                }
            },
            error: function (res) {
                console.log(res)
                console.log("验证失败，请稍后重试")
            }
        })
    })

    // 弹窗关闭
    $(".popup-close").click(function () {
        popupBox.fadeOut();
        qccPhone.val("");
        qccCode.val("");
        $('#code-warn').html('');
        $('#phone-mark').html('');
        $("#embed-captcha").html("");
        setTimeout(function () { countDown($("#qcc-codetext"), getCodeBtn, 0) }, 1000);
        $('body').css('overflow-y', 'auto')
    });

    // 活动说明
    $('.jzj-hdsm').click(function () {
        $('body').css('overflow-y', 'hidden')
        popup($('#popup-box-rule'))
    });
    // 领取齐装保
    $('#jzj-qzb-btn').click(function () {
        if (inputTel.val()) {
            popType = 1
            fadan(inputTel.val())
        } else {
            popup($('#popup-box-form'), 1, '领取齐装保')
        }
    });
    // 领取 津贴
    $('#jzj-jt4-btn').click(function () {
        if (inputTel.val()) {
            popType = 2
            fadan(inputTel.val())
        } else {
            popup($('#popup-box-form'), 2, '领取装修津贴')
        }
    });
    $('#jzj-jt1-btn').click(function () {
        if (inputTel.val()) {
            popType = 2
            fadan(inputTel.val())
        } else {
            popup($('#popup-box-form'), 2, '领取装修津贴')
        }
    });
    // 立即预约
    $('.ljyy-btn-name').click(function () {
        if (inputTel.val()) {
            popType = 4
            fadan(inputTel.val())
        } else {
            popup($('#popup-box-form'), 4, '免费预约')
        }
    });

    function popup(box, type, title) {
        popupBox.fadeIn();
        box.show();
        box.siblings().hide();
        popType = type
        if(type === 1 || type === 2){
            receiveBtn.text('一键领取')
            $("#cj-show").hide()
        }else if(type === 3){
            $("#cj-show").show()
            receiveBtn.text('立即抽奖')
        }else{
            $("#cj-show").hide()
            receiveBtn.text('立即预约')
        }
        if (title) {
            $('#jzj-tel-title').html(title)
        }
    }


    // 一键领取
    receiveBtn.click(function () {
        $("#phone-mark").html('');
        var phone = qccPhone.val();
        var verify_code = qccCode.val();
        if (phone == '') {
            $("#phone-mark").html('请填写您的手机号');
            return false;
        } else {
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if (!phone.match(reg)) {
                $("#phone-mark").html('请填写11位正确手机号');
                qccPhone.focus();
                return false;
            }
        };
        if (verify_code == '') {
            $('#code-warn').html('请输入6位数短信验证码');
            return false;
        } else if(verify_code.length < 6){
            $('#code-warn').html('请输入6位数短信验证码');
            return false;
        }else {
            $('#code-warn').html('')
        }
        var disclamer = $(".disclamer-check").attr("data-checked");
        if(disclamer == "false"){
            alert("请勾选我已阅读并同意齐装网的《免责声明》！");
            return false;
        }
        fadan(phone,verify_code)
    });
    
    function fadan(phone, code){
        var url = ''
        var data = {}
        data.tel = phone
        data.source = 20080302
        if(code){
            data.code = code
        }
        if (popType === 1 || popType === 4) {
            url = '/v1/platformactivity/qzb'
        } else if (popType === 2) {
            url = '/v1/platformactivity/jintie'
        } else if (popType === 3) {
            url = '/v1/platformactivity/draw'
        }
        $.ajax({
            type: 'POST',
            url: apiUrl + url,
            headers: {
                'App-env': AppEnv
            },
            data: data,
            dataType: 'json',
            success: function (res) {
                if (res.error_code == 0) {
                    inputTel.val(phone)
                    if (popType === 1) {
                        popup($('#popup-success-lq'))
                    } else if (popType === 2) {
                        popup($('#popup-success-lj'))
                    } else if (popType === 3) {
                        popupBox.hide();
                        rotate(res.data.offset, res.data.prize)
                    } else if (popType === 4) {
                        popupBox.hide();
                        alert('预约成功')
                    }
                } else {
                    alert(res.error_msg)
                }
            },
            error: function (res) {
                console.log(res)
                console.log("验证失败，请稍后重试")
            }
        })
    }
    // 获取短信验证码按钮
    getCodeBtn.click(function () {
        $("#phone-mark").html('');
        var tel = qccPhone.val();
        if (tel == '') {
            $("#phone-mark").html('请填写11位正确手机号');
        } else {
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if (!qccPhone.val().match(reg)) {
                $("#phone-mark").html('请填写11位正确手机号');
                qccPhone.focus();
            } else {
                getCodeBtn.hide();
                $("#qcc-codetext").show();
                $("#qcc-codetext").html("获取验证码");
                initG2();
            }
        };
    });


    //倒计时
    var setTimer = ''
    function countDown(obj, btn, num) {
        if (num > 0) {
            obj.show();
            obj.html(num + " 秒后重新发送");
            btn.hide();
            num--;
            setTimer = setTimeout(function (obj, btn, num) {
                countDown(obj, btn, num);
            }, 1000, obj, btn, num);
        } else {
            clearInterval(setTimer)
            obj.hide();
            obj.html("获取验证码")
            btn.show();
        }
    };
    // 获取验证码接口
    function sendSms(tel, type, target, chanllenge) {
        $("#embed-captcha").html("");
        var json = {};
        json["phone"] = tel;
        json["type"] = type;
        json["verify_type"] = 2;
        json['challenge'] = chanllenge;
        $("#phone-mark").html("");
        target.hide();
        $("#qcc-codetext").show();
        $("#qcc-codetext").html("获取验证码")
        $.ajax({
            type: "POST",
            url: apiUrl + "/uc/v1/msg/send",
            dataType: "json",
            data: json,
            success: function (data) {
                if (data.error_code == 0) {
                    $("#code-warn").html('短信发送成功');
                    $("#phone-mark").html("");
                    countDown($("#qcc-codetext"), target, 60);
                } else {
                    $("#code-warn").html(data.error_msg);
                    target.show();
                    $("#qcc-codetext").hide();
                }
            },
            error: function (xhr) {
                $("#code-warn").html("发送失败,请稍后再试！");
                target.show();
                $("#qcc-codetext").hide();
            }
        });
    }

    /******************  极验  ******************************************/
    var initG2 = function () {
        $("#embed-captcha").html("");
        console.log(apiUrl)
        $.ajax({
            url: apiUrl + "/v2/geetestapi1?t=" + (new Date()).getTime(), // 加随机数防止缓存
            type: 'GET',
            dataType: 'JSON',
            data: {
                client_type: "web"
            }
        })
            .done(function (data) {
                initGeetest({
                    // 以下配置参数来自服务端 SDK
                    gt: data.gt,
                    challenge: data.challenge,
                    new_captcha: data.new_captcha,
                    product: "embed", // 产品形式，包括：float，embed，popup。注意只对PC版验证码有效
                    offline: !data.success // 表示用户后台检测极验服务器是否宕机，一般不需要关注
                }, function (captchaObj) {
                    // 这里可以调用验证实例 captchaObj 的实例方法
                    $("#embed-captcha").html("");
                    captchaObj.appendTo("#embed-captcha");
                    captchaObj.onSuccess(function () {
                        var validate = captchaObj.getValidate();
                        $.ajax({
                            url: apiUrl + "/v2/geetestapi2?t=" + (new Date()).getTime(), // 加随机数防止缓存
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                client_type: "web",
                                geetest_challenge: validate.geetest_challenge,
                                geetest_validate: validate.geetest_validate,
                                geetest_seccode: validate.geetest_seccode
                            }
                        }).done(function (data) {
                            if (data.status == 'success') {
                                $("#phone-mark").html('');
                                sendSms(qccPhone.val(), 10, getCodeBtn, validate.geetest_validate);
                            } else {
                                initG2();
                            }
                        });
                    });
                })
            });
    }
    /***********************  极验  *************************/
    
    // 转盘
    function rotate(num, data) {
        var deg = 45
        deg += 45 * (7 - num) - (40 - Math.round(Math.random() * 20)) + 1080;
        $(".pic-bj img").css('transform', 'rotate(' + deg + 'deg)')
        setTimeout(function () {
            $('.jzj-popup').fadeIn()
            if (num === 2 || num === 6) {
                $('#popup-regret').show()
                $('#popup-regret').siblings().hide()
            } else {
                if(num ===0) {
                    $("#success-l-mark").text('量房成功即可领取奖励～')
                }else{
                    $("#success-l-mark").text('完成装修签约即可兑换奖品～')  
                }
                $("#success-img").attr('src', data.thumb)
                $("#popup-success").show()
                $('#popup-success').siblings().hide()
            }
        }, 2000)
    }
    // 抽奖接口
    var luckDraw = function () {
        if (btnOff) {
            btnOff = false
            var dataJson = {}
            dataJson.activity_id = 35
            dataJson.tel = inputTel.val()
            $.ajax({
                type: "POST",
                url: apiUrl + "/v1/platformactivity/draw",
                dataType: "json",
                data: dataJson,
                success: function (data) {
                    console.log(data)
                    if (data.error_code === 0) {
                        rotate(data.data.offset, data.data.prize)
                    } else {
                        alert(data.error_msg)
                    }
                },
            });
            
        setTimeout(function () {
            btnOff = true
        },2000)
        } else {
            return false
        }
    }
    // 抽奖
    $(".jzj-djcj").click(function () {
        if (inputTel.val()) {
            luckDraw()
        } else {
            popup($("#popup-box-form"), 3, "转盘抽奖")
        }
    })

})