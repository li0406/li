/*
* @Author: qz_dc
* @Date:   2018-09-06 16:48:43
* @Last Modified by:   qz_dc
* @Last Modified time: 2018-09-11 15:55:52
*/
$(function(){


    $('#time').change(function(event) {
        $('#time').css('color','#333');
    });
    $('.save-submit').click(function(event) {

        var tel = $("input[name=tel-number]").val();
        var cs = $('input[name=city]').attr('data-id');
        var qx = $('input[name=area]').attr('data-id');
        var time = $('#time option:selected').val();
        var _me = $(this) 
        if (!App.validate.run(tel)) {
            $("input[name=tel]").focus().val('');
            alert("请输入您的手机号码");
            return false;
        }
        var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
        if(!reg.test(tel)){
            $("input[name=tel]").focus().val('');
            alert("请填写正确的手机号码");
            return false;
        }

        if(cs == '' || qx == ''){
            alert('请选择您所在的区域 ≧▽≦');
            return false;
        }

        if(!checkDisclamer('.form')){
            return false;
        }
        orderButtonChangeStatus(_me, "btn")
        $.ajax({
            url: '/fb_order/',
            type: 'post',
            data: {
                lftime: time,
                cs: cs,
                qx: qx,
                tel: tel,
                source: '18082757',
            },
            success:function(res, status, xhr){
                orderButtonChangeStatus(_me, "btn","success")
                if(res.status == 1){
                    // cpa 转化代码
                    _taq.push({convert_id: "1611369185153037", event_type: "form"})

                    var h = $(window).height();
                    $("body").css({"overflow":"hidden","height":h+"px","position":'absolute'});
                    $('.mask').show();
                    $('.fixed').show();
                    $('.fix').hide();
                }else{
                    alert(res.info);
                }
            },
            fail:function(){
                orderButtonChangeStatus(_me, "btn","success")
            }
        })
    });

    // 点击预约量房
    $('.yuyue-btn').click(function(event) {
        $("input[name=tel-number]").focus();
    });
    // 点击悬浮框预约
    $('.liji').click(function(event) {
        var _me=$(this)
        setTimeout(function(){
            $('.mask').hide();
            $('.fixed').hide();
            $('.success').hide();
        },15000);

        var tel = $("input[name=tel-number]").val();
        var cs = $('input[name=city]').attr('data-id');
        var qx = $('input[name=area]').attr('data-id');
        var time = $('#time option:selected').val();
        var name = $("input[name=chenghu]").val() ? $("input[name=chenghu]").val() : '';
        var xiaoqu = $("input[name=xiaoqu]").val() ? $("input[name=xiaoqu]").val() : '';
        var mianji = $("input[name=mianji]").val() ? $("input[name=mianji]").val() : '';
        orderButtonChangeStatus(_me, "btn")
        $.ajax({
            url: '/fb_order/',
            type: 'post',
            data: {
                cs: cs,
                qx: qx,
                lftime:time,
                tel:tel,
                source: '18082757',
                name:name,
                xiaoqu: xiaoqu,
                mianji: mianji,
            },
            success:function(res, status, xhr){
                orderButtonChangeStatus(_me, "btn", "success")
                if(res.status == 1){
                    $('.fixed').hide();
                    $('.success').show();
                }else{
                    alert(res.info);
                }
            },
            fail:function(){
                orderButtonChangeStatus(_me, "btn","success")
            }

        })


    });
    // 点击知道了
    $('.know').click(function(event) {
        $('.mask').hide();
        $('.success').hide();
        location.reload();
    });


    var swiper = new Swiper('.swiper-container', {
        loop : true,
        effect: 'coverflow',
        grabCursor: true,
        centeredSlides: true,
        slidesPerView: 'auto',
        coverflow: {
            rotate: 0,// 旋转的角度
            stretch: 0,// 拉伸   图片间左右的间距和密集度
            depth: 150,// 深度   切换图片间上下的间距和密集度
            modifier: 4,// 修正值 该值越大前面的效果越明显
            slideShadows : true// 页面阴影效果
        }
    })
    //ios真机兼容样式问题
    var browser = {
        versions: function () {
        var u = navigator.userAgent, app = navigator.appVersion;
        return { //移动终端浏览器版本信息
            ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端
            android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或uc浏览器
            iPhone: u.indexOf('iPhone') > -1, //是否为iPhone或者QQHD浏览器
            iPad: u.indexOf('iPad') > -1, //是否iPad
            uc:u.indexOf('UCBrowser') > -1, //
            huawei:u.indexOf('AppleWebKit') > -1,
            };
            }(),
        };

        if (browser.versions.ios) {
            $('.relative').css('top','1.71rem');
            $('.numitem ').css('margin-right','0.2rem');
            $('.save-submit').click(function(event) {
                $('body,html').css({
                     "position":"relative",
                     "width":"100%",
                     "overflow":"auto",
                     "height":"auto"
                });
            })
        }
        if(browser.versions.android) {
            $("input[name=tel-number]").focus(function(event) {
                $('.fix').hide();
            });
            $("input[name=tel-number]").blur(function(event) {
                $('.fix').show();
            });
        }
        if(browser.versions.uc) {
            $("input[name=tel-number]").focus(function(event) {
                $('.fix').hide();
            });
            $("input[name=tel-number]").blur(function(event) {
                $('.fix').show();
            });
            // 兼容uc浏览器样式问题
            $("input[name=chenghu]").focus(function(event) {
                $('.yuyue-box').css('top','0.3rem');
                $('.fixed').css('position','absolute');
            });
            $("input[name=chenghu]").blur(function(event) {
                $('.yuyue-box').css('top','1.3rem');
                $('.fixed').css('position','fixed');

            });
            $("input[name=xiaoqu]").focus(function(event) {
                $('.yuyue-box').css('top','0.2rem');
                $('.fixed').css('position','absolute');
            });
            $("input[name=xiaoqu]").blur(function(event) {
                $('.yuyue-box').css('top','1.3rem');
                $('.fixed').css('position','fixed');
            });
            $("input[name=mianji]").focus(function(event) {
                $('.yuyue-box').css('top','0.1rem');
                $('.fixed').css('position','absolute');
            });
            $("input[name=mianji]").blur(function(event) {
                $('.yuyue-box').css('top','1.3rem');
                $('.fixed').css('position','fixed');
            });
        }
        if(browser.versions.android&&browser.versions.huawei) {
            $('.save-submit').click(function(event) {
                $('body,html').css({
                     "position":"relative",
                     "width":"100%",
                     "overflow":"auto",
                     "height":"auto"
                });
            })

            $("input[name=chenghu]").focus(function(event) {

                $('.fixed').css('position','absolute');
            });
            $("input[name=chenghu]").blur(function(event) {
                $('.yuyue-box').css('top','1.3rem');
                $('.fixed').css('position','fixed');

            });
            $("input[name=xiaoqu]").focus(function(event) {
                $('.fixed').css('position','absolute');
            });
            $("input[name=xiaoqu]").blur(function(event) {
                $('.yuyue-box').css('top','1.3rem');
                $('.fixed').css('position','fixed');
            });
            $("input[name=mianji]").focus(function(event) {
                $('.fixed').css('position','absolute');
            });
            $("input[name=mianji]").blur(function(event) {
                $('.yuyue-box').css('top','1.3rem');
                $('.fixed').css('position','fixed');
            });
        }

})