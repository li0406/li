/*
* @Author: qz_xsc
* @Date:   2017-08-25 09:35:40
* @Last Modified by:   qz_dc
* @Last Modified time: 2018-07-17 14:54:39
*/
$(document).ready(function(){
    $("#calculator-closed").click(function(){
        $('body,html').css({
                     "position":"relative",
                     "width":"100%",
                     "overflow":"auto",
                     "height":"auto"
                });
        App.setCookie("w_index",1,3600*24);
        $("#calculator,#calculator2").fadeOut(function(){
            $(this).remove();
            location.reload();
        });
        $("body").css('overflow','auto');
        $("body").css("position","relative");
    });

    $("#calculator-exit").click(function(){
        $('body').css("position","relative");
        $("#calculator2").fadeOut(function(){
             $(this).remove();
        });
        $("body").css('overflow','auto');
        $("body").css("position","relative");
        location.reload();
    });
     $("#calculator2-closed").click(function(){
        $('body').css("position","relative");
        $("#calculator2").fadeOut(function(){
             $(this).remove();
        });
        $("body").css('overflow','auto');
        $("body").css("position","relative");
        location.reload();
    });
    $("#calculator-closed3").click(function(){
        $('body,html').css({
            "position":"relative",
            "width":"100%",
            "overflow":"auto",
            "height":"auto"
        });
        App.setCookie("w_index",1,3600*24);
        $("#calculator3").fadeOut(function(){
            $(this).remove();
            location.reload();
        });
        $("body").css('overflow','auto');
        $("body").css("position","relative");
    });

});
