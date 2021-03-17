window.onload = function() {
    //页面初始化时“预约到店”按钮的层级高于“切换城市”
    $(".jxzq-box .jxzq-list .yuyue").css("z-index",11)
    // 一键领取
    $(".lingqu").click(function () {
        var checked = $("#new-mianze").is(':checked');
        window.order({
            url: 'https://api.qizuang.com/v1/platformactivity/jintie',
            extra: {
                cs: $('input[name=city]').attr('data-id'),
                qx: $('input[name=area]').attr('data-id'),
                tel: $(".new-fadan input[name=tel-number]").val(),
                source: '20080318'
            },
            submitBtn: {
                className: $(this),
                type: 'btn'
            },
            error: function () {
                alert("发生了未知的错误,请稍后再试！");
            },
            success: function (data, status, xhr) {
                if (data.error_code == 0) {
                    $(".lqsuccess-dialog").show()
                    $("input[name=tel-number]").val("")
                } else {
                    alert(data.error_msg)
                }
            },
            validate: function (item, value, method, info) {
                if (('cs' == item || 'qx' == item) && 'notempty' == method) {
                    alert('请选择城市')
                    return false;
                }
                if ('tel' == item && 'notempty' == method) {
                    alert('您还没有填写手机号噢！')
                    $(".new-fadan input[name=tel-number]").focus();
                    return false;
                }
                if ('tel' == item && 'ismobile' == method) {
                    alert('请填写正确的手机号码！')
                    $(".new-fadan input[name=tel-number]").focus();
                    return false;
                }
                if (!checked) {
                    alert('请勾选我已阅读并同意齐装网的《免责声明》！')
                    return false;
                }
                return true;
            }
        });
    })

    // 预约到店-一键预约
    $("body").on("click",".yuyue-again-btn",function () {
        var checked = $("#new-mianze2").is(':checked');
        window.order({
            extra: {
                cs: $('input[name=city]').attr('data-id'),
                qx: $('input[name=area]').attr('data-id'),
                tel: $(".new-fadan input[name=yuyue-tel]").val(),
                source: '20080318'
            },
            submitBtn: {
                className: $(this),
                type: 'btn'
            },
            error: function () {
                alert("发生了未知的错误,请稍后再试！");
            },
            success: function (data, status, xhr) {
                if (data.status == 1) {
                    $(".new-fadan input[name=yuyue-tel]").val('')
                    $(".success-top").html("预约成功!")
                    $(".yuyue-content").hide()
                    $(".yuyue-success-content").show()
                } else {
                    alert(data.info)
                }
            },
            validate: function (item, value, method, info) {
                if (('cs' == item || 'qx' == item) && 'notempty' == method) {
                    alert('请选择城市')
                    return false;
                }
                if ('tel' == item && 'notempty' == method) {
                    alert('您还没有填写手机号噢！')
                    $(".new-fadan input[name=yuyue-tel]").focus();
                    return false;
                }
                if ('tel' == item && 'ismobile' == method) {
                    alert('请填写正确的手机号码！')
                    $(".new-fadan input[name=yuyue-tel]").focus();
                    return false;
                }
                if (!checked) {
                    alert('请勾选我已阅读并同意齐装网的《免责声明》！')
                    return false;
                }
                return true;
            }
        });
    })

    // 城市切换
    $(".activity-city p").click(function (){
        $(".activity-city ul").toggleClass("show")
        if(!$(".activity-city ul").hasClass("show")){
            $(".jxzq-box .jxzq-list .yuyue").css("z-index",1)
        } else {
            $(".jxzq-box .jxzq-list .yuyue").css("z-index",11)
        }
    })


    $(".activity-city ul li").click(function (){
        var html = $(this).html()
        var city_id = $(this).attr("data-id")
        $(".activity-city p").html(html+'<img src="/assets/mobile/zt/images/more-city.png" />')
        $(".activity-city p").addClass("center")
        $(".activity-city ul").addClass("show")
        $(".jxzq-box .jxzq-list .yuyue").css("z-index",11)

        companyList(city_id, 2)
    })
    companyList(510100, 1)
    function companyList(city_id, type) {
        $.ajax({
            type: "GET",
            url: "https://api.qizuang.com//v1/platformactivity/select_activity",
            dataType: "json",
            data: {
                cs: city_id,
                type: type
            },
            success: function (data) {
                if (data.error_code == 0) {
                    var company_html = ''
                    $.each(data.data,function (index,item){
                        company_html += '<div class="jxzq-list">\n' +
                            '                            <a href="/'+ item.bm +'/company_home/'+ item.id +'">\n' +
                            '                                <div>\n' +
                            '                                    <img src="'+ item.img_url +'?imageView2/1/w/200/h/160"/>\n' +
                            '                                </div>\n' +
                            '                                <p class="company-name">'+ item.company_jc +'</p>\n' +
                            '                                <p class="jzxq-details">\n' +
                            '                                    <span class="cu">促</span>\n' +
                            '                                    <span>'+ item.activity_content +'</span>\n' +
                            '                                </p>\n' +
                            '                                <p class="jzxq-details">\n' +
                            '                                    <span class="li">礼</span>\n' +
                            '                                    <span>满4万减1000，满10万减4999装修津贴</span>\n' +
                            '                                </p>\n' +
                            '                            </a>\n' +
                            '                            <button type="button" class="yuyue" style="z-index: 11">预约到店</button>\n' +
                            '                        </div>'
                    })
                    $(".jxzq-height").html(company_html)
                } else {
                    alert(data.error_msg)
                }
            },
            error: function (xhr) {
                alert(data.error_msg)
            }
        });
    }

    // 使用规则
    $(".rule").click(function () {
        $(".rule-dialog").show()
    })

    // 预约到店
    $("body").on("click",".yuyue",function() {
        $(".yuyue-dialog").show()
        $(".success-top").html("预约到店")
        $(".yuyue-content").show()
        $(".yuyue-success-content").hide()
    })

    // 领取福利切换免责
    $("#new-check").click(function () {
        $(this).toggleClass('fa-check');
    })

    // 预约到店切换免责
    $("#new-check2").click(function () {
        $(this).toggleClass('fa-check');
    })

    // 大转盘切换免责
    $("#new-check3").click(function () {
        $(this).toggleClass('fa-check');
    })

    var turnplate={
        restaraunts:[],				//大转盘奖品名称
        //outsideRadius:190,	    //大转盘外圆的半径
        //textRadius:155,			//大转盘奖品位置距离圆心的距离
        //insideRadius:50,			//大转盘内圆的半径
        startAngle:22.5,			//开始角度
        bRotate:false				//false:停止;ture:旋转
    };

    $(document).ready(function(){
        //动态添加大转盘的奖品与奖品区域背景颜色
        turnplate.restaraunts = ["优酷视频会员", "指纹锁", "谢谢参与", "云南豪华旅游", "追觅科技无线吸尘器", "瑞尔特智能马桶", "谢谢参与 ", "TCL三开门冰箱"];

        var rotateTimeOut = function (){
            $('#dzp-box').rotate({
                angle:0,
                animateTo:2160,
                duration:8000,
                callback:function (){
                    alert('网络超时，请检查您的网络设置！');
                }
            });
        };
        var dzp_index = ''
        //旋转转盘 item:奖品位置; txt：提示语;
        var rotateFn = function (item, txt){
            var angles = item
            $('#dzp-box').stopRotate();
            $('#dzp-box').rotate({
                angle:22.5,
                animateTo:angles+1800,
                duration:8000,
                callback:function (){
                    turnplate.bRotate = !turnplate.bRotate;
                    if(dzp_index != 2 && dzp_index != 6) {
                        $(".dzp-dialog").show()
                        $(".success-top").html("恭喜中奖!")
                        $(".dzp-before").hide()
                        $(".dzp-unsuccess").hide()
                        $(".dzp-success").show()
                        $(".dzp-success-img img").attr("src",'/assets/mobile/zt/images/gift'+ dzp_index +'.png')
                        $(".dzp-dialog .dzp-box").css("height", "2.56rem")
                        if(dzp_index === 0) {
                            $(".dzp-tips").html("量房成功即可领取奖励～")
                        } else {
                            $(".dzp-tips").html("完成装修签约即可兑换奖品~")
                        }

                    } else {
                        $(".dzp-dialog .dzp-box").css("height", "2.56rem")
                        $(".dzp-dialog").show()
                        $(".success-top").html("谢谢参与")
                        $(".dzp-before").hide()
                        $(".dzp-success").hide()
                        $(".dzp-unsuccess").show()
                    }


                }
            });
        };

        // 立即抽奖
        $("body").on("click",".choujiang",function (){
            var tele_value = $("input[name=dzp-tel]").val()
            var code_value = $("input[name=dzp-yzm]").val()
            var checked_value = $("#new-mianze3").is(':checked');
            var tel_reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$")
            var code_reg = /^\d{6}$/
            if (!tele_value) {
                alert("请输入您的手机号~")
                return false
            } else if(!tel_reg.test(tele_value)) {
                alert("请输入正确的手机号~")
                return false
            }
            if(!code_value){
                alert("请输入您的验证码~")
                return false
            } else if(!code_reg.test(code_value)) {
                alert("请输入正确的验证码")
                return false
            }
            if (!checked_value) {
                alert('请勾选我已阅读并同意齐装网的《免责声明》！')
                return false;
            }

            $.ajax({
                type : "POST",
                url : "https://api.qizuang.com/v1/platformactivity/draw",
                dataType : "JSON",
                data:{
                    activity_id: 35,
                    tel: tele_value,
                    code: code_value
                },
                success : function(data){
                    if(data.error_code == 0){
                        $(".dzp-dialog").hide()
                        var index = data.data.offset + 1
                        dzp_index = data.data.offset
                        if(turnplate.bRotate)return;
                        turnplate.bRotate = !turnplate.bRotate;
                        //奖品数量等于10,指针落在对应奖品区域的中心角度[22.5, 67.5, 112.5, 157.5, 202.5, 247.5, 292.5, 337.5]
                        switch (index) {
                            case 8:
                                rotateFn(22.5, turnplate.restaraunts[7]);
                                break;
                            case 7:
                                rotateFn(67.5, turnplate.restaraunts[6]);
                                break;
                            case 6:
                                rotateFn(112.5, turnplate.restaraunts[5]);
                                break;
                            case 5:
                                rotateFn(157.5, turnplate.restaraunts[4]);
                                break;
                            case 4:
                                rotateFn(202.5, turnplate.restaraunts[3]);
                                break;
                            case 3:
                                rotateFn(247.5, turnplate.restaraunts[2]);
                                break;
                            case 2:
                                rotateFn(292.5, turnplate.restaraunts[1]);
                                break;
                            case 1:
                                rotateFn(337.5, turnplate.restaraunts[0]);
                                break;
                        }
                    }else{
                        alert(data.error_msg)
                    }
                },
                error:function(xhr){
                    alert("发生了未知的错误,请稍后再试！");
                }
            });
        })
    });
}
