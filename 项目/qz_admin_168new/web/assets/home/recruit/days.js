/**
 * +----------------------------------------------------------------------
 * | days
 * +----------------------------------------------------------------------
 * | Copyright (c) 2018 http://gitee/honoryao All rights reserved.
 * +----------------------------------------------------------------------
 * | Author: 姚荣<honoryao@qq.com> 2851986856@qq.com
 * +----------------------------------------------------------------------
 */
/**
 * 获取每页具体数据,并追加入容器
 * @param id
 * @param page
 * @param day
 */
function getData(id,page,day)
{
    $.ajax({
        type: "GET",
        url: itemUrl,
        dataType: "JSON",
        data: {id: id, day: day, pageNum: page, pageSize: 50}
    })
        .done(function (data) {
            if (data.status == 1) {
                if (data.data != "") {
                    if (data.data.length < 50){
                        window.flag = false;
                    }
                    var html = getHtml(data.data);
                    $('table .appendhtml').append(html)
                } else {
                    window.flag = false;
                }
            }else{
                alert(data.info);
            }
        })
        .fail(function (xhr) {
            alert("网络发生错误,请稍后重试！");
        });
}

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

/**
 * 分析数据并拼接完整的HTML
 * @param data
 * @returns {string}
 */
function getHtml(data)
{
    var html = '';
    var length = data.length;

    $.each(data, function (index) {
        action = getAction(data[index].action);
        type = getTypes(data[index].byetype);
        if(data[index].action == 'Hangup'&&data[index].byetype!=1){
            buttonstr ='<button class="play-record" data-callsid="'+data[index].callsid+'" onclick="shiting($(this));" title="点击播放 和 下载">'+
                '<img src="/assets/home/img/imgother/u95.png">'+
                '</button>';
        }else{
            buttonstr = '';
        }
        type = getTypes(data[index].byetype);
        html += '<tr>' +
            '<td>' + data[index].time_add + '</td>' +
            '<td>' + action + '</td>' +
            '<td>' + type + '</td>' +
            '<td>' + buttonstr+ '</td>' +
            '<td>' + data[index].callsid + '</td>' +
            '<td>' + data[index].caller + '</td>' +
            '<td>' + data[index].called + '</td>' +
            '<td>' + data[index].uname + '</td>' +
            '</tr>'
    });
    return html;
}

/**
 * 获取方法汉语名称
 * @param str
 * @returns {*}
 */
function getAction(str)
{
    var ret ;
    switch(str)
    {
        case 'CallAuth':
            ret='开始';
            break;
        case 'CallEstablish':
            ret='接通';
            break;
        case 'Hangup':
            ret='挂断';
            break;
        case 'CallEstablish_Sub':
            ret='主叫接通';
            break;
        case 'Hangup_Sub':
            ret='主叫挂断';
            break;
        default:
            ret='--';
            break;
    }
    return ret;
}

/**
 * 获取通话类型汉语名称
 * @param str
 * @returns {*}
 */
function getTypes(str)
{
    var ret ;
    if (str===0||str==='0'){
        ret = '呼叫挂机(0)';
    }else if(str===1||str==='1'){
        ret = '通话失败(1)';
    } else {
        ret = '';
    }
    return ret;
}