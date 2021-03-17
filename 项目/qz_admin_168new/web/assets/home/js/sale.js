$(function() {
    //选择角色
    $("button[data-type='city']").click(function(event) {
        var _this = $(this);
        if (_this.hasClass('btn-success')) {
            _this.removeClass('btn-success');
            _this.removeClass('editing');
        } else {
            _this.addClass('btn-success');
            _this.addClass('editing');
        }
    });
    // 全选
    $('.select-all').find('input').on('click',
    function() {
        if ($(this).is(':checked')) {
            $('.select-list').find('input').each(function(index, el) {
                $(el).attr('gou', 'yes');
                $(el).prop('checked', true);
            });
        } else {
            $('.select-list').find('input').each(function(index, el) {
                $(el).attr('gou', 'no');
                $(el).prop('checked', false);
            });
        }
    });
    // 单个选择// 全选联动
    $('.select-list input').each(function() {
        $(this).on('click',
        function() {
            if (!$(this).is(':checked')) {
                $(this).attr('gou', 'no');
            } else {
                $(this).attr('gou', 'yes');
            }
            if ($('.select-list input').length == $('input[gou="yes"]').length) {
                $('.select-all input').prop('checked', true);
            } else {
                $('.select-all input').prop('checked', false);
            }
        });
    });
    // 列表选择
    $('.btn-ok').on('click',
    function() {
        var arr = [];
        if ($('.select-list input[gou="yes"]').length <= 0) {
            $('#myTable thead tr th').show();
            $('#myTable tbody tr td').show();
        } else {
            $('#myTable thead tr th').hide();
            $('#myTable tbody tr td').hide();
            $('.select-list input:checked').each(function(index, el) {
                arr.push($('.select-list input:checked').parents().parents().eq(index).index());
            });
            for (var i = 0; i < arr.length; i++) {
                $('#myTable thead tr th').eq(arr[i]).show();
            }
            $('#myTable tbody tr').each(function(index, el) {
                for (var i = 0; i < arr.length; i++) {
                    $(el).children().eq(arr[i]).show();
                }
            });
        }
    });
});$(window).load(function() {
    var windowH = $(window).height();
    $(".shade").css("height", windowH + "px");
    //城市重点系数删除
    $(".fa-trash-o").bind("click",
    function() {
        if (confirm("确定删除该系数值？")) {
            var pointid = $(this).attr("data-id");
            $.ajax({
                url: "/salesetting/removeCityPoint/",
                type: 'POST',
                dataType: 'json',
                data: {
                    pointid: pointid
                }
            }).done(function(result) {
                if (result.status == 1) {
                    //var mbPoingID = $(this).attr("data-id");
                    alert(result.info);
                    window.location.reload();
                }
            }).fail(function(xhr) {
                alert("失败请重试！");
            });
        }
    });
    //添加城市系数
    $("#addCityPoint").bind("click",
    function() {
        $("#addPointAlert").removeClass("sale-none");
        var newCityPoint = $("#addCityPointContainer").children("li").length + 1;
        var newCityPointHtml = "<li id='addNow'><span class='sale-width-100' id='mdPoint-" + newCityPoint + "' data-id=''>&nbsp;<i  class='fa fa-trash-o sale-float-right cursor'></i><i class='fa fa-edit sale-float-right sale-marginright-20 cursor'></i></span></li>";
        $("#addCityPointContainer").append(newCityPointHtml);
    });
    //确认添加系数
    $("#confirmAddPoint").bind("click",
    function() {
        //var newCityPoint = "<input type='radio' name='cityPoint' /><span>2</span><br />";
        var citypoint = $("input[name=pointnowadd]").val();
        if (citypoint == '') {
            alert("请输入一个系数值！");
            return false;
        }
        //确定时写入数据库
        $.ajax({
            url: "/salesetting/addCityPoint/",
            type: 'POST',
            dataType: 'json',
            data: {
                citypoint: citypoint
            }
        }).done(function(result) {
            if (result.status == 1) {
                //var mbPoingID = $(this).attr("data-id");
                alert(result.info);
                window.location.reload();
            } else {
                alert(result.info);
            }
        }).fail(function(xhr) {
            alert("失败请重试！");
        });
    })
    //关闭添加窗口
    $("#cityClose").bind("click",
    function() {
        window.location.reload();
    })
    //城市重点系数编辑
    $(".fa-edit-tree").bind("click",
    function() {
        var name = $(this).attr("data-name");
        $(".editNow").removeClass("editNow");
        $(this).addClass("editNow");
        $("input[name=pointnowedit]").val(name);
    });
    //确认修改系数
    $("#confirmEditPoint").bind("click",
    function() {
        //var newCityPoint = "<input type='radio' name='cityPoint' /><span>2</span><br />";
        var citypoint = $("input[name=pointnowedit]").val();
        var name = $(".editNow").attr("data-id");
        if (citypoint == '') {
            alert("请输入一个系数值！");
            return false;
        }
        //alert("系数:"+citypoint+"id:"+name);
        //确定时写入数据库
        $.ajax({
            url: "/salesetting/editCityPoint/",
            type: 'POST',
            dataType: 'json',
            data: {
                name: name,
                citypoint: citypoint
            }
        }).done(function(result) {
            if (result.status == 1) {
                //var mbPoingID = $(this).attr("data-id");
                alert(result.info);
                window.location.reload();
            } else {
                alert(result.info);
            }
        }).fail(function(xhr) {
            alert("失败请重试！");
        });
    })
    //点击切换城市列表
    $(".checkcitys").each(function() {
        $(this).unbind().bind('click',
        function() {
            $(this).siblings().removeClass("btn-success");
            $(this).addClass("btn-success");
            var bm = $(this).attr("data-bm");
            var pointid = $(this).attr("data-id");
            var type = 4;
            $.ajax({
                url: "/salesetting/getListByCityBm/",
                type: 'GET',
                dataType: 'json',
                data: {
                    bm: bm,
                    pointid: pointid,
                    type: type
                }
            }).done(function(result) {
                if (result.status == 1) {
                    //var mbPoingID = $(this).attr("data-id");
                    $("#checkcitys").html(result.data);
                } else {
                    $("#checkcitys").html(result.data);
                }
            }).fail(function(xhr) {
                alert("失败请重试！");
            });
        });
    });
    //添加城市
    $("#confirmAdd").bind('click',
    function() {
        var pointid = $("#addCity").attr("data-id");
        var citys = '';
        var leng = $("input[name=city]:checked").length;
        if (leng == 0) {
            alert("请选择一个城市！");
            return false;
        }
        $("input[name=city]:checked").each(function() {
            citys += $(this).val() + '|'; //使用|将添加的城市ID拼接成字符串
        });
        $.ajax({
            url: "/salesetting/setCityPointValues/",
            type: 'POST',
            dataType: 'json',
            data: {
                pointid: pointid,
                citys: citys
            }
        }).done(function(result) {
            if (result.status == 1) {
                //var mbPoingID = $(this).attr("data-id");
                alert(result.info);
                window.location.reload();
            } else {
                alert("失败请重试！");
                window.location.reload();
            }
        }).fail(function(xhr) {
            alert("失败请重试！");
        });
    });
    //移出城市
    $("#removeCity").bind('click',
    function() {
        var pointid = $("#editCity").attr("data-id");
        var ids = '';
        var leng = $(".editing").length;
        if (leng == 0) {
            alert("请选择一个城市！");
            return false;
        }
        $(".editing").each(function() {
            ids += $(this).attr("data-id") + '|';
        });
        $.ajax({
            url: "/salesetting/removeCityPointValues/",
            type: 'POST',
            dataType: 'json',
            data: {
                pointid: pointid,
                ids: ids
            }
        }).done(function(result) {
            if (result.status == 1) {
                //var mbPoingID = $(this).attr("data-id");
                alert(result.info);
                window.location.reload();
            } else {
                alert("失败请重试！");
                window.location.reload();
            }
        }).fail(function(xhr) {
            alert("失败请重试！");
        });
    });
    //全选城市
    var flag = 0;
    $("#selectAll").bind("click",
    function() {
        if (flag == 0) {
            $("button[data-type='city']").addClass("btn-success");
            $("button[data-type='city']").addClass("editing");
            flag = 1;
        } else {
            $("button[data-type='city']").removeClass("btn-success");
            $("button[data-type='city']").removeClass("editing");
            flag = 0;
        }
    });
    //确认移动系数
    $("#confirmMove").bind('click',
    function() {
        var pointid = $("#editCity").attr("data-id");
        var ids = '';
        var leng = $(".editing").length;
        if (leng == 0) {
            alert("请选择一个城市！");
            return false;
        }
        $(".editing").each(function() {
            ids += $(this).attr("data-id") + '|';
        });
        //移动到的系数
        var pid = $("input[name=cityPoint]:checked").attr("data-id");
        $.ajax({
            url: "/salesetting/editCityPointValues/",
            type: 'POST',
            dataType: 'json',
            data: {
                pointid: pointid,
                ids: ids,
                pid: pid
            }
        }).done(function(result) {
            if (result.status == 1) {
                //var mbPoingID = $(this).attr("data-id");
                alert(result.info);
                window.location.reload();
            } else {
                alert("失败请重试！");
                window.location.reload();
            }
        }).fail(function(xhr) {
            alert("失败请重试！");
        });
    });
    //移动城市：确认添加系数
    $("#confirmMoveAdd").bind("click",
    function() {
        var citypoint = $("input[name=addonmove]").val();
        if (citypoint == '') {
            alert("请输入一个系数值！");
            return false;
        }
        //确定时写入数据库
        $.ajax({
            url: "/salesetting/addCityPoint/",
            type: 'POST',
            dataType: 'json',
            data: {
                citypoint: citypoint
            }
        }).done(function(result) {
            if (result.status == 1) {
                var html = '<input type="radio" name="cityPoint" data-id="' + result.data + '" checked /><span>' + citypoint + '</span><br />';
                $("#addPointContainer").append(html);
                $("#addPointOnMove").css("display", "none");
            } else {
                alert(result.info);
            }
        }).fail(function(xhr) {
            alert("失败请重试！");
        });
    });
    //表格排序
    $(".sort").bind("click",
    function() {
        var thisEl = $(this).parents().attr("tableindex");
             $(".sort").parents().addClass("sale-gray");
        if ($(this).hasClass("fa-sort-amount-desc") == true) {
            $(this).addClass("fa-sort-amount-asc");
            $(this).removeClass("fa-sort-amount-desc");
            $(this).parents().removeClass("sale-gray");
        } else {
            $(this).addClass("fa-sort-amount-desc");
            $(this).removeClass("fa-sort-amount-asc");
            $(this).parents().removeClass("sale-gray");
        }
    });

    //编辑系数城市
    //  $("#editCity").bind("click",function(){
    //      $("#editCityList").removeClass("sale-none");
    //  })
    //  //放弃当前编辑
    //  $("#giveupEdit").bind("click",function(){
    //      $("#editCityList").addClass("sale-none");
    //  })
    //  //增加系数
    //  $("#addPoint").bind("click",function(){
    //      $("#addPointAlert").removeClass("sale-none");
    //  })
    //
    //
    //  //打开移动系数
    //  $("#moveOtherPoint").bind("click",function(){
    //      $("#movePointContainer").removeClass("sale-none");
    //  })
    //  //关闭确认系数
    //  $("#movePointClose").bind("click",function(){
    //      $("#movePointContainer,#manageCitySet").addClass("sale-none");
    //  })
    //  //打开新增城市
    //  $("#addCity,i[data-manageCitySet='2']").bind("click",function(){
    //      $("#addCityContainer").removeClass("sale-none");
    //  })
    //  $("#giveupAdd").bind("click",function(){
    //      $("#addCityContainer").addClass("sale-none");
    //  })
    //
    //  $("i[data-manageCitySet='1']").bind("click",function(){
    //      $("#manageCitySet").removeClass("sale-none");
    //
    //  })
    //关闭历史记录相亲
    //  $("#cfsHistoryClose").bind("click",function(){
    //      $(".sale-alert-full").addClass("sale-none");
    //      $(".shade").hide();
    //  })
    //  $(".sale-unfold-history").bind("click",function(){
    //      $(".shade").show();
    //      $(".sale-alert-full").removeClass("sale-none");
    //  })
});

function deleteCityPoint(e) {
    alert(e);
}
function editCityPoint(e) {
    alert(e);
}
var selectAllCity = function(a) {
    console.log(a);
    a = $(a);
    var cks = a.parent().parent().find(":checkbox"); ! a.data("checked") ? cks.prop("checked", true) & a.data("checked", 1) : cks.prop("checked", false) & a.data("checked", 0);
}