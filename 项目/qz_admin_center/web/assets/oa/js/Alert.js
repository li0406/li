/**
 * 自定义ALERT弹出框
 * @param  {[type]} $ [description]
 * @return {[type]}   [description]
 */
!function($){
     var methods = {
        init: function(options){
            /**
             * level 级别 1.成功 2.错误 3.警告 4.正常
             * msg 提示内容
             * @type {Object}
             */
            var defaults = {
                level:1,
                msg:"",
                isTop:false
            }
            var _this = this;
            var options =  $.extend({},defaults, options);
            var style = "alert-success";

            switch(options.level){
                case 1:
                    style = "alert-success";
                break;
                case 2:
                    style = "alert-danger";
                break;
                case 3:
                    style = "alert-warning";
                break;
                case 4:
                    style = "alert-info";
                break;
            }

            var close = $("<a href='#' class='close'>&times;</a>");
            var alert = $("<div class='alert "+style+"'></div>");
            if(options.isTop){
                alert.addClass('isTop');
            }else{
                alert.removeClass('isTop');
            }
            alert.append(close);
            alert.append(options.msg);
            close.click(function(event) {
                alert.remove();
            });
            var t = setTimeout(function(){
                 clearTimeout(t);
                 alert.remove();
            }, 3000);
            $("body").append(alert);
        }
    }

    $.fn.Alert = function(method) {
        if(methods[method]) {
            return methods[method].apply(this, arguments);
        } else if( typeof(method) == 'object' || !method ) {
           return methods.init.apply(this, arguments);
        } else {
            $.error( 'Method ' +  method + ' does not exist on Alert' );
            return this;
        }
    }

}(window.jQuery);