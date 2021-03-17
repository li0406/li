$(function(){
    var center_password_token = $.cookie('center_password_token');
    var ssid = Math.ceil(Math.random()*10000);
    $.ajax({
        type: 'GET',
        url: baseUrl +'/uc/v1/account/protection',
        headers: {
            token:center_password_token
        },
        dataType: 'json',
        success: function (res) {
            if(res.error_code == 0){
                var phoneText = res.data.phone;
                phoneText = phoneText.substr(0, 3) + '*****' + phoneText.substring(8, 11);
                $("#user-phone").val(res.data.phone);
                $("#safe-phone").val(phoneText);
            }else{
                console.log(res)
            }
        },
        error: function (res) {
            console.log(res) 
        } 
    })
    $("#next").click(function(){
        var verify = $('#step1-yzm').val();
        if(verify == ''){
            $('#step1-yzm').siblings('.warn').html('请输入正确图形验证码');
            return false;
        }
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
                }else{
                    $('#step1-yzm').siblings('.warn').html(res.error_msg);
                }
            },
            error: function(res) {
                console.log(res)
            }
        })
    });
    $('#next-two').click(function(){
        var phone = $("#user-phone").val();
        var verify_code = $('#step2-yzm').val();
        if(verify_code == ''){
            $('#step2-yzm').siblings('.warn').html('验证码不能为空');
            return false;
        }else {
            if(verify_code.length < 6){
                $('#step2-yzm').siblings('.warn').html('请输入6位数短信验证码');
                return false;
            }
        };
        $.ajax({
            type: "POST",
            url: baseUrl+'/uc/v1/msg/checkphonecode',
            dataType: 'json',
            data:{
                phone: phone,
                verify_code: verify_code,
                type: 1
            },
            success: function(res) {
                if(res.error_code == 0){
                    $('.forC').addClass('for-cur');
                    $('.form1').hide();
                    $('.form2').hide();
                    $('.form3').show();
                }else{
                    $('#step2-yzm').siblings('.warn').html(res.error_msg);
                }
            },
            error: function(res) {
                console.log(res)
            }
        })
    });
    $('#sure').click(function(){
        $('.warn').html('');
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
        }
        if(user_pwd != user_pwd_confirm){
            $('#user_pwd_confirm').siblings('.warn').html('新密码和确认密码请输入一致');
            return false;
        }
        $.ajax({
            type: "POST",
            url: baseUrl+'/uc/v1/modify/pwd',
            headers:{
                token: center_password_token
            },
            dataType: 'json',
            data:{
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
                        $.removeCookie('nickname',{ path: '/',domain: '.qizuang.com' });
                        $.removeCookie('logo',{ path: '/',domain: '.qizuang.com' });
                        $.removeCookie('city_name',{ path: '/',domain: '.qizuang.com' });
                        $.removeCookie('quyu_name',{ path: '/',domain: '.qizuang.com' });
                        $.removeCookie('center_password_token',{ path: '/',domain: '.qizuang.com' });
                        window.location.href = '/login';
                    },3000);
                }else{
                    $('#user_pwd_confirm').siblings('.warn').html(res.error_msg);
                }
            },
            error: function(res) {
                console.log(res)
            }
        });
    });
    // 退出至登录页
    $('.login-out').click(function () {
        $.removeCookie('nickname',{ path: '/',domain: '.qizuang.com' });
        $.removeCookie('logo',{ path: '/',domain: '.qizuang.com' });
        $.removeCookie('city_name',{ path: '/',domain: '.qizuang.com' });
        $.removeCookie('quyu_name',{ path: '/',domain: '.qizuang.com' });
        $.removeCookie('center_password_token',{ path: '/',domain: '.qizuang.com' });
        window.location.href = '/login';
    });
    $('#captcha-verify').attr('src', baseUrl + '/uc/v1/verify?ssid=' + ssid);
    $('.change-verify').click(function(){
        var src = baseUrl + '/uc/v1/verify?ssid='+ ssid + '&time=' + Date.parse(new Date());
        $('#captcha-verify').attr('src',src);
    });
    // 发送验证码，确认发送
    var flag = true;
    $('.sure-code').click(function(){
        sendSms($("#user-phone").val(),1,$('#img-code').val(),$('.send-yzm'));
    });
    $('#img-code').keyup(function(e){
        if(e.keyCode == 13){
            sendSms($("#user-phone").val(),1,$('#img-code').val(),$('.send-yzm'));
        }
    });
    $('.send-yzm').click(function(){
        if (flag) {
            $('.send-yzm').siblings('.warn').html('');
            flag = false;
            initG2();
        }
    });
    var ssid1 = Math.ceil(Math.random()*10000);
    $('#captcha-verify1').attr('src',baseUrl + '/uc/v1/verify?ssid=' + ssid1);

    $('.change-verify1').click(function(){
        var src = baseUrl + '/uc/v1/verify?ssid='+ ssid1 + '&time=' + Date.parse(new Date());
        $('#captcha-verify1').attr('src',src);
    });
    $('.close').click(function(){
        $(this).parent().parent().parent().hide();
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
                                    $('.send-yzm').siblings('.warn').html('');
                                    sendSms($("#user-phone").val(),1,$('#img-code').val(),$('.send-yzm'),validate.geetest_validate);
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