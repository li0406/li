$(function(){
    $(".windowbg .guanbisheji2").click(function(event) {
        $(".windowbg").remove();
    });

    $(".big-order-window button").click(function(event) {
        var container = $(".big-order-window");
        $(".err").html('');

        if ($("input[name=mianji]", container).val() == '') {
            $(".err").html("您还没有填写面积哦 ^_^!");
            $("input[name=mianji]").addClass('focus').focus();
            return false;
        }
        if (isNaN($("input[name=mianji]", container).val())) {
            $(".err").html("面积只能是纯数字 ^_^!");
            $("input[name=mianji]").addClass('focus').focus();
            return false;
        }
        if ($("input[name=mianji]", container).val()>10000) {
            $(".err").html("面积不能超过10000 ^_^!");
            $("input[name=mianji]").addClass('focus').focus();
            return false;
        }
        $("input[name=mianji]", container).removeClass('focus');

        if ($("select[name=yusuan]", container).val() == '') {
            $(".err").html("您还没有选择装修预算噢 ^_^!");
            $("select[name=yusuan]", container).addClass('focus').focus();
            return false;
        }
        $("select[name=yusuan]", container).removeClass('focus');

        if ($("select[name=start]", container).val() == '') {
            $(".err").html("您还没有选择开工时间噢 ^_^!");
            $("select[name=start]", container).addClass('focus').focus();
            return false;
        }
        $("select[name=start]", container).removeClass('focus');

        if ($("input[name=xiaoqu]", container).val() == '') {
            $(".err").html("您还没有填写楼盘名称哦 ^_^!");
            $("input[name=xiaoqu]", container).addClass('focus').focus();
            return false;
        }
        if(!isNaN($("input[name=xiaoqu]", container).val())){
            $(".err").html("楼盘名称不能为纯数字 ^_^!");
            $("input[name=xiaoqu]", container).addClass('focus').focus();
            return false;
        }
        $("input[name=xiaoqu]", container).removeClass('focus');
        var source = $('.apply-btn').attr('data-source');
        window.order({
            extra:{
                name:$("input[name=name]", container).val(),
                xiaoqu:$("input[name=xiaoqu]", container).val(),
                text:$("textarea[name=text]", container).val(),
                mianji:$("input[name=mianji]", container).val(),
                yusuan:$("select[name=yusuan]", container).val(),
                start:$("select[name=start]", container).val(),
                tel: $('#phone').val(),
                source: source
            },
            error:function(){
                container.remove();
            },
            success:function(data, status, xhr){
                if (data.status == 1) {
                    $.ajax({
                        url: '/poplayer/pop',
                        type: 'GET',
                        dataType: 'JSON',
                        data: {
                            type: 'sqsuccess'
                        }
                    })
                    .done(function (data) {
                        if (data.status == 0) {
                            $('.windowbg').remove();
                            container.remove();
                            $("body").append(data.tmp);
                        }
                    });
                }
            },
            validate:function(item, value, method, info){

                return true;
            }
        });
    });
    if( navigator.userAgent.indexOf("MSIE 8.0") > -1 ){
        $("input[name=mianji]").on("keyup",function () {
            $(this).val($(this).val().replace(/\s+/g,""));
        });
    }else{
        $("input[name=mianji]").on("input",function () {
            $(this).val( $(this).val().replace(/\s+/,"") );
        });
    }
});
