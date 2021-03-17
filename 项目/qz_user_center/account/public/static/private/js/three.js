$(function(){
    var uid = $('input[name=unionid]').val();
    var third_type = $('input[name=type]').val();
    var AppEnv = 'platform=WEB&user-agent='+ navigator.userAgent;

    $('.bind-account').click(function(){
        $('.warn').html('');
        var third_nick_name = $('.user-name').html();
        var avatar = $('.avator img').attr('src');
        var phone = $('.phone').val();
        var verify_code = $('.verify_code').val();
        if(phone == ''){
            $('.phone').siblings('.warn').html('请填写手机号');
            return false;
        }else{
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if (!$('.phone').val().match(reg)) {
                $('.phone').siblings('.warn').html('请填写11位正确手机号');
                $('.phone').focus();
                $('.phone').val('');
                return false;
            }
        }
        if(verify_code == ''){
            $('.verify_code').siblings('.warn').html('请输入6位数短信验证码');
            return false;
        }else{
            if(verify_code.length < 6){
                $('.verify_code').siblings('.warn').html('请输入6位数短信验证码');
                return false;
            }
        }
        $.ajax({
            type: 'POST',
            url: baseUrl +'/uc/v1/third/party/phone',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
                'App-From':'9f6910a77bc9c8d4cf9db31ed41af490',
                'Addr': 'PC_QZ',
                'App-env': AppEnv
            },
            data: {
                uid: uid,
                third_nick_name: third_nick_name,
                avatar:avatar,
                third_type: third_type,
                phone:phone,
                verify_code:verify_code
            },
            dataType: 'json',
            success: function(res) {
                if(res.error_code == 0){
                    if(res.data.success_code == 1){
                        $.cookie('center_password_token',res.data.jwt_token,{expires: 1, path: '/',domain: '.qizuang.com'});
                        window.location.href = spaceUrl;
                    }
                 }else{
                    $('.verify_code').siblings('.warn').html(res.error_msg)
                 }
            },
            error: function(res) {
                console.log(res)
            }
        })
    });
    $('.get-code').click(function(){
        var tel = $('.phone').val();
        if(tel == ''){
            $('.phone').siblings('.warn').html('请填写11位正确手机号');
            return false;
        }else{
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if (!$('.phone').val().match(reg)) {
                $('.phone').siblings('.warn').html('请填写11位正确手机号');
                $('.phone').focus();
                $('.phone').val('');
                return false;
            } else {
                if (flag) {
                    // $('.mask').show();
                    // $('#img-code').val('');
                    // $('.phone').siblings('.warn').html('请先完成验证');
                    flag = false;
                    initG2();
                }
            }
        };
    });
    $('.close').click(function(){
        $(this).parent().parent().parent().hide();
    });
    var flag = true;
    $('.sure-code').click(function(){
        if($('#img-code').val() == ''){
            $('#img-code').siblings('.warn').html('请输入右侧验证码');
            return false;
        }
        sendSms($(".phone").val(),6,$('#img-code').val(),$('#sendCode'));
    });
    $('#img-code').keyup(function(e){
        if(e.keyCode == 13){
            if($('#img-code').val() == ''){
                $('#img-code').siblings('.warn').html('请输入右侧验证码');
                return false;
            }
            sendSms($(".phone").val(),6,$('#img-code').val(),$('#sendCode'));
        }
    });
    // 获取图形验证码
    var ssid = Math.ceil(Math.random()*10000);
    $('#captcha-verify').attr('src',baseUrl +'/uc/v1/verify?ssid=' + ssid);
    $('.change-verify').click(function(){
        var src = baseUrl +'/uc/v1/verify?ssid=' + ssid + '&time=' + Date.parse(new Date());
        $('#captcha-verify').attr('src',src);
    });
    $('#img-code').keyup(function(e){
        if(e.keyCode == 13){
            if($('#img-code').val() == ''){
                $('#img-code').siblings('.warn').html('请输入右侧验证码');
                return false;
            }
            sendSms($(".phone").val(),6,$('#img-code').val(),$('#sendCode'));
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
            flag = true;
        }
    };
    function sendSms(tel,type,imgverify,target,challenge){
        var json ={};

        json["phone"] = tel;
        json["type"] = type;
        json["verify_type"] = 2;
        json['challenge'] = challenge;
        $.ajax({
            type : "POST",
            url : baseUrl +"/uc/v1/msg/send",
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



    /******************  极验  ******************************************/
    var initG2 = function(){
        $("#embed-captcha").html("");
        $('#sendCode').siblings('.warn').html('');
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
                                    $('.phone').siblings('.warn').html('');
                                    sendSms($(".phone").val(),6,$('#img-code').val(),$('#sendCode'),validate.geetest_validate);
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