$(".popup-close").click(function(){
    $("#qcc-popup").fadeOut();
})

$("#del").click(function(){
    $("#top-input").val('')
    $("#del").hide()
})
$('#top-input').on('input propertychange', function() {
    console.log('123')
    getDel()
})

getDel()
function getDel(){
    if($("#top-input").val()){
        $("#del").show()
    }else{
        $("#del").hide()
    }
}

$("#back").click(function(){
    console.log('123')
    window.history.go(-1); 
})
