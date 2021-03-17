$(function () {
    var isVerify = false;
    var flag = true;
    var timer = null;

    function countDown(obj, num) {
        if (num > 0) {
            obj.text(num + "s");
            num--;
            timer = setTimeout(function (obj, num) {
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

    $('body').on("click", ".win_box_close", function () {
        $(".mask").hide();
        $('.idcheckBox').hide();
        $(".modifytelBox").hide();
        $(".modifypswBox").hide();
        window.location.reload();
    });
    //修改绑定手机
    $(".sj-modify").click(function () {
        $(".mask").show();
        verifyID(1);
    });

    $(".modifyTelBtn").click(function () {
        var _this = $(this);
        var newPhone = $(".modifynewTel").val().trim();
        var reg1 = RegExp("^((13[0-9])|(14[5,7,8,9])|(15[0-3,5-9])|(17[0,1,3,5-8])|(18[0-9])|166|198|199|(147))\\d{8}$");
        if (newPhone == '') {
            msg("手机号不能为空");
            return false;
        }
        if (!reg1.test(newPhone)) {
            msg("请输入正确手机号");
            return false;
        }
        if (flag) {
            flag = false;
            if (newPhone) {
                $.ajax({
                    url: "/user/sendsms",
                    method: "POST",
                    dataType: "JSON",
                    data: {tel: newPhone}
                })
                    .done(function (res) {
                        if (res.status == 1) {
                            msg(res.info, function () {
                                countDown(_this, 60);
                            });
                        } else {
                            msg(res.info);
                            flag = false;
                        }
                    })
                    .fail(function (xhr) {
                        msg("未知错误，请刷新重试")
                    })
            }
        }


    })

    $(".TelsureBtn").click(function () {
        var newPhone = $(".modifynewTel").val().trim();
        var yzCode = $(".modifyTelyzm").val().trim();
        if (newPhone == '') {
            msg("请输入绑定的手机");
            return false;
        }
        if (yzCode == "") {
            msg("请输入验证码");
            return false;
        }
        if (newPhone && yzCode) {
            $.ajax({
                url: '/user/addtelsafe',
                method: "POST",
                dataType: "JSON",
                data: {bind: 1, tel: newPhone, code: yzCode}
            })
                .done(function (res) {
                    if (res.status == 1) {
                        $(".modifynewTel").val("");
                        $(".modifyTelyzm").val("");
                        msg(res.info);
                        $(".mask").hide();
                        $(".modifytelBox").hide();
                        setTimeout(function () {
                            window.location.reload();
                        }, 1000)
                    }
                    else {
                        msg(res.info);
                    }
                });
        }
        else {
            msg("请确认手机号和验证码正确");
            return false;
        }
    });

    //账号密码
    $(".sj-modifypw").click(function () {
        $(".mask").show();
        verifyID(2);
    });
    $(".mofifyPswBtn").click(function () {
        var newPassword = $(".newPsw").val().trim();
        var dbConfirmPsw = $(".dbconfirm").val().trim();
        var reg2 = /^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z!@#\$%_\.\/+\-\*]{6,18}$/;
        if (newPassword == '') {
            msg("请输入新密码");
            return false;
        }
        if (newPassword.length > 18 || newPassword.length < 6) {
            msg("请输入6-18位新密码");
            return false;
        }
        if (!reg2.test(newPassword)) {
            msg("请勿输入纯数字/字母");
            return false
        }

        if (dbConfirmPsw == '') {
            msg("确认密码不能为空");
            return false;
        }

        if (newPassword != dbConfirmPsw) {
            msg("两次密码不一致");
            return false;
        }
        if (newPassword && dbConfirmPsw) {
            $.ajax({
                url: '/user/editpwd',
                method: "POST",
                dataType: "JSON",
                data: {pwd: newPassword, rpwd: dbConfirmPsw}
            })
                .done(function (res) {
                    if (res.status == 1) {
                        msg(res.info, function () {
                            $(".newPsw").val("");
                            $(".dbconfirm").val();
                            $(".modifypswBox").hide();
                            $(".mask").hide();
                            setTimeout(function () {
                                window.location.reload();
                            }, 1000);
                        });
                    } else {
                        msg(res.info);
                    }
                })
                .fail(function (xhr) {
                    msg('发生未知错误，请稍后重试~');
                    return false;
                })
        } else {
            msg("参数错误，请刷新重试");
        }

    })


    //身份验证
    function verifyID(num) {
        $(".idcheckBox").show();
        $(".input-checktypeyzm").val('');
        //验证码
        $(".btncode").click(function () {
            var _this = $(this);
            var checkPhone = $(".input-checktype").data("value");
            if (flag) {
                flag = false;
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
                            $(".sj-tipBlue p").show().delay(2000).hide(0);
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
                    url: 'http://f.qizuang.com/user/verifysmscode',
                    method: 'POST',
                    dataType: "JSON",
                    data: {tel: checkPhone, code: yzCode}
                })
                    .done(function (res) {
                        if (res.status == 1) {
                            msg("验证通过");
                            $(".btncode").html("获取验证码");
                            $(".idcheckBox").hide();
                            if (num == 1) {
                                //绑定手机
                                $(".modifytelBox").show();
                                clearTimeout(timer);
                                flag = true;
                                $(".modifyTelBtn").removeAttr("disabled");
                                $(".modifyTelyzm").val('')
                                $(".modifyTelyzm").blur('')
                                $('.modifynewTel').focus();
                            } else if (num == 2) {
                                //账号密码
                                $(".modifypswBox").show();
                                $('.dbconfirm').val('')
                                $('.dbconfirm').blur()
                            }
                        } else {
                            msg("验证码输入错误");
                        }

                    }).fail(function (xhr) {
                    msg("未知错误，请刷新重试")
                })
            }

        })


    }
})


function msg(msg, fn) {
    layer.msg(
        msg,
        {time: 1300},
        fn || function () {
        }
    );
}
