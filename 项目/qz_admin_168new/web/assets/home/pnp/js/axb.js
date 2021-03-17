$(function () {

    $(".datepicker").datepicker({
        autoclose:true,
        formate:"yyyy-mm-dd"
    })

    $("#btnReset").click(function(event) {
        $("input[name=order_id],input[name=company_id],input[name=begin],input[name=end],input[name=type_fw]").attr("value","");
        $("#city").select2("val","");
    });

    $(".oedit").click(function(event) {
        var _this = $(this);
        var id = _this.attr("data-id")
        $("#unbindButton").attr("data-id", id);
    });

    $("#unbindButton").click(function(event) {
        var _this = $(this);
        $.ajax({
            url: "/pnp/axborderunbind",
            type: "POST",
            dataType: "JSON",
            data: {order_id: _this.attr("data-id")}
        })
        .done(function(data) {
            $("#unbindModal").modal("hide");
            if (data.error_code == 0) {
                alert("解绑成功");
            } else {
                alert(data.error_msg);
            }
        });
    });

    $(".orecode").click(function() {
        $(".music_box").removeClass("showhide")
    })
    $(".stopbi").on("click",function() {
        $(".music_box").addClass("showhide")
    })
    
    // 录音记录
    $(".btn_volice").click(function(){
        var tdElement = $(this).parents("td");

        $.ajax({
            url: "/pnp/axbvolice",
            type: "GET",
            dataType: "JSON",
            data: {
                "order_id": tdElement.data("oid"),
                "company_id": tdElement.data("comid"),
                "sub_id": tdElement.data("subid")
            }
        })
        .done(function(response) {
            if (response.error_code == 0) {
                $("#voliceModal").find(".modal-body").html(response["data"]["template"]);
                $("#voliceModal").modal("show");
            } else {
                alert(response.error_msg);
            }
        });
    });
});
