$(function () {
    $('.m-close').click(function(){
        $('.mask').hide();
        $('.first-order').hide();
    });
    var moneyArr = [];
    $.ajax({
        url: '/get_city_baojia/',
        type: 'POST',
        dataType: 'JSON',
        data: {
            cs: $('#m-city').val()
        }
    })
        .done(function (data) {
            if (data.error_code == 0) {
                setInterval(function () {
                    var num = getRandom();
                    moneyArr = data.data
                    for (var i = 0; i < moneyArr.length; i++) {
                        if (num == i) {
                            $('.money1').html(moneyArr[i].wsj);
                            $('.money2').html(moneyArr[i].cf);
                            $('.money3').html(moneyArr[i].kt);
                            $('.money4').html(moneyArr[i].sd);
                            $('.money5').html(moneyArr[i].zw);
                            $('.money6').html(moneyArr[i].other);
                            $('.total-money').html(moneyArr[i].total);
                        }
                    }
                }, 200)
            }
        });

    $('.m-box .clickbtn').click(function(){
        $('.m-warn').hide();
        var city = $('#m-city').val();
        var area = $('#m-area').val();
        var mianji = $('.m-mianji').val();
        var tel = $('.m-tel').val();
        var source = $(this).attr('data-source');
        if (city == '') {
            $('#m-city').siblings('.m-warn').show().find('span').html("请选择城市");
            return false;
        }
        if (area == '') {
            $('#m-city').siblings('.m-warn').show().find('span').html("请选择区域");
            return false;
        }
        if (mianji == '') {
            $('.m-mianji').siblings('.m-warn').show().find('span').html("请填写房屋面积");
            return false;
        }
        if (isNaN(mianji)) {
            $('.m-mianji').siblings('.m-warn').show().find('span').html("面积只能是纯数字");
            return false;
        }
        if (mianji < 1 || mianji > 10000) {
            $('.m-mianji').siblings('.m-warn').show().find('span').html("房屋面积请输入1-10000之间的数字^_^!");
            return false;
        }
        //检查手机号
        if (tel == '') {
            $('.m-tel').siblings('.m-warn').show().find('span').html("请填写手机号码 ^_^!");
            return false;
        } else {
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if (!reg.test(tel)) {
                $('.m-tel').siblings('.m-warn').show().find('span').html("请填写正确的手机号码 ^_^!");
                return false;
            }
        }

        if (!checkDisclamer(".order-group1")) {
            return false;
        }
        var data = {
            mianji: mianji,
            cs: city,
            qx: area,
            tel: tel,
            source: source
        };
        window.order({
            extra: data,
            error: function () {
                alert('获取报价失败,请刷新页面');
            },
            success: function (data, status, xhr) {
                if (data.status == 1) {
                    $.ajax({
                        url: '/getdetailsbyajax/',
                        type: 'GET',
                        dataType: 'JSON'
                    }).done(function (data) {
                        if (data.status == 1) {
                            $('.m-box').hide();
                            $('.g-box').show();
                            $('.g-money').text(data.data.total);
                            $(".g-kt").text(data.data.kt);
                            $(".g-cf").text(data.data.cf);
                            $(".g-ws").text(data.data.zw);
                            $(".g-wsj").text(data.data.wsj);
                            $(".g-sd").text(data.data.sd);
                            $(".g-qt").text(data.data.other);
                        } else {
                            alert(data.info);
                        }
                    }).fail(function (xhr) {
                        alert('获取报价失败,请刷新页面');
                    });
                }else {
                    alert(data.info);
                }
            }
        })
    });
    $('#btn-step2').click(function(){
        var name = $('.mess1').val();
        var sex = $("input[name='msex']:checked").val();
        var start = $("input[name='mstart']:checked").val();
        var time = $("input[name='mtime']:checked").val();
        if(name == ''){
            $("#znbj-tck").show();
            $("#znbj-tck").html("请填写您的称呼");
            setTimeout(function(){
                $("#znbj-tck").hide();
            },1000)
            return false;
        }else{
            var reg_rename = /^[\u4e00-\u9fa5a-zA-Z]{1,10}$/;
            if (!reg_rename.test(name)) {
                $("#znbj-tck").show();
                $("#znbj-tck").html("请填写真实的姓名");
                setTimeout(function(){
                    $("#znbj-tck").hide();
                },1000)
                return false;
            }
        }
        if(sex == undefined || sex == ''){
            $("#znbj-tck").show();
            $("#znbj-tck").html("请填写您的称呼");
            setTimeout(function(){
                $("#znbj-tck").hide();
            },1000)
            return false;
        }
        if (start == undefined || start=="") {
            $("#znbj-tck").show();
            $("#znbj-tck").html("请选择您家的装修时间");
            setTimeout(function(){
                $("#znbj-tck").hide();
            },1000)
            return false;
        }
        if (time == undefined || time=="") {
            $("#znbj-tck").show();
            $("#znbj-tck").html("请选择您家的量房时间");
            setTimeout(function(){
                $("#znbj-tck").hide();
            },1000)
            return false;
        }
        var data1 = {
            name: name,
            sex: sex,
            start: start,
            lftime: time
        }
        $.ajax({
            url: '/getdetailsbyajax/',
            type: 'POST',
            data: data1,
            dataType: 'JSON'
        }).done(function (data) {
            if (data.status == 1) {
                $('.g-box').hide();
                $('.s-box').show();
                
            }
        })

    });
    function getRandom() {
        var num = Math.random() * 5;
        num = Math.floor(num)
        return num;
    }

    // tab切换
    $(".fd-lei div").click(function (){
        var index = $(this).index()
        $(".s-box").hide()
        $(".g-box").hide()
        $(".fd-lei div").removeClass("active")
        $(this).addClass("active")
        if(index === 0) {
            $(".baojia").show()
            $(".baojia .m-box").show()
            $(".sheji").hide()
        } else {
            $(".baojia").hide()
            $(".sheji").show()
            $(".sheji .m-box").show()
        }
    })

    // 免费设计--立即申请
    $(".sheji .sheji-btn").click(function (){
        $('.m-warn').hide();
        var city = $('#m-city1').val();
        var area = $('#m-area1').val();
        var name = $('.m-name').val();
        var tel = $('.m-tel1').val();
        var source = $(this).attr('data-source');
        if (city == '') {
            $('#m-city1').siblings('.m-warn').show().find('span').html("请选择城市");
            return false;
        }
        if (area == '') {
            $('#m-city1').siblings('.m-warn').show().find('span').html("请选择区域");
            return false;
        }
        // 检查称呼
        if(name == '') {
            $('.m-name').siblings('.m-warn').show().find('span').html("请输入您的称呼 ^_^!");
            return false;
        } else {
            var reg_name = /^[a-zA-Z\u4e00-\u9fa5]+$/
            if(!reg_name.test(name)) {
                $('.m-name').siblings('.m-warn').show().find('span').html("请输入正确的姓名，只支持中文和英文 ^_^!");
                return false;
            }
        }
        //检查手机号
        if (tel == '') {
            $('.m-tel1').siblings('.m-warn').show().find('span').html("请填写手机号码 ^_^!");
            return false;
        } else {
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if (!reg.test(tel)) {
                $('.m-tel1').siblings('.m-warn').show().find('span').html("请填写正确的手机号码 ^_^!");
                return false;
            }
        }

        if (!checkDisclamer(".order-group2")) {
            return false;
        }
        var data = {
            cs: city,
            qx: area,
            name: name,
            tel: tel,
            source: source
        };
        window.order({
            extra: data,
            error: function () {
                alert('获取报价失败,请刷新页面');
            },
            success: function (data, status, xhr) {
                if (data.status == 1) {
                    $(".sheji").hide()
                    $(".s-box").show()
                }else {
                    alert(data.info);
                }
            }
        })
    })

})