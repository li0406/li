/*
* @Author: qz_dc
* @Date:   2018-08-15 11:18:58
* @Last Modified by:   qz_dc
* @Last Modified time: 2018-09-29 10:43:54
*/
$(function(){
    //缩略图大图
    $('.thumbs').click(function(){
        var imgUrl = $(this).children('img').attr('src');
        $("#Preview").attr("src",imgUrl);
        $('#myModal').modal('show');
    });
    // console.log("============")
    // console.log($("#liucheng").data("id"));
    // console.log('++++++++++++');
    //初始订单动态值
    var index = $("#liucheng").data("id"),j;
    //是否显示箭头颜色
    for(var i=0;i < index;i++){
        $('.jieduan').children('div').eq(i).addClass('current');
    }
    // 是否显示操作人和时间
    for(var i = 0;i < index;i++){
        if($('.jieduan').children('div').eq(i).hasClass('current')){
            $('.jieduan-info').children('div').eq(i).children('div').show();
        }
    }
    // 施工状态
    for(var i = 0;i < $('.zt').length;i++){
        if(parseInt($('.zt').eq(i).attr('data-index')) == index){
            $('.zt').eq(i).addClass('cur').siblings('div').removeClass('cur');
        }
    }

    $('.part').children('.bl').eq(index-1).addClass('active').siblings('.bl').removeClass('active');
    $('.prev').click(function(event) {
        if($('.cur').attr('data-index') == 1){
            return;
        }else{
            $('.cur').removeClass('cur').prev().addClass('cur');
            j = $('.cur').attr('data-index');
            $('.part').children('.bl').eq(j-1).addClass('active').siblings('.bl').removeClass('active');

        }
    });
    $('.next').click(function(event) {
        if($('.cur').attr('data-index') >= index){
            return;
        }else{
            $('.cur').removeClass('cur').next().addClass('cur');
            var j = $('.cur').attr('data-index');
            $('.part').children('.bl').eq(j-1).addClass('active').siblings('.bl').removeClass('active');

        }
    });

})