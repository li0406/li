/*
* @Author: qz_dc
* @Date:   2018-09-17 17:35:18
* @Last Modified by:   qz_lb
* @Last Modified time: 2018-09-28 15:31:52
*/
$(function(){
    //tab切换
    $('.tabqiehuan').click(function(event) {
        var _this = $(this);
        _this.addClass('tabcurr').siblings('span').removeClass('tabcurr');
        var tabindex=$(this).index();
        //0是处理，1是处理记录
        if(tabindex==0){
            $('.con').show();
            $('.record').hide();
        }else if(tabindex==1){
            $('.record').show();
            $('.con').hide();
        }
    });

    // 取消、返回
    $('.quxiao').click(function(event) {
        window.location = '/yxb/suggest';
    });
    $('.back').click(function(event) {
        window.location = '/yxb/suggest';
    });

    // 保存
    $('.save').click(function(event) {
        var handlerid = $('.handlerid option:selected').val(),
            handlername = $('.handlerid option:selected').text(),
            handlestatus = $('#handlestatus option:selected').val(),
            remarkcontent = $('#remarkcontent').val(),
            feedbackid = $('#feedbackid').val();
        if(handlestatus === ''){
            alert('请选择处理方式');
            return false;
        }
        if(remarkcontent.length<1){
            alert('请输入备注，字数不超过500字。');
            return false;
        }
        $.ajax({
            url: '/yxb/submithandle',
            type: 'Post',
            dataType: '',
            data: {
                handlerid:handlerid,
                handlername:handlername,
                handlestatus:handlestatus,
                remarkcontent:remarkcontent,
                feedbackid:feedbackid,
            },
        })
        .done(function(data) {
            if(data.status==1){
                $('mask').show();
                $('.success').show();

                // 点击确定隐藏成功提示
                $('.sure').click(function(event){
                    $('mask').hide();
                    $('.success').hide();
                    window.location = '/yxb/suggest';
                });
            }
        })
        .fail(function() {
            console.log("error");
        })

    });

    $('#deal').select2({
        language: "zh-CN",
        placeholder:'城市'
    });
})