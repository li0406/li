$(function(){
    $.fn.popVerify = function(callback) {
        var _this = this;
        var top = _this.offset().top;
        var left = _this.offset().left;
        $(".popOut").remove();
        _this.attr("disabled","disabled");
        var popOut = $("<div></div>");
        var popIn = $("<div></div>");
        popIn.addClass('popIn');
        popOut.addClass('popOut');
        popOut.offset({
            left:left,
            top:top-_this.outerHeight()-50
        }).css("position","absolute");
        var div = $("<div></div>");
        var code = $("<img></img>");
        code.attr("src","/verify/"+Math.random());
        code.appendTo(div);
        code.click(function(event) {
            $(this).attr("src","/verify/"+Math.random());
        });
        var text = $("<a href='javascript:void(0)'></a>");
        text.html("换一张");
        text.click(function(event) {
           code.attr("src","/verify/"+Math.random());
        });
        text.appendTo(div);
        div.appendTo(popIn);
        var div = $("<div></div>");
        var input = $("<input type='text' maxlength='4'/>");
        input.appendTo(div);
        var btn = $("<button></button>");
        btn.html("确认");
        btn.click(function(event) {
            if(input.val() == "" || input.val().length == 0){
                input.focus();
                return false;
            }

            if(typeof callback == "function"){
                callback(input);
            }
        });
        btn.appendTo(div);
        div.appendTo(popIn);
        var em = $("<em></em>");
        em.addClass('arrar_bg')
        em.appendTo(popIn);
        var em = $("<em></em>");
        em.appendTo(popIn);
        popIn.appendTo(popOut);
        $("body").append(popOut);
    };
});