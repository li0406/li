/*
* @Author: qz_dc
* @Date:   2018-08-16 10:22:27
* @Last Modified by:   qz_dc
* @Last Modified time: 2018-08-16 10:27:33
*/
$(function(){
    $('.fa-user').mouseenter(function(event) {
        $(this).siblings('.job').show();
    });
    $('.fa-user').mouseleave(function(event) {
        $(this).siblings('.job').hide();
    });
    $('.back').click(function () {
        window.location.href = '/yxb/index/';
    })
})