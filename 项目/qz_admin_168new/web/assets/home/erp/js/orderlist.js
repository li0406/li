/*
* @Author: qz_dc
* @Date:   2018-08-16 13:19:47
* @Last Modified by:   qz_xsc
* @Last Modified time: 2018-09-26 11:24:20
*/


$(function(){
    //排序箭头
    $('table').on('click','.paixu',function(){
        $(this).removeClass('fa-sort-desc').addClass('fa-sort-asc')
    })
    $('table').on('click','.paixu',function(){
        $(this).removeClass('fa-sort-asc').addClass('fa-sort-desc')
    })
    $('.paixu').click(function(event) {
        var dataType = $(this).attr('data-type');
        var dataOrder = $(this).attr('data-order');
        var cid = $('input[name=cid]').val();
        var btype = $('input[name=btype]').val();
        window.location = '/yxb/orderlist?ordertype='+dataType+'&order='+dataOrder+'&cid='+cid+'&btype='+btype+"&"+$("#sx_form").serialize();
    });

})