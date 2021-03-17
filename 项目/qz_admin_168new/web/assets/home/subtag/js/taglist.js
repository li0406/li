$(function () {
    $("#input_cs").select2();
    $("#tag_cs").select2({
        allowClear: true,
        placeholder: "请选择城市",
    });

    $(".datepicker").datepicker({
        format: "yyyy-mm-dd",
        minViewMode: 0,
        autoclose: true,
        todayBtn:true
    });
    
    function toTimeStamp(time){
        if(time!=undefined){
            var date = time;
            date = date.substring(0,19);
            date = date.replace(/-/g,'/');
            var timestamp = new Date(date).getTime();
            return timestamp;
        }
    };
    $('#search').click(function(){
        if (toTimeStamp($("input[name=date_begin]").val()) > toTimeStamp($("input[name=date_end]").val())) {
            alert('开始时间不能大于结束时间');
            return false;
        }
    })
    // 重置
    $('#reset').click(function(){
        $("#input_cs").select2("val", '');
        $('select').val('');
        $('input[type=text]').val('');
    });
    $('#reverse').click(function () {
        var chsub = $("input[class='bids']").length; 
        var checkedsub = $("input[class='bids']:checked").length;

        $("input[class='bids']").each(function () {
            // 判断只要选中数小于总数，就全选
            if (checkedsub < chsub) {  
                $(this).prop("checked", true);  
            }else{
                $(this).prop('checked', false);
            }     
        });
    });

    $("#tagsbox").click(function () {
        $("#tag_id").val("");
        $("#tag_name").val("");
        $("#tag_cs").select2("val",'');
        $("#switch").bootstrapSwitch('state', true,true);
        $('#tagsModal').modal('show');
        $('#tagsModal').find('.modal-title').html('新增标识');
    });

    $('#pl-excel').click(function () {
        $('#excelModal').modal('show')
    });
    $("#fileUp").fileinput({
        language: 'zh', //设置语言,
        allowedFileExtensions: ['xls', 'xlsx'],
        uploadUrl: "/subtag/importExcel",
        browseClass: "btn btn-primary",
        uploadClass: "btn btn-info",
        removeClass: "btn btn-danger",
        showPreview: false,
        uploadAsync: true,
        showRemove: true,
        showCancel: false,
        showUpload: true,
        maxFileCount: 1,
        dropZoneEnabled: false
    }).on('fileuploaded', function (event, data) {
        if (0 == data.response.status) {
            alert("导入成功");
            window.location.reload();
        } else {
            alert(data.response.info);
        }
    });

    $('#switch').bootstrapSwitch({
        onText: "开启",
        offText: "关闭",
        onColor: "success",
        offColor: "info",
        size: "small",
        onSwitchChange: function (e, data) {
            var $el = $(e.target);
            if ($el.attr("checked") == "checked") {
                $el.attr("checked", false);
            } else {
                $el.attr("checked", "checked")
            }
        }
    });

    $(".table").on("click", ".edit", function(){
        $('#tagsModal').find('.modal-title').html('编辑标识');
        $("#tag_id").val($(this).data("id"));
        $("#tag_cs").select2("val",$(this).data("cs"));
        $("#tag_name").val($(this).data("name"));

        if ($(this).data("enabled") == 1) {
            $("#switch").bootstrapSwitch('state', true,true);
        } else {
            $("#switch").bootstrapSwitch('state', false,true);
        }

        $('#tagsModal').modal('show');
    });

    // 禁用
    $('.table').on('click', '.stop', function () {
        var id = $(this).attr('data-id');
        var enabled = $(this).attr('data-val');
        var tip = enabled == 1 ? "启用" : "禁用";
        layer.confirm('确定要' + tip + '吗？', {
            title: '禁用/启用确认',
            btn: ['确定', '取消'] //按钮
        }, function (index) {
            layer.close(index);
            $.ajax({
                url: '/subtag/setEnabled',
                type: 'POST',
                dataType: 'json',
                data: {
                    id: id,
                    enabled: enabled
                },
            })
            .done(function (data) {
                if (data.status == 0) {
                    alert(data.info);
                } else {
                    window.location.reload();
                }
            });
        })
    });

    //批量删除数据
    $('#deleteall').click(function () {
        var id = [];
        $("#form").find(".bids").each(function(){
            if ($(this).prop("checked")) {
                id.push($(this).data("id"))
            }
        });

        if (id.length == 0) {
            alert("请先选择要操作的数据");
            return false;
        }

        id = id.join(",");
        layer.confirm('确定要批量删除吗？此操作不可挽回！！！', {
            title: '删除确认',
            btn: ['确定', '取消'] //按钮
        }, function (index) {
            layer.close(index);
            if (id === '') {
                alert('数据错误');
                return;
            }
            $.ajax({
                url: '/subtag/delete',
                type: 'POST',
                dataType: 'json',
                data: {
                    id: id
                }
            })
            .done(function (data) {
                if (data.status == 0) {
                    alert(data.info);
                } else {
                    window.location.reload();
                }
            });
        });
    });

    //删除一条数据
    $('.table').on('click', '.del', function () {
        var id = $(this).attr('data-id');
        layer.confirm('确定要删除吗？此操作不可挽回！！！', {
            title: '删除确认',
            btn: ['确定', '取消'] //按钮
        }, function (index) {
            layer.close(index);
            if (id === '') {
                alert('数据错误');
                return;
            }
            $.ajax({
                url: '/subtag/delete',
                type: 'POST',
                dataType: 'json',
                data: {
                    id: id,
                }
            })
            .done(function (data) {
                if (data.status == 0) {
                    alert(data.info);
                } else {
                    window.location.reload();
                }
            });
        });
    });

    $(".tag-save-btn").click(function(){
        var enabled = $("#switch").bootstrapSwitch('state') ? 1 : 2;
        $.post("/subtag/tagsave", {
            id: $("#tag_id").val(),
            cs: $("#tag_cs").val(),
            name: $("#tag_name").val(),
            enabled: enabled
        }, function(data){
            if (data.status == 0) {
                alert(data.info);
            } else {
                window.location.reload();
            }
        });
    });

    $('.table').on('click', '.zx-gs', function () {
        var tag_id = $(this).parent().data("id");

        $(".zzc").fadeIn();
        $(".win-title").text('选择装修公司');
        $("#selectBox", parent.document.body).attr("src", "/subtag/relation_company?tag_id=" + tag_id);
    });

    $('.table').on('click', '.sjs', function () {
        var tag_id = $(this).parent().data("id");

        $(".zzc").fadeIn();
        $(".win-title").text('选择设计师');
        $("#selectBox", parent.document.body).attr("src", "/subtag/relation_designer?tag_id=" + tag_id);
    });

    $('.table').on('click', '.zx-al', function () {
        var tag_id = $(this).parent().data("id");
        
        $(".zzc").fadeIn();
        $(".win-title").text('选择装修案例');
        $("#selectBox", parent.document.body).attr("src", "/subtag/relation_case?tag_id=" + tag_id);
    });

    $('.table').on('click', '.fz-wz', function () {
        var tag_id = $(this).parent().data("id");

        $(".zzc").fadeIn();
        $(".win-title").text('选择分站文章');
        $("#selectBox", parent.document.body).attr("src", "/subtag/relation_article?tag_id=" + tag_id);
    });

    $('.close').click(function () {
        $(".zzc").fadeOut();
        window.location.reload();
    });
});