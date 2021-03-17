window.onload = function() {
    //页面初始化时“预约到店”按钮的层级高于“切换城市”
    $(".jxzq-box .jxzq-list .yuyue").css("z-index",11)
    // 一键领取
    $(".lingqu").click(function () {
        var checked = $("#new-mianze").is(':checked');
        window.order({
            url: 'https://api.qizuang.com/v1/platformactivity/jintie',
            header: {
                "X-QZ-SRC": fadan_src
            },
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
                    var dzpnums = Number($("#dzpnums").html()) + 1;
                    $("#dzpnums").html(dzpnums);
                    $(".lqsuccess-dialog .xcjms").text("恭喜您获得一次抽奖机会！");
                  $(".lqsuccess-dialog .xcjmore").removeClass("del")
                } else {
                    $(".lqsuccess-dialog").show()
                    $(".lqsuccess-dialog .xcjms").text("您今天已领完抽奖次数");
                    $(".lqsuccess-dialog .xcjmore").addClass("del")
                }
            },
            validate: function (item, value, method, info) {
                if (('cs' == item || 'qx' == item) && 'notempty' == method) {
                    alert('请选择城市')
                    return false;
                }
                if ('tel' == item && 'notempty' == method) {
                    alert('请输入手机号码')
                    $(".new-fadan input[name=tel-number]").focus();
                    return false;
                }
                if ('tel' == item && 'ismobile' == method) {
                    alert('请输入正确的手机号码')
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
    // 关闭
    $("body").on("click",".xcjmore",function (){
        $(".lqsuccess-dialog").hide()
        if(!($('.xcjmore').hasClass('del'))){
          $("html,body").animate({scrollTop: 600},1000)
        }
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
                duration:6000,
                callback:function (){
                    turnplate.bRotate = !turnplate.bRotate;
                    if(dzp_index != 2 && dzp_index != 6) {
                        $(".dzp-dialog").show()
                        $(".success-top").html("恭喜中奖!")
                        $(".dzp-before").hide()
                        $(".dzp-unsuccess").hide()
                        $(".dzp-success").show()
                        $(".dzp-success-img img").attr("src",'/assets/mobile/zt/images/gift'+ dzp_index +'.png')
                        $(".dzp-dialog .dzp-box").css("height", " 1.152rem;")
                    } else {
                        $(".dzp-btn").css("pointer-events","auto")
                        $(".no-dialog").show()
                    }
                }
            });
        };
    
        // 点击抽奖
        $(".dzp-btn").click(function () {
            var dzpnums = Number($("#dzpnums").html());
            if(dzpnums > 0){
                $(".dzp-dialog").hide()
                $(".xydzp .dzp").css("pointer-events","none")
                var index = 2 + 1
                dzp_index = 2
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
              
                $("#dzpnums").html(dzpnums-1)
            
            }else{
                $(".time-lave").hide()
                $(".dzp-dialog").show()
            }
        })
        
        // 获取更多抽奖次数
        $("body").on("click",".cjmore",function (){
            $(".dzp-dialog").hide()
            $("html,body").animate({scrollTop: 100},1000)
        })
    
        $("body").on("click",".nocjmore",function (){
            $(".no-dialog").hide()
        })
    });
}
