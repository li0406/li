// $('.tipshs').hide()
// $('.trial-remarks-div').hide()
// $('.dialog-trial').hide()
function opentipsdialog(){
  $('.tipshs').show()
}
function closetipsdialog(){
  event.stopPropagation()
  $('.tipshs').hide()
}
function stopprop(){
  event.stopPropagation()
}
function closetips(){
  $('.dialog-tips').hide()
}
function getRadioVal(){
  let radioVal = $('input[name="radio"]:checked').val();
  if(radioVal==5){
    $('.trial-remarks-div').show()
  }else{
    $('.trial-remarks-div').hide()
  }
}
function opentrial(){
  $('.dialog-trial').show()
  $("#check_id").val($(this).data("id"));
}
function closetrial(){
  $('.dialog-trial').hide()
}