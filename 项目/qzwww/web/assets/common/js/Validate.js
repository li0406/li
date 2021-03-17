!function($){
    var methods = {
        init: function(options){
            var defaults = {
                rules:null,
                errorElement:"span",
                errorClass:"validate",
                ajaxSubmit:false,
                data:null,
                url:null,
                ajaxType:"POST"
            }
            var options =  $.extend({},defaults, options);
            var _this = this;
            _this.addClass(options.errorClass);

            for(var item in options.rules){
                var flag = methods.validate(_this,item,options.rules[item].message,options.rules[item].type,options.rules[item].length);

                if(!flag){
                    return false;
                }
            }

            if(options.ajaxSubmit){
                methods.ajax(_this,options);
            }
        },
        validate:function(event,target,msg,type,length){
            var _self = methods;
            var _this = event;
            var target = $("[name="+target+"]",event);
            var flag = _self.invalidHandler(type,target.val(),length);
            _self.unhighlight(_this);
            if(!flag){
                _self.highlight(target,msg);
                return false;
            }else{
                return true;
            }
        },
        highlight:function(event,msg){
            event.parent().addClass('height_auto');
            event.addClass('focus').focus();
            var span = $("<span class='valdate-info'></span>");
            span.html(msg);
            event.parent().append(span);
        },
        unhighlight:function(event){
            $(".focus", event).removeClass('focus');
            $(".height_auto", event).removeClass('height_auto');
            $(".valdate-info", event).remove();
        },
        invalidHandler:function(type,value,length){
            var _this = this;
            switch(type){
                case "name":
                flag = _this.validate_name(value);
                    break;
                case "num":
                flag = _this.validate_numberic(value);
                    break;
                case "tel":
                flag =  _this.validate_tel(value);
                    break;
                case "email":
                flag =  _this.validate_email(value);
                    break;
                case "decimal":
                flag =  _this.validate_decimal(value,length);
                    break;
                case "mobile":
                flag =  _this.validate_moblie(value);
                    break;
                case "length":
                flag =  _this.validate_length(value,length);
                    break;
                case "blend":
                flag =  _this.validate_blend(value);
                    break;
                case "maxlength":
                flag =  _this.validate_maxlength(value,length);
                    break;
                case "specialchar":
                flag =  _this.validate_specialchar(value);
                    break;
                default:
                flag =  _this.validate_string(value);
                    break;
            }
            return flag;
        },
        validate_specialchar:function(value){
            var reg = /[\`~!@#$%^&*\(\)_+<>?:"{},.\/;\[\]\-]/i;
            if(reg.test(value)){
                return false;
            }
            return true;
        },
        validate_string:function(value){
            //验证字符串
            if(value == "" || value.length <=0){
                return false;
            }
            return true;
        },
        validate_numberic:function(value){
            //验证纯数字
            var reg = /^[0-9]+$/i;
            if(!reg.test(value)){
                return false;
            }
            return true;
        },
        validate_decimal:function(value){
            //验证小数点2位
            var reg = /^[0-9]{1,4}(\.[0-9]{1,2})?$/i;
            if(!reg.test(value)){
                return false;
            }
            return true;
        },
        validate_tel:function(value){
            //验证电话/手机
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if(!reg.test(value)){
                return false;
            }
            return true;
        },
        validate_moblie:function(value){
            //验证电话/手机
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if(!reg.test(value)){
                return false;
            }
            return true;
        },
        validate_email:function(value){
            //验证邮箱地址
            var reg = /^[A-Za-z\d]+([-_.][A-Za-z\d]+)*@([A-Za-z\d]+[-.])+[A-Za-z\d]{2,5}$/i;
            if(!reg.test(value)){
                return false;
            }
            return true;
        },
        validate_length:function(value,length){
            //验证最少字符串长度
            if(value < length){
                return false;
            }
            return true;
        },
        validate_maxlength:function(value,length){
            //验证最大字符串长度
            if(value > length){
                return false;
            }
            return true;
        },
        validate_blend:function(value){
            //混合字符，不能纯字母或数字
            var reg = /^(?=.*[a-z_\-])[0-9a-z][a-z0-9_\-]+$/i;
            if(!reg.test(value)){
                return false;
            }
            return true;
        },
        validate_name:function(value){
            //只支持中文和英文
            var reg = /^[\u4e00-\u9fa5a-zA-Z]+$/i;
            if(!reg.test(value)){
                return false;
            }
            return true;
        },
        ajax:function(event,options){
            $.ajax({
                url: options.url,
                type: options.ajaxType,
                dataType: 'JSON',
                data:options.data
            })
            .done(function(data) {
                if(data.status == 1){
                    $("body").append(data.data.tmp);
                }
            });
        }
    }

    $.fn.Validate = function(method) {
        if(methods[method]) {
            return methods[method].apply(this, arguments);
        } else if( typeof(method) == 'object' || !method ) {
           return methods.init.apply(this, arguments);
        } else {
            $.error( 'Method ' +  method + ' does not exist on jQuery.Validate' );
            return this;
        }
    }
}(window.jQuery);