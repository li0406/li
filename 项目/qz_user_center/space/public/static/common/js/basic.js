$(function(){
    $('.avator').hover(function(){
        $('.user-box').show();
    },function(){
        $('.user-box').hide();
    });
    $('#down-code').click(function(){
        $('#down-box').show();
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
    $('.close').click(function(){
        $(this).parent().parent().parent().hide();
    });
    var center_password_token = $.cookie('center_password_token');

    $('.load-bg').show();

    // 获取基本信息
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
                    $('.avator-img img').attr('src','/static/common/img/default-avator.jpg');
                    $('.user-img img').attr('src','/static/common/img/default-avator.jpg');
                }else{
                    $('.avator-img img').attr('src',res.data.logo);
                    $('.user-img img').attr('src',res.data.logo);
                }
                $('.nickname').html(res.data.nickname);
                $('.user-name').html(res.data.nickname);
                if(res.data.city_name != ''){
                    $('.user-city').show();
                    $('.user-city .city').html(res.data.city_name);
                    if(res.data.quyu_name != ''){
                        $('.user-city .quyu').html('|&nbsp;' + res.data.quyu_name);
                    }else{
                        $('.user-city .quyu').hide();
                    }
                } else {
                    $('.user-city').hide();
                }
            }else{
                console.log(res)
            }
        },
        error: function(res) {
            console.log(res)
        }
    });
    
    $('.login-out').click(function () {
        $.removeCookie('nickname',{ path: '/',domain: '.qizuang.com' });
        $.removeCookie('logo',{ path: '/',domain: '.qizuang.com' });
        $.removeCookie('city_name',{ path: '/',domain: '.qizuang.com' });
        $.removeCookie('quyu_name',{ path: '/',domain: '.qizuang.com' });
        $.removeCookie('center_password_token',{ path: '/',domain: '.qizuang.com' });
        window.location.href = accountUrl + '/login';
    });
})