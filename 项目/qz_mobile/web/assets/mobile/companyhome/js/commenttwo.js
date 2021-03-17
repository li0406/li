$(function () {
    // 更多评论内容
    var strArr = [];
    $('.comments-list').on('click','.more',function(){
        var _this = $(this);
        var index = _this.parent().parent().parent().index();
        var prev = _this.prev();
        var str = prev.text();
        if (_this.text() == '全文') {
            _this.prev().show();
            _this.prev().prev().hide();
            _this.text('收起');
        } else {
            _this.prev().hide();
            _this.prev().prev().show();
            _this.text('全文');
        }
    })
    // function ajaxFun(p, id, type) {
    //     $.ajax({
    //         url: '/getcommentlistbyid',
    //         type: 'GET',
    //         dataType: 'json',
    //         data: {
    //             page: p,
    //             id: id,
    //             type: type
    //         },
    //     })
    //         .done(function (data) {
    //             if (data.error_code == 0) {
    //                 var tpl = '';
    //                 var tpl2 = '';
    //                 var list = data.data.data;
    //                 pageTotal = data.data.page.page_total_number;
    //                 if(list == ''){
    //                     $('.comments-list').html('<div class="no-result"><p>该分类下暂无点评</p><img src="/assets/mobile/companyhome/img/result.png" alt=""></div>')
    //                     $('.load').hide();
    //                     $('#footer').show();
    //                 }else{
    //                     for (let i = 0; i < list.length; i++) {
    //                         strArr.push(list[i].text);
    //                         var num = Math.floor(list[i].totalCount / 2);
    //                         if(num == 0){
    //                             tpl2 = '<i class="fa fa-star red"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>'
    //                         }else if(num == 1){
    //                             tpl2 = '<i class="fa fa-star red"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>'
    //                         }else if(num == 2){
    //                             tpl2 = '<i class="fa fa-star red"></i><i class="fa fa-star red"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>'
    //                         }else if(num == 3){
    //                             tpl2 = '<i class="fa fa-star red"></i><i class="fa fa-star red"></i><i class="fa fa-star red"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>'
    //                         }else if(num == 4){
    //                             tpl2 = '<i class="fa fa-star red"></i><i class="fa fa-star red"></i><i class="fa fa-star red"></i><i class="fa fa-star red"></i><i class="fa fa-star"></i>'
    //                         }else if(num == 5){
    //                             tpl2 = '<i class="fa fa-star red"></i><i class="fa fa-star red"></i><i class="fa fa-star red"></i><i class="fa fa-star red"></i><i class="fa fa-star red"></i>'
    //                         }
    //                         var str = list[i].text;
    //                         if(str.length > 100){
    //                             var show = str.substring(0,100);
    //                             list[i].text = show+'...';
    //                             // var strend = str.substring(100, str.length);
    //                             // var newstr = str.replace(strend, "...");
    //                             // list[i].text = newstr;
    //                         }
    //                         if (list[i].rptxt == null) {
    //                             if(str.length > 100){
    //                                 tpl += '<li><div class="designer-pic">' +
    //                                 '<img src="' + list[i].logo + '" alt="">' +
    //                                 '</div><div class="designer-detail">' +
    //                                 '<div class="clearfix">' +
    //                                 '<span class="designer-name">' + list[i].name + '</span>' +
    //                                 '<span class="designer-status">' + list[i].step + '</span>' +
    //                                 '</div>' +
    //                                 '<div>' +
    //                                 '<span class="star-text">打分</span>' +
    //                                 '<p class="stars">' + tpl2 +'</p>' +
    //                                 '<span class="designer-time">' + list[i].time + '</span>' +
    //                                 '</div>' +
    //                                 '<div class="desinger-text">' +
    //                                 '<div class="desc">' + list[i].text + '</div>' +
    //                                 '<span class="more">全文</span>' +
    //                                 '</div>' +
    //                                 '</div>' +
    //                                 '</li>';
    //                             }else{
    //                                 tpl += '<li><div class="designer-pic">' +
    //                                 '<img src="' + list[i].logo + '" alt="">' +
    //                                 '</div><div class="designer-detail">' +
    //                                 '<div class="clearfix">' +
    //                                 '<span class="designer-name">' + list[i].name + '</span>' +
    //                                 '<span class="designer-status">' + list[i].step + '</span>' +
    //                                 '</div>' +
    //                                 '<div>' +
    //                                 '<span class="star-text">打分</span>' +
    //                                 '<p class="stars">' + tpl2 +'</p>' +
    //                                 '<span class="designer-time">' + list[i].time + '</span>' +
    //                                 '</div>' +
    //                                 '<div class="desinger-text">' +
    //                                 '<div class="desc">' + list[i].text + '</div>' +
    //                                 '</div>' +
    //                                 '</div>' +
    //                                 '</li>';
    //                             }
    //                         } else {
    //                             if(str.length > 100){
    //                                 tpl += '<li><div class="designer-pic">' +
    //                                 '<img src="' + list[i].logo + '" alt="">' +
    //                                 '</div><div class="designer-detail">' +
    //                                 '<div class="clearfix">' +
    //                                 '<span class="designer-name">' + list[i].name + '</span>' +
    //                                 '<span class="designer-status">' + list[i].step + '</span>' +
    //                                 '</div>' +
    //                                 '<div>' +
    //                                 '<span class="star-text">打分</span>' +
    //                                 '<p class="stars">' + tpl2 +'</p>' +
    //                                 '<span class="designer-time">' + list[i].time + '</span>' +
    //                                 '</div>' +
    //                                 '<div class="desinger-text">' +
    //                                 '<div class="desc">' + list[i].text + '</div>' +
    //                                 '<span class="more">全文</span>' +
    //                                 '</div>' +
    //                                 '<div>' +
    //                                 '</div>' +
    //                                 '<div class="replay">' +
    //                                 '<p class="replay-title clearfix">' +
    //                                 '<span>商家回复:</span>' +
    //                                 '<span>' + list[i].reply_time + '</span>' +
    //                                 '</p>' +
    //                                 '<p class="replay-text">' + list[i].rptxt + '</p>' +
    //                                 '</div>' +
    //                                 '</div>' +
    //                                 '</li>';
    //                             }else{
    //                                 tpl += '<li><div class="designer-pic">' +
    //                                 '<img src="' + list[i].logo + '" alt="">' +
    //                                 '</div><div class="designer-detail">' +
    //                                 '<div class="clearfix">' +
    //                                 '<span class="designer-name">' + list[i].name + '</span>' +
    //                                 '<span class="designer-status">' + list[i].step + '</span>' +
    //                                 '</div>' +
    //                                 '<div>' +
    //                                 '<span class="star-text">打分</span>' +
    //                                 '<p class="stars">' + tpl2 +'</p>' +
    //                                 '<span class="designer-time">' + list[i].time + '</span>' +
    //                                 '</div>' +
    //                                 '<div class="desinger-text">' +
    //                                 '<div class="desc">' + list[i].text + '</div>' +
    //                                 '</div>' +
    //                                 '<div>' +
    //                                 '</div>' +
    //                                 '<div class="replay">' +
    //                                 '<p class="replay-title clearfix">' +
    //                                 '<span>商家回复:</span>' +
    //                                 '<span>' + list[i].reply_time + '</span>' +
    //                                 '</p>' +
    //                                 '<p class="replay-text">' + list[i].rptxt + '</p>' +
    //                                 '</div>' +
    //                                 '</div>' +
    //                                 '</li>';
    //                             }
    //                         }
    //
    //                     }
    //                     $('.comments-list').append(tpl);
    //
    //                     for (var i = 0; i < $('.comments-list').find('.designer-status').length; i++) {
    //                         if ($('.comments-list').find('.designer-status')[i].textContent == '') {
    //                             $('.comments-list').find('.designer-status')[i].setAttribute('class', 'designer-status hide');
    //                         }
    //                     }
    //                     if (data.data.page.total_number <= 10) {
    //                         $('.load').hide();
    //                         $('#footer').show();
    //                     }
    //                 }
    //                 off_on = true;
    //             } else {
    //                 alert(data.info)
    //             }
    //         })
    // }



})
