$(function(){
    $(".tk-top textarea").bind("input propertychange",function(){
        var length = $(this).val().length;
        var _this = $(this);
        $(".rel_note .word").html(length+"/200");
    });

    $("#btnReplay").click(function(event) {
        var parent = $(this).parents(".tk-top");
        var rel_id = $(this).attr("data-rel");
        var module = $(this).attr("data-module");
        $(".login_k_acc").hide();
        $(".login_k_pass").hide();

        $.ajax({
            url: '/reply/',
            type: 'POST',
            dataType: 'JSON',
            data: {
                 module: module,
                 content:$("textarea",parent).val(),
                 rel_id:rel_id,
                 name:$(".rel_login input[name=name]", parent).val(),
                 password:$(".rel_login input[name=password]", parent).val()
            }
        })
        .done(function(data) {
            if (data.status == 1) {
                window.location.href =  window.location.href;
            } else if (data.status == 2){
                window.location.href = geturl +'/login?redirect='+window.location.href
                // $(".rel_login input[name=name]", parent).focus();
                // $(".login_k_acc").show();
            } else if (data.status == 3){
                window.location.href = geturl +'/login?redirect='+window.location.href
                // $(".rel_login input[name=password]", parent).focus();
                // $(".login_k_pass").show();
            }else if (data.status == 3){
               $("textarea",parent).focus();
            }else{
                alert(data.info);
            }
        });
    });

    $(".tk-list .login_btn").click(function(event) {
        var parent = $(this).parents(".rel_form");
        var reply_id = $(this).attr("data-reply");
        var rel_id = $(this).attr("data-rel");
        var module = $(this).attr("data-module");
        $(".login_k_acc").hide();
        $(".login_k_pass").hide();
        $.ajax({
            url: '/reply/',
            type: 'POST',
            dataType: 'JSON',
            data: {
                 module: module,
                 content:$("textarea",parent).val(),
                 rel_id:rel_id,
                 name:$(".rel_login input[name=name]", parent).val(),
                 password:$(".rel_login input[name=password]", parent).val(),
                 reply_id:reply_id
            }
        })
        .done(function(data) {
            if (data.status == 1) {
                window.location.href =  window.location.href;
            } else if (data.status == 2){
                window.location.href = geturl +'/login?redirect='+window.location.href
                // $(".rel_login input[name=name]", parent).focus();
                // $(".login_k_acc").show();
            } else if (data.status == 3){
                window.location.href = geturl +'/login?redirect='+window.location.href
                // $(".rel_login input[name=password]", parent).focus();
                // $(".login_k_pass").show();
            }else if (data.status == 3){
               $("textarea",parent).focus();
            }else{
                alert(data.info);
            }
        });
    });

    $(".tk-list .fa-thumbs-o-up").click(function(event) {
        var parent = $(this).parents(".rel_form");
        var _this = $(this);
        $.ajax({
            url: '/replyup/',
            type: 'POST',
            dataType: 'JSON',
            data: {
                id:_this.attr("data-id")
            }
        })
        .done(function(data) {
            if (data.status == 1) {
                _this.unbind('click');
                 _this.parent().addClass('unbind');
                var i = _this.next("span").text();
                i = parseInt(i) + 1;
                _this.next("span").html(i);
            }
        });
    });

    $(".tk-list .fa-thumbs-o-down").click(function(event) {
        var parent = $(this).parents(".rel_form");
        var _this = $(this);
        $.ajax({
            url: '/replydown/',
            type: 'POST',
            dataType: 'JSON',
            data: {
                id:_this.attr("data-id")
            }
        })
        .done(function(data) {
            if (data.status == 1) {
                _this.unbind('click');
                _this.parent().addClass('unbind');
                var i = _this.next("span").text();
                i = parseInt(i) + 1;
                _this.next("span").html(i);
            }
        });
    });
    // 541 需求暂时关闭回复功能
    // $(".tk-list .res_btn").click(function(){

    //     var parent = $(this).parents(".main-msg");
    //     if (parent.find(".rel_form").is(":hidden")) {

    //         parent.find(".rel_form").show();
    //     } else {
    //         parent.find(".rel_form").hide();
    //     }
    // });

    $(".login_hezuo a").click(function(event) {
        getAccount();
    });

    var interval = null;
    function getAccount(){
        interval = setInterval(function(){
            $.ajax({
                url: '/run/',
                type: 'GET',
                dataType: 'JSON'
            })
            .done(function(data) {
                if(data.status == 1){
                    clearInterval(interval);
                    window.location.href = window.location.href;
                }
            })
            .fail(function(xhr) {
                clearInterval(interval);
            });
        },3000);
    }
});
