$(function(){
    $('.guanbiniu').click(function(){
        $('.beijingyiny').remove();
        $('.appointment').remove();
    });

    $('.appointment .lijiyuyue').click(function () {
        var _this = $(this);
        var name = $('input[name="c_name"]');
        var tel = $('input[name="c_tel"]');
        var qx = $('#c_qy');
        var cs = $('#c_cs');
        $(".warn").html('')
        if (name.val() == "" || name.val().length == 0) {
            name.focus();
            $('input[name="c_name"]').siblings('.warn').html("请输入您的称呼");
            return false;
        } else {
            var reg = new RegExp("^[\u4e00-\u9fa5a-zA-Z]+$");
            if (!reg.test(name.val())) {
                name.focus();
                $('input[name="c_name"]').siblings('.warn').html("请输入正确的称呼，只支持中文和英文 ^_^!");
                return false;
            }
        }
        if (qx.val() == "" || cs.val() == "") {
            $('#c_qy').siblings('.warn').html("请选择地区");
            return false;
        }
        if (tel.val() == "" || tel.val().length == 0) {
            tel.focus();
            $('input[name="c_tel"]').siblings('.warn').html("请输入您的手机号码");
            return false;
        } else {
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if (!tel.val().match(reg)) {
                tel.focus();
                $('input[name="c_tel"]').siblings('.warn').html("请填写正确的手机号码");
                return false;
            }
        }

        if (!checkDisclamer(".appointment")) {
            return false;
        }
        var source = $('.order-btn').attr('data-source');
        var data = {
            tel: tel.val(),
            cs: cs.val(),
            qx: qx.val(),
            name: name.val(),
            source: source
        };
        window.order({
            extra: data, 
            error: function () {
                alert("网络发生错误,请稍后重试！");
            },
            success: function (data, status, xhr) {
                if (data.status == 1) {
                    $.ajax({
                        url: '/poplayer/pop',
                        type: 'GET',
                        dataType: 'JSON',
                        data: {
                            type: 'yuyuesuccess'
                        }
                    })
                    .done(function (data) {
                        if (data.status == 0) {
                            _this.parent().remove();
                            $("body").append(data.tmp);
                        }
                    });
                } else {
                    alert(data.info);
                }
            },
            validate: function (item, value, method, info) {
                return true;
            }
        });
    });
})
