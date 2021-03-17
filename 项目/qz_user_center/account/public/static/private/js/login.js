$(function(){
    var AppEnv = 'platform=WEB&user-agent='+ navigator.userAgent;
    $.removeCookie('nickname',{ path: '/',domain: '.qizuang.com' });
    $.removeCookie('logo',{ path: '/',domain: '.qizuang.com' });
    $.removeCookie('city_name',{ path: '/',domain: '.qizuang.com' });
    $.removeCookie('quyu_name',{ path: '/',domain: '.qizuang.com' });
    $.removeCookie('center_password_token',{ path: '/',domain: '.qizuang.com' });
    $('.user-login').click(function(){
        $(this).addClass('active').siblings('div').removeClass('active');
        $('.code-box').hide();
        $('.user-box').show();
        $('#user-login').show();
        $('#code-login').hide();
    });
    $('.code-login').click(function(){
        $(this).addClass('active').siblings('div').removeClass('active');
        $('.code-box').show();
        $('.user-box').hide();
        $('#user-login').hide();
        $('#code-login').show();
    });
    var interval;
    getAndCheck();
    $('.change-login-way').click(function(){
        $(this).children().toggleClass('computer-icon')
        if ($(this).children().hasClass('computer-icon')) {
            $('.code-login-box').show();
            $('.user-login-box').hide();
            $('.reflash').siblings('canvas').remove();
            $('.reflash').siblings('img').remove();
            getAndCheck();
        } else {
            clearInterval(interval);
            $('.code-login-box').hide();
            $('.user-login-box').show();
        }
    });
    $('.relog .zh').click(function () {
        $(".change-login-way").children().removeClass('computer-icon')
        clearInterval(interval);
        $('.code-login-box').hide();
        $('.user-login-box').show();
        $('.code-box').hide();
        $('.user-box').show();
        $('#user-login').show();
        $('#code-login').hide();
        $('.code-login').removeClass('active')
        $('.user-login').addClass('active')
    })
    $('.go-sweep-status').click(function () {
        $('.not-sweep-status').show();
        $('.sweep-in-status').hide();
        $('.reflash').hide();
        // 移除已生成的code
        $('.reflash').siblings('canvas').remove();
        $('.reflash').siblings('img').remove();
        getAndCheck();
    });
    $('.img-div').click(function () {
        $('.reflash').hide();
        // 移除已生成的code
        $('.reflash').siblings('canvas').remove();
        $('.reflash').siblings('img').remove();
        getAndCheck();
    });
    function getAndCheck() {
        var url = '',uniq = ''; 
        $.ajaxSetup({ cache: false });
        $.ajax({
            type: "GET",
            url: baseUrl +'/uc/v1/qrcode/uniq',
            dataType: 'json',
            success: function(res) {
                if(res.error_code == 0){
                    uniq = res.data.uniq;
                    url = res.data.url;
                    var qrcode = new QRCode("codeLogin", {
                        text: url,
                        colorDark : "#000000",
                        colorLight : "#ffffff",
                        correctLevel : QRCode.CorrectLevel.H
                    });
                    qrcode.clear();
                    qrcode.makeCode(url);
                    // 轮询
                    clearInterval(interval);
                    interval = setInterval(function(){
                        $.ajax({
                            type: "POST",
                            url: baseUrl+'/uc/v1/qrcode/check',
                            data: {
                                uniq: uniq
                            },
                            dataType: 'json',
                            success: function(res) {
                                if(res.error_code == 0){
                                    // NEW 初始化 SCANNED 已扫码 CONFIRMED 确认 CANCELED 已取消 EXPIRED 已过期
                                    if (res.data.status == 'EXPIRED') {
                                        $('.reflash').show();
                                        // 停止轮询
                                        clearInterval(interval);
                                    } else if (res.data.status == 'SCANNED') {
                                        $('.not-sweep-status').hide();
                                        $('.sweep-in-status').show();
                                    } else if (res.data.status == 'CONFIRMED') {
                                        $('.reflash').hide();
                                        $.cookie('center_password_token',res.data.token,{expires: 1, path: '/',domain: '.qizuang.com'});
                                        window.location.href = spaceUrl;
                                    } else if ( res.data.status== 'CANCELED') {
                                        $('.reflash').hide();
                                    }
                                }else{
                                    alert(res.error_msg);
                                }
                            },
                            error: function(res) {
                                console.log(res)
                            }
                        })
                    },2000);
                }else{
                    alert(res.error_msg);
                }
            },
            error: function(res) {
                console.log(res)
            }
        })
    }
    // 用户普通登陆
    $('#user-login').click(function(){
        $(".warn").html('');
        var user_name = $('#username').val();
        var user_pwd = $('#userpwd').val();
        if(user_name == ''){
            $('#username').siblings('.warn').html('请填写您的手机号！');
            return false;
        }else{
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if (!reg.test(user_name)) {
                $('#username').siblings('.warn').html('请填写11位正确手机号');
                $('#username').focus();
                $('#username').val('');
                return false;
            } 
        }
        if(user_pwd == ''){
            $('#userpwd').siblings('.warn').html('请填写您的登录密码！');
            return false;
        }
        $.ajax({
            type: "POST",
            url: baseUrl+'/uc/v1/account/login',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
                'App-From':'9f6910a77bc9c8d4cf9db31ed41af490',
                'Addr': 'PC_QZ',
                'App-env': AppEnv
            },
            data: {user_name: user_name,user_pwd: user_pwd},
            dataType: 'json',
            success: function(res) {
                if(res.error_code == 0){
                    $.cookie('center_password_token',res.data.jwt_token,{expires: 1, path: '/',domain: '.qizuang.com'});
                    var url = window.location.href;
                    if(url.indexOf('?') != -1){
                        url = url.substring(url.indexOf('?')+1).split('=')[1];
                        window.location.href = url;
                    } else {
                        window.location.href = spaceUrl;
                    }
                 }else{
                     if(res.error_code == 4000010){
                        $('#userpwd').siblings('.warn').html(res.error_msg);
                     }else{
                        $('#userpwd').siblings('.warn').html('账号密码不正确');
                     }
                 }
            },
            error: function(res) {
               console.log(res)
            }
        })
    });
    // 验证码登陆
    $('#code-login').click(function(){
        $('.warn').html('');
        var phone = $('.tel').val();
        var verify_code = $('#msg-code').val();
        if(phone == ''){
            $('.tel').siblings('.warn').html('请填写手机号');
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
        if(verify_code == ''){
            $('#msg-code').siblings('.warn').html('请输入6位数短信验证码');
            return false;
        }else {
            if(verify_code.length < 6){
                $('#msg-code').siblings('.warn').html('请输入6位数短信验证码');
                return false;
            }
        };
        
        $.ajax({
            type: 'POST',
            url: baseUrl +'/uc/v1/login',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
                'App-From':'9f6910a77bc9c8d4cf9db31ed41af490',
                'Addr': 'PC_QZ',
                'App-env': AppEnv
            },
            data: {phone: phone,verify_code: verify_code},
            dataType: 'json',
            success: function(res) {
                if(res.error_code == 0){
                    $.cookie('center_password_token',res.data.jwt_token,{expires: 1, path: '/',domain: '.qizuang.com'});
                    var url = window.location.href;
                    if(url.indexOf('?') != -1){
                        url = url.substring(url.indexOf('?')+1).split('=')[1];
                        window.location.href = url;
                    } else {
                        window.location.href = spaceUrl;
                    }
                 }else{
                    $('#msg-code').siblings('.warn').html(res.error_msg);
                 }
            },
            error: function(res) {
               console.log(res)
            }
        })
    });
    // 用户普通登陆
    $('#userpwd').keyup(function(e){
        if(e.keyCode == 13){
            $(".warn").html('');
            var user_name = $('#username').val();
            var user_pwd = $('#userpwd').val();
            if(user_name == ''){
                $('#username').siblings('.warn').html('请填写您的手机号！');
                return false;
            }else{
                var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
                if (!reg.test(user_name)) {
                    $('#username').siblings('.warn').html('请填写11位正确手机号');
                    $('#username').focus();
                    $('#username').val('');
                    return false;
                } 
            }
            if(user_pwd == ''){
                $('#userpwd').siblings('.warn').html('请填写您的登录密码！');
                return false;
            }
            $.ajax({
                type: "POST",
                url: baseUrl+'/uc/v1/account/login',
                headers:{
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'App-From':'9f6910a77bc9c8d4cf9db31ed41af490',
                    'Addr': 'PC_QZ',
                    'App-env': AppEnv
                },
                data: {user_name: user_name,user_pwd: user_pwd},
                dataType: 'json',
                success: function(res) {
                    if(res.error_code == 0){
                        $.cookie('center_password_token',res.data.jwt_token,{expires: 1, path: '/',domain: '.qizuang.com'});
                        var url = window.location.href;
                        if(url.indexOf('?') != -1){
                            url = url.substring(url.indexOf('?')+1).split('=')[1];
                            window.location.href = url;
                        } else {
                            window.location.href = spaceUrl;
                        }
                    }else{
                        if(res.error_code == 4000010){
                            $('#userpwd').siblings('.warn').html(res.error_msg);
                        }else{
                            $('#userpwd').siblings('.warn').html('账号密码不正确');
                        }
                    }
                },
                error: function(res) {
                console.log(res)
                }
            })
        }
    });
    // 验证码登陆
    $('#msg-code').keyup(function(e){
        if(e.keyCode == 13){
            $('.warn').html('');
            var phone = $('.tel').val();
            var verify_code = $('#msg-code').val();
            if(phone == ''){
                $('.tel').siblings('.warn').html('请填写手机号');
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
            if(verify_code == ''){
                $('#msg-code').siblings('.warn').html('请输入6位数短信验证码');
                return false;
            }else {
                if(verify_code.length < 6){
                    $('#msg-code').siblings('.warn').html('请输入6位数短信验证码');
                    return false;
                }
            };
            
            $.ajax({
                type: 'POST',
                url: baseUrl +'/uc/v1/login',
                headers:{
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'App-From':'9f6910a77bc9c8d4cf9db31ed41af490',
                    'Addr': 'PC_QZ',
                    'App-env': AppEnv
                },
                data: {phone: phone,verify_code: verify_code},
                dataType: 'json',
                success: function(res) {
                    if(res.error_code == 0){
                        $.cookie('center_password_token',res.data.jwt_token,{expires: 1, path: '/',domain: '.qizuang.com'});
                        var url = window.location.href;
                        if(url.indexOf('?') != -1){
                            url = url.substring(url.indexOf('?')+1).split('=')[1];
                            window.location.href = url;
                        } else {
                            window.location.href = spaceUrl;
                        }
                    }else{
                        $('#msg-code').siblings('.warn').html(res.error_msg);
                    }
                },
                error: function(res) {
                console.log(res)
                }
            })
        }
    });
    var flag = true;
    // 获取短信验证码
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
                    // $('.mask').show();
                    // $('#img-code').val('');
                    //显示提示
                    // $('.tel').siblings('.warn').html('请先完成验证');
                    flag = false;
                    initG2();
                }
            }
        };
    });

     // 获取图形验证码
    var ssid = Math.ceil(Math.random()*10000);
    $('#captcha-verify').attr('src',baseUrl + '/uc/v1/verify?ssid=' + ssid + '&time=' + Date.parse(new Date()));
    $('.change-verify').click(function(){
        var src = baseUrl + '/uc/v1/verify?ssid='+ ssid + '&time=' + Date.parse(new Date());
        $('#captcha-verify').attr('src',src);
    });
    $('.close').click(function(){
        $(this).parent().parent().parent().hide();
    });
    $('.sure-code').click(function(){
        sendSms($(".tel").val(),9,$('#img-code').val(),$('#sendCode'));
    });
    $('#img-code').keyup(function(e){
        if(e.keyCode == 13){
            sendSms($(".tel").val(),9,$('#img-code').val(),$('#sendCode'));
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
            $('#sendCode').attr("disabled",false);    //解除禁用
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
                    $('#sendCode').attr("disabled",true);   //禁用
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
                                sendSms($(".tel").val(),9,$('#sendCode'),validate.geetest_validate);
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