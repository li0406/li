$(function () {
    $(".datepicker").datepicker({
        autoclose: true,
        formate: 'yyyy-mm-dd'
    })

    // 点击受理
    // 1.弹框打开
    $(".oedit").click(function (event) {
        var _this = $(this);
        $.ajax({
            url: '/roundorderapply/apply_detail',
            type: 'GET',
            dataType: 'JSON',
            data: {round_id: _this.attr("data-id")}
        })
        .done(function (data) {
            if (data.error_code == 0) {
                $("body").append(data.data.template);
            }
        });
    });

    // 2.弹框关闭
    $("body").on("click", ".closeedit", function () {
        $(".p_domask").remove();
    });

    // 3.保存审核
    $("body").on("click", "#btnSave", function () {
        var round_ids = [];
        var order_id = $(this).data("orderid");
        $(".input_exam_ids:checked").each(function(){
            if ($(this).attr("disabled") == undefined) {
                round_ids.push($(this).val());
            }
        });

        if (round_ids.length == 0) {
            alert("请先选择审核公司");
            return false;
        }

        var status = $("input[name=status]:checked").val();
        var reason = $("[name=exam_reason]").val();
        var record = $("[name=record]").val();
        if(status == undefined) {
            alert("请选择审核状态");
            return false;
        }

        if (status == 3 && reason == "") {
            alert("请填写审核不通过原因");
            return false;
        }

        if (confirm(status == 3 ? "是否不通过该补轮申请?" : "是否通过该补轮申请?") == true) {
            $.ajax({
                url: "/roundorderapply/apply_exam",
                type: "POST",
                dataType: "JSON",
                data: {
                    order_id: order_id,
                    round_ids: round_ids,
                    exam_status: status,
                    exam_remark: reason,
                    track_info: record
                }
            }).done(function (data) {
                if (data.error_code == 0) {
                    alert("操作成功！");
                    window.location.href = window.location.href;
                } else {
                    alert(data.error_msg);
                }
            });
        }
    });

    // 重置
    $("#btnReset").click(function (event) {
        $("input[name=order],input[name=begin],input[name=end],input[name=company],input[name=check_begin],input[name=check_end]").attr("value", "");
        $("#city").select2("val", "");
        $("#people").select2("val", "");
        $("#status").val("");
    });

    $("#btnSearch").click(function (event) {
        var begin = $("input[name=begin]").val();
        var end = $("input[name=end]").val();
        if (begin != "" && end != "" && begin > end) {
            alert("截止日期不能小于起始日期");
            return false;
        }

        return true;
    });

    
})
