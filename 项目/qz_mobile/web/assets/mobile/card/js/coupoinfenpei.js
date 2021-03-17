
 var ullilength=$('.companylogin ul li').length;
            var kuandu=$('.companylogin ul li').outerWidth(true)+2;
            var ulkuandu=kuandu*ullilength;
            var xianzhikd=kuandu*5
            if(ulkuandu>=xianzhikd){
                $('.companylogin ul').width(xianzhikd)
            }else{
                $('.companylogin ul').width(ulkuandu)
            }

      new JRoll(".companylogin",{scrollX:true,scrollY:false});





        $(function(){
            //只有一个订单的时候默认请求
            if(ordercount == 1){
                var getorderid = $('#onlyorder').data('id');
                console.log($('#onlyorder').data('id'));
                 $.ajax({
                     url: '/getcompanybyorderid/',
                     type: 'POST',
                     dataType: 'json',
                     data: {
                         orderid: getorderid
                     },
                 })
                 .done(function(data) {
                     if(data.error_code == 1){  //1表示请求成功
                         if(data.data.hadspecialcard == 0){   //0表示没有可领取的装修优惠
                             $('.takezige').text('抱歉，没有可领的装修优惠');
                             $('#cardlist').hide();
                             $('.platformjijin').show();
                             $('.zhuanshuyh').show();
                             // return
                         }else{
                             $('.takezige').text('您有装修优惠可领哦');
                             document.getElementById("cardlist").innerHTML =data.data.gethtml;
                             $('#cardlist').show();
                             $('.platformjijin').show();
                             $('.zhuanshuyh').show();

                             //确认量房
                             $('.sureliagnfwk .sureniu').click(function(){
                                 console.log('订单id：'+$(this).data('orderid'));
                                 console.log('公司id：'+$(this).data('comid'));
                                 $('#comid_text').val($(this).data('comid'));     //记录公司id
                                 $('#orderid_text').val($(this).data('orderid'));   //记录订单id

                                 $('.rulesyiny').fadeIn();
                                 $('.surelfwk').fadeIn();
                             })

                             //  未确认量房点击立即领取弹窗
                             $('.takeendyy .nolftake').click(function(){
                                 $('.rulesyiny').fadeIn();
                                 $('.nolfwk').fadeIn();
                             })

                             //  点击【立即领取】弹窗
                             $('.youhuiqwk .cantake').click(function(){
                                 $('#cardid_text').val($(this).data('cardid'));
                                 // $('.ceshi').text($(this).data('cardid'));\
                                 var cardid = $(this).data('cardid');
                                 var comid = $(this).data('comid');
                                 var orderid = $(this).data('orderid');
                                 /*  领取操作  */
                                 $.ajax({
                                     url: '/card/receiveCard/',
                                     type: 'POST',
                                     dataType: 'json',
                                     data: {
                                         cardid: cardid,
                                         comid:comid,
                                         orderid:orderid
                                     },
                                 })
                                 .done(function(data){
                                     if(data.error_code == 1){
                                         $('.rulesyiny').fadeIn();
                                         $('.takesuccesswk').fadeIn();
                                     }else{
                                         alert(data.error_msg);
                                     }
                                 })
                                 .fail(function() {
                                     console.log("请求失败！");
                                     alert('请求失败！');
                                 });
                             })

                             //跳转
                             $('.youhqneir').click(function(){
                                 var cardid = $(this).data('cardid');
                                 var orderid = $(this).data('orderid');
                                 if(cardid){
                                     window.location.href='/coupontake/'+cardid+'?orderid='+orderid;
                                 }else{
                                     alert('未获取到优惠券id!');
                                     return ;
                                 }
                             });

                             // 点击已领完弹窗
                             $('.youhuiqwk .takeendniu').click(function(){
                                 $('.rulesyiny').fadeIn();
                                 $('.takeendwk').fadeIn();
                             })
                             $('.takeendwk-niu').click(function(){
                                 $('.rulesyiny').fadeOut();
                                 $('.takeendwk').fadeOut();
                             })

                             //去使用跳转到卡券包
                             $('.pleaseuse').click(function(){
                                 window.location.href = '/card/cardbag/';
                             });

                         }
                     }else if(data.error_code == 4000104){   //4000104表示无分配装修公司
                         $('.takezige').text('小齐火速帮您处理中，请注意接听电话！');
                         $('.platformjijin').hide();
                         $('.zhuanshuyh').hide();
                         $('#cardlist').hide();
                     }else if(data.error_code == 4000103){  //4000103表示未获取到订单id
                         alert(data.error_msg);
                     }else{
                         alert('请求失败');  //无感叹号表示返回错误码未知
                     }
                 })
                 .fail(function() {
                     console.log("请求失败！");
                     alert('请求失败！');
                 });
            }

           var sw=new Swiper(".swiper-container",{
                  autoplay: 5000,
                  loop : true,
                  nextButton: '.swiper-button-next',
                  prevButton: '.swiper-button-prev',
                  on: {
                     slideChangeTransitionEnd: function(){
                         console.log(this.realIndex)
                         var kk = this.realIndex;
                        var orderid = $('.swiper-slide-active').data('id');
                        console.log($('.swiper-slide-active').data('id'));
                         $.ajax({
                             url: '/getcompanybyorderid/',
                             type: 'POST',
                             dataType: 'json',
                             data: {
                                 orderid: orderid
                             },
                         })
                         .done(function(data) {
                             if(data.error_code == 1){  //1表示请求成功
                                 if(data.data.hadspecialcard == 0){   //0表示没有可领取的装修优惠
                                     $('.takezige').text('抱歉，没有可领的装修优惠');
                                     $('#cardlist').hide();
                                     $('.platformjijin').show();
                                     $('.zhuanshuyh').show();
                                     // return
                                 }else{
                                     $('.takezige').text('您有装修优惠可领哦');
                                     document.getElementById("cardlist").innerHTML =data.data.gethtml;
                                     $('#cardlist').show();
                                     $('.platformjijin').show();
                                     $('.zhuanshuyh').show();

                                     //确认量房
                                     $('.sureliagnfwk .sureniu').click(function(){
                                         console.log('订单id：'+$(this).data('orderid'));
                                         console.log('公司id：'+$(this).data('comid'));
                                         $('#comid_text').val($(this).data('comid'));     //记录公司id
                                         $('#orderid_text').val($(this).data('orderid'));   //记录订单id

                                         $('.rulesyiny').fadeIn();
                                         $('.surelfwk').fadeIn();
                                     })

                                     //  未确认量房点击立即领取弹窗
                                     $('.takeendyy .nolftake').click(function(){
                                         $('.rulesyiny').fadeIn();
                                         $('.nolfwk').fadeIn();
                                     })

                                     //  点击【立即领取】弹窗
                                     $('.youhuiqwk .cantake').click(function(){
                                         $('#cardid_text').val($(this).data('cardid'));
                                         // $('.ceshi').text($(this).data('cardid'));\
                                         var cardid = $(this).data('cardid');
                                         var comid = $(this).data('comid');
                                         var orderid = $(this).data('orderid');
                                         /*  领取操作  */
                                         $.ajax({
                                             url: '/card/receiveCard/',
                                             type: 'POST',
                                             dataType: 'json',
                                             data: {
                                                 cardid: cardid,
                                                 comid:comid,
                                                 orderid:orderid
                                             },
                                         })
                                         .done(function(data){
                                             if(data.error_code == 1){
                                                 $('.rulesyiny').fadeIn();
                                                 $('.takesuccesswk').fadeIn();
                                             }else{
                                                 alert(data.error_msg);
                                             }
                                         })
                                         .fail(function() {
                                             console.log("请求失败！");
                                             alert('请求失败！');
                                         });
                                     })

                                     //跳转
                                     $('.youhqneir').click(function(){
                                         var cardid = $(this).data('cardid');
                                         var orderid = $(this).data('orderid');
                                         if(cardid){
                                             window.location.href='/coupontake/'+cardid+'?orderid='+orderid;
                                         }else{
                                             alert('未获取到优惠券id!');
                                             return ;
                                         }
                                     });

                                     // 点击已领完弹窗
                                     $('.youhuiqwk .takeendniu').click(function(){
                                         $('.rulesyiny').fadeIn();
                                         $('.takeendwk').fadeIn();
                                     })
                                     $('.takeendwk-niu').click(function(){
                                         $('.rulesyiny').fadeOut();
                                         $('.takeendwk').fadeOut();
                                     })

                                     //去使用跳转到卡券包
                                     $('.pleaseuse').click(function(){
                                         window.location.href = '/card/cardbag/';
                                     });

                                 }
                             }else if(data.error_code == 4000104){   //4000104表示无分配装修公司
                                 $('.takezige').text('小齐火速帮您处理中，请注意接听电话！');
                                 $('.platformjijin').hide();
                                 $('.zhuanshuyh').hide();
                                 $('#cardlist').hide();
                             }else if(data.error_code == 4000103){  //4000103表示未获取到订单id
                                 alert(data.error_msg);
                             }else{
                                 alert('请求失败');  //无感叹号表示返回错误码未知
                             }
                         })
                         .fail(function() {
                             console.log("请求失败！");
                             alert('请求失败！');
                         });
                     },
                   },
            });

            //    活动规则
             $('.zxxianghaoli01 .huodrule').click(function(){
                 $('.rulesyiny').fadeIn();
                 $('.rulesneirogn').fadeIn();
             })

             $('.rulesneirogn .knowniu').click(function(){
                $('.rulesyiny').fadeOut();
                $('.rulesneirogn').fadeOut();
             })
             //    活动规则

             //  确认量房
             $('.sureliagnfwk .sureniu').click(function(){
                $('.rulesyiny').fadeIn();
                $('.surelfwk').fadeIn();
             })


             $('.sureniuwk .consolider').click(function(){
                $('.rulesyiny').fadeOut();
                $('.surelfwk').fadeOut();
             })

             $('.sureniuwk .surequeren').click(function(){

                 var orderid = $('#orderid_text').val();
                 var comid = $('#comid_text').val();
                 //标记量房
                 $.ajax({
                     url: '/card/signliangfang/',
                     type: 'POST',
                     dataType: 'json',
                     data: {
                         orderid: orderid,
                         comid:comid
                     },
                 })
                 .done(function(data){
                     if(data.error_code == 1){
                         alert('操作成功');
                         $('.rulesyiny').fadeOut();
                         $('.surelfwk').fadeOut();
                         history.go(0); //刷新
                     }else{
                         alert(data.error_msg);
                     }
                 })
                 .fail(function() {
                     console.log("请求失败！");
                     alert('请求失败！');
                 });
                $('.rulesyiny').fadeOut();
                $('.surelfwk').fadeOut();
             })
             //  确认量房

            //   未确认量房点击立即领取弹窗
             $('.takeendyy .nolftake').click(function(){
                $('.rulesyiny').fadeIn();
                $('.nolfwk').fadeIn();
             })

             $('.nolfwk .nolfniu').click(function(){
                $('.rulesyiny').fadeOut();
                $('.nolfwk').fadeOut();
             })
            //   未确认量房点击立即领取弹窗


            // 点击已领完弹窗

           //   领取成功弹窗
          // $('.youhuiqwk .cantake').click(function(){
          //       $('.rulesyiny').fadeIn();
          //       $('.takesuccesswk').fadeIn();
          // })

          $('.takesuccesswk-close').click(function(){

                $('.rulesyiny').fadeOut();
                $('.takesuccesswk').fadeOut();
          })

          if($("body").height()<$("html").height()){
            $("article").height($("html").height() - $("#footer").outerHeight()-$('header').outerHeight())
          }

        })