$(function(){
    $('.show-perfect').click(function () {
        if($('.show-perfect').find('.icon').hasClass('icon-down')){
            $('.show-perfect').find('.text').html('收起已完善项');
            $('.show-perfect').find('.icon').removeClass('icon-down').addClass('icon-up');
            $('.tel-login-email').hide();
        }else{
            $('.show-perfect').find('.text').html('展开已完善项');
            $('.show-perfect').find('.icon').removeClass('icon-up').addClass('icon-down');
            $('.tel-login-email').show();
        }
    })
    var center_password_token = $.cookie('center_password_token');
    $.ajax({
        type: 'GET',
        url: baseUrl +'/uc/v1/accountspeed',
        headers:{
            'Content-Type': 'application/x-www-form-urlencoded',
            'token': center_password_token
        },
        dataType: 'json',
        success: function(res) {
            if(res.error_code == 0){
               var speed = res.data.speed;
               $('.progress-div .text').html(speed +'%');
               $('.progress .bar').css({'width':speed +'%'});
               if(speed >= 100){
                   $('.progress-div .tips').hide();
               }else{
                $('.progress-div .tips').show();
               }
               if(res.data.nc_tx == 0){
                   $('.nc_tx').hide();
                   $('.perfect-info').show();
               }else{
                   $('.nc_tx').show();
                   $('.perfect-info').hide();
               }
               if(res.data.password == 0){
                    $('.password').hide();
                    $('.perfect-psw').show();
                }else{
                    $('.password').show();
                    $('.perfect-psw').hide();
                }
                if(res.data.safe_phone == 0){
                    $('.safe_phone').hide();
                }else{
                    $('.safe_phone').show();
                }
                if(res.data.safe_login == 0){
                    $('.safe_login').hide();
                }else{
                    $('.safe_login').show();
                }
                if(res.data.safe_email == 0){
                    $('.safe_email').hide();
                    $('.perfect-email').show();
                }else{
                    $('.safe_email').show();
                    $('.perfect-email').hide();
                }
             }else{
                alert(res.error_msg);
             }
        },
        error: function(res) {
           console.log(res)
        }
    });
    $.ajax({
        type: 'GET',
        url: baseUrl+'/uc/v1/basicinfo',
        headers: {token:center_password_token},
        dataType: 'json',
        success: function(res) {
            if(res.error_code == 0){
                $('.load-bg').hide();
                $.cookie('nickname',res.data.nickname,{expires: 1, path: '/',domain: '.qizuang.com'});
                $.cookie('logo',res.data.logo,{expires: 1, path: '/',domain: '.qizuang.com'});
                $.cookie('city_name',res.data.city_name,{expires: 1, path: '/',domain: '.qizuang.com'});
                $.cookie('quyu_name',res.data.quyu_name,{expires: 1, path: '/',domain: '.qizuang.com'});

                if(res.data.logo == ''){
                    $('.avator-img img').attr('src','https://zxsqn.qizuang.com/ucenter/20191109/FgaaGcPAjBrdNm2Hp1uLimD1g7HB');
                    $('.user-avator img').attr('src','https://zxsqn.qizuang.com/ucenter/20191109/FgaaGcPAjBrdNm2Hp1uLimD1g7HB');
                }else{
                    $('.avator-img img').attr('src',res.data.logo);
                    $('.user-avator img').attr('src',res.data.logo);
                }
                $('.username span').html(res.data.nickname);
                $('.user-name').html(res.data.nickname);
                if(res.data.city_name == ''){
                    $('.user-city').hide();
                }else{
                    $('.user-city').show();
                    $('.cityname').html(res.data.city_name);
                    if(res.data.quyu_name != ''){
                        $('.areaname').html('&nbsp;|&nbsp;' + res.data.quyu_name);
                    }else{
                        $('.areaname').hide();
                    }
                }
            }else{
                console.log(res)
            }
        },
        error: function(res) {
            console.log(res)
        }
    })
})