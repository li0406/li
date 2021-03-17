(function ($) {
    var methods = {
        init:function(options){
            var defaults = {
                target:"checkbox"
            }
            var options =  $.extend({},defaults, options);
            var _this = $(this);
            return this.each(function(){

                //判断是否选中
                rdochecked();

                // //单选or多选
                if (options.target == "radio") {
                    //单选
                    _this.find(".rdobox").click(function () {

                        if ($(this).prev().prop("checked") == true) {
                            $(this).prev().removeAttr("checked");
                        } else {
                            $(this).prev().prop("checked", "checked");
                        }
                        rdochecked();
                    });
                } else {
                    //多选
                    _this.find(".rdobox").click(function () {
                        if ($(this).prev().prop("checked") == true) {
                            $(this).prev().removeAttr("checked");
                        }
                        else {
                            $(this).prev().prop("checked", true);
                        }
                        rdochecked();
                    });
                }

                //判断是否选中
                function rdochecked() {
                    var checkGroup = _this.find("input[type="+options.target+"]");
                    checkGroup.each(function (i) {
                        var rdobox = checkGroup.eq(i).next();

                        if (checkGroup.eq(i).prop("checked") == false) {
                            rdobox.removeClass("checked");
                            rdobox.addClass("unchecked");
                        } else {
                            rdobox.removeClass("unchecked");
                            rdobox.addClass("checked");
                        }
                    });
                }
            });
        }

    }

    $.fn.labelauty = function(method) {
        if(methods[method]) {
            return methods[method].apply(this, arguments);
        } else if( typeof(method) == 'object' || !method ) {
           return methods.init.apply(this, arguments);
        } else {
            $.error( 'Method ' +  method + ' does not exist on jQuery.goalProgress' );
            return this;
        }
    }
}(jQuery));