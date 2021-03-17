$(document).ready(function(){
    (city_id == '') ? cityId = ip_cityid : cityId = city_id;
    if(cityId == ''){
        getLocation();
    }else{
        initCity(cityId);
    }

    function initCity(cityId){
        App.citys.init("#sj-cs","#sj-qy",shen,shi,cityId);
    }

    App.citys.init(".freesj_cs",".freesj_qx",shen,shi,cityId);

    $('.bj_btn').mouseover(function(){
        $('.s-p').css("display","block");
    }).mouseout(function(){
        $('.s-p').css("display","none");
    });

    $('.commom-item').on('mouseenter','.item-bd',function(){
        $('.btn-fd').html('<a href="javascript:;" class="a-like btn-sheji" rel="nofollow">我要装修成这样</a><a href="javascript:;" class="a-how btn-baojia" rel="nofollow">装修成这样花多少钱</a>')
    });
    $('.commom-item').on('mouseleave','.item-bd',function(){
        $('.btn-fd').html('')
    });

    $('.accordion').jaccordion();

    $("#getFreeDesign").click(function(event) {

        //检查姓名
        if ($(".freeDesign input[name=meitu-name]").val() == "") {
            alert("请填写您的名称 ^_^!");
            $(".freeDesign input[name=meitu-name]").addClass('fal').focus();
            return false;
        } else {
            var reg1 = new RegExp("^[\u4e00-\u9fa5a-zA-Z]+$");
            if (!$(".freeDesign input[name=meitu-name]").val().match(reg1)) {
                alert("请输入正确的名称，只支持中文和英文");
                $(".freeDesign input[name=meitu-name]").addClass('fal').focus();
                $(".freeDesign input[name=meitu-name]").val('');
                return false;
            }
        }
        $(".freeDesign input[name=meitu-name]").removeClass('fal');

        //检查手机号
        var tel = $(".freeDesign input[name=meitu-tel]").val();
        if (tel == "" || tel.length == 0) {
            alert("请填写正确的手机号码 ^_^!");
            $(".freeDesign input[name=meitu-tel]").addClass('fal').focus();
            return false;
        } else {
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if (!$(".freeDesign input[name=meitu-tel]").val().match(reg)) {
                alert("请填写正确的手机号码 ^_^!");
                $(".freeDesign input[name=meitu-tel]").addClass('fal').focus();
                $(".freeDesign input[name=meitu-tel]").val('');
                return false;
            }
        }
        if(!checkDisclamer(".baojia-zb")){
            return false;
        }
        $(".freeDesign input[name=meitu-tel]").removeClass('fal');

        if ($("#sj-cs").val() == '') {
            alert("您还没有选择所在城市噢 ^_^!");
            $("#sj-cs").addClass('fal').focus();
            return false;
        }
        $("#sj-cs").removeClass('fal');

        if ($("#sj-qy").val() == '') {
            alert("您还没有选择所在区域噢 ^_^!");
            $("#sj-qy").addClass('fal').focus();
            return false;
        }
        $("#sj-qy").removeClass('fal');

        var data ={
            name:$(".freeDesign input[name=meitu-name]").val(),
            tel:tel,
            fb_type: $(".freeDesign input[name=fb_type]").val(),
            cs:$("#sj-cs").val(),
            qx:$("#sj-qy").val(),
            source: 172,
            step:2
        };

        window.order({
            extra:data,
            error:function(){
                alert("网络发生错误,请稍后重试！");
            },
            success:function(data, status, xhr){
                if (data.status == 1) {
                    $("body").append(data.data.tmp);
                    $(".freeDesign input[name=meitu-name]").val('');
                    $(".freeDesign input[name=meitu-tel]").val('')
                }else{
                    alert(data.info);
                }
            },
            validate:function(item, value, method, info){
                return true;
            }
        });
        return false;
    });

    $(window).scroll(function(){
        var scrollTop = document.documentElement.scrollTop || document.body.scrollTop ;
        var height = $(".nav1,.nav").offset().top + $(".nav1").outerHeight()+10;
        if(scrollTop > height){
            $(".zb_footer_box1").show();
            $(".zb_footer_box_little").show();
        }else{
            $(".zb_footer_box1").hide();
            $(".zb_footer_box_little").hide();
        }
    });


    $(".zb_footer_box1 .zb_close").click(function(event) {
        animate($(".zb_footer_box1"),$(".zb_footer_box_little"));
    });

    function animate(from,to){
        var length = from.outerWidth();
        from.animate({"left":"-="+length},800,function(){
            to.animate({"left":"0"},800);
        });
    }
    $(".zb_footer_box_little").click(function(event) {
        animate($(".zb_footer_box_little"),$(".zb_footer_box1"));
    });
});