jQuery.extend(jQuery.fn.dataTableExt.oSort, {
    "html-second-pre": function (a) {
        var x = String(a).replace(/<[\s\S]*?>/g, "");    //去除html标记
        x = x.replace(/&nbsp;/ig, "");                   //去除空格
        x = x.replace(/\s/, "");                          //去除秒
        return parseFloat(x);
    },

    "html-second-asc": function (a, b) {                //正序排序引用方法
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    },

    "html-second-desc": function (a, b) {                //倒序排序引用方法
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    }
});