$(function () {
    if (m_to_pc == '') {
        $.ajax({
            url: '/poplayer/pop',
            type: 'GET',
            dataType: 'JSON',
            data: {
                type: "kf_box",
            }
        })
            .done(function (data) {
                if (data.status == 0) {
                    $(".fix_warp").html(data.tmp);
                }
            });
    }
})