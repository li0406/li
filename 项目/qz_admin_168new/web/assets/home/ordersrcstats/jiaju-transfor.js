$(function () {
    var Global_Repeat_Url = ""
    var $popupIP = $('div[data-name="popup-ip"]'),
        $popupPhone = $('div[data-name="popup-phone"]'),
        $closeBtn = $(".popup .close-btn");
    $closeBtn.on('click', function () {
        $(this).parent(".popup").fadeOut(0);
    })
    // $("html, body").on("click", function (event) {
    //     var $target = $(event.target);
    //     console.log($target.attr('data-name'))
    //     if($target.attr('data-name') && $target.attr('data-name').toLowerCase() == "repeat") {
    //         var type = $target.attr('data-type');
    //         alert(type)
    //         if(type == 'ip') {
    //             $popupPhone.css("z-index", 999)
    //             $popupIP.fadeIn(0).css("z-index", 9999)
    //         }else if(type == 'tel') {
    //             $popupIP.css("z-index", 999)
    //             $popupPhone.fadeIn(0).css("z-index", 9999)
    //         }
    //         $.ajax({
    //             url: Global_Repeat_Url,
    //             type: 'POST',
    //             dataType: 'JSON',
    //             data: {
    //                 gid: 1,
    //             }
    //         }).done(function (data) {
    //             if (data.error_code == 0) {
    //                 var html = '';
    //                 $.each(data.info.list, function (k, v) {
    //                     html += '<tr class="two-leveltop">' +
    //                         '<td><i class="fa fa-plus-square-o sub two-level" data-type="subitem" data-id=' + v.id + ' data-on="0" data-level=1></i>' + v.name + '</td>' +
    //                         '<td>' + v.uv + '</td>' +
    //                         '<td>' + v.pv + '</td>' +
    //                         '<td>' + v.all_count + '</td>' +
    //                         '<td>' + v.tel_count + '</td>' +
    //                         '<td>' + v.un_tel_count + '</td>' +
    //                         '<td>' + v.real_yx_count + '</td>' +
    //                         '<td>' + v.real_fen_count + '</td>' +
    //                         '<td>' + v.fen_count + '</td>' +
    //                         '<td>' + v.fen_rate + '%</td>' +
    //                         '</tr>';
    //                 });
    //                 if(type == 1) {
    //                     $popupIP.html(html);
    //                 }else if(type == 2) {
    //                     $popupPhone.html(html);
    //                 }
    //             } else {
    //                 layer.msg(data.error_msg);
    //                 return
    //             }
    //         })
    //     }
    // })
})
