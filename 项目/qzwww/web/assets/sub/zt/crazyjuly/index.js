/**
 * Created by Administrator on 2016/6/14.
 */

$(function(){
  $(window).scroll(function(){
    var scrollTop = document.documentElement.scrollTop || document.body.scrollTop ;
    var height = $(".zt-head").offset().top +200;
    var height1=$(".container").innerHeight()-500;
    if(scrollTop > height){
        $(".zb_footer_box_event").show();
        $(".zb_footer_box_event_little").show();
    }else{
        $(".zb_footer_box_event").hide();
        $(".zb_footer_box_event_little").hide();
    }
    if(scrollTop > height1){
      $("#l-nav").show(500);
    }else{
      $("#l-nav").hide(500);
    }
  });

  var f1=$(".container").outerHeight();
  var f2=f1+$(".hls").outerHeight();
  var f3=f2+$(".explain").outerHeight()+120;

  $(".f-cur").click(function(){
    $('body,html').animate({scrollTop:0},1000);
    return false;
  });
  $(".f-gift").click(function(){
    $('body,html').animate({scrollTop:f1},1000);
    return false;
  });
  $(".f-explain").click(function(){
    $('body,html').animate({scrollTop:f2},1000);
    return false;
  });
  $(".f-ask").click(function(){
    $('body,html').animate({scrollTop:f3},1000);
    return false;
  });


  $(".zb_footer_box_event .zb_close").click(function(){
    var length = $(".zb_footer_box_event").outerWidth();
    $(".zb_footer_box_event_little").css({display:"block"});
    $(".zb_footer_box_event").animate({"left":"-="+length},800,function(){
      $(".zb_footer_box_event_little").animate({left:"0px"},800);
    });
  });
  $(".zb_footer_box_event_little").click(function(){
    $(".zb_footer_box_event_little").animate({left:"-158px"},800,function(){
      $(".zb_footer_box_event").animate({left:"0px"},800);
    });
  });
});




