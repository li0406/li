
if(tiao == 1){
    var url = "/yxb/index/";
}else{
    var url = "/yxb/examine/";
}
//城市选择
    $('.select2').select2({
            language: "zh-CN",
            placeholder:'城市'
        })



        //日期插件
         $(".datepicker").datetimepicker({
                startView:2,
                minView:'decade',
                format:"yyyy-mm-dd",
                autoclose:true,
                pickerPosition: "bottom-left",
                todayBtn:1,
                clearBtn:true,
            });

        //
         function copyUrl2()
		    {
		        var Url2=document.getElementById("mimatext").innerText;
		        var oInput = document.createElement('input');
		        oInput.value = Url2;
		        document.body.appendChild(oInput);
		        oInput.select(); // 选择对象
		        document.execCommand("Copy"); // 执行浏览器复制命令
		        oInput.className = 'oInput';
		        oInput.style.display='none';
		        alert('复制成功');
		    }

         

       $(function(){
       	$('.hangwaik .resetmm').click(function(event) {
            var id = $.trim($('.gongshiid_val').val());
            $.ajax({
                url: '/yxb/makeErpPsw/',
                type: 'POST',
                dataType: 'JSON',
                data:{id:id}
            })
                .done(function(data) {
                    if(data.status == 1){
                        $('#mimatext').html(data.data);
                        $('.successyingy').show();
                        $('.successbox').show();
                        setTimeout(function(){
                            $('.surebox .surequed').attr("disabled",false)
                        },3000)
                    }else{
                        alert(data.info);
                    }
                })
                .fail(function(xhr) {
                    alert('发生未知错误，请稍后重试~');
                    return false;
                })
       	});

        //保存验证

         $('.hangwaik .savebaocun').click(function(event) {
         	var Contact_val=$.trim($('.contact_val').val()),
         	    Phone_val=$.trim($('.phone_val').val()),
         	    Weixin_val=$.trim($('.weixin_val').val()),
                id = $.trim($('.gongshiid_val').val());

                if(Contact_val==""){
                	$('.tishi').html("");
                   $('.contact_tishi').html("请输入联系人名称");
                   return false;
                }else{
                	var Contactreg=/^[\u4E00-\u9FA5A-Za-z0-9]{1,6}$/;
                	if(!Contactreg.test(Contact_val)){
                		$('.tishi').html("");
                       $('.contact_tishi').html("最大不超过6位中文字符，可使用中英文、数字");
                       return false;
                	}
                }
                if(Phone_val==""){
                	$('.tishi').html("");
                   $('.phone_tishi').html("请输入联系人电话号码");
                   return false;
                }else{
                  var phonereg=/^[\d-]{1,13}$/;
                  if(!phonereg.test(Phone_val)){
                       $('.tishi').html("");
                       $('.phone_tishi').html("最大不超过13位字符，可使用数字、-");
                       return false;
                  }
                }

                $('.tishi').html("");
                 $.ajax({
                     url: '/yxb/editErp/',
                     type: 'POST',
                     dataType: 'JSON',
                     data:{name:Contact_val,tel:Phone_val,wx: Weixin_val,id:id}
                 })
                     .done(function(data) {
                         if(data.status == 1){
                             window.location.href=url;
                         }else{
                             alert(data.info);
                         }
                     })
                     .fail(function(xhr) {
                         alert('发生未知错误，请稍后重试~');
                         return false;
                     })

         });
            //续费
           $('.hangwaik .xufei').click(function(){
               $('.riqitishi02').html('');
               var Time_start=$.trim($('.time_start').val()),
                   Time_end=$.trim($('.time_end').val()),
                   id = $.trim($('.gongshiid_val').val());

               if(Time_start==""){
                   $('.tishi').html("");
                   $('.riqitishi01').html("请选择有效开始时间");
                   return false;
               }else if(Time_end==""){
                   $('.tishi').html("");
                   $('.riqitishi02').html("请选择有效结束时间");
                   return false;
               }else if(toTimeStamp(Time_end)<toTimeStamp(Time_start)) {
                   $('.tishi').html("");
                   $('.riqitishi02').html("选择的结束时间不能小于开始时间");
                   return false;
               }

               $.ajax({
                   url: '/yxb/addErpTime/',
                   type: 'POST',
                   dataType: 'JSON',
                   data:{id:id,start_time:Time_start,end_time: Time_end}
               })
                   .done(function(data) {
                       if(data.status == 1){
                           $('.tishi').html("");
                           $('.caozsuccesswk .caozsuccesswk_title span.successmiaos').text('续费成功');
                           $('.successyingy').show();
                           $('.caozsuccesswk').show();
                           setTimeout(function(){
                            $('.successyingy').hide();
                            $('.caozsuccesswk').hide();
                               window.location.href=url;
                           },3000)
                       }else{
                           alert(data.info);
                       }
                   })
                   .fail(function(xhr) {
                       alert('发生未知错误，请稍后重试~');
                       return false;
                   })
           })

         //申请开通验证
         $('.hangwaik .applykaitong').click(function(event) {
             $('.riqitishi02').html('');
             var Time_start=$.trim($('.time_start').val()),
                 Time_end=$.trim($('.time_end').val()),
                 id = $.trim($('.gongshiid_val').val()),
                 time_id = $("input[name=time_id ]").val();

             if(Time_start==""){
                 $('.tishi').html("");
                 $('.riqitishi01').html("请选择有效开始时间");
                 return false;
             }else if(Time_end==""){
                 $('.tishi').html("");
                 $('.riqitishi02').html("请选择有效结束时间");
                 return false;
             }else if(Time_end<Time_start) {
                 $('.tishi').html("");
                 $('.riqitishi02').html("选择的结束时间不能小于开始时间");
                 return false;
             }
             $('.tishi').html("");
             $.ajax({
                 url: '/yxb/editErpTime/',
                 type: 'POST',
                 dataType: 'JSON',
                 data:{id:id,time_id:time_id,start_time:Time_start,end_time: Time_end}
             })
                 .done(function(data) {
                     if(data.status == 1){
                         $('.caozsuccesswk .caozsuccesswk_title span.successmiaos').text('操作成功');
                         $('.successyingy').show();
                         $('.caozsuccesswk').show();
                         setTimeout(function(){
                            $('.successyingy').hide();
                            $('.caozsuccesswk').hide();
                             window.location.href=url;

                           },3000)
                     }else{
                         alert(data.info);
                     }
                 })
                 .fail(function(xhr) {
                     alert('发生未知错误，请稍后重试~');
                     return false;
                 })

         });

         //tab切换
           var type =  $("input[name=old_type]").val();
           if(type == 11 || type == 12 || type == 13 ||type == 4 ){
               $('.historywk .historybox2').show();
               $('.historywk .historybox1').hide();
           }
         $('.historywk_title .tabqiehuan').click(function(event) {
            var tabindex=$(this).index();
            $(this).addClass('tabcurr');
            $(this).siblings().removeClass('tabcurr');
            $('.historywk .historybox').eq(tabindex).show();
            $('.historywk .historybox').eq(tabindex).siblings().hide();

         });


         $('.hangwaik .stopzz').click(function(event) {
             $('.caozuotank .caozuobox').text("确定要终止吗？");
             var type = 4;
             var log_type = 4;
             goStatusClick(type,log_type,'操作成功')
         });

         $('.tancfootwk .tanchuang_cancel').click(function(event) {
         	$('.successyingy').hide();
         	$('.caozuotank').hide();
         });

         $('.caozuotank_waik .fa-close').click(function(event) {
         	$('.successyingy').hide();
         	$('.caozuotank').hide();
         });

         $('.surebox .surequed').click(function(event) {
         	 $('.successyingy').hide();
             $('.successbox').hide();
             $('.surebox .surequed').attr("disabled",true)
         });


         $('.hangwaik .cancelniu2').click(function(event) {
             window.location.href=url;
         });

        $('.cancelniu').click(function(event) {
            window.location.href=url;
        });

        $('.hangwaik .cancelapply').click(function(event) {
        	$('.caozuotank .caozuobox').text("确定要取消申请吗？");
            var type = 5;
            var log_type = 5;
            goStatusClick(type,log_type,'操作成功')
        });

     

       function goStatusClick(type,log_type,text){
           var time_id = $("input[name=time_id ]").val();
           var old_type =  $("input[name=old_type]").val();
           var id = $("input[name=id]").val();
           var remark = $("textarea[name=remark]").val();
           $('.successyingy').show();
           $('.caozuotank').show();
           $('.tanchuang_sure').click(function(){
               $.ajax({
                   url: '/yxb/editErpType/',
                   type: 'POST',
                   dataType: 'JSON',
                   data:{type:type,old_type:old_type,log_type:log_type,time_id:time_id,id:id,remark:remark}
               })
                   .done(function(data) {
                       if(data.status == 1){
                           $('.caozsuccesswk .caozsuccesswk_title span.successmiaos').text(text);
                           $('.successyingy').show();
                           $('.caozuotank').hide();
                           $('.caozsuccesswk').show();
                           setTimeout(function(){
                            $('.successyingy').hide();
                            $('.caozsuccesswk').hide();
                               window.location.href=url;
                           },3000)
                       }else{
                           alert(data.info);
                       }
                   })
                   .fail(function(xhr) {
                       alert('发生未知错误，请稍后重试~');
                       return false;
                   })

           })

           }

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
