function toTimeStamp(time) {
    if (time) {
        var date = time;
        date = date.substring(0, 19);
        date = date.replace(/-/g, '/');
        return new Date(date).getTime();
    }
}

function msg(msg, fn) {
    layer.msg(msg, {time: 800}, fn || function () {})
}

$(function () {
    $('.datepicker').datetimepicker({
        weekStart: 1,
        todayHighlight: 1,
        todayBtn: true,
        minView: "month",
        autoclose: true,
        clearBtn: true,
        format:'yyyy-mm-dd',
        language: 'zh-CN'
    }).on("change", function () {
        var start = $("input[name=start]").val();
        var end = $("input[name=end]");
        if (toTimeStamp(start) > toTimeStamp(end.val())) {
            msg("结束时间不得早于开始时间", function () {
                end.val('');
            });
        }
    });

    //tab切换
    $('.ol-tab').on('click', function () {
        $('.ol-tab').removeClass('ol-tab-active');
        $(this).addClass('ol-tab-active');
        var status = $(this).attr('data-tab');
        if (status == '0') {
            window.location.href = '/orderreviewNew/index';
        } else {
            window.location.href = '/orderreviewNew/index?review_type=' + status;
        }
    });
    //重置按钮 
    $('#reset').on('click', function() {
        $("#city").select2('val','')
        $("#kf").select2('val','')
        $("[name=review_type]").val('999')
        $("[name=search]").val('')
        $("[name=company]").val('')
        $("[name=remark_type]").val('')
        $("[name=is_measure_room]").val('')
        $("[name=applytel]").val('')
        $("input[name=start]").val('')
        $("input[name=end]").val('')
        $("select[name=order_type]").val('')
    });

    // 编辑
    $(".operater-edit").on('click', function () {
        $('body').addClass('modal-open');
        var itemId = $(this).data("id");
        $.ajax({
            url: '/orderreviewNew/operate',
            type: 'GET',
            data: {
                id: itemId
            },
            success: function (res) {
                if (parseInt(res.status) === 1) {
                    var header =
                        "  修改订单  " + res.info.id +
                        " (上次修改  " + res.info.lasttime +
                        "  )   |   订单归属人:" + res.info.op_name +
                        "<input type='hidden' class='order_id' value=" +
                        res.info.id + ">";
                    $("#operate .modal-header span").html(header);
                    $("#operate .modal-body").html(res.data);
                    $(".my-dialog").show();
                }
            },
            error: function () {
                msg('发生未知错误，请联系技术部门~');
            }
        })
    });

    $('#city,#kf').select2();

    $("#operate .modal-header em").click(function (event) {
        if (confirm("确定关闭？")) {
            $('body').removeClass('modal-open')
            $(".my-dialog").modal("hide");
            // $(".mask-self").show();
            $(".my-dialog").hide()
            // window.location.href = '/orderreview/index';
        }
    });
    //呼叫记录
    $(".operater-record").on('click', function () {
        var orderId = $(this).data("id");
        $.ajax({
            url: '/orderreviewNew/voiprecord/',
            type: 'GET',
            data: {
                id: orderId
            },
            success: function (res) {
                if (parseInt(res.status) === 1) {
                    $('.common-model').fadeIn();
                    $(".common-model-content").html(res.data)
                } else {
                    msg('发生未知错误，请联系技术部门~');
                }
            },
            error: function () {
                msg('发生未知错误，请联系技术部门~');
            }
        })
    });
    $(".common-model .close").click(function () {
        $(".common-model").fadeOut()
    });


    // 申请显号
    $('.apply_tel').click(function () {
        var orderid = $(this).data('id');
        if (!orderid) {
            return false;
        }
        $.ajax({
            url: '/OrderReviewNewApplyTel/getApplyTelList',
            type: 'GET',
            dataType: 'JSON',
            data: {id: orderid},
            success: function (data) {
                if (parseInt(data.error_code) === 1) {
                    layerindex = layer.open({
                        type: 1,
                        title: '记录列表',
                        area: '1080px', //宽高
                        content: data.data + "<div class=\"modal-footer\"><button type=\"button\" class=\"btn btn-default eye-close-button\" >关闭</button></div>"
                    });
                    //审核操作
                    $(".btn-apply-tel").on("click", function () {
                        var _this = $(this);
                        var btns = $(".btn-apply-tel");
                        var data = {};
                        data.status = _this.data('status');
                        data.id = _this.data('id');
                        btns.attr('disabled', true);
                        //这里获取数据，增加验证
                        $.ajax({
                            url: '/OrderReviewNewApplyTel/pass_apply_tel',
                            type: 'POST',
                            dataType: 'JSON',
                            data: data
                        }).done(function (data) {
                            msg(data.error_msg || data.info, function () {
                                layer.close(layerindex);
                                btns.removeAttr('disabled');
                            });
                        }).error(function () {
                            msg('服务器请求出错，刷新重试',function () {
                                btns.removeAttr('disabled');
                            });
                        });
                    });
                    //取消
                    $(".eye-close-button").on("click", function () {
                        layer.close(layerindex);
                    });
                } else {
                    msg(data.error_msg);
                    return false;
                }
            },
            error: function () {
                msg('发生未知错误，请联系技术部门~');
            }
        })
    });

    $("body").on("click","#distance_check",function(){
        var _this = $(this);
        if($("input[name=zuobiao]").val() == "") {
            return false;
        }
        $.ajax({
            url: '/orders/getorderdistance',
            type: 'POST',
            dataType: 'JSON',
            data: {
                zuobiao: $("input[name=zuobiao]").val(),
                cs:$("#edit-cs").val()
            },
        })
          .done(function(data) {
            if (data.code == 0) {
                $("#cityTmp").find(".distance").each(function(i){
                    var id = $(this).attr("data-id");
                    if (data.data.hasOwnProperty(id)) {
                        $(this).text(data.data[id]).show();
                    }
                });
                _this.attr("data-status",1);
            } else {
                alert(data.info);
            }
        });
    });
});
