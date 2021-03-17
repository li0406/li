$(function(){
    var source = '';
    $(document).on('click','.find-sheji',function(){
        source = $(this).attr('data-source');
        $('input[name=money_mianji]').val('');
        $('input[name=money_name]').val('');
        $('input[name=money_tel]').val('');
        var title = $(this).siblings('.img').children('.img-title').html();
        var src = $(this).siblings('.img').children('img').attr('src');

        $('.zxmoney-info').html(title);
        $('#zxmoneyPic').attr('src',src);

        $('.zxmoney').show();
        $('.zxmoney-box').show();
    });
    $(".zxmoney .fa-close").click(function(event) {
        $('.zxmoney').remove();
    });

    $(".zxmoney-btn").click(function(event) {
        var container = $(".zxmoney-box");
        $(".focus", container).removeClass('focus');
        $('.valdate-info').html('');
        var source = $('.find-sheji').attr('data-source');
        window.order({
            extra:{
                cs:$("select[name=cs]",container).val(),
                qx:$("select[name=qy]",container).val(),
                mianji:$("input[name=mianji]",container).val(),
                name:$("input[name=money_name]",container).val(),
                tel:$("input[name=money_tel]",container).val(),
                source: source
            },
            error:function(){
                alert('获取报价失败,请刷新页面');
            },
            success:function(data, status, xhr){
                if(data.status == 1){
                    $.ajax({
                        url: '/getdetailsbyajax/',
                        type: 'GET',
                        dataType: 'JSON'
                    })
                        .done(function(data) {
                            if(data.status == 1){
                                $('#ktPrice').html(data.data.kt);
                                $('#zwPrice').html(data.data.zw);
                                $('#wsjPrice').html(data.data.wsj);
                                $('#cfPrice').html(data.data.cf);
                                $('#sdPrice').html(data.data.sd);
                                $('#otherPrice').html(data.data.other);
                                $('#totalPrice').html(data.data.total);
                                $('.zxmoney .big-title').hide().siblings('.big-title1').show();
                                $('.zxmoney .disclaimer').show();
                                $('.priceold',container).addClass('price');
                            }else{
                                $('.zxmoney-box').remove();
                                $(".zxmoney").hide();
                                alert(data.info);
                                return false;
                            }
                        })
                        .fail(function(xhr) {
                            alert('获取报价失败,请刷新页面');
                        });
                }else{
                    alert(data.info);
                }
            },
            validate:function(item, value, method, info){
                if ('cs' == item && 'notempty' == method) {
                    $("select[name=cs]", container).addClass('focus').focus();
                    $("select[name=cs]").siblings('.valdate-info').html('请选择城市');
                    return false;
                }

                if ('mianji' == item) {
                    if (!App.validate.run($("input[name=mianji]", container).val())) {
                        $("input[name=mianji]", container).addClass('focus').focus();
                        $("input[name=mianji]").siblings('.valdate-info').html('请填写房屋面积');
                        return false;
                    }
                    if (!App.validate.run($("input[name=mianji]", container).val(), "num")) {
                        $("input[name=mianji]", container).addClass('focus').focus();
                        $("input[name=mianji]").siblings('.valdate-info').html('无效的房屋面积');
                        return false;
                    }
                    if($("input[name=mianji]",container).val() <= 0 || $("input[name=mianji]",container).val() > 10000){
                        $("input[name=mianji]", container).addClass('focus').focus();
                        $("input[name=mianji]").siblings('.valdate-info').html('房屋面积请输入1-10000之间的数字 ^_^!');
                        return false;
                    }
                }

                if($("input[name=money_name]", container).val()==""){
                     $("input[name=money_name]", container).addClass('focus').focus();
                     $("input[name=money_name]").siblings('.valdate-info').html('请输入您的称呼 ^_^!');
                     return false;
                }
                 var reg = new RegExp("^[\u4e00-\u9fa5a-zA-Z]+$");
                 if (!reg.test($("input[name=money_name]", container).val())) {
                    $("input[name=money_name]").siblings('.valdate-info').html('请输入正确的名称，只支持中文和英文');
                    $("input[name=money_name]", container).addClass('focus').focus();
                    return false;
                 }
                 if (!App.validate.run($("input[name=money_tel]", container).val())) {
                        $("input[name=money_tel]").siblings('.valdate-info').html('请输入正确的手机号码 ^_^!');
                        $("input[name=money_tel]", container).addClass('focus').focus();
                        return false;
                    } else {
                        var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
                        if (!reg.test($("input[name=money_tel]", container).val())) {
                            $("input[name=money_tel]").siblings('.valdate-info').html('请输入正确的手机号码 ^_^!');
                            $("input[name=money_tel]", container).addClass('focus').focus();
                            $("input[name=money_tel]", container).val('');
                            return false;
                        }
                    }

                if(!checkDisclamer(".zxmoney-box")){
                    return false;
                }
                return true;
            }
        });
    });
})