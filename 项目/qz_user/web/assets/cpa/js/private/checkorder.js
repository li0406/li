$(function () {

    $(".sj-rightDateBtn").click(function () {
        var keyword = $(".sj-ordercheck").val().trim();
        var myhref;
        if (!keyword) {
            myhref = window.location.pathname;
        } else {
            myhref = window.location.pathname + '?keyword=' + keyword;
        }
        window.location.href = myhref;
    });

    //  产看详情
    $(".sj-check-detail").click(function () {
        var _this = $(this);
        var id = _this.data('id');
        console.log(id);
        $.ajax({
            url: '/order/orderinfo',
            type: 'POST',
            dataType: "JSON",
            data: {id: id}
        }).done(function (res) {
            if (res.status == 1) {
                layerindex = layer.open({
                    type: 1,
                    title: '查看订单',
                    area: ['1030px ','620px'], //宽高
                    content: "<div class='sj-infoBox'>" +
                    "<div> " +
                    "<section class='sj-contenter '> " +
                    "<div class='sj-order-detail clearfix'>" +
                    " <ul class='clearfix'> " +
                    "<li> <span>业主姓名:</span> <span>"+res.data.name+"</span>"+(res.data.sex == '' ? '':'('+res.data.sex+')') +"</li> " +
                    "<li> <span>小区名称:</span> <span>"+res.data.xiaoqu+"</span> </li> " +
                    "<li> <span>户型结构:</span> <span>"+res.data.hxing+"</span> </li> " +
                    "<li> <span>小区地址:</span> <span>"+res.data.dz+"</span> </li> " +
                    "</ul> <ul class='clearfix'> " +
                    "<li> <span>手机号码:</span> <span>"+res.data.real_tel+"</span> </li> " +
                    "<li> <span>房屋面积:</span> <span>"+res.data.mianji+"m<sup>2</sup></span> </li> " +
                    "<li> <span>家具风格:</span> <span>"+res.data.fge+"</span> </li> </ul> " +
                    "<ul class='clearfix'> " +
                    "<li> <span>备用联系:</span> <span>"+res.data.other_contact+"</span> </li> " +
                    "<li> <span>家具类型:</span> <span>"+res.data.furniture_type+"</span> </li> " +
                    "<li> <span>家具预算:</span> <span>"+res.data.ysjg+"</span> </li> " +
                    "</ul> " +
                    "</div> " +
                    "</section> " +
                    "<div class='line'> <span>辅助信息</span> </div> " +
                    "<section class='sj-contenter'> " +
                    "<div class='sj-order-detail sj-assist-detail clearfix'> " +
                    "<ul> " +
                    "<li> <span>装修进度:</span> <span>"+res.data.step+"</span> </li> " +
                    "<li> <span>到店时间:</span> <span>"+res.data.view_time+"</span> </li> " +
                    "<li> <span>装修用途:</span> <span>"+res.data.yt+"</span> </li> " +
                    "<li class='clearfix'> " +
                     "<span>特殊需求:</span> <div class='sj-special-text'>"+res.data.special_remarks+"<hr style='border-top:1px dashed  #c5c5c5;margin: 10px 0'>说明服务和保障：<br>①联系时给业主名片并说明是齐装网推荐的</div> " +
                    "</li> " +
                    "</ul> " +
                    "</div> " +
                    "<div class='sj-special-close'>关闭</div> " +
                    "</section> " +
                    "</div>" +
                    "</div>"
                });
                _this.parents('tr').children('.info-statu').children('label').text('已读');
                _this.parents('tr').children('.info-statu').children('input').attr('checked', true).attr('disabled', true);
                //取消
                $(".sj-contenter").on("click",'.sj-special-close', function(){
                    layer.close(layerindex);
                });
                return false;
            } else {
                msg(res.info);
                return false;
            }
        }).fail(function (xhr) {
            msg('发生未知错误，请稍后再试');
            return false;
        });
    });
    $('body').on('click', '.sj-win-close,.sj-special-close', function (event) {
        $(".mask").css('display', 'none')
        $(".idcheckBox").css('display', 'none')
    });
});

function msg(msg, fn) {
    layer.msg(
        msg,
        {time: 1300},
        fn || function () {
        }
    )
}
