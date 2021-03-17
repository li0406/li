$(function(){
    var initCropper = function (img, input) {
        var $image = img;
        var options = {
            aspectRatio: 1, // 纵横比
            viewMode: 2,
            preview: '.img-preview' // 预览图的class名
        };
        $image.cropper(options);
        var $inputImage = input;
        var uploadedImageURL;
        if (URL) {
            // 给input添加监听
            $inputImage.change(function () {
                var files = this.files;
                var file;
                if (!$image.data('cropper')) {
                    return;
                }
                if (files && files.length) {
                    file = files[0];
                    // 判断是否是图像文件
                    if (/^image\/\w+$/.test(file.type)) {
                        // 如果URL已存在就先释放
                        if (uploadedImageURL) {
                            URL.revokeObjectURL(uploadedImageURL);
                        }
                        uploadedImageURL = URL.createObjectURL(file);
                        // 销毁cropper后更改src属性再重新创建cropper
                        $image.cropper('destroy').attr('src', uploadedImageURL).cropper(options);
                        $inputImage.val('');
                    } else {
                        window.alert('请选择一个图像文件！');
                    }
                }
            });
        } else {
            $inputImage.prop('disabled', true).addClass('disabled');
        }
    }
    $('.yl-img,.yulan').hide();
    $('.uploadimg').click(function(){
        $('.yl-img,.yulan').show();
        initCropper($('#photo'), $('#input'));
    })

    // 获取基本信息
    var center_password_token = $.cookie('center_password_token');
    var city_name = $.cookie('city_name');
    var quyu_name = $.cookie('quyu_name');

    // 上传头像
    $('.sc-tx').click(function(){
        $("#photo").cropper('getCroppedCanvas').toBlob(function (blob) {
            var formdata = new FormData();
            formdata.append('file',blob)
            $.ajax({
                url: baseUrl +'/uc/v1/img/imgupload',
                headers: {'token':center_password_token},
                processData: false,
                contentType: false,
                type: 'POST',
                data: formdata,
                success: function (res) {
                    if(res.error_code == 0){
                        var src = 'https://zxsqn.qizuang.com/' + res.data.img_path;
                        $('.default-img').attr('src',src);
                    }
                },
                error: function (res) {
                    console.log(res)
                }
            });    
        });
    });
    $('#save').click(function () {
        var city = $("#city").val();
        var quyu = $("#area").val();
        var nickname = $('.nickname').val();
        var sex = $("input[type='radio']:checked").val();
        var contact_phone = $('.contact_phone').val();
        var email = $('.email').val();
        var qq = $('.wechat').val();
        var logo = $('.touxiang img').attr('src');
        if(city == 0||quyu == 0){
            $("#city").siblings('.warn').html('请选择您的所在城区');
            return false;
        };
        if(nickname == ''){
            $('.nickname').siblings('.warn').html('昵称不可为空')
            return false;
        }else{
            var reg = new RegExp('^[\u4E00-\u9FA5a-zA-Z0-9]*$');
            if(!reg.test(nickname)){
                $('.nickname').siblings('.warn').html('昵称不支持特殊字符');
                return false;
            }
        }
        if(contact_phone == ''){
            $('.contact_phone').siblings('.warn').html('请填写您的联系手机号码');
            return false;
        }else{
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if (!$('.contact_phone').val().match(reg)) {
                $('.contact_phone').siblings('.warn').html('请填写11位正确手机号');
                $('.contact_phone').focus();
                return false;
            }
        };
        if(email!= ''){
            var reg = /^\w+@[a-z0-9]+\.[a-z]+$/i;
            if(!reg.test(email)){
                $('.email').siblings('.warn').html('请输入有效邮箱');
                return false;
            }
        };
        
        $.ajax({
            type: 'POST',
            url: baseUrl +'/uc/v1/basicinfo/edit',
            headers: {token:center_password_token},
            data: {
                city:city,
                quyu:quyu,
                nickname:nickname,
                sex:sex,
                contact_phone:contact_phone,
                email:email,
                qq:qq,
                logo:logo
            },
            dataType: 'json',
            success: function (res) {
                if(res.error_code == 0){
                    $.cookie('nickname',nickname,{expires: 1, path: '/',domain: '.qizuang.com'});
                    $.cookie('logo',logo,{expires: 1, path: '/',domain: '.qizuang.com'});
                    $.cookie('city_name',city_name,{expires: 1, path: '/',domain: '.qizuang.com'});
                    $.cookie('quyu_name',quyu_name,{expires: 1, path: '/',domain: '.qizuang.com'});
                    alert('保存成功！');
                    window.location.href=window.location.href; 
                }else{
                    console.log(res)
                }
            },
            error: function (res) {
                console.log(res)
            }
        })
    });
    // 获取所有城市
    var city = [],area = [];
    $('.load-bg').show();
    $.ajax({
        type: 'GET',
        url: baseUrl +'/uc/v1/getallcitylist',
        dataType: 'json',
        success: function (res) {
            if(res.error_code == 0){
                for(var key in res.data.chen){
                    if(!res.data.chen.hasOwnProperty(key)){
                        continue;
                    }
                    var item = {}; 
                    item[key] = res.data.chen[key]; 
                    city.push(item); 
                }
                for(var key in res.data.qu){
                    if(!res.data.qu.hasOwnProperty(key)){
                        continue;
                    }
                    var item = {}; 
                    item[key] = res.data.qu[key]; 
                    area.push(item); 
                }
                var cityOptions = '<option value="0">请选择</option>';
                $.each(city,function(index,item){
                    cityOptions += '<option value="'+ item[index].id +'">'+ item[index].cname +'</option>'
                });
                $('#city').html(cityOptions);
                // 第二次回调 获取基本信息
                $.ajax({
                    type: 'GET',
                    url: baseUrl +'/uc/v1/basicinfo',
                    headers: {token:center_password_token},
                    dataType: 'json',
                    success: function (res) {
                        if(res.error_code == 0){
                            $.cookie('nickname',res.data.nickname,{expires: 1, path: '/',domain: '.qizuang.com'});
                            $.cookie('logo',res.data.logo,{expires: 1, path: '/',domain: '.qizuang.com'});
                            $.cookie('city_name',res.data.city_name,{expires: 1, path: '/',domain: '.qizuang.com'});
                            $.cookie('quyu_name',res.data.quyu_name,{expires: 1, path: '/',domain: '.qizuang.com'});
                            $('.username span').html(res.data.nickname);
                            $('.user-name').html(res.data.nickname);
                            if(res.data.city_name!=''){
                                $('.user-city').show();
                                $('.cityname').html(res.data.city_name);
                                if(res.data.quyu_name != ''){
                                    $('.areaname').html('&nbsp;|&nbsp;' + res.data.quyu_name);
                                }else{
                                    $('.areaname').hide();
                                }
                            }else{
                                $('.user-city').hide();
                            }
                            
                            var logo = '';
                            if(res.data.logo == ''){
                                logo = 'https://zxsqn.qizuang.com/ucenter/20191109/FgaaGcPAjBrdNm2Hp1uLimD1g7HB'
                                $('.avator-img img').attr('src',logo);
                                $('.user-avator img').attr('src',logo);
                            }else{
                                logo = res.data.logo;
                                $('.avator-img img').attr('src',logo);
                                $('.user-avator img').attr('src',logo);
                            }
                            var city = res.data.city
                            $('#city').val(city);
                            var areaOptions = '<option value="0">请选择</option>';
                            $.each(area,function(index,item){
                                for(var key in item){
                                    if(key == city){
                                        $.each(item[key],function(index,item){
                                            areaOptions += '<option value="'+ item.qz_areaid +'">'+ item.oldName +'</option>'
                                        })
                                    }
                                }
                            });
                            $('#area').html(areaOptions);
                            $('#area').val(res.data.quyu);
                            $('.nickname').val(res.data.nickname);
                            $('.contact_phone').val(res.data.contact_phone);
                            $('.wechat').val(res.data.qq);
                            $('.email').val(res.data.email);
                            // initCropper($('#photo'), $('#input'));
                            $('.touxiang img').attr('src',logo);
                            $('#photo').attr('src',logo);
                            $('.img-preview img').attr('src',logo);
                            $('.cropper-view-box img').attr('src',logo);
                            $('.cropper-canvas img').attr('src',logo);
                            if(res.data.sex == 1 ){
                                $("input[type='radio'][value='1']").attr("checked",true); 
                            } else if(res.data.sex == 2){
                                $("input[type='radio'][value='2']").attr("checked",true); 
                            } else {
                                $("input[type='radio'][value='1']").attr("checked",true); 
                            }
                            $('.load-bg').hide();
                        } else {
                            console.log(res)
                        }
                    },
                    error: function (res) {
                        console.log(res)
                    }
                });
            } else {
                alert(res.error_msg);
            }
        },
        error: function (res) {
            console.log(res) 
        }
    })
    
    $("#city").change(function(){
        var cityid = $(this).val()
        var areaOptions = '<option value="">请选择</option>';
        $.each(area,function(index,item){
            for(var key in item){
                if(key == cityid){
                    $.each(item[key],function(index,item){
                        areaOptions += '<option value="'+ item.qz_areaid +'">'+ item.oldName +'</option>'
                    })
                }
            }
        });
        $('#area').html(areaOptions);
    })

})