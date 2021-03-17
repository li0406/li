$.fn.Alert = function(options){
    var defaults = {
        css:{},
        text:"",
        speed:1500,
        callback:null
    }
    $(".alert-box").remove();
    var options = $.extend({},defaults,options);
    var _this = this;
    var alertBox = $("<div class='alert-box'></div>");
    alertBox.css(options.css);
    alertBox.html(options.text);
    alertBox.animate({
        opacity:"0"
    },options.speed,function(){
        alertBox.remove();
        if(typeof options.callback == "function"){
            options.callback();
        }
    });
    $("body").append(alertBox);
}