/**
 * Version  1.1.2
 * Author   y
 */;
(function () {
    'use strict';
    if (window.order) {
        return window.order
    }

    //默认配置信息
    var defaults = {
        //默认发单入口
        url: '/fb_order/',
        //默认表单外层包裹
        wrap: '.order-wrap',
        //默认需要提交的元素，遍历获取这些参数
        //参数的名称为属性data-order-name，参数值为jquery的val()函数获取的值，如果为空，则获取属性data-order-value的值
        item: '.order-item',
        //额外参数，可覆盖上面根据类order-item获取的数据
        extra: {},
        //字段排序，一个数组，用于验证的顺序，例如该值是['cs', 'tel'] 则第一个验证cs，然后是tel，然后是其它字段
        order: [],
        //发送失败的回调，-可省略
        error: function (xhr, status, error) {
            alert('请求失败：' + error);
        },
        //发送成功的回调，-可省略
        success: function (data, status, xhr) {},
        //验证失败的回调，如果忽略错误，[必须]返回true，如果返回false，则终止请求；如需其它验证，提前自行验证
        validate: function (item, value, method, info) {
            return true;
        }
    };

    //各个参数的验证不通过时的提示信息
    var prompt = {
        'name':{
            'notempty':'您填写的姓名为空 ^_^!',
            'isword':'请输入正确的姓名，只支持中文和英文 ^_^!'
        },
        'tel':{
            'notempty':'亲，您还没有填写手机号码！',
            'ismobile':'亲，请输入11位手机号码！'
        },
        'cs':{
            'notempty':'请选择您所在的城市 ≧▽≦'
        },
        'qx':{
            'notempty':'请选择您所在的区县 ≧▽≦'
        },
        'mianji':{
            'notempty':'亲,您还没有填写房屋面积!',
            'isnumber':'请您填写真实的房屋面积!',
            'acreagerange':'房屋面积请输入1-10000之间的数字 ^_^!'
        },
        'xiaoqu':{
            'notempty':'亲,您还没有填写小区!',
            'isnotnumber':'亲，请填写正确的小区名称！'
        },
        'disclamer':{
            'noChecked':'请勾选我已阅读并同意齐装网的《免责声明》！'
        }
    };

    //验证函数，结果符合发单要求必须返回真
    var verification = {
        //判断参数是否有效，有效返回真
        isvalid: function (param) {
            if(null == param || "undefined" == typeof(param) || "" == param){
                return false;
            }
            return true;
        },
        //验证字符串为空
        notempty:function(param) {
            if("" == param || 'undefined' ==  typeof(param)){
                return false;
            }
            return true;
        },
        //验证中英文
        isword:function(param) {
            var reg = new RegExp("^[\u4e00-\u9fa5a-zA-Z]+$");
            if (!reg.test(param)) {
                return false;
            }
            return true;
        },
        //验证手机
        ismobile:function(param) {
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if(!reg.test(param)){
                return false;
            }
            return true;
        },
        //验证是否是数字
        isnumber:function(param){
            var reg = /^(-?\d+)(\.\d+)?$/;
            if(!reg.test(param)){
                return false;
            }
            return true;
        },
        //验证面积范围
        acreagerange:function(param) {
            if (param < 1 || param > 10000) {
                return false;
            }
            return true;
        },
        //验证是否是数字
        isnotnumber:function(param){
            var reg = /^(-?\d+)(\.\d+)?$/;
            if(reg.test(param)){
                return false;
            }
            return true;
        },
        noChecked:function(param){
            return param;
        }
    };

    //发送信息核心代码
    var order = function (params) {

        //定义提交数据对象
        var data = {};

        //合并默认参数配置
        $.each(defaults, function(k, v) {
            if (!params.hasOwnProperty(k)) {
                params[k] = v;
            }
        });

        //遍历获取待提交表单元素，根据class属性，自动获取表单的值
        $(params.wrap + ' ' + params.item).each(function(index, el) {
            var k = $(this).attr('data-order-name');
            //如果无效的KEY，则返回继续获取下一个参数
            if(!verification.isvalid(k)){
                return true;
            }
            //元素的值优先取val()函数获取的，如果无效，则取属性data-order-value的值
            var v = $(this).val();
            if(!verification.isvalid(v)){
                v = verification.isvalid($(this).attr('data-order-value')) ? $(this).attr('data-order-value') : v;
            }
            data[k] = v;
        });

        //如果额外参数可用，则合并额外参数
        $.each(params.extra, function(k, v) {
            if(verification.isvalid(k)){
                data[k] = v;
            }
        });

        //参数排序，用于验证字段的顺序
        if (params.order.length > 0) {
            var temp = {};
            $.each(params.order, function(k, v) {
                temp[v] = data[v];
            });
            $.each(data, function(k, v) {
                if (!temp.hasOwnProperty(k)) {
                    temp[k] = data[k];
                }
            });
            data = temp;
        }

        //定义参数检查完毕标识
        var checked = true;
        //数据验证
        $.each(data, function(item, value){
            if (prompt.hasOwnProperty(item)) {
                //step-1,默认验证的字段
                $.each(prompt[item], function(method, info) {
                    //如果符合验证要求，继续验证下一条
                    if(false != verification[method](value)){
                        return true;
                    }

                    //回调传入的验证函数
                    if(false == params.validate(item, value, method, info)){

                        //验证不通过，并停止其它方法验证
                        checked = false;
                        return false;
                    }
                });
            } else {
                //step-2,不在默认默认验证的字段内，也要将数据传回，用于用户自定义验证，如果返回false，则不通过验证
                if(false == params.validate(item, value, '', '')){
                    checked = false;
                }
            }
            //step-3,验证不通过，并停止其它属性验证
            if (!checked) {
                return false;
            }
        });

        //验证不通过，停止请求发单
        if (!checked) {
            return false;
        }
        //发送表单
        $.ajax({
            url: params.url,
            type: 'POST',
            dataType: 'JSON',
            data: data
        })
        .done(function(data, status, xhr) {
            //请求成功回调函数
            params.success && params.success(data, status, xhr);
        })
        .fail(function(xhr, status, error) {
            //请求失败回调函数
            params.error && params.error(xhr, status, error);
        });
    };

    window.order = order;
})();