$(function () {
    var flag = true;
    var vertifyFlag = true;
    var timer = null;
    $("body").on("click", ".win_box_close", function () {
        $(".mask").hide();
        $(".telbindBox").hide();
        $(".idcheckBox").hide();
        $(".sj-bindphone").val("");
        $(".sj-bindyzm").val("");
        window.location.reload();
    })

    $(".sj-changePhone").click(function () {
        $(".mask").show();
        // $(".telbindBox").show();
        verifyID();
    })

    //身份验证
    function verifyID() {
        $(".idcheckBox").show();
        $(".input-checktypeyzm").val("");
        //验证码
        $(".btncode").click(function () {
            var _this = $(this);
            var checkPhone = $(".input-checktype").data("value");
            if (vertifyFlag) {
                vertifyFlag = false;
                $.ajax({
                    url: 'http://f.qizuang.com/user/sendsms',
                    method: "POST",
                    dataType: "JSON",
                    data: {tel: checkPhone}
                })
                    .done(function (res) {
                        if (res.status == 1) {
                            msg(res.info);
                            countDown(_this, 60)
                            $(".sj-tipBlue p").show().delay(1000).hide(0);
                        } else {
                            msg(res.info)
                        }
                    })
                    .fail(function (xhr) {
                        alert('发生未知错误，请稍后重试~');
                        return false;
                    })
            }

        })
        $(".idVertifyBtn").click(function (event) {
            event.stopPropagation();
            $(".btncode").removeAttr("disabled");
            var checkPhone = $(".input-checktype").data("value");
            var yzCode = $(".input-checktypeyzm").val().trim();
            if (yzCode == '') {
                msg("验证码不能为空");
                return false;
            }
            if (checkPhone && yzCode) {
                $.ajax({
                    url: '/user/verifysmscode',
                    method: 'POST',
                    dataType: "JSON",
                    data: {tel: checkPhone, code: yzCode}
                })
                    .done(function (res) {
                        if (res.status == 1) {
                            msg("验证通过");
                            $(".btncode").html("获取验证码");
                            $(".idcheckBox").hide();
                            $(".telbindBox").show();
                            clearTimeout(timer);
                            vertifyFlag = true;
                        } else {
                            msg("验证码输入错误");
                        }
                    }).fail(function (xhr) {
                    msg("未知错误，请刷新重试")
                })
            }
        })
    }

    function countDown(obj, num) {
        if (num > 0) {
            obj.text(num + "s");
            num--;
            setTimeout(function (obj, num) {
                countDown(obj, num);
            }, 1000, obj, num);
            obj.attr('disabled', true)
        }
        else {
            obj.text("获取验证码");
            obj.removeAttr('disabled');
            flag = true;
        }
    }

    $(".getyzm").click(function () {
        var _this = $(this);
        var verifyPhoneNum = $(".sj-bindphone").val().trim();
        if (verifyPhoneNum == '') {
            msg('手机号码不能为空');
            return false;
        }
        var reg1 = RegExp("^((13[0-9])|(14[5,7,8,9])|(15[0-3,5-9])|(17[0,1,3,5-8])|(18[0-9])|166|198|199|(147))\\d{8}$");
        if (!reg1.test(verifyPhoneNum)) {
            msg("请输入正确的手机号");
            return false
        }
        if (flag) {
            flag = false;
            if (verifyPhoneNum) {
                $.ajax({
                    url: "/user/sendsms",
                    type: "POST",
                    dataType: "JSON",
                    data: {tel: verifyPhoneNum}
                })
                    .done(function (res) {
                        console.log(res);
                        if (res.status == 1) {
                            msg("验证码已发出");
                            countDown(_this, 60);
                        } else {
                            flag = true;
                            msg(res.info);
                        }
                    })
                    .fail(function (xhr) {
                        msg("未知错误，请刷新重试")
                    })
            }
            else {
                msg("参数错误，请刷新重试");
            }
        }

    })
    $(".sureBtn").click(function () {
        var phoneNumVal = $(".sj-bindphone").val().trim(),
            yanZhengMa = $(".sj-bindyzm").val().trim();
        if (phoneNumVal == '') {
            msg("请输入绑定的手机号");
            return false
        }
        var reg2 = new RegExp("^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199|(147))\\d{8}$");
        if (!reg2.test(phoneNumVal)) {
            msg("请输入正确手机号码");
            return false
        }
        if (yanZhengMa == '') {
            msg('请输入验证码');
            return false
        }
        if (phoneNumVal && yanZhengMa) {
            $.ajax({
                url: '/user/addtelsafe',
                type: 'POST',
                dataType: "JSON",
                data: {bind: 1, tel: phoneNumVal, code: yanZhengMa}
            })
                .done(function (res) {
                    if (res.status == 1) {
                        msg(res.info, function () {
                            $(".sj-bindphone").val("");
                            $(".sj-bindyzm").val("");
                            window.location.reload();
                        });
                    } else {
                        msg(res.info)
                    }
                })
                .fail(function (xhr) {
                    msg('发生未知错误，请稍后再试');
                    return false
                })
        } else {
            msg("参数错误,请刷新重试")
        }
    })
})

function msg(msg, fn) {
    layer.msg(
        msg,
        {tiem: 1000},
        fn || function () {
        }
    )
}
