$(function(){
    $('.avator').hover(function(){
        $('.user-box').show();
    },function(){
        $('.user-box').hide();
    });
    var cityId = $('#getCity').attr('data-cityid');
    if(cityId == ''|| cityId == '000001'){
        $.ajax({
            type: 'GET',
            url: baseUrl+'/v2/getLocationv2',
            dataType: 'json',
            success: function(res) {
                if(res.error_code == 0){
                    $('.cityName').html(res.data.city_name);
                }else{
                    $('.cityName').html('全国');
                }
            }
        })
    } else {
        $.ajax({
            type: 'GET',
            url: baseUrl+'/v1/getcityinfo',
            dataType: 'json',
            data:{
                city_id: cityId
            },
            success: function(res) {
                if(res.error_code == 0){
                    $('.cityName').html(res.data.city_name);
                }else{
                    $('.cityName').html('全国');
                }
            }
        })
    };
    var center_password_token = $.cookie('center_password_token');
    var nickname = $.cookie('nickname');
    var logo = $.cookie('logo');
    var city_name = $.cookie('city_name');
    var quyu_name = $.cookie('quyu_name');
    $('.load-bg').show();
    // 获取基本信息
    if(nickname == undefined){
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
    } else {
        $('.load-bg').hide();
        if(logo == ''){
            $('.avator-img img').attr('src','https://zxsqn.qizuang.com/ucenter/20191109/FgaaGcPAjBrdNm2Hp1uLimD1g7HB');
            $('.user-avator img').attr('src','https://zxsqn.qizuang.com/ucenter/20191109/FgaaGcPAjBrdNm2Hp1uLimD1g7HB');
        }else{
            $('.avator-img img').attr('src',logo);
            $('.user-avator img').attr('src',logo);
        }
        $('.username span').html(nickname);
        $('.user-name').html(nickname);
        if(city_name == ''){
            $('.user-city').hide();
        }else{
            $('.user-city').show();
            $('.cityname').html(city_name);
            if(quyu_name == ''){
                $('.areaname').hide();
            }else{
                $('.areaname').html('&nbsp;|&nbsp;' + quyu_name);
            }
        }
    }
    
    $('.login-out').click(function () {
        $.removeCookie('nickname',{ path: '/',domain: '.qizuang.com' });
        $.removeCookie('logo',{ path: '/',domain: '.qizuang.com' });
        $.removeCookie('city_name',{ path: '/',domain: '.qizuang.com' });
        $.removeCookie('quyu_name',{ path: '/',domain: '.qizuang.com' });
        $.removeCookie('center_password_token',{ path: '/',domain: '.qizuang.com' });
        window.location.href = '/login';
    });
})