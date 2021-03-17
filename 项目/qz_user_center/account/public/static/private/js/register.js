$(function(){
    $.removeCookie('nickname',{ path: '/',domain: '.qizuang.com' });
    $.removeCookie('logo',{ path: '/',domain: '.qizuang.com' });
    $.removeCookie('city_name',{ path: '/',domain: '.qizuang.com' });
    $.removeCookie('quyu_name',{ path: '/',domain: '.qizuang.com' });
    $.removeCookie('center_password_token',{ path: '/',domain: '.qizuang.com' });
    var ssid = Math.ceil(Math.random()*10000);
    var AppEnv = 'platform=WEB&user-agent='+ navigator.userAgent;
    $('.get-code').click(function(){
        $('.warn').html('');
        var tel = $('.tel').val();
        if(tel == ''){
            $('.tel').siblings('.warn').html('请填写11位正确手机号');
            return false;
        }else{
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if (!$('.tel').val().match(reg)) {
                $('.tel').siblings('.warn').html('请填写11位正确手机号');
                $('.tel').focus();
                $('.tel').val('');
                return false;
            } else {
                // $('.tel').siblings('.warn').html('请先完成验证');
                if (flag) {
                    flag = false;
                    initG2();
                }
            }
        };
    });
    // 立即注册
    $('.register-btn').click(function(){
        $(".warn").html('');
        var tel = $('.tel').val();
        var user = $('.user').val();
        var psw = $('.psw').val();
        var verify_code = $('#msg-code').val();
        if(tel == ''){
            $('.tel').siblings('.warn').html('请填写11位正确手机号');
            return false;
        }else{
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if (!$('.tel').val().match(reg)) {
                $('.tel').siblings('.warn').html('请填写11位正确手机号');
                $('.tel').focus();
                $('.tel').val('');
                return false;
            }
        };
        if(user == ''){
            $('.user').siblings('.warn').html('请填写您的昵称！');
            return false;
        }
        if(psw == ''){
            $('.psw').siblings('.warn').html('请填写您的登录密码！');
            return false;
        }else{
            var reg = /((?=.*[a-z])(?=.*\d)|(?=[a-z])(?=.*[#@!~%^&*])|(?=.*\d)(?=.*[#@!~%^&*]))[a-z\d#@!~%^&*]{6,16}/i;
            if(!reg.test(psw)){
                $('.psw').siblings('.warn').html('请填写6-16位由数字、字母或特殊符号组成的密码');
                return false; 
            }
        }
        if(verify_code == ''){
            $('#msg-code').siblings('.warn').html('请输入6位数短信验证码');
            return false;
        }else{
            if(verify_code.length < 6){
                $('#msg-code').siblings('.warn').html('请输入6位数短信验证码');
                return false;
            }
        }
        var agree = $('input[name=agree]').prop('checked');
        if(!agree){
            $('#msg-code').siblings('.warn').html('请阅读并同意齐装网用户服务协议');
            return false;
        }
        $.ajax({
            url: baseUrl +'/uc/v1/register',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
                'App-From':'9f6910a77bc9c8d4cf9db31ed41af490',
                'Addr': 'PC_QZ',
                'App-env': AppEnv
            },
            type: 'POST',
            dataType: 'json',
            data: {
                phone: tel,
                user_pwd: psw,
                nickname: user,
                verify_code: verify_code
            },
            success: function(res) {
                if(res.error_code == 0){
                    $.cookie('center_password_token',res.data.jwt_token,{expires: 1, path: '/',domain: '.qizuang.com'});
                    window.location.href = spaceUrl;
                }else{
                    $("#msg-code").siblings('.warn').html(res.error_msg)
                }
                
            },
            fail: function(res) {
                console.log(res)
            }
        });
    });
    $('#msg-code').keyup(function(e){
        if(e.keyCode == 13){
            $(".warn").html('');
            var tel = $('.tel').val();
            var user = $('.user').val();
            var psw = $('.psw').val();
            var verify_code = $('#msg-code').val();
            if(tel == ''){
                $('.tel').siblings('.warn').html('请填写11位正确手机号');
                return false;
            }else{
                var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
                if (!$('.tel').val().match(reg)) {
                    $('.tel').siblings('.warn').html('请填写11位正确手机号');
                    $('.tel').focus();
                    $('.tel').val('');
                    return false;
                }
            };
            if(user == ''){
                $('.user').siblings('.warn').html('请填写您的昵称！');
                return false;
            }
            if(psw == ''){
                $('.psw').siblings('.warn').html('请填写您的登录密码！');
                return false;
            }else{
                var reg = /((?=.*[a-z])(?=.*\d)|(?=[a-z])(?=.*[#@!~%^&*])|(?=.*\d)(?=.*[#@!~%^&*]))[a-z\d#@!~%^&*]{6,16}/i;
                if(!reg.test(psw)){
                    $('.psw').siblings('.warn').html('请填写6-16位由数字、字母或特殊符号组成的密码');
                    return false; 
                }
            }
            if(verify_code == ''){
                $('#msg-code').siblings('.warn').html('请输入6位数短信验证码');
                return false;
            }else{
                if(verify_code.length < 6){
                    $('#msg-code').siblings('.warn').html('请输入6位数短信验证码');
                    return false;
                }
            }
            var agree = $('input[name=agree]').prop('checked');
            if(!agree){
                $('#msg-code').siblings('.warn').html('请阅读并同意齐装网用户服务协议');
                return false;
            }
            $.ajax({
                url: baseUrl +'/uc/v1/register',
                headers:{
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'App-From':'9f6910a77bc9c8d4cf9db31ed41af490',
                    'Addr': 'PC_QZ',
                    'App-env': AppEnv
                },
                type: 'POST',
                dataType: 'json',
                data: {
                    phone: tel,
                    user_pwd: psw,
                    nickname: user,
                    verify_code: verify_code
                },
                success: function(res) {
                    if(res.error_code == 0){
                        $.cookie('center_password_token',res.data.jwt_token,{expires: 1, path: '/',domain: '.qizuang.com'});
                        window.location.href = spaceUrl;
                    }else{
                        $("#msg-code").siblings('.warn').html(res.error_msg)
                    }
                    
                },
                fail: function(res) {
                    console.log(res)
                }
            });
        }
    });
    $('.close').click(function(){
        $(this).parent().parent().parent().hide();
    });
    var flag = true;
    $('.sure-code').click(function(){
        sendSms($(".tel").val(),3,$('#img-code').val(),$('#sendCode'));
    });
    $('#img-code').keyup(function(e){
        if(e.keyCode == 13){
            sendSms($(".tel").val(),3,$('#img-code').val(),$('#sendCode'));
        }
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
            $('#sendCode').attr("disabled",false);   //解除禁用
            flag = true;
        }
    };

    // 获取图形验证码
    $('.change-verify').click(function(){
        var src = baseUrl +'/uc/v1/verify?ssid=' + ssid + '&time=' + Date.parse(new Date());
        $('#captcha-verify').attr('src',src);
    });
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
                    $('#sendCode').attr("disabled",true);   //禁用
                    flag = false;
                    $('.mask').hide();
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
    var initG2 = function(){
        $("#embed-captcha").html("");
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
                    $("#embed-captcha").html("");
                    captchaObj.appendTo("#embed-captcha");
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
                                    sendSms($(".tel").val(),3,$('#img-code').val(),$('#sendCode'),validate.geetest_validate);
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