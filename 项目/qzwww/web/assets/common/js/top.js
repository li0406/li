$(function(){
    $.ajax({
        url: '/header_city',
        type: 'GET',
        dataType: 'JSON',
      })
      .done(function(data) {
          if (data.error_code == 200) {
            var hotCity = data.list.hotCity;
            if(hotCity.length > 35){
                hotCity.slice(0,34);
            }
            var oneArr = data.list.accordCity.one.slice(0,35);
            var twoArr = data.list.accordCity.two.slice(0,35);
            var threeArr = data.list.accordCity.three.slice(0,35);
            var hotTpl = '<li><a rel="nofollow" target="_blank" href="'+ baseUrl +'">全国</a></li>',oneTpl = '',twoTpl = '',threeTpl = '';
            for(var i = 0;i < hotCity.length;i++){
                hotTpl += '<li><a rel="nofollow" target="_blank" href="'+ http + hotCity[i].bm+'.'+ domain +'/">'+ hotCity[i].cname +'</a></li>'
            }
            for(var i = 0;i < oneArr.length;i++){
                oneTpl += '<li><a rel="nofollow" target="_blank" href="'+ http + oneArr[i].bm+'.'+ domain +'/">'+ oneArr[i].cname +'</a></li>'
            }
            for(var i = 0;i < twoArr.length;i++){
                twoTpl += '<li><a rel="nofollow" target="_blank" href="'+ http + twoArr[i].bm+'.'+ domain +'/">'+ twoArr[i].cname +'</a></li>'
            }
            for(var i = 0;i < threeArr.length;i++){
                threeTpl += '<li><a rel="nofollow" target="_blank" href="'+ http + threeArr[i].bm+'.'+ domain +'/">'+ threeArr[i].cname +'</a></li>'
            }

            oneTpl += '<li><a rel="nofollow" target="_blank" href="'+ baseUrl +'/city/">更多城市</a></li>';
            twoTpl += '<li><a rel="nofollow" target="_blank" href="'+ baseUrl +'/city/">更多城市</a></li>';
            threeTpl += '<li><a rel="nofollow" target="_blank" href="'+ baseUrl +'/city/">更多城市</a></li>';
            $('.hot-list ul').html(hotTpl);
            $('.list1 ul').html(oneTpl);
            $('.list2 ul').html(twoTpl);
            $('.list3 ul').html(threeTpl);
          }
      });

    $('.pub-top .city').on('mouseover',function(){
        $(this).addClass('on');
        $(this).find('.city-list').show();
    });
    $('.pub-top .city').on('mouseout',function(){
        $(this).removeClass('on');
        $(this).find('.city-list').hide();
    });
    $('.pub-top .nav-name').on('mouseover',function(){
        $(this).addClass('on').siblings('.nav-name').removeClass('on');
        $(this).find('.list-box').show();
    });
    $('.pub-top .nav-name').on('mouseout',function(){
        $(this).removeClass('on');
        $(this).find('.list-box').hide();
    });
    $('.city-tabs span').on('mouseover',function(){
        $(this).addClass('act').siblings('span').removeClass('act');
        var index = $(this).index();
        switch(index){
            case 0:
                $('.hot-list').show().siblings('.lists').hide();
                break;
            case 1:
                $('.list1').show().siblings('.lists').hide();
                break;
            case 2:
                $('.list2').show().siblings('.lists').hide();
                break;
            case 3: 
                $('.list3').show().siblings('.lists').hide();
                break;
        }
    });
    $('.c-center').on('mouseover',function(){
        $(this).addClass('on');
        $(this).find('.list-box').show();
    });
    $('.pub-top .c-center').on('mouseout',function(){
        $(this).removeClass('on');
        $(this).find('.list-box').hide();
    });
    $('.menu .u-div').on('mouseover',function(){
        $(this).find('.name').addClass('on');
        $(this).find('.more-info').show();
    });
    $('.menu .u-div').on('mouseout',function(){
        $(this).find('.name').removeClass('on');
        $(this).find('.more-info').hide();
    });
    $('.menu .icon-phone').on('mouseover',function(){
        $(this).find('.down-box').show();
    })
    $('.menu .icon-phone').on('mouseout',function(){
        $(this).find('.down-box').hide();
    });
    $('.menu .icon-wechat').on('mouseover',function(){
        $(this).find('.wx-box').show();
    });
    $('.menu .icon-wechat').on('mouseout',function(){
        $(this).find('.wx-box').hide();
    });
    $('.menu .icon-call').on('mouseover',function(){
        $(this).find('.call-box').show();
    });
    $('.menu .icon-call').on('mouseout',function(){
        $(this).find('.call-box').hide();
    });

})