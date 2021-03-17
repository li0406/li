$(function(){
    var center_password_token = $.cookie('center_password_token');
    // 是否有发单
    $.ajax({
        type: 'GET',
        url: baseUrl +'/uc/v1/isordered',
        headers: {
            token: center_password_token
        },
        data: {
            day: 14
        },
        dataType: 'json',
        success: function(res) {
            if(res.error_code == 0){
                $('.needs-list').hide();
            }else{
                $('.needs-list').show();
            }
        },
        error: function(res) {
            console.log(res)
        }
    })
    // 猜你喜欢数据渲染
    $.ajax({
        type: 'GET',
        url: baseUrl +'/uc/v1/guess/gettu',
        dataType: 'json',
        success: function(res) {
            if(res.error_code == 0){
                var tpl = '';
                $.each(res.data,function (index,item) {
                    tpl += '<li><a href="'+item.pageurl +'" target="_blank"><img src="'+ item.meitu_url +'" alt="'+ item.meitu_title +'"><div class="a-title">'+ item.meitu_title +'</div></a></li>'
                });
                $('.zx-lg ul').html(tpl)
            }else{
                alert(res.error_msg)
            }
        },
        error: function(res) {
            console.log(res)
        }
    })
 
    $('.sp-ds').on('click','.like-num',function(e){
        e.preventDefault();
        var id = $(this).attr('data-id');
        var _this = $(this)
        if(!$(this).find('i').hasClass('icon-heart')){
            $.ajax({
                type: 'POST',
                url: baseUrl +'/uc/v1/video/likeaction',
                dataType: 'json',
                data: {
                    id:id
                },
                success: function(res) {
                    if(res.error_code == 0){
                        _this.find('.icon').addClass('icon-heart');
                        var num = parseInt(_this.find('em').text()) + 1;
                        _this.find('em').html(num);
                    }else{
                        alert(res.error_msg)
                    }
                },
                error: function(res) {
                    console.log(res)
                }
            })
        }else{
            $.ajax({
                type: 'POST',
                url: baseUrl +'/uc/v1/video/nolikeaction',
                dataType: 'json',
                data: {
                    id:id
                },
                success: function(res) {
                    if(res.error_code == 0){
                        _this.find('.icon').removeClass('icon-heart');
                        var num = parseInt(_this.find('em').text()) - 1;
                        _this.find('em').html(num);
                    }else{
                        alert(res.error_msg)
                    }
                },
                error: function(res) {
                    console.log(res)
                }
            })
        }
        
    });
    $('.tabs li').click(function(){
        $(this).addClass('active').siblings('li').removeClass('active');
        var index = $(this).index();
        $(this).parent().parent().siblings('.tab-main').find('.tab-item').eq(index).show().siblings().hide();
        if(index != 0){
            if(index == 1){
                $.ajax({
                    type: 'GET',
                    url: baseUrl +'/uc/v1/guess/getvideo',
                    dataType: 'json',
                    success: function(res) {
                        if(res.error_code == 0){
                            var tpl = '';
                            $.each(res.data,function (index,item) {
                                tpl += '<li>'+
                                        '<a href="'+ item.pageurl +'" target="_blank">'+
                                            '<img src="'+ item.video_face +'" alt="">'+
                                            '<div class="icon-shipin"></div>'+
                                            '<div class="s-title">'+ item.video_title +'</div>'+
                                            '<div class="s-info">'+ item.video_descriable +'</div>'+
                                            '<div class="v-l clearfix">'+
                                                '<span class="eye-num"><i class="icon-eye"></i>'+ item.video_pv +'</span>'+
                                                '<span class="like-num" data-id="'+ item.id +'"><i class="icon"></i><em>'+ item.video_like +'</em></span>'+
                                            '</div>'+
                                        '</a>'+
                                        '</li>'
                            });
                            $('.sp-ds ul').html(tpl);
                        }else{
                            alert(res.error_msg)
                        }
                    },
                    error: function(res) {
                        console.log(res)
                    }
                })
            }else if(index == 2){
                $.ajax({
                    type: 'GET',
                    url: baseUrl +'/uc/v1/guess/getcases',
                    dataType: 'json',
                    data: {
                        cs: $('#getCity').attr('data-cityid')
                    },
                    success: function(res) {
                        if(res.error_code == 0){
                            var tpl = '';
                            $.each(res.data,function (index,item) {
                                if(item.huxing == ''){
                                    huxing = '';
                                } else {
                                    if(item.mianji == ''){
                                        huxing = item.huxing;
                                    }else{
                                        huxing = '/'+ item.huxing;
                                    }
                                }
                                if(item.fengge == ''){
                                    fengge = '';
                                } else {
                                    if(item.mianji == ''&&item.huxing == ''){
                                        fengge = item.fengge;
                                    }else{
                                        fengge = '/' + item.fengge;
                                    }
                                }
                                tpl += '<li>'+
                                        '<a href="'+ item.pageurl +'" target="_blank">'+
                                            '<img src="'+ item.case_face+'" alt="">'+
                                            '<div class="s-title">'+ item.case_title+'</div>'+
                                            '<div class="s-info">'+ item.company_name+'</div>'+
                                            '<div class="style">'+ item.mianji + huxing + fengge +'</div>'+
                                            '<div class="fb-time">发布时间：'+ item.release_time+'</div>'+
                                        '</a>'+
                                        '</li>'
                            });
                            $('.zs-al ul').html(tpl);
                        }else{
                            alert(res.error_msg)
                        }
                    },
                    error: function(res) {
                        console.log(res)
                    }
                })
            }else if(index == 3){
                $.ajax({
                    type: 'GET',
                    url: baseUrl +'/uc/v1/guess/getgonglue',
                    dataType: 'json',
                    success: function(res) {
                        if(res.error_code == 0){
                            var tpl1 = '',tpl2 = '' ,tpl3 = '';
                            $.each(res.data.one,function (index,item) {
                                tpl1 += '<li><a href="'+ qzUrl + item.page_url +'" target="_blank"><i class="yuan"></i>'+ item.title +'</a></li>'
                            });
                            $.each(res.data.two,function (index,item) {
                                tpl2 += '<li><a href="'+ qzUrl+item.page_url +'" target="_blank"><i class="yuan"></i>'+ item.title +'</a></li>'
                            });
                            $.each(res.data.three,function (index,item) {
                                tpl3 += '<li><a href="'+ qzUrl+item.page_url +'" target="_blank"><i class="yuan"></i>'+ item.title +'</a></li>'
                            });
                            $('.stage-one ul').html(tpl1);
                            $('.stage-two ul').html(tpl2);
                            $('.stage-three ul').html(tpl3);
                        }else{
                            alert(res.error_msg)
                        }
                    },
                    error: function(res) {
                        console.log(res)
                    }
                })
            }else if(index == 4){
                $.ajax({
                    type: 'GET',
                    url: baseUrl +'/uc/v1/guess/getwenda',
                    dataType: 'json',
                    success: function(res) {
                        if(res.error_code == 0){
                            var tpl1 = '',tpl2 = '' ,tpl3 = '';
                            $.each(res.data.hot,function (index,item) {
                                tpl1 += '<div class="question"><a href="'+ qzUrl+item.pageurl +'" target="_blank"><img src="'+ item.face +'" alt=""><p>'+ item.title +'</p></a></div>'
                            });
                            $.each(res.data.new,function (index,item) {
                                tpl2 += '<li>'+
                                            '<a href="'+ qzUrl+item.pageurl +'" target="_blank">'+
                                            '<div class="li-title"><span>【'+ item.category +'】</span>'+ item.title +'</div>'+
                                            '<div class="li-info">'+ item.description +'</div>'+
                                            '<div class="li-reply">'+ item.nickname +'<span>于</span>'+ item.post_time +'<span>回复了该问题</span></div>'+
                                            '</a>'
                                        '</li>'
                            });
                            $.each(res.data.wait,function (index,item) {
                                tpl3 += ' <li>'+
                                            '<a href="'+ qzUrl+item.pageurl +'" target="_blank">'+
                                            '<div class="caina">'+
                                                '<div class="cai-num">0</div>'+
                                                '<div>采纳</div>'+
                                            '</div>'+
                                            '<div class="q-a">'+
                                                '<div class="li-q"><span>【'+ item.category +'】</span>'+ item.title +'</div>'+
                                                '<div class="li-a">'+ item.content +'</div>'+
                                            '</div>'+
                                            '<div class="w-time">'+ item.post_time +'</div>'+
                                            '</a>'
                                        '</li>'
                            });
                            $('.wd-img').html(tpl1);
                            $('.wd-ul ul').html(tpl2);
                            $('.wait-ul ul').html(tpl3);
                        }else{
                            alert(res.error_msg)
                        }
                    },
                    error: function(res) {
                        console.log(res)
                    }
                });
            }
        }else{
            $.ajax({
                type: 'GET',
                url: baseUrl +'/uc/v1/guess/gettu',
                dataType: 'json',
                success: function(res) {
                    if(res.error_code == 0){
                        var tpl = '';
                        $.each(res.data,function (index,item) {
                            tpl += '<li><a href="'+ item.pageurl +'" target="_blank"><img src="'+ item.meitu_url +'" alt="'+ item.meitu_title +'"><div class="a-title">'+ item.meitu_title +'</div></a></li>'
                        });
                        $('.zx-lg ul').html(tpl)
                    }else{
                        alert(res.error_msg)
                    }
                },
                error: function(res) {
                    console.log(res)
                }
            })
        }
    });
    $('#go-account').click(function(){
        window.location.href = accountUrl + '/account/home';
    })
})