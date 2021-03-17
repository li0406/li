$(function(){
    var a=0;
    function getNumber(){
        var date=new Date(),hour=date.getHours(),num=99,array,old_num;
        old_num=[$("#bai .inner_top").text(), $("#shi .inner_top").text(), $("#ge .inner_top").text()];
        if(hour>0&&hour<6){
            num=num-hour;
        }else if(hour>=6&&hour<12){
            num=94-(hour-6)*5;
        }else if(hour>=12&&hour<18){
            num=64-(hour-12)*4;

        }else if(hour>=18&&hour<=23){
            num=40-(hour-18)*7;
        }else{
            num=100;
        }
        num=num.toString();
        array=num.split("");

        if(array.length==3){
            fanye("#bai",1);
            fanye("#shi",0);
            fanye("#ge",0);
        }else if(array.length==2){
            $("#bai .inner_top").text(0);
            $("#bai .num_yl").text(0);
            if(old_num[1]!=array[0]){
                fanye("#shi",array[0]);
            }
            if(old_num[2]!=array[1]){
                fanye("#ge",array[1]);
            }
        }else{
            $("#bai .inner_top").text(0);
            $("#bai .num_yl").text(0);
            $("#shi .inner_top").text(0);
            $("#shi .num_yl").text(0);
           if(old_num[2]!=array[0]){
                fanye("#ge",array[0]);
            }
        }

        setTimeout(getNumber,2000);
    }
    getNumber();
    function fanye(obj,number){
        $(obj).find(".xzt:first").addClass("fan");
        $(obj).find(".num_up:first").text(number);
        setTimeout(function(){
            $(obj).find(".bottom_num").append("<div class='inner_bottom xzt'><div class='num_yl'>"+number+"</div><div class='num_up'></div></div>");
        },120);
        setTimeout(function(){
            $(obj).find(".num_up:first").css("z-index","999");
            $(obj).find(".inner_top").text(number);
        },200);
        setTimeout(function(){
            $(obj).find(".fan").remove();
        },400)
    }



    $(".home-zb .m-b-btn").click(function(event) {
        var container = $(this).parents(".home-zb");

        var name = $(".m-bj-edit input[name=name]").val();
        var tel = $(".m-bj-edit input[name=tel]").val();

        var cs = $('input[name=city]').attr('data-id');
        var qx = $('input[name=area]').attr('data-id');

        var checked = $("#mianze").is(':checked');
        // console.log(checked);
         
        
        if (!App.validate.run(name)) {
            $(".m-bj-edit input[name=name]").focus();
            alert("请输入您的称呼");
            return false;
        }else{
            var reg = new RegExp("^[\u4e00-\u9fa5a-zA-Z]+$");
            if(!reg.test(name)){
                $(".m-bj-edit input[name=name]").focus();
                $(".m-bj-edit input[name=name]").val('');
                alert("请输入正确的名称，只支持中文和英文");
                return false;
            }
        }

        if (!App.validate.run(tel)) {
            $(".m-bj-edit input[name=tel]").focus();
            $(".m-bj-edit input[name=tel]").val('');
            alert("请填写正确的手机号码 ^_^!");
            return false;
        }else{
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if(!reg.test(tel)){
                $(".m-bj-edit input[name=tel]").focus();
                $(".m-bj-edit input[name=tel]").val('');
                alert("请填写正确的手机号码 ^_^!");
                return false;
            }
        }

        if('' == cs || '' == qx){
            alert('请选择您所在的区域 ≧▽≦')
            return false;
        }

        if(!checked){
            alert('请勾选我已阅读并同意齐装网的《免责声明》！')
            return false;
        }
         
        window.order({
            extra:{
                cs: cs,
                qx: qx,
                name:$("input[name=name]",container).val(),
                tel:$("input[name=tel]",container).val(),
                fb_type:$("input[name=fb_type]",container).val(),
                source: '312'
            },
            submitBtn:{
                className:$(this),
                type:'btn'
            },
            error:function(){
                 
            },
            success:function(data, status, xhr){
                 
                if(data.status == 1){
                    window.location.href = "/baojiawanshan/";
                }else{
                    alert(data.info);
                }
            },
            validate:function(item, value, method, info){
                return true;
            }
        });
    });
    //首页装修效果图滑动效果
        new JRoll("#wrapper",{scrollX:true,scrollY:false});
        new JRoll("#wrapper1",{scrollX:true,scrollY:false});
        $("#menu_list li").click(function(){
            var index=$(this).index();
            $(this).addClass('menu_active');
            $(this).siblings().removeClass("menu_active");
            $($(".list-box")[index]).attr("id","wrapper");
            $($(".list-box")[index]).siblings().attr("id","");
            new JRoll("#wrapper",{scrollX:true,scrollY:false});
        });


        $("#study_list li").click(function(){
            var index=$(this).index();
            $(this).addClass('menu_active');
            $(this).siblings().removeClass("menu_active");
            $($(".study_item")[index]).addClass('menu_chose');
            $($(".study_item")[index]).siblings().removeClass("menu_chose");

        });
        $("#study_list2 li").click(function(){
            var index=$(this).index();
            $(this).addClass('menu_active');
            $(this).siblings().removeClass("menu_active");
            $($(".study_item2")[index]).addClass('menu_chose');
            $($(".study_item2")[index]).siblings().removeClass("menu_chose");

        });


// 获取随机数的方法
    function GetRandomNum(Min,Max){
        var Range = Max - Min;
        var Rand = Math.random();
        return(Min + Math.round(Rand * Range));
    }
    // 随机数
    var timer = setInterval(function(){
        var num = GetRandomNum(30000,120000)+'';
        if(num<99999){
            var num1 = 'num num-gray',
                num2 = 'num num-' + num.charAt(0),
                num3 = 'num num-' + num.charAt(1),
                num4 = 'num num-' + num.charAt(2),
                num5 = 'num num-' + num.charAt(3),
                num6 = 'num num-' + num.charAt(4);
        }else{
            var num1 = 'num num-' + num.charAt(0),
                num2 = 'num num-' + num.charAt(1),
                num3 = 'num num-' + num.charAt(2),
                num4 = 'num num-' + num.charAt(3),
                num5 = 'num num-' + num.charAt(4),
                num6 = 'num num-' + num.charAt(5);
        }
        $('#num-1').removeClass().addClass(num1);
        $('#num-2').removeClass().addClass(num2);
        $('#num-3').removeClass().addClass(num3);
        $('#num-4').removeClass().addClass(num4);
        $('#num-5').removeClass().addClass(num5);
        $('#num-6').removeClass().addClass(num6);

    },400);

    //切换免责对勾
    $("#check").click(function(){
        $(this).toggleClass('fa-check');
    });

});