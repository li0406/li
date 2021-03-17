$(function () {
    //立即预约
    $(".ljyy-btn").click(function () {
        var cs = $(".order-cs").val();
        var qy = $(".order-qy").val();
        var mianji = $(".order-mianji").val();
        var tele = $(".order-tel").val();
        var reg_mianji = /^(-?\d+)(\.\d+)?$/;
        var reg_tel = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
        if(cs == ''){
            $(".errorArea-tit").html('请选择城市');
            return false
        }else{
            $(".errorArea-tit").html('');
        }
        if(qy == ''){
            $(".errorArea-tit").html('请选择 quyu');
            return false
        }else{
            $(".errorArea-tit").html('');
        }
        if(mianji == ''){
            $(".errorMianji-tit").html('亲,您还没有填写房屋面积!');
            return false
        }else if(!reg_mianji.test(mianji)){
            $(".errorMianji-tit").html('请您填写真实的房屋面积!');
            return false
        }else if(mianji < 1 || mianji > 10000){
            $(".errorMianji-tit").html('房屋面积请输入1-10000之间的数字 ^_^!!');
            return false
        }else{
            $(".errorMianji-tit").html('');
        }
        if(tele == ''){
            $(".errorTele-tit").html('请输入您的手机号码');
            return false
        }else if(!reg_tel.test(tele)){
            $(".errorTele-tit").html('请输入正确的11位手机号');
            return false
        }else{
            $(".errorTele-tit").html('');
        }
        if(!checkDisclamer(".order-right")){
            return false;
        }
        window.order({
            extra:{
                tel:tele,
                cs:cs,
                qx:qy,
                mianji:mianji,
                source:19121139
            },
            success:function (data, status, xhr) {
                console.log(data)
                if(data.status == 1){
                    $.ajax({
                        url: '/poplayer/pop',
                        type: 'GET',
                        dataType: 'JSON',
                        data: {
                            type: 'yysuccess'
                        }
                    })
                        .done(function (data) {
                            if (data.status == 0) {
                                $("body").append(data.tmp);
                            }
                        });
                }
            },
            error:function (data) {
                alert(data.info)
            }
        })
    })
})
