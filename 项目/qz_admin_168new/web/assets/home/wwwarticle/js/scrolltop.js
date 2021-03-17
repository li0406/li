jQuery(document).ready(function($) {
    $("<div id='liToTop'><img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAMAAABg3Am1AAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAADPUExURQAAAHd3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d6e8RxwAAABEdFJOUwDMoBv2NgPz52YLCQwVe1oSBq6tt3iQ5PzP206sCnJs+fIzpQ8kOQiNYx6TKpayxqIwmLQY8EuxkbP9ma+6YLCjae1dYLg+TwAAAjRJREFUSMedVtd2gzAMNWW4hJFFkpJJ06Ztmu69p///mwoxlifkpHrgYPnKGogrI6RK+6GfWA4hjpWsHtpog7SaHUwEwZ1mqwYexPdEk/teUAH3lrvEKLtLz4RfWKRSrIWOH2FSI3ik4v09Uit7voxvko3SFPGuIx6WTN25583daSK6dVyOtwdCSfxDvnHoC4Ub2FDPS35MHMqxhjF3fsmq2+PHu3r5XO6kRzV3UND01PSB7BSKe7dWnMD5trkFbPBxsu435iAT4ml8fTaEqDLmoujEKTOPOeJ4TMj4mK9jhpnmC9ZCA16fRqdQdLiPkNXdyiNixjPYnnSppjsB1YyhTqEpcKDiRYsAQ4P0y7cDOR4qPKqDUtNHSfnmC/lygcz9UpGgYfl2pJ8v+jgq10PEgosovqt2dZdaRCxVxDYooVzQxQs8CLmg5MNwisE5PXWdx5h6O5cMMiUkryja2+S10LxO3oqyeFJIP2rS6Ik8h2in0Oyg8Jk8ITVptazeMm+w0gC1gJF4WbUPVwgz4MI/nN4aJgNojV/IhnzXGQjNZ2pvzUBsb+MPdFWsryp+oIiF55wB4GY/lxtYnjGqwZFEAsPITALRUCIBZAPNWEbasGEOYPt/RIbafJQ46nwKepwqrbbOVHVkLPKcRPfZ7Wx+jdD1fHabVdD99gNl+5G1/VDMx25ajU8XxsHuVw1236u4C4SxIS6sTjH5cvLxKFaYOI8frU0XmmC0el+nk76vRvq95A8v6ChmotDC4QAAAABJRU5ErkJggg=='></div>").appendTo('body');
    $("#liToTop").css({
        width: '50px',
        height: '50px',
        bottom: '10px',
        right: '15px',
        position: 'fixed',
        cursor: 'pointer',
        zIndex: '999999',
    });
    if ($(this).scrollTop() == 0) {
        $("#liToTop").hide();
    }
    $(window).scroll(function(event) {
        /* Act on the event */
        if ($(this).scrollTop() == 0) {
            $("#liToTop").hide();
        }
        if ($(this).scrollTop() != 0) {
            $("#liToTop").show();
        }
    });
    $("#liToTop").click(function(event) {
        /* Act on the event */
        $("html,body").animate({
            scrollTop: "800px"
        },
        666)
    });
});