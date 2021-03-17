
    	 $('.select2').select2({
            language: "zh-CN",
            placeholder:'城市'
        })

    	$('.paixupic').click(function(event) {
            var dataType = $(this).attr('data-type');
            var dataOrder = $(this).attr('data-order');
            var dataStr = $('#dataform').serialize();
            window.location = '/yxb/index?ordertype='+dataType+'&order='+dataOrder+'&'+dataStr;
    	});

      datariqi(".startdate");
      datariqi(".jiesudate");

      
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



       $(function(){

         

          $('.dianjisos').click(function(event) {
          
          	 var Youxiaostartval = $.trim($('.youxiaostart').val()),
          	     Youxiaoendval = $.trim($('.jiesudate').val());
                if(toTimeStamp(Youxiaostartval.split('~')[1])>toTimeStamp(Youxiaoendval.split('~')[0]) && Youxiaostartval!="" && Youxiaoendval!=""){
                   alert("有效日期-结束的起始日期必须大于等于有效日期-开始的结束日期")
                   return false;
                }

              var dataStr = $('#dataform').serialize();
              window.location = '/yxb/index/?'+dataStr;
          
          });

          $('.bdniuwaik .dianjireset').click(function(event) {

               $('form')[0].reset();
               $('#city').select2("val","");
               $('.companyname').val("");
               $('.zhuangtai').val("");
               $('.loginhao').val("");
               $('.youxiaostart').val("");
               $('.jiesudate').val(""); 
               datariqi(".startdate");
               datariqi(".jiesudate");

          });

          $('.seelog').click(function(event) {
              var jc = $(this).parent().parent().find('.jc').text();
              var company_id = $(this).attr('data-id');
              $.ajax({
                  url: '/yxb/getErpLog/',
                  type: 'POST',
                  dataType: 'JSON',
                  data:{company_id:company_id}
              })
                  .done(function(data) {
                      if(data.status == 1){
                          $('.tanclogwk_title2').text(jc);
                          var html = '';
                          $.each(data.data, function(index, val) {
                              html = html + '<tr><td>'+val.action+'</td><td>'+val.start_time+'</td><td>'+val.end_time+'</td> <td>'+val.name+'</td><td class="color_blue">'+val.time+'</td></tr>';;
                          });
                          $("#logbody").html(html);
                          $('.tcbeijing').show();
                          $('.tanclogwk').show();
                      }else{
                          alert(data.info);
                      }
                  })
                  .fail(function(xhr) {
                      alert('发生未知错误，请稍后重试~');
                      return false;
                  })




          });

          $('.tanclogwk_foot .tancclose').click(function(event) {
          	 $('.tcbeijing').hide();
          	 $('.tanclogwk').hide();

          });


        // 日期转化成时间戳
          function toTimeStamp(time){
              if(time!=undefined){
                var date = time;
                date = date.substring(0,19);
                date = date.replace(/-/g,'/');
                var timestamp = new Date(date).getTime();
                return timestamp;
              }  
          };

       })
