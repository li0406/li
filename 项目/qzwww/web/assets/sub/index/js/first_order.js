$(function () {
    if (alertOrder) {
        $.ajax({
            url: '/poplayer/pop',
            type: 'GET',
            dataType: 'JSON',
            data: {
                type: "first_order_fen",
            }
        })
            .done(function (data) {
                if (data.status == 0) {
                    $(".first_order").html(data.tmp);
                }
            });
    }
    // 底部发单显示
    $.ajax({
        url: '/poplayer/pop',
        type: 'GET',
        dataType: 'JSON',
        data: {
            type: "bottom_fadan",
        }
    })
        .done(function (data) {
            if (data.status == 0) {
                $(".bottom_order").append(data.tmp);
            }
        });

    $.ajax({
        url: '/poplayer/pop',
        type: 'GET',
        dataType: 'JSON',
        data: {
            type: "smzxdk",
        }
    })
        .done(function (data) {
            if (data.status == 0) {
                $("#smzxdk").append(data.tmp);
            }
        });
})
