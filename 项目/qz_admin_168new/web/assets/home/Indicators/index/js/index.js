// $('.dialog-tips').hide()
// $('.trial-remarks-div').hide()
// $('.dialog-trial').hide()
function opentips(){
  $('.dialog-tips').show()
}
function closetips(){
  $('.dialog-tips').hide()
}
function getRadioVal(){
  let radioVal = $('input[name="invoice_status"]:checked').val();
  if(radioVal==5){
    $('.trial-remarks-div').show()
  }else{
    $('.trial-remarks-div').hide()
  }
}
function opentrial(){
  $('.dialog-trial').show()
}
function closetrial(){
  $('.dialog-trial').hide()
}