
    	 $('.select2').select2({
            language: "zh-CN",
            placeholder:'城市'
        })


         $(".datepicker").datetimepicker({
                startView:2,
                minView:'decade',
                format:"yyyy-mm-dd",
                autoclose:true,
                pickerPosition: "bottom-left",
                todayBtn:1,
                clearBtn:true,
            });

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

        //创建验证
          window.xianzhi=true;
         $('.createniu').click(function(event) {
             
             var userid = $("input[name=userid ]").val();
         	 var Jiajie_val=$.trim($('.jiajie_val').val()),
         	    City=$.trim($('#city').val()),
         	    Logname_val=$.trim($('.logname_val').val()),
         	    Companyall=$.trim($('.companyall').val()),
         	    Companyaddress=$.trim($('.companyaddress').val()),
         	    Contact_val=$.trim($('.contact_val').val()),
         	    Phone_val=$.trim($('.phone_val').val()),
         	    // Weixin_val=$.trim($('.weixin_val').val()),
         	    Time_start=$.trim($('.time_start').val()),
         	    Time_end=$.trim($('.time_end').val());




                if(userid == ''){
                    if(Jiajie_val==""){
                        $('.tishi').html("");
                        $('.jiajie_tishi').html("请输入公司简称");
                        return false;
                    }

                    if(City==""){
                        $('.tishi').html("");
                        $('.city_tihsi').html("请选择所属城市");
                        return false;
                    }

                    if(Logname_val==""){
                        $('.tishi').html("");
                        $('.logname_tishi').html("请输入登录账号");
                        return false;
                    }else{
                        var lognamereg=/[\u4e00-\u9fa5_a-zA-Z0-9_]{6,18}/;
                        if(!lognamereg.test(Logname_val)){
                            $('.tishi').html("");
                            $('.logname_tishi').html("登录账号为6-18位，可使用中英文、数字、下划线");
                            return false;
                        }
                    }

                    if(Companyall==""){
                        $('.tishi').html("");
                        $('.allname_tishi').html("请输入公司全称");
                        return false;
                    }else{
                        var Companyreg=/^[\u4E00-\u9FA5A-Za-z0-9]{6,20}$/;
                        if(!Companyreg.test(Companyall)){
                            $('.tishi').html("");
                            $('.allname_tishi').html("6-20位字符，可使用中英文和数字");
                            return false;
                        }
                    }
                    // if(Companyaddress==""){
                    //     $('.tishi').html("");
                    //     $('.address_tishi').html("请输入公司地址");
                    //     return false;
                    // }
                }else{

                    if(Logname_val==""){
                        $('.tishi').html("");
                        $('.logname_tishi').html("请输入登录账号");
                        return false;
                    }else{
                        var lognamereg=/[\u4e00-\u9fa5_a-zA-Z0-9_]{6,18}/;
                        if(!lognamereg.test(Logname_val)){
                            $('.tishi').html("");
                            $('.logname_tishi').html("登录账号为6-18位，可使用中英文、数字、下划线");
                            return false;
                        }
                    }

                }



                if(Contact_val==""){
                   $('.tishi').html("");
                   $('.contact_tishi').html("请输入联系人");
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
                if(Time_start==""){
                	$('.tishi').html("");
                   $('.riqitishi01').html("请选择有效日期-开始");
                   return false;
                }else if(Time_end==""){
                	$('.tishi').html("");
                   $('.riqitishi02').html("请选择有效日期-结束时间");
                   return false;
                }else if(toTimeStamp(Time_end)<toTimeStamp(Time_start)){
                	$('.tishi').html("");
                   $('.riqitishi02').html("选择的结束时间不能早于开始时间");
                   return false;
                }else{

                    if(xianzhi==true){
                       xianzhi=false;
                      $.ajax({
                        url: '/yxb/addCompany/',
                        type: 'POST',
                        dataType: 'JSON',
                        data:$("#form-add").serializeArray()
                    })
                        .done(function(data) {
                            xianzhi=true;
                            console.log(data);
                            switch (data.status){
                                case 1:
                                    $('#mimatext').html(data.data);
                                    $('.tishi').html("");
                                    $('.successyingy').show();
                                    $('.successbox').show();
                                    setTimeout(function(){
                                        $('.surebox .surequed').attr("disabled",false)
                                    },3000)
                                    break;
                                case -1:
                                    //简称
                                    $('.tishi').html("");
                                    $('.jiajie_tishi').html(data.info);
                                    break;
                                case -2:
                                    //账号
                                    $('.tishi').html("");
                                    $('.logname_tishi').html(data.info);
                                    break;
                                case -3:
                                    //全称
                                    $('.tishi').html("");
                                    $('.allname_tishi').html(data.info);
                                    break;
                                default:
                                    alert(data.info);
                                    break;
                            }
                        })
                        .fail(function(xhr) {
                            alert('发生未知错误，请稍后重试~');
                            return false;
                        })

                    }
                    
                }
         });

         $('.surebox .surequed').click(function(event) {
         	 $('.successyingy').hide();
             $('.successbox').hide();
             xianzhi=false;
             window.location.href="/yxb/index";
             

         });


        $('.cancelniu').click(function(event) {
        	window.location.href="/yxb/index"
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
