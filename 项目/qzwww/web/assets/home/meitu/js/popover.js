$(function(){

    //图片列表免费设计
    $('.btn-fd').on('click','.btn-sheji',function(){
        var title = $(this).parent().attr('img-title');
        var src = $(this).parent().attr('img-src');
        $('.iwantzx-info').html(title);
        $('#iwantPic').attr('src',src);
        $('.iwantzx').show();
        $('.iwantzx-box').show();
    });

    //图片列表免费报价
    $('.btn-fd').on('click','.btn-baojia',function(){
        var title = $(this).parent().attr('img-title');
        var src = $(this).parent().attr('img-src');

        $('.zxmoney-info').html(title);
        $('#zxmoneyPic').attr('src',src);

        $('.zxmoney').show();
        $('.zxmoney-box').show();
    });

    $(".iwantzx .fa-close").click(function(event) {
        $('.iwantzx').hide();
        $('.iwantzx-box').hide();
    });

    $(".zxmoney .fa-close").click(function(event) {
        $('.zxmoney').hide();
        $('.zxmoney-box').hide();
    });

    $(".zxmoney-btn").click(function(event) {
        var container = $(".zxmoney-box");
        window.order({
            extra:{
                mianji:$("input[name=mianji]",container).val(),
                cs:$("select[name=cs]",container).val(),
                qx:$("select[name=qx]",container).val(),
                name:$("input[name=money_name]",container).val(),
                tel:$("input[name=money_tel]",container).val(),
                fb_type:$("input[name=fb_type]",container).val(),
                source:Global_source1
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
                                $('#kt-price').html(data.data.kt);
                                $('#zw-price').html(data.data.zw);
                                $('#wsj-price').html(data.data.wsj);
                                $('#cf-price').html(data.data.cf);
                                $('#sd-price').html(data.data.sd);
                                $('#other-price').html(data.data.other);
                                $('#total-price').html(data.data.total);
                                $('.zxmoney .big-title').hide().siblings('.big-title1').show();
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

                if ('mianji' == item) {
                    if (!App.validate.run($("input[name=mianji]", container).val())) {
                        $("input[name=mianji]", container).addClass('focus').focus();
                        alert("请填写房屋面积");
                        return false;
                    }
                    if (!App.validate.run($("input[name=mianji]", container).val(), "num")) {
                        $("input[name=mianji]", container).addClass('focus').focus();
                        alert("无效的房屋面积");
                        return false;
                    }
                };
                if ('cs' == item && 'notempty' == method) {
                    $("select[name=cs]", container).addClass('focus').focus();
                    alert("请选择城市");
                    return false;
                };

                if($("input[name=money_name]", container).val()==""){
                     $("input[name=money_name]", container).addClass('focus').focus();
                     alert('请输入您的称呼 ^_^!');
                     return false;
                }
                 var reg = new RegExp("^[\u4e00-\u9fa5a-zA-Z]+$");
                 if (!reg.test($("input[name=money_name]", container).val())) {
                    alert("请输入正确的名称，只支持中文和英文");
                    $("input[name=money_name]", container).addClass('focus').focus();
                    return false;
                 }
                 if (!App.validate.run($("input[name=money_tel]", container).val())) {
                        alert("请输入正确的手机号码 ^_^!");
                        $("input[name=money_tel]", container).addClass('focus').focus();
                        return false;
                    } else {
                        var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
                        if (!reg.test($("input[name=money_tel]", container).val())) {
                            alert("请输入正确的手机号码 ^_^!");
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

    $(".iwantzx-btn-1").click(function(event) {
        var container = $(".iwantzx-box");
        var name = $("input[name=iwant_name]", container).val();
        var tel = $("input[name=iwant_tel]", container).val();
        var city = $("select[name=iwant_cs]", container).val();
        var quyu = $("select[name=iwant_qy]", container).val();
        if (city == '') {
            alert('您还没有选择城市哦 ^_^!');
            $("select[name=iwant_cs]", container).addClass('focus').focus();
            return false;
        }
        if (!App.validate.run(name)) {
            alert('请输入您的称呼 ^_^!');
            $("input[name=iwant_name]", container).addClass('focus').focus();
            return false;
        } else {
            var reg = new RegExp("^[\u4e00-\u9fa5a-zA-Z]+$");
            if (!reg.test($("input[name=iwant_name]", container).val())) {
                alert("请输入正确的名称，只支持中文和英文");
                $("input[name=iwant_name]", container).addClass('focus').focus();
                return false;
            }
        }

        if (!App.validate.run(tel)) {
            alert("请输入正确的手机号码 ^_^!");
            $("input[name=iwant_tel]", container).addClass('focus').focus();
            return false;
        } else {
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if (!reg.test($("input[name=iwant_tel]", container).val())) {
                alert("请输入正确的手机号码 ^_^!");
                $("input[name=iwant_tel]", container).addClass('focus').focus();
                $("input[name=iwant_tel]", container).val('');
                return false;
            }
        }
        if(!checkDisclamer(".iwantzx")){
            return false;
        }
        window.order({
            extra:{
                cs: city,
                qx: quyu,
                name: name,
                tel: tel,
                fb_type: $("input[name=fb_type]", container).val(),
                source: Global_source2,
                step:2,
                tpl:'meituSetup2',
                select_desid : "" || 0,
                select_comid : "" || 0,
                display_type : "" || 0,
                des:""
            },
            error:function(){
                alert('操作失败了');
                //container.remove();
            },
            success:function(data, status, xhr){
                $('.iwantzx-box').hide();
                if (data.status == 1) {
                    $(".iwantzx").append(data.data.tmp);
                    $('.iwantzx-ok').show();

                    var img = $(".left-modal",container).html();
                    $('.iwantzx-ok .left-modal').html(img);
                } else {
                    $('.iwantzx-box').remove();
                    $(".iwantzx").hide();
                    alert(data.info);
                    return false;
                }
            },
            validate:function(item, value, method, info){
                return true;
            }
        });
    });

});