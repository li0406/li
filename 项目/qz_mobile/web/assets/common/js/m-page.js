(function() {
    $("#page-count").click(function(){
        $("#mask1").fadeIn();
        $("#jump-num-box").animate({
            bottom:"4em"
        });
    });
    $("#mask1").click(function(){
         $("#mask1").fadeOut();
         $("#jump-num-box").animate({
            bottom:"-20em"
         });
    });
})();
