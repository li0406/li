(function(window) {
    // declare
    var _bayimob = {};
    _bayimob.VERSION = '1.0';
    _bayimob.API_BASE = 'https://openapi.bayimob.com';
    _bayimob.gconfig = {
        accountId: 'ecb697253235538031478cab37252eb9',
        secret: '3235538031478cab'
    };

    _bayimob.deepTranslateSend = function(param) {
        var path = '/openApi/deepTranslate/v2';
        var url = _bayimob.API_BASE + path;
        var paramSign = signParam(param);
        console.log(paramSign)
        httpAjax({type:'GET', url: url, data: paramSign})
    };

    // 加签
    function signParam(param) {
        var urlParam =  GetUrlParam();
        var bayimobParam = {accountId: _bayimob.gconfig.accountId};
        if (typeof urlParam.dkey !== "undefined") {
            bayimobParam.dkey = urlParam.dkey;
        }
        Object.assign(param, bayimobParam);
        param = sortParamToAbc(param);
        param.sign= md5(sortParamValueToAbc(param)+_bayimob.gconfig.secret);
        return param
    }

    // 参数按照ABC排序
    // 把参数的key按照 abc排序
    function sortParamToAbc(param) {
        var arr=[];
        for(var key in param){
            arr.push(key)
        }
        arr.sort();
        var sparam={};
        for(var i in arr){
            //str +=arr[i]+"="+param[arr[i]]+"&"
            sparam[arr[i]] = param[arr[i]];
        }
        return sparam
    }

    // 参数的key按照abc排序,并把排序后的value顺序拼接
    function sortParamValueToAbc(param) {
        var str = '';
        for(var i in param){
            str +=param[i]
        }
        return str
    }

    /**
     * [获取URL中的参数名及参数值的集合]
     * 示例URL:http://htmlJsTest/getrequest.html?uid=admin&rid=1&fid=2&name=小明
     * @param {[string]} urlStr [当该参数不为空的时候，则解析该url中的参数集合]
     * @return {[string]}       [参数集合]
     */
    function GetUrlParam(urlStr) {
        if (typeof urlStr == "undefined") {
            var url = decodeURI(location.search); //获取url中"?"符后的字符串
        } else {
            var url = "?" + urlStr.split("?")[1];
        }
        var theRequest = new Object();
        if (url.indexOf("?") != -1) {
            var str = url.substr(1);
            strs = str.split("&");
            for (var i = 0; i < strs.length; i++) {
                theRequest[strs[i].split("=")[0]] = decodeURI(strs[i].split("=")[1]);
            }
        }
        return theRequest;
    }

    // 请求
    function httpAjax(e) {
        var t = (e = e || {}).type || "GET";
        t = t.toUpperCase() || "GET";
        var n = e.url || "",
            a = e.async,
            r = e.data || null,
            i = e.success ||
                function() {},
            o = e.fail ||
                function() {},
            l = null;
        l = XMLHttpRequest ? new XMLHttpRequest: new ActiveXObject("Microsoft.XMLHTTP");
        var c = [];
        for (var d in r) c.push(d + "=" + r[d]);
        var s = c.join("&");
        "POST" === t ? (l.open(t, n, a), l.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=utf-8"), l.send(s)) : (l.open(t, n + "?" + s, a), l.send(null)),
            l.onreadystatechange = function() {
                if (4 === l.readyState) {
                    var e = l.status;
                    200 <= e && e < 300 ? i && i(l.responseText) : o && o(e)
                }
            }
    }

    // define your namespace myApp
    window._bayimob = _bayimob;
})(window, undefined);
