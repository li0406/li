;(function($,window,document,toastr,undefined){
    //定义一些默认参数
    var defaults = {
        "url": "", //ajax轮询请求地址
        "interval": 5, //ajax轮询间隔时间，单位s
        "messageCount": 3, //页面最多同时出现消息提示的个数
        "closeButton": true,
        "closeButtonClass": 'toast-clear', //如果设置了此项，ajax返回的内容如果有该类，则点击该类后消息弹窗会消失
        "debug": false,
        "newestOnTop": false,
        "positionClass": "toast-bottom-right",
        "onclick": null,
        "timeOut": 0,
        "extendedTimeOut": 0,
        "hideEasing": "linear",
        "tapToDismiss": false,
        "closeUrl": "", //关闭消息框后访问的url
        "closeIdClass": "toast-data", //记录消息ID的样式
        "closeIdAttr": "toast-data-id", //消息表ID的属性
        //关闭消息提示框回调函数，用于记录是否知道该消息
        "closeCallBack": function (toast){
            return true;
        }
    }

    //定义一些api
    var executeFunc = function(options){
        //合并参数
        defaults = $.extend(defaults, options || {});
        //补充设置关闭类
        if (defaults.closeButtonClass != '') {
            defaults.closeButtonClass = '.' + defaults.closeButtonClass;
        };
        //toastr配置项生成
        var toastrOption = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": false,
            "positionClass": "toast-bottom-right",
            "onclick": null,
            "timeOut": 0,
            "extendedTimeOut": 0,
            "hideEasing": "linear",
            "tapToDismiss": false
        }
        for (var item in toastrOption){
            if(defaults.hasOwnProperty(item)){
                toastrOption[item] = defaults[item];
            }
        }
        toastr.options = toastrOption;

        //检查必要参数是否完善
        if (defaults.url == '') {
            console.log('toastr 消息提示缺失请求地址');
            return false;
        };

        //定义关闭消息框访问
        var closeVisitedFunc = function(toast){
            if (defaults.closeUrl != "" && defaults.closeIdClass != "" && defaults.closeIdAttr != "") {
                var id = toast.find('.' + defaults.closeIdClass).attr(defaults.closeIdAttr);
                if (id != "") {
                    $.ajax({
                        url: defaults.closeUrl,
                        type: 'POST',
                        async:false,
                        dataType: 'JSON',
                        data: {
                            id:id
                        }
                    });
                };
            };
            return true;
        };

        //定时器定时获取消息内容
        var setToastrTimeInterval = setInterval(function(){
            //设置全局loading为false
            isLoading = false;
            //当前屏幕只显示一个通知消息
            var count = $('body').find('#toast-container>div').length;
            if (count >= defaults.messageCount) {
                return false;
            };

            //ajax请求消息数据
            $.ajax({
                url: defaults.url,
                type: 'GET',
                async:true,
                dataType: 'JSON',
                data: {
                    start:count
                }
            })
            .done(function(data) {
                if (data.status == 1) {
                    var title = data.data.title;
                    var content = data.data.content;
                    if (title != '' && content != '') {
                        //实例化消息提示
                        var toast = toastr["info"](content, title);
                        //判断closeButton属性是否为真，
                        if (defaults.closeButton == true && defaults.closeButtonClass != '') {
                            if (toast.find(defaults.closeButtonClass).length) {
                                toast.delegate(defaults.closeButtonClass, 'click', function () {
                                    //执行回调函数
                                    defaults.closeCallBack(toast);
                                    closeVisitedFunc(toast);
                                    //关闭消息窗口
                                    toastr.clear(toast, { force: true });
                                });
                            }
                        };
                        //关闭消息提示按钮
                        if (toastr.options.closeButton) {
                            toast.find('.toast-close-button').click(function(event) {
                                //执行回调函数
                                defaults.closeCallBack(toast);
                                closeVisitedFunc(toast);
                            });
                        };
                    } else {
                        alert('消息提示请求错误!');
                    }
                }
                isLoading = true;
            })
            .fail(function(xhr) {
                console.log('网络请求错误');
            })
        }, defaults.interval * 1000);
    }
    //这里确定了插件的名称
    window.toastrMessage = executeFunc;
})(jQuery,window,document,toastr);