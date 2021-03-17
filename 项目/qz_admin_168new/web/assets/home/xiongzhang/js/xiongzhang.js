/*
* @Author: qz_dc
* @Date:   2018-07-13 17:21:55
* @Last Modified by:   qz_chk
* @Last Modified time: 2018-07-19 11:33:32
*/
$(document).ready(function() {
    var htmlNode="";
    //开关
    $('.switch input').bootstrapSwitch({
        onText: "是",
        offText: "否",
        onColor: "info",
        offColor: "danger",
        onSwitchChange:function(e, data){
            var $el = $(e.target);
            if($el.attr("checked") == "checked"){
                $el.attr("checked",false);
            }else{
                $el.attr("checked","checked")
            }

            $.ajax({
                url: '/xiongzhang/switchreply',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    enabled:$el.attr("checked") == "checked"?1:0
                }
            });
        }
    });

    $(".reply-box > div").click(function(event) {
        var _this = $(this);
        var index = $(".reply-box > div").index(_this);
        _this.addClass('current').siblings('div').removeClass('current');
        $("[class*='-reply-box']").hide();
        switch(index){
            case 0:
                //被关注回复
                $('.guanzhu-reply-box').show();
                break;
            case 1:
                //收到消息回复
                $('.shoudao-reply-box').show();
                break;
            case 2:
                //关键词回复
                $('.guanjian-reply-box').show();
                break;
        }
    });

    //添加回复
    $('#add-reply').click(function(event) {
        $('.search-list').hide();
        $('.edit-list').show();
        var tab = $(".reply-box > div").index($(".reply-box .current"));
        var target = $("[class*='-reply-box']").eq(tab);
        $(".shanchu",target).trigger('click');
        $('input[name=id]').val("");
        $("#keyword-form")[0].reset();
    });

    //搜索素材
    $("input[name=search-sucai]").keydown(function(event) {
        if(event.keyCode==13){
            if ($("#search-container")){
                $("#search-container").remove();
            }
            var subStr=$(this).val();
            $(".sucai-content").append("<div id='search-container'></div>");
            $("").html("").fadeIn(0);
            $('#sc-contanier').fadeOut(0);
            for(var i=0; i<$("#sc-contanier .sucai-item").length; i++){
                var searchStr=$("#sc-contanier .sucai-item").eq(i).children('.sc-detail').text().trim("");
                if(searchStr.match(subStr)){
                     var htmlNode="<div class='sucai-item' data-select='false'>"+$("#sc-contanier .sucai-item").eq(i).html().trim("")+"</div>";
                     $("#search-container").append(htmlNode);
                }
            }

            var $container = $('#search-container');
            $container.imagesLoaded(function() {
                $container.masonry({
                    itemSelector: '.sucai-item',
                    isAnimated: true,
                });
            });

        }
    });

    //选择素材
    $(".add-tuwen").click(function(event) {
        $("#mask, #sucai-box").fadeIn();
        container.imagesLoaded(function() {
            container.masonry({
                itemSelector: '.sucai-item',
                isAnimated: true,
            });
        });
    });

    //选择图片
    $(".add-img").click(function(event) {
        var tab = $(".reply-box > div").index($(".reply-box .current"));
        var target = $("[class*='-reply-box']").eq(tab);

        $("#mask").fadeIn();
        $("#img-box").fadeIn();
        var imgContainer = $('#sc-img-contanier',$("#img-box"));
        imgContainer.imagesLoaded(function() {
            imgContainer.masonry({
                itemSelector: '.sucai-img-item',
                isAnimated: true,
            });
        });
    });

    //图文切换
    $(".text-type").click(function(event) {
        var _this =  $(this);
        _this.addClass('text-active').siblings('span').removeClass('text-active');
        var subType = _this.attr("data-type");

        var tab = $(".reply-box > div").index($(".reply-box .current"));
        var target = $("[class*='-reply-box']").eq(tab);

        $(".tuwen",target).hide();
        $(".textarea",target).removeClass('showBlock');
        $(".img").hide();
        $(".baocun").attr("data-sub-type",subType);
        if (subType == "view_text") {
            $(".textarea",target).addClass('showBlock');
        } else if (subType == "view_limited") {
            $(".tuwen",target).show();
            if ($(".tw-content").html().trim() == "") {
                $(".add-tuwen").show();
            }
        } else if (subType == "view_image") {
            $(".img").show();
            if ($(".tw-img-content").html().trim() == "") {
                $(".add-img").show();
            }
        }
    });

    //选中素材
    $("#sc-contanier").on("click",".sucai-item",function(){
        var select = $(this).attr("data-select");
        htmlNode = $(this).prop("outerHTML");
        $(".sucai-itme-mask").remove();
        if(select=="true"){
            $(this).attr("data-select","false").find(".sucai-itme-mask").remove();
        }else{
            $(this).children('.sc-detail').append("<div class='sucai-itme-mask'><div class='center-i'><i class='fa fa-check'></i></div></div>").attr("data-select","true");
            $(".sucai-itme-mask").height($(this).find(".sc-detail").outerHeight());
            $(this).siblings('.sucai-item').attr("data-select","false").find(".sucai-itme-mask").remove();
            var index = $(".reply-box > div").index($(".reply-box .current"));
            var parent = $("[class *= '-reply-box']").eq(index);
            $(".baocun",parent).attr("data-media", $(this).attr("data-id"));
        }
    });

    $("#sc-img-contanier").on("click",".sucai-item",function(){
        var select = $(this).attr("data-select");
        htmlNode = $(this).prop("outerHTML");
        $(".sucai-itme-mask").remove();
        if(select=="true"){
            $(this).attr("data-select","false").find(".sucai-itme-mask").remove();
        }else{
            $(this).children('.sc-detail').append("<div class='sucai-itme-mask'><div class='center-i'><i class='fa fa-check'></i></div></div>").attr("data-select","true");
            $(".sucai-itme-mask").height($(this).find(".sc-detail").outerHeight());
            $(this).siblings('.sucai-item').attr("data-select","false").find(".sucai-itme-mask").remove();
            var index = $(".reply-box > div").index($(".reply-box .current"));
            var parent = $("[class *= '-reply-box']").eq(index);
            $(".baocun",parent).attr("data-media", $(this).attr("data-id"));
        }
    });


    $("#select-sc").click(function(){
        if (htmlNode=="") {
            alert("请选择素材");
            return
        }
        var tab = $(".reply-box > div").index($(".reply-box .current"));
        var target = $("[class*='-reply-box']").eq(tab);
        $(".add-tuwen",target).fadeOut();
        $("#mask,#sucai-box").fadeOut();
        $(".tw-content",target).html(htmlNode).fadeIn();
        $(".tw-content",target).find('.sucai-item').attr("style","");
        $(".tw-content",target).find(".sucai-itme-mask").remove();
    });

    $("#select-img-sc").click(function(){
        if (htmlNode=="") {
            alert("请选择素材");
            return
        }
        var tab = $(".reply-box > div").index($(".reply-box .current"));
        var target = $("[class*='-reply-box']").eq(tab);
        $(".add-img",target).fadeOut();
        $("#mask,#img-box").fadeOut();
        $(".tw-img-content",target).html(htmlNode).fadeIn();
        $(".tw-img-content",target).find('.sucai-item').attr("style","");
        $(".tw-img-content",target).find(".sucai-itme-mask").remove();
    });

    $("#cancel-sc,#close-sec,#cancel-img-sc").click(function(event) {
        var index = $(".reply-box > div").index($(".reply-box .current"));
        var target = $("[class*='-reply-box']").eq(index);
        $("#mask,#sucai-box,#img-box").fadeOut();
        $(".add-tuwen",target).fadeIn();
    });

    //文本框赋值
    $(".textarea").on("input propertychange",function(){
        var index = $(".reply-box > div").index($(".reply-box .current"));
        var target = $("[class*='-reply-box']").eq(index);
        $(".baocun",target).attr("data-msg",$(this).val());
    });

    //删除操作
    $(".shanchu").click(function(event) {
        var index = $(".reply-box > div").index($(".reply-box .current"));
        var target = $("[class*='-reply-box']").eq(index);
        $(".textarea",target).val("");
        $(".tw-content,.tw-img-content",target).html("");
        $(".add-tuwen,.add-img",target).show();
        $('.baocun',target).attr("data-msg","").attr("data-media","");
    });

    $(".baocun").click(function(event) {
        var index = $(".reply-box > div").index($(".reply-box .current"));
        var target = $("[class*='-reply-box']").eq(index);
        var subType = $(this).attr("data-sub-type");
        var param =  {
            type:$(this).attr("data-type"),
            media_id:$(this).attr("data-media"),
            msg:$(this).attr("data-msg"),
            subType:$(this).attr("data-sub-type"),
            keyword:$("input[name=keyword]").val(),
            rule:$("input[name=rule]").val(),
            id:$("input[name=id]").val()
        };

        $.ajax({
            url: '/xiongzhang/reply/',
            type: 'POST',
            dataType: 'JSON',
            data:param
        })
        .done(function(data) {
            if (data.status == 1) {
                $(".back",target).trigger('click');
                var tr = $("<tr></tr>");
                var td = $("<td></td>");
                td.html(param.rule);
                tr.append(td);
                td = $("<td></td>");
                td.html(param.keyword);
                tr.append(td);
                td = $("<td></td>");
                switch(subType){
                    case "view_text":
                        td.html(param.msg);
                        break;
                    case "view_image":
                        td.html("图片");
                        break;
                    case "view_limited":
                        td.html("图文");
                        break;
                }

                tr.append(td);
                td = $('<td><i class="fa fa-pencil-square-o" data-id="'+data.data+'"></i><i class="fa fa-trash-o" data-id="'+data.data+'"></i></td>');
                tr.append(td);
                $("#keyword-table tbody").prepend(tr);
            }
        });
    });

    $(".back").click(function(event) {
        $(".search-list").show();
        $(".edit-list").hide();
    });

    $("body").on("click",".fa-pencil-square-o",function(event) {
        var id = $(this).attr("data-id");
        var index = $(".reply-box > div").index($(".reply-box .current"));
        var target = $("[class*='-reply-box']").eq(index);
        $.ajax({
            url: '/xiongzhang/findkeyword/',
            type: 'POST',
            dataType: 'JSON',
            data: {id: id}
        })
        .done(function(data) {
            if (data.status == 1) {
                var data = data.data;
                $('.edit-list',target).show();
                $("input[name=keyword]").val(data.keyword);
                $("input[name=rule]").val(data.rule);
                $("input[name=id]").val(data.id);
                $(".add-tuwen,.add-img,.tw-img-content,.tw-content,.img,.tuwen,.search-list",target).hide();
                $(".text-active",target).removeClass("text-active");
                $(".textarea",target).removeClass('showBlock');


                switch(data.msgtype){
                    case "text":
                        $(".textarea",target).val(data.content).addClass('showBlock');
                        $(".text-type",target).eq(0).addClass('text-active');
                        $(".baocun",target).attr("data-msg",data.content);
                        $(".baocun",target).attr("data-sub-type","view_text");
                        break;
                    case "image":
                        $(".img,.tw-img-content").show();
                        var info = data.media;
                        var src = info.src;
                        if (src == null || src == "") {
                            src = "/assets/home/xiongzhang/img/placeholder-xiongzhang.png";
                        }
                        var tmp =  '<div class="sucai-item" data-id="'+info.media_id+'" data-select="false"><div class="sc-detail"><img  src="'+src+'" /><span>'+info.title+'</span></div></div>';
                        $(".tw-img-content",target).html( tmp );
                        $(".text-type",target).eq(2).addClass('text-active');
                        $(".baocun",target).attr("data-media",data.content);
                        $(".baocun",target).attr("data-sub-type","view_image");
                        break;
                    case "mpnews":
                        $(".tuwen,.tw-content").show();
                        var info = data.media;
                        var src = info.src;
                        if (src == null || src == "") {
                            src = "/assets/home/xiongzhang/img/placeholder-xiongzhang.png";
                        }
                        var tmp =  '<div class="sucai-item" data-id="'+info.media_id+'" data-select="false"><div class="sc-detail"><img  src="'+src+'" /><span>'+info.title+'</span></div></div>';
                        $(".tw-content",target).html( tmp );
                        $(".text-type",target).eq(1).addClass('text-active');
                        $(".baocun",target).attr("data-media",data.content);
                        $(".baocun",target).attr("data-sub-type","view_limited");
                        break;
                }
            }
        });
    });

    $("body").on("click",".fa-trash-o",function(event) {
        var id = $(this).attr("data-id");
        var target = $(this).parents("tr");
        if (confirm("确定删除该规则吗？")) {
            $.ajax({
                url: '/xiongzhang/removekeyword',
                type: 'POST',
                dataType: 'JSON',
                data: {id,id}
            })
            .done(function(data) {
                if (data.status == 1) {
                    target.remove();
                }
            });
        }
    });
});

