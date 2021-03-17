$(function(){
    $('.left-list').on('click','li',function(){
        $(this).addClass('cur').siblings('li').removeClass('cur');
        $(this).prev('li').css('border-bottom-color','#fff').siblings('li').css('border-bottom-color','#e6e6e6');
        $(this).next('li').css('border-bottom-color','#e6e6e6');
        $(this).css('border-bottom-color','#fff');
        var index = $(this).index();
        if(index == 0){
            $('.register').show().siblings('div').hide();
        }else if(index == 1){
            $('.login').show().siblings('div').hide();
        }else if(index == 2){
            $('.account').show().siblings('div').hide();
        }else if(index ==3){
            $('.bind').show().siblings('div').hide();
        }else if(index == 4){
            $('.others').show().siblings('div').hide();
        }
    });
})