$(function () {
    $(".datepicker").datepicker({
        autoclose: true,
        format: 'yyyy-mm--dd',
        showOtherMonths: true,
        selectOtherMonths: true,
        timePicker24Hour: true,
        numberOfMonths: 1,
        showButtonPanel: true,
        dateFormat: 'yy-mm-dd',
        clearText: "清除",
        closeText: "关闭",
        yearSuffix: '年',
        showMonthAfterYear: true,
        monthNames: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
        dayNames: ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'],
        dayNamesShort: ['周日', '周一', '周二', '周三', '周四', '周五', '周六'],
        dayNamesMin: ['日', '一', '二', '三', '四', '五', '六']

    });

    $(".sj-rightDateBtn").click(function () {
        var first = $(".sj-starDate").val(),
            end = $(".sj-endDate").val(),
            arry1 = first.split('-'),
            sDate = new Date(arry1[0], arry1[1], arry1[2]),
            arry2 = end.split('-'),
            eDate = new Date(arry2[0], arry2[1], arry2[2]),
            diffDay = daysDistance(first, end);
        if (arry1.length == 1 || arry2.length == 1) {
            msg("请输入开始结束日期");
            return false;
        } else if (sDate > eDate) {
            msg("开始日期大于结束日期，请重新输入");
            return false;
        } else if (diffDay > 90) {
            msg("请查询90天以内的数据~");
            return false;
        }

        window.location.href = window.location.pathname + '?first=' + first + '&end=' + end;

    })
})
;

function daysDistance(d1, d2) {
    d1 = new Date(d1);
    d2 = new Date(d2);
    var dateTime = 24*60*60*1000,
        t1 = d1.getTime(),
        t2 = d2.getTime();
    var minusDays = Math.floor(((t2-t1)/dateTime));//计算出两个日期的天数差
    var days = Math.abs(minusDays);//取绝对值
    return days;
}

function msg(msg, fn) {
    layer.msg(
        msg,
        {time: 1300},
        fn || function () {
        }
    )
}
