/**
 * +----------------------------------------------------------------------
 * | voice
 * +----------------------------------------------------------------------
 * | Copyright (c) 2018 http://gitee/honoryao All rights reserved.
 * +----------------------------------------------------------------------
 * | Author: 姚荣<honoryao@qq.com> 2851986856@qq.com
 * +----------------------------------------------------------------------
 */
//试听
function shiting(e)
{
    var callid = e.attr("data-callsid");

    //设置属性订单ID和callsid属性
    $(".player").attr("data-callsid", callid);
    //设置默认没有记录过日志,已记录过日志则该数值为0
    $(".player").attr("data-on", '0');

    var data_content = {
        "callid": callid
    };
    $.post("/telcenter/cuctcallRecordUrl", data_content, function(data, status) {
        audio(data.data, data.status);
    },'json');
    //弹窗(试听)
    function audio(url, status) {
        if (status == 1) {
            //打开弹窗
            $("#box-bg").removeClass("hidden-record");
            $("#box-bg button").addClass("hidden-record");
            $(".box-prompt span").html("录音播放");
            $("#pic").addClass("hidden-record")
            $(".auto-record").removeClass("hidden-record");

            //设置录音路径
            $(".player").attr("src", url);

            //取消
            $(".box-prompt input").on("click", function() {
                $("#box-bg").addClass("hidden-record");
                $("#box-bg button").removeClass("hidden-record");
                $(".box-prompt span").html("提示");
                $("#pic").removeClass("hidden-record");
                $(".auto-record").addClass("hidden-record");
                $(".player").removeAttr("src");
            });
        } else {
            //打开弹窗
            $("#box-bg").removeClass("hidden-record");
            $("#box-bg button").addClass("hidden-record");
            $("#pic").html("播放录音失败");

            $(".box-prompt input").on("click", function() {
                $("#box-bg").addClass("hidden-record");
                $("#box-bg button").removeClass("hidden-record");
            });
        }
    }
}

function record_log(event)
{
    if (event.attr('data-on') == '1') {
        return false;
    }
    var callSid = event.attr('data-callsid');
    $.ajax({
        url: '/logtelcenterlistenordercall/addlistenordercalllog/',
        type: 'POST',
        async: false,
        dataType: 'JSON',
        data: {
            orders_id: '-',
            callSid: callSid,
            type: 2
        }
    })
        .done(function (data) {
            if (data.status == '1') {
                event.attr('data-on', '1');
            } else {
                alert('ERROR录音错误！请及时联系技术部');
            }
        })
        .fail(function (xhr) {
            alert('服务器开小差了！');
        })
}