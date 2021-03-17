$(function () {
    $(".datepicker").datepicker({
        autoclose:true,
        formate:'yyyy-mm-dd'
    })


    $("body").on("click",".closeedit",function(){
        $(".p_domask").remove();
    });


    $(".oedit").click(function(event) {
        var _this = $(this);
        var company_name=_this.parent().parent().children().eq(-3).text()
        $.ajax({
            url: '/orders/qiandanup/',
            type: 'GET',
            dataType: 'JSON',
            data: {id: _this.attr("data-id")}
        })
        .done(function(data) {
            if (data.error_code == 0) {
                $("body").append(data.data);
                $(".companyName").html(company_name);
            }
        });
    });

    $("body").on("click", "#btnSave", function () {
        $.ajax({
            url: '/orders/qiandanup/',
            type: 'POST',
            dataType: 'JSON',
            data: {
                status: $("input[name=status]:checked").val(),
                id: orderid,
                reason:$("[name=exam_reason]").val(),
                addtime:$("[name=addtime]").val(),
                xiaoqu:$("[name=xiaoqu]").val(),
                cname:$("[name=cname]").val(),
                money:$("[name=money]").val(),
                company:$(".companyName").html(),
                qz_area:$("[name=qz_area]").val(),
            }
        }).done(function (data) {
            if (data.error_code == 0) {
                alert('操作成功！');
                window.location.href = window.location.href;
            } else {
                alert(data.error_msg);
            }
        });
    });

    $(".canncel").click(function(event) {
        if (confirm("确定取消订单状态？")) {
            var _this = $(this);

            var data = _this.attr("data-source");
            data = JSON.parse(data);
            $("#cancelModel .modal-title").html("订单号："+data.id);
            $("#cancelModel [name=id]").val(data.id);
            $("#cancelModel [name=addtime]").val(data.qiandan_addtime);
            $("#cancelModel [name=xiaoqu]").val(data.xiaoqu);
            $("#cancelModel [name=cname]").val(data.cname);
            $("#cancelModel [name=qz_area]").val(data.qz_area);
            $("#cancelModel [name=money]").val(data.qiandan_jine);
            $("#cancelModel [name=company]").val(data.qiandan_companyid);
            $("#cancelModel").modal();
        }
    });

    $("#btnCancel").click(function(){
        $.ajax({
            url: '/orders/qiandanup',
            type: 'POST',
            dataType: 'JSON',
            data: $("#cancelForm").serializeArray()
        })
        .done(function(data) {
            if (data.error_code == 0) {
                window.location.href = window.location.href;
            } else {
                alert(data.error_msg);
            }
        });
    });

    $("body").on("click",".icon_headset",function(){
        var _this = $(this);
        if (!confirm("确定IP话机拨打电话?")) {
            return false;
        };
        $.ajax({
            url: '/voip/other_order_voipcallback',
            type: 'POST',
            dataType: 'JSON',
            data: {
                id: _this.attr("data-id")
            }
        })
        .done(function(data) {
            if (data.code == 200) {
                alert(data.errmsg);
            } else {
                alert(data.errmsg || data.info);
            }
        });
    });

    $("#btnReset").click(function(event) {
        $("input[name=id],input[name=begin],input[name=end],input[name=company]").attr("value","");
        $("#city").select2("val","");
    });
    $("body").on("click","#modal-close",function(){
        $('#myModal').remove();
        $('.modal-backdrop').remove();
        $('#switch-menu').removeClass('modal-open').css('padding-right','0')
    });
    $('#tablelist').on('click','.order-num',function(){
        $.ajax({
            url: '/orders/qiandanup/',
            type: 'GET',
            dataType: 'JSON',
            data: {
                id: $(this).attr('data-id'),
                detail: 1,
            }
        }).done(function (data) {
            if (data.error_code == 0) {
                $("body").append(data.data);
                $("#myModal").modal('show');
            }else {
                alert(data.error_msg)
            }
        });
    })

    $("#btnUnList").click(function(){
        $("#failModal").modal();
       document.getElementById('myframe').contentWindow.location.reload(true);
     });

})
