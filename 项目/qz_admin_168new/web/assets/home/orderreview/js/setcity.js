$(function(){
    function fun_date(aa){
        var date1 = new Date(),
            time1=date1.getFullYear()+"-"+(date1.getMonth()+1)+"-"+date1.getDate();//time1表示当前时间
        var date2 = new Date(date1);
        date2.setDate(date1.getDate()+aa);
        var time2 = date2.getFullYear()+"-"+(date2.getMonth()+1)+"-"+date2.getDate();
        return time2;
    }

    $(".datepicker").datepicker({
        format: "yyyy-mm-dd",
        minViewMode: 0,
        autoclose: true,
        startDate: set_date_begin,
        endDate: set_date_end
    });

    // 筛选城市选择
    $("#select_city").select2({
        allowClear: true,
        placeholder: "选择城市",
        language: "zh-CN"
    });

    $("#select_city").select2("val", default_city_id);
    $('#select_city').trigger('change');

    // 设置弹窗城市选择
    $("#add_citys").select2({
        placeholder: "选择城市",
        language: "zh-CN"
    });

    // 城市选择事件
    $("#add_citys").on("change", function(){
        var city_id = $(this).val();
        if (!!city_id) {
            $.ajax({
                url: "/orderreview/setcity_info",
                type: "GET",
                dataType: "JSON",
                data: {
                   city_id: city_id
                }
            })
            .done(function (response) {
                var detail = response["data"]["detail"];
                $("#add_visit_begin").val(detail["visit_begin"]);
                $("input[name=mianji_min]").val(detail["mianji_min"]);
                $("input[name=mianji_max]").val(detail["mianji_max"]);
            });
        }
    });

    // 显示日志
    $(".showlog").click(function (event) {
        var city_id = $(this).parent("td").data("id");

        $.ajax({
            url: "/orderreview/setcity_log",
            type: "GET",
            dataType: "JSON",
            data: {
               city_id: city_id
            }
        })
        .done(function (response) {
            $("#logModal .modal-body").html(response["data"]["template"]);
            $("#logModal").modal();
        });
    });

    // 设置回访城市
    $("#btnAdd").click(function(){
        $("#addModal").modal();
    });

    // 设置回访城市
    $(".addbtn").click(function(){
        var city_id = $(this).parent("td").data("id");
        $("#add_citys").select2("val", city_id);
        $("#addModal").modal();
    });

    // 取消弹窗
    $("#btn_add_cancel").click(function(){
        $("#btn_add_submit").removeAttr("disabled");
        $("#add_citys").select2("val", "");
        $("#add_visit_begin").val("");
        $(".add_mianji").val("");

        $("#addModal").modal("hide");
    });

    // 取消弹窗
    $("#addModal").on("hidden.bs.modal", function(){
        $("#btn_add_submit").removeAttr("disabled");
        $("#add_citys").select2("val", "");
        $("#add_visit_begin").val("");
        $(".add_mianji").val("");

        $("#addModal").modal("hide");
    });

    // 设置回访城市提交
    $("#btn_add_submit").click(function(){
        var city_id = $("#add_citys").select2("val");
        var visit_begin = $("#add_visit_begin").val();
        var mianji_min = $("input[name=mianji_min]").val();
        var mianji_max = $("input[name=mianji_max]").val();

        if (city_id == "") {
            alert("请选择城市");
            return false;
        } else if (visit_begin == "") {
            alert("请选择回访时间");
            return false;
        } else if (isNaN(mianji_min) || isNaN(mianji_max)) {
            alert("订单面积不合法");
            return false;
        } else if (Number(mianji_min) > Number(mianji_max) && Number(mianji_max) > 0) {
            alert("订单面积最大值不能小于最小值");
            return false;
        }

        $("#btn_add_submit").attr("disabled", true);

        $.ajax({
            url: "/orderreview/setcity_submit",
            type: "POST",
            dataType: "JSON",
            data: {
               city_id: city_id,
               visit_begin: visit_begin,
               mianji_min: mianji_min,
               mianji_max: mianji_max
            }
        })
        .done(function (response) {
            if (response.error_code == 0) {
                window.location.reload();
            } else {
                $("#btn_add_submit").removeAttr("disabled");
                alert(response.error_msg);
            }
        });
    });

    // 取消回访城市
    $(".cancelbtn").click(function(){
        var city_id = $(this).parent("td").data("id");
        if (confirm("确认要取消该回访城市吗？取消后该城市分配的订单将不再进入新回访池！") == true) {
            $.ajax({
                url: "/orderreview/setcity_cancel",
                type: "POST",
                dataType: "JSON",
                data: {
                   city_id: city_id
                }
            })
            .done(function (response) {
                if (response.error_code == 0) {
                    window.location.reload();
                } else {
                    alert(response.error_msg);
                }
            });
        }
    });


    // 导出回访城市数据
    $("#btnExport").click(function(){
        var dataForm = getFormData("#searchForm");
        var url = urlencode(dataForm);

        window.location.href = "/orderreview/setcityExport?" + url;
    });
});