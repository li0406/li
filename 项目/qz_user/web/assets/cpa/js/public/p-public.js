$(function () {
    $(".sj-alertInfo li>a").hover(
        function () {
            $(this).addClass("sj-now");
        },
        function () {
            $(this).removeClass("sj-now");
        }
    )

})
