$(function(){
    var center_password_token = $.cookie('center_password_token');
    var phone = '';
    $.ajax({
        type: 'GET',
        url: baseUrl +'/uc/v1/account/protection',
        headers: {
            token:center_password_token
        },
        dataType: 'json',
        success: function (res) {
            if(res.error_code == 0){
                if(res.data.phone == ''){
                    $('.phone').html('手机：未设置');
                    $('.bind-phone').show();
                    $('.unbind-phone').hide();
                } else {
                    $('.bind-phone').hide();
                    $('.unbind-phone').show();
                    phone = res.data.phone;
                    $('#change-phone-value').val(res.data.phone);
                    var phoneText = res.data.phone;
                    phoneText = phoneText.substr(0, 3) + '*****' + phoneText.substring(8, 11);
                    $('.phone').html('手机:'+ phoneText);
                    $("#change-phone").val(phoneText);
                }
                if(res.data.safe_email == ''){
                    $('#setEamil').html('邮箱：未设置');
                    $('.bind-email').show();
                    $('.unbind-email').hide();
                }else{
                    $('.email-address').html(res.data.safe_email);
                    $('#setEamil').html('邮箱：'+ res.data.safe_email);
                    $('.bind-email').hide();
                    $('.unbind-email').show();
                }
            }else{
                console.log(res)
            }
        },
        error: function (res) {
            console.log(res) 
        } 
    })
    // 获取图形验证码
    var ssid = Math.ceil(Math.random()*10000);
    var src = baseUrl + '/uc/v1/verify?ssid=' + ssid;
    $('#captcha-verify1').attr('src',src);
    $('#captcha-verify2').attr('src',src);
    $('.change-verify1').click(function(){
        var src = baseUrl + '/uc/v1/verify?ssid=' + ssid + '&time=' + Date.parse(new Date());
        $('#captcha-verify1').attr('src',src);
    });
    $('.change-verify2').click(function(){
        var src = baseUrl + '/uc/v1/verify?ssid=' + ssid + '&time=' + Date.parse(new Date());
        $('#captcha-verify2').attr('src',src);
    });
    // 绑定邮箱 弹窗
    $('.bind-email').click(function(){
        $('#bind-email-box').show();
        $('#bind-email-box .d-main').show();
        $('#bind-email-box .success-box').hide();
    });
    // 解绑邮箱 弹窗
    $('.unbind-email').click(function(){
        $('#unbind-email-box').show();
        $('#unbind-email-box .d-main').show();
        $('#unbind-email-box .success-box').hide();
    });
    // 确认解绑按钮
    $('.next').click(function(){
        $.ajax({
            type: 'POST',
            url: baseUrl +'/uc/v1/delemail',
            headers: {
                token:center_password_token
            },
            dataType: 'json',
            success: function (res) {
                if (res.error_code == 0) {
                    $('#unbind-email-box .d-main').hide();
                    $('#unbind-email-box .success-box').show();
                    setTimeout(function(){
                        window.location.href = window.location.href;
                    },3000);
                } else {
                    alert(res.error_msg);
                }
            }
        });

    });
    // 确认绑定邮箱按钮
    $('#bind-sure').click(function(){
        var email = $('.email-input').val();
        var verify_code = $('.emailCode').val();
        $('.warn').html('');
        if (email == '') {
            $('.email-input').siblings('.warn').html('邮箱不能为空');
            return false;
        } else {
            var reg = /^\w+@[a-z0-9]+\.[a-z]+$/i;
            if(!reg.test(email)){
                $('.email-input').siblings('.warn').html('请输入有效邮箱');
                return false;
            }
        };
        if(verify_code == ''){
            $('.emailCode').siblings('.warn').html('请输入邮箱验证码');
            return false;
        }else{
            if(verify_code.length<6){
                $('.emailCode').siblings('.warn').html('请输入6位数邮箱验证码');
                return false;
            }
        }
        $.ajax({
            type: 'POST',
            url: baseUrl +'/uc/v1/bingemailaction',
            headers: {
                token:center_password_token
            },
            data: {
                email:email,
                verify_code: verify_code
            },
            dataType: 'json',
            success: function (res) {
                if(res.error_code == 0){
                    $('#bind-email-box .d-main').hide();
                    $('#bind-email-box .success-box').show();
                }else{
                    $('.emailCode').siblings('.warn').html(res.error_msg);
                }
            },
            error: function (res) {
                console.log(res) 
            }
        });
    });
    // 绑定邮箱成功，刷新页面
    $('#bind-email-box').on('click','.close',function(){
        window.location.href = window.location.href;
    })
    $('.close').click(function(){
        window.location.href = window.location.href;
    });
    $('.cancel').click(function(){
        $('#unbind-email-box').hide();
    });
    // 更换手机号
    $('.change-phone').click(function(){
        $('#change-phone-box').show();
    });
    // 确定更换手机按钮
    $('#change-phone-sure').click(function(){
        $('.warn').html('');
        var phone = $('#change-phone-value').val();
        var verify_change = $('#verify-change').val();
        var verify_code = $('#verify_code').val();
        if(verify_change == ''){
            $('#verify-change').siblings('.warn').html('请输入图形验证码');
            return false;
        }
        if(verify_code == ''){
            $('#verify_code').siblings('.warn').html('请输入验证码');
            return false;
        }else{
            if(verify_code.length < 6){
                $('#verify_code').siblings('.warn').html('请输入6位验证码');
                return false;
            }
        }

        $.ajax({
            type: 'POST',
            url: baseUrl +'/uc/v1/msg/checkphonecode',
            headers: {
                token:center_password_token
            },
            data: {
                phone:phone,
                verify_code:verify_code,
                type: 4
            },
            dataType: 'json',
            success: function (res) {
                if(res.error_code == 0){
                    $('#bind-phone-box').show();
                    flag = true;
                }else{
                    $('#verify_code').siblings('.warn').html(res.error_msg);
                }
            },
            error: function (res) {
                console.log(res) 
            }
        }) 
    });
    // 确定绑定手机按钮
    $('#bind-phone-sure').click(function(){
        $('.warn').html('');
        var phone = $('#bind-phone').val();
        var verify_code = $('#verify-bind').val();
        var msg_code = $('#msg-code').val();
        if(phone == ''){
            $('#bind-phone').siblings('.warn').html('请输入手机号');
            return false;
        }
        if(verify_code == ''){
            $('#verify-bind').siblings('.warn').html('请输入图形验证码');
            return false;
        }
        if(msg_code == ''){
            $('#msg-code').siblings('.warn').html('请输入短信验证码');
            return false;
        }else{
            if(msg_code.length < 6){
                $('#msg-code').siblings('.warn').html('请输入6位数验证码');
                return false;
            }
        }
        $.ajax({
            type: 'POST',
            url: baseUrl +'/uc/v1/bindphone',
            headers: {
                token:center_password_token
            },
            data: {
                phone: phone,
                verify_code: msg_code
            },
            dataType: 'json',
            success: function (res) {
                if(res.error_code == 0){
                    $('#bind-phone-box .d-main').hide();
                    $('#bind-phone-box .success-box').show();
                }else{
                    $('#msg-code').siblings('.warn').html(res.error_msg);
                }
            },
            error: function (res) {
                console.log(res) 
            }
        }) 
    });
    // 更换手机号发送验证码
    $('#change-send').click(function(){
        $('.warn').html('');
        // var verify_change = $('#verify-change').val();
        // if(verify_change == ''){
        //     $('#verify-change').siblings('.warn').html('请输入图形验证码');
        //     return false;
        // }
        if(flag){
            // $('#change-send').siblings('.warn').html('请先完成验证');
            flag = false;
            initG1();
        }
    });
    // 绑定手机号发送验证码
    $('#bind-send').click(function(){
        $('.warn').html('');
        var phone = $('#bind-phone').val();
        var verify_code = $('#verify-bind').val();
        if(phone == ''){
            $('#bind-phone').siblings('.warn').html('请填写11位正确手机号');
            return false;
        }else{
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if (!$('#bind-phone').val().match(reg)) {
                $('#bind-phone').siblings('.warn').html('请填写11位正确手机号');
                return false;
            }
        }
        // if(verify_code == ''){
        //     $('#verify-bind').siblings('.warn').html('请输入图形验证码');
        //     return false;
        // }
        if(flag){
            // $('#bind-send').siblings('.warn').html('请先完成验证');
            flag = false;
            initG2();
        }
    });
    var flag = true;
    $('.email-code').on('click','span',function () {
        $('.email-input').siblings('.warn').html('');
        var email = $('.email-input').val();
        if (email == '') {
            $('.email-input').siblings('.warn').html('邮箱不能为空');
            return false;
        } else {
            var reg = /^\w+@[a-z0-9]+\.[a-z]+$/i;
            if(!reg.test(email)){
                $('.email-input').siblings('.warn').html('请输入有效邮箱');
                return false;
            }
        };
        if(flag){
            sendEmail(center_password_token,email);
        };       
    });
    //倒计时
    function countDown(obj, num) {
        if (num > 0) {
            obj.text(num + " 秒后重新发送");
            obj.css({'background':'#E6E6E6','color':'#B3B3B3'});
            num--;
            setTimeout(function (obj, num) {
                countDown(obj, num);
            }, 1000, obj, num);
        } else {
            obj.text("获取验证码");
            obj.css({'background':'#FF5353','color':'#fff'});
            flag = true;
        }
    };
    function sendSms(tel,type,imgverify,target,chanllenge){
        var json ={};

        json["phone"] = tel;
        json["type"] = type;
        json["verify_type"] = 2;
        json['challenge'] = chanllenge;
        $.ajax({
            type : "POST",
            url : baseUrl + "/uc/v1/msg/send",
            dataType : "json",
            data:json,
            success : function(data){
                if(data.error_code == 0){
                    target.siblings('.warn').html('短信发送成功');
                    flag = false;
                    countDown(target, 60);
                } else {
                    flag = true;
                    target.siblings('.warn').html(data.error_msg);
                }
            },
            error:function(xhr){
                target.siblings('.warn').html("发送失败,请稍后再试！");
            }
        });
    }
    function sendEmail (token,email) {
        $.ajax({
            type: 'POST',
            url: baseUrl +'/uc/v1/sendemail',
            headers: {
                token:token
            },
            data: {
                email:email,
            },
            dataType: 'json',
            success: function (res) {
                if(res.error_code == 0){
                    countDown($('.send-code'), 60);
                    flag = false;
                    $('.email-code .warn').html('发送成功');
                } else {
                    $('.email-code .warn').html(res.error_msg);
                }
            },
            error: function (res) {
                console.log(res) 
            }
        })     
    }


    /******************  极验  ******************************************/
        //第二步使用
    var initG1 = function(){
            $("#embed-captcha1").html("");
            $.ajax({
                url: baseUrl +"/v2/geetestapi1?t=" + (new Date()).getTime(), // 加随机数防止缓存
                type: 'GET',
                dataType: 'JSON',
                data:{
                    client_type:"web"
                }
            })
                .done(function(data) {
                    initGeetest({
                        // 以下配置参数来自服务端 SDK
                        gt: data.gt,
                        challenge: data.challenge,
                        new_captcha: data.new_captcha,
                        product: "embed", // 产品形式，包括：float，embed，popup。注意只对PC版验证码有效
                        offline: !data.success // 表示用户后台检测极验服务器是否宕机，一般不需要关注
                    }, function (captchaObj) {
                        // 这里可以调用验证实例 captchaObj 的实例方法
                        $("#embed-captcha1").html("");
                        captchaObj.appendTo("#embed-captcha1");
                        captchaObj.onSuccess(function(){
                            var validate = captchaObj.getValidate();
                            $.ajax({
                                url: baseUrl +"/v2/geetestapi2?t="+ (new Date()).getTime(), // 加随机数防止缓存
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    client_type:"web",
                                    geetest_challenge: validate.geetest_challenge,
                                    geetest_validate: validate.geetest_validate,
                                    geetest_seccode: validate.geetest_seccode
                                }
                            }).done(function(data) {
                                if(data.status == 'success'){
                                    flag = true;
                                    if (flag) {
                                        $('#change-send').siblings('.warn').html('');
                                        sendSms($('#change-phone-value').val(),4,$('#verify-change').val(),$('#change-send'),validate.geetest_validate);
                                    }
                                }else{
                                    flag = false;
                                    initG1();
                                }
                            });
                        });
                    })
                });
        }

    //第三步使用
    var initG2 = function(){
        $("#embed-captcha2").html("");
        $.ajax({
            url: baseUrl +"/v2/geetestapi1?t=" + (new Date()).getTime(), // 加随机数防止缓存
            type: 'GET',
            dataType: 'JSON',
            data:{
                client_type:"web"
            }
        })
            .done(function(data) {
                initGeetest({
                    // 以下配置参数来自服务端 SDK
                    gt: data.gt,
                    challenge: data.challenge,
                    new_captcha: data.new_captcha,
                    product: "embed", // 产品形式，包括：float，embed，popup。注意只对PC版验证码有效
                    offline: !data.success // 表示用户后台检测极验服务器是否宕机，一般不需要关注
                }, function (captchaObj) {
                    // 这里可以调用验证实例 captchaObj 的实例方法
                    $("#embed-captcha2").html("");
                    captchaObj.appendTo("#embed-captcha2");
                    captchaObj.onSuccess(function(){
                        var validate = captchaObj.getValidate();
                        $.ajax({
                            url: baseUrl +"/v2/geetestapi2?t="+ (new Date()).getTime(), // 加随机数防止缓存
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                client_type:"web",
                                geetest_challenge: validate.geetest_challenge,
                                geetest_validate: validate.geetest_validate,
                                geetest_seccode: validate.geetest_seccode
                            }
                        }).done(function(data) {
                            if(data.status == 'success'){
                                flag = true;
                                if (flag) {
                                    $('.bind-send').siblings('.warn').html('');
                                    sendSms($('#bind-phone').val(),8,$('#verify-bind').val(),$('#bind-send'),validate.geetest_validate);
                                }
                            }else{
                                flag = false;
                                initG2();
                            }
                        });
                    });
                })
            });
    }

    /***********************  极验  *************************/



})