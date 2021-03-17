/*
* @Author: qz_dc
* @Date:   2018-09-17 16:33:47
* @Last Modified by:   qz_lb
* @Last Modified time: 2018-09-28 16:31:37
*/
$(function(){
    $('#city').select2({
        language: "zh-CN",
        placeholder:'城市'
    });

    datariqi(".date");

    $('.dianjireset').click(function(event) {

      // $('#searchForm')[0].reset();

      $('.loginhao').val('');
      $('.companyname').val('');
      $('.date').val('');
      $('.chuliren').val('');
      $('select[name=qudao]').val('');
      $('select[name=handlestatus]').val('');
      $('select[name=city]').select2("val","");
    });

    $('.paixupic').click(function(event) {
        var dataType = $(this).attr('data-type');
        var dataOrder = $(this).attr('data-order');
        var dataStr = $('#searchForm').serialize();
        window.location = '/yxb/suggest?ordertype='+dataType+'&order='+dataOrder+'&'+dataStr;
    });
    function datariqi(select){
      $(select).daterangepicker({
          timePicker: true,
          timePickerIncrement: 30,
          format: 'YYYY-MM-DD',
          separator: ' ~ ',
      }, function(start, end, label) {
          console.log(start.toISOString(), end.toISOString(), label);
      });
    }



})