function bind_content(data,target) {
    if (data == "null") {
        return false;
    }
    data = JSON.parse(data);
    var button = $(".baocun",target);
    switch(data.msgtype) {
        case "text":
            $(".textarea",target).val(data.content);
            button.attr("data-msg",data.content);
            button.attr("data-sub-type","view_text");
            break;
        case "image":
            var src = data.media.src == null?'/assets/home/xiongzhang/img/placeholder-xiongzhang.png':data.media.src;
            var tmp = '<div class="sucai-item" data-id="'+ data.content +'"  data-select="false"><div class="sc-detail"><img  src="'+src+'" /><span>'+data.media.title+'</span></div></div>';
            $(".tw-img-content",target).html(tmp);
            button.attr("data-media",data.content);
            button.attr("data-sub-type","view_image");
            break;
        case "mpnews":
            var src = data.media.src == null?'/assets/home/xiongzhang/img/placeholder-xiongzhang.png':data.media.src;
            var tmp = '<div class="sucai-item" data-id="'+ data.content +'"  data-select="false"><div class="sc-detail"><img  src="'+src+'" /><span>'+data.media.title+'</span></div></div>';
            $(".tw-content",target).html(tmp);
            button.attr("data-media",data.content);
            button.attr("data-sub-type","view_limited");
            break;
    }

    switch_content(target);
}

function switch_content(target) {
    var subType = $(".baocun",target).attr("data-sub-type");
    $(".text-active",target).removeClass('text-active');
    $(".tuwen",target).hide();
    $(".textarea",target).removeClass('showBlock');

    switch(subType) {
        case "view_text":
            $("[data-type=view_text]",target).addClass('text-active');
            $(".textarea",target).addClass('showBlock');
            break;
        case "view_image":
            $("[data-type=view_image]",target).addClass('text-active');
            $(".add-img",target).hide();
            $(".img",target).show();
            $(".tw-img-content",target).show();
            break;
        case "view_limited":
            $("[data-type=view_limited]",target).addClass('text-active');
            $(".tuwen",target).show();
            $(".add-tuwen",target).hide();
            $(".tw-content",target).show();
            break;
    }

}