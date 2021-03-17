function toTimeStamp(time){
    if(time!=undefined){
        var date = time;
        date = date.substring(0,19);
        date = date.replace(/-/g,'/');
        var timestamp = new Date(date).getTime();
        return timestamp;
    }
};
function msg(msg, fn) {
    layer.msg(
        msg,
        {time: 2000,},
        fn || function () {
        }
    )
}
$(function () {
    var $tab = $(".tab-box > a"),
        begin = $('input[name="begin"]').val(),
        end = $('input[name="end"]').val(),
        dpt = $('input[name="dpt"]').val(),
        channel = $('input[name="channel"]').val();

    $tab.on("click", function (event) {
        event.preventDefault()
        event.stopPropagation()
        var href = $(this).attr("href");
        href+=("/?begin=" + begin + "&end=" + end + "&department=" + dpt + "&charge=" + channel)
        location.href = href;
    })
})
