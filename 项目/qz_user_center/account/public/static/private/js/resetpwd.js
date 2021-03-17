$(function(){
    var ssid = Math.ceil(Math.random()*10000);
    $('#captcha-verify').attr('src', baseUrl + '/uc/v1/verify?ssid=' + ssid);
    $('.change-verify').click(function(){
        var src = baseUrl + '/uc/v1/verify?ssid='+ ssid + '&time=' + Date.parse(new Date());
        $('#captcha-verify').attr('src',src);
    });
    var ssid1 = Math.ceil(Math.random()*10000);
    $('#captcha-verify1').attr('src', baseUrl + '/uc/v1/verify?ssid=' + ssid1);
    $('#change-verify').click(function(){
        var src = baseUrl + '/uc/v1/verify?ssid='+ ssid1 + '&time=' + Date.parse(new Date());
        $('#captcha-verify1').attr('src',src);
    });
    $("#next").click(function(){
        var verify = $('#step1-yzm').val();
        var phone = $('#user-phone').val();
        $('.warn').html('');
        if (phone == '') {
            $('#user-phone').siblings('.warn').html('请填写手机号');
            return false;
        } else {
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if (!reg.test(phone)) {
                $('#user-phone').siblings('.warn').html('请填写11位正确手机号');
                $('#user-phone').focus();
                $('#user-phone').val('');
                return false;
            }
        }
        if (verify == '') {
            $('#step1-yzm').siblings('.warn').html('请输入正确图形验证码');
            return false;
        }
        $.ajax({
            type: "POST",
            url: baseUrl + '/uc/v1/checkaccount',
            dataType: 'json',
            data:{
                phone: phone,
            },
            success: function(res) {
                if(res.error_code == 0){
                    $.ajax({
                        type: "GET",
                        url: baseUrl + '/uc/v1/checkverify',
                        dataType: 'json',
                        data:{
                            verify: verify,
                            ssid: ssid
                        },
                        success: function(res) {
                            if(res.error_code == 0){
                                $('.forB').addClass('for-cur');
                                $('.form1').hide();
                                $('.form2').show();
                                var phone = $('#user-phone').val();
                                $(".safe-phone").html(phone);
                            }else{
                                $('#step1-yzm').siblings('.warn').html(res.error_msg);
                            }
                        },
                        error: function(res) {
                            console.log(res)
                        }
                    })
                }else{
                    $('#user-phone').siblings('.warn').html(res.error_msg);
                }
            },
            error: function(res) {
                console.log(res)
            }
        })
        
    });
    $('#next-two').click(function(){
        // $('.mask').show();
        // $('.tel').siblings('.warn').html('请先完成验证');
        flag = false;
        initG1();
    });
    $('.sure-code').click(function(){
        sendSms($(".safe-phone").html(),1,$('#img-code').val(),ssid1,$('.send-yzm'));
    });
    $('#sure').click(function(){
        $('.warn').html('');
        var phone = $(".safe-phone").html();
        var verify_code = $('#get-code').val();
        var user_pwd = $('#user_pwd').val();
        var user_pwd_confirm = $('#user_pwd_confirm').val();

        if(user_pwd == ''){
            $('#user_pwd').siblings('.warn').html('密码不能为空');
            return false;
        }else{
            var reg = /((?=.*[a-z])(?=.*\d)|(?=[a-z])(?=.*[#@!~%^&*])|(?=.*\d)(?=.*[#@!~%^&*]))[a-z\d#@!~%^&*]{6,16}/i;
            if(!reg.test(user_pwd)){
                $('#user_pwd').siblings('.warn').html('请填写6-16位由数字、字母或特殊符号组成的密码');
                return false; 
            }
        }
        if(user_pwd_confirm == ''){
            $('#user_pwd_confirm').siblings('.warn').html('密码不能为空');
            return false;
        }else{
            var reg = /((?=.*[a-z])(?=.*\d)|(?=[a-z])(?=.*[#@!~%^&*])|(?=.*\d)(?=.*[#@!~%^&*]))[a-z\d#@!~%^&*]{6,16}/i;
            if(!reg.test(user_pwd)){
                $('#user_pwd_confirm').siblings('.warn').html('请填写6-16位由数字、字母或特殊符号组成的密码');
                return false; 
            }
        }
        if(user_pwd != user_pwd_confirm){
            $('#user_pwd_confirm').siblings('.warn').html('新密码和确认密码请输入一致');
            return false;
        }
        if(verify_code == ''){
            $('#get-code').siblings('.warn').html('请输入6位数验证码');
            return false;
        }else{
            if(verify_code < 6){
                $('#get-code').siblings('.warn').html('请输入6位数验证码');
                return false;
            }
        }
        $.ajax({
            type: "POST",
            url: baseUrl+'/uc/v1/modify/forgetpwd',
            dataType: 'json',
            data:{
                phone:phone,
                verify_code: verify_code,
                user_pwd: user_pwd,
                user_pwd_confirm: user_pwd_confirm
            },
            success: function(res) {
                if(res.error_code == 0){
                    $('.forD').addClass('for-cur');
                    $('.form1').hide();
                    $('.form2').hide();
                    $('.form3').hide();
                    $('.form4').show();
                    setTimeout(function(){
                        window.location.href = '/login'
                    },3000);
                }else{
                    $('#get-code').siblings('.warn').html(res.error_msg);
                }
            },
            error: function(res) {
                console.log(res)
            }
        });
    });
    // 重置密码重新发送验证码
    $("#again-step2, .send-yzm").click(function(){
        // $('.mask').show();
        // $('#img-code').siblings('.warn').html('');
        // $('#img-code').val('');
        // ssid1 = Math.ceil(Math.random()*10000);
        // $('#captcha-verify1').attr('src', baseUrl + '/uc/v1/verify?ssid=' + ssid1);
        // $('#change-verify').click(function(){
        //     var src = baseUrl + '/uc/v1/verify?ssid='+ ssid1 + '&time=' + Date.parse(new Date());
        //     $('#captcha-verify1').attr('src',src);
        // });
        if (flag) {
            // $('.tel').siblings('.warn').html('请先完成验证');
            flag = false;
            initG2();
        }
    });
    $('.close').click(function(){
        $(this).parent().parent().parent().hide();
    });
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
    function sendSms(tel,type,target,chanllenge){
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
                    $('.mask').hide();
                    $('.forC').addClass('for-cur');
                    $('.form1').hide();
                    $('.form2').hide();
                    $('.form3').show();
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
                                    $('.tel').siblings('.warn').html('');
                                    sendSms($(".safe-phone").html(),1,$('.send-yzm'),validate.geetest_validate);
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
                                    $('.tel').siblings('.warn').html('');
                                    sendSms($(".safe-phone").html(),1,$('.send-yzm'),validate.geetest_validate);
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