
(function($){
    if(typeof App == "undefined"){
        App = {}
    }

    /**
     * //城市联动
     * @citys 城市的对象ID
     * @quyu  区县的对象ID
     * @shen  城市的数据
     * @shi   区县的数据
     * @bcs  绑定的城市
     * @bqy  绑定到区县
     * @isALL 是否显示一个城市
     */
    App.citys = {
           init:function(cs,qy,shen,shi,bcs,bqy,isAll){
            var _this = this;
            if(typeof cs == "undefined" || typeof qy == "undefined"){
                return false;
            }

           // if(typeof bcs == "undefined" || bcs == null || bcs == ""){
            var option = $("<option value=''>城市</option>");
            option.appendTo($(cs));
            var option = $("<option value=''>地区</option>");
            option.appendTo($(qy));
            // }else{

            //      if(isAll == true){


            //      }else{
            //         // $(cs).attr("disabled","disabled");
            //          // $('.cs_winbox').wrap('<span style="border: 1px solid #CCC;overflow: hidden;margin: 0px 0 10px 5px;float: left;height: 28px;padding: 0 0 0 21px;width: 72px;"></span>'); //包裹一层用于做下拉框三角形隐藏
            //          // $('.cs_winbox').css({
            //          //     margin: '-2px -16px -2px -22px',
            //          //     width: '138px',
            //          //     border: 'none'
            //          // });
            //      }

           // }

            for(var i in shen){
                var option = $("<option ></option>");
                option.val(shen[i].id);
                if(typeof bcs == "undefined" || bcs == null || bcs == ""){
                    option.html(shen[i].cname);
                }else{
                    option.html(shen[i].cname);
                }

                if(typeof bcs != "undefined" && bcs == shen[i].id){
                    option.attr("selected","selected");
                }
                option.appendTo($(cs));
            }

            $(cs).change(function(event) {
                _this.changed($(qy),shi[$(this).val()],bqy);
            });

            if(typeof bcs != "undefined"){
                $(cs).trigger('change');
            }
        },
        changed:function(target,data,bqy){
            target.find("option").remove();
            if(typeof data != "undefined"){
                  for(var i in data){
                    var option = $("<option ></option>");
                    option.val(data[i].qz_areaid);
                    option.html(data[i].oldName);
                    if(typeof bqy != "undefined" && bqy == data[i].qz_areaid){
                        option.attr("selected","selected");
                    }
                    option.appendTo(target);
                }
            }else{
                var option = $("<option value=''>地区</option>");
                option.appendTo(target);
            }

        }
    }

   /**
    * 表单验证类
    * value [验证的值]
    * type  [验证的类型 num tel email string decimal ]
    * length  [验证长度 ]
    * callback [回调函数]
    * @type {Object}
    */
    App.validate ={
        run:function(value,type,length){
            var _this = this;
            var flag = false;
            switch(type){
                case "num":
                flag = _this.validate_numberic(value);
                    break;
                case "tel":
                flag =  _this.validate_tel(value);
                    break;
                case "email":
                flag =  _this.validate_email(value);
                    break;
                case "decimal":
                flag =  _this.validate_decimal(value,length);
                    break;
                case "moblie":
                flag =  _this.validate_moblie(value);
                    break;
                case "length":
                flag =  _this.validate_length(value,length);
                    break;
                case "blend":
                flag =  _this.validate_blend(value);
                    break;
                case "maxlength":
                flag =  _this.validate_maxlength(value,length);
                    break;
                case "specialchar":
                flag =  _this.validate_specialchar(value);
                    break;
                default:
                flag =  _this.validate_string(value);
                    break;
            }
            return flag;
        },
        validate_specialchar:function(value){
            var reg = /[\`~!@#$%^&*\(\)_+<>?:"{},.\/;\[\]\-]/i;
            if(reg.test(value)){
                return false;
            }
            return true;
        },
        validate_string:function(value){
            //验证字符串
            if(value == "" || value.length <=0){
                return false;
            }
            return true;
        },
        validate_numberic:function(value){
            //验证纯数字
            var reg = /^[0-9]+$/i;
            if(!reg.test(value)){
                return false;
            }
            return true;
        },
        validate_decimal:function(value){
            //验证小数点2位
            var reg = /^[0-9]{1,4}(\.[0-9]{1,2})?$/i;
            if(!reg.test(value)){
                return false;
            }
            return true;
        },
        validate_tel:function(value){
            //验证电话/手机
            var reg = /^([0-9]{3,4}\-)?([0-9]{7,8})$|^[0-9]{11}$/i;
            if(!reg.test(value)){
                return false;
            }
            return true;
        },
        validate_moblie:function(value){
            //验证电话/手机
            var reg = /^[1]{1}[0-9]{10}$/i;
            if(!reg.test(value)){
                return false;
            }
            return true;
        },
        validate_email:function(value){
            //验证邮箱地址
            var reg = /^[A-Za-z\d]+([-_.][A-Za-z\d]+)*@([A-Za-z\d]+[-.])+[A-Za-z\d]{2,5}$/i;
            if(!reg.test(value)){
                return false;
            }
            return true;
        },
        validate_length:function(value,length){
            //验证最少字符串长度
            if(value < length){
                return false;
            }
            return true;
        },
        validate_maxlength:function(value,length){
            //验证最大字符串长度
            if(value > length){
                return false;
            }
            return true;
        },
        validate_blend:function(value){
            //混合字符，不能纯字母或数字
            var reg = /^(?=.*[a-z_\-])[0-9a-z][a-z0-9_\-]+$/i;
            if(!reg.test(value)){
                return false;
            }
            return true;
        }
    }


    App.SMS = {
        run:function(savedata,callback){
            $.ajax({
                url: '/dispatcher/',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    type:"sms",
                    action:"load",
                    savedata:savedata
                }
            })
            .done(function(data) {
                if (data.status == 1) {
                    $("body").append(data.data);
                    $(".win_sms").fadeIn(400);
                    $(".win_smsmain .botton").click(function(event) {
                        $(".win_smsmain .tip").html("");
                        $(".win_smsmain .focus").removeClass("focus");
                        var _this = $(this);
                        if(!App.validate.run($(".win_smsmain input[name=tel]").val(),"string")){
                            $(".win_smsmain input[name=tel]").focus();
                            $(".win_smsmain input[name=tel]").addClass('focus');
                            $(".win_smsmain .tip").html("请填写手机号");
                            return false;
                        }

                        if(!App.validate.run($(".win_smsmain input[name=tel]").val(),"moblie")){
                            $(".win_smsmain input[name=tel]").focus();
                            $(".win_smsmain input[name=tel]").addClass('focus');
                            $(".win_smsmain .tip").html("无效的手机号");
                            return false;
                        }

                        if(!App.validate.run($(".win_smsmain input[name=code]").val(),"string")){
                            $(".win_smsmain input[name=code]").focus();
                            $(".win_smsmain input[name=code]").addClass('focus');
                            $(".win_smsmain .tip").html("请填写验证码");
                            return false;
                        }

                        $.ajax({
                            url: "/verifysmscode/",
                            type: 'POST',
                            dataType : "JSON",
                            data: {
                                code: $(".win_smsmain input[name=code]").val(),
                                tel:$(".win_smsmain input[name=tel]").val()
                            },
                            success: function(data) {
                                if (data.status == 1) {
                                    $(".win_sms").remove();
                                    $(".popOut").remove();
                                    if(typeof callback == "function"){
                                        callback();
                                    }
                                }else{
                                     $(".win_smsmain .tip").html(data.info);
                                }
                            },
                            error: function(xhr) {
                                $(".win_smsmain .tip").html("提交失败,请稍后再试！");
                            }
                        });
                    });
                }
            });
        }
    }

    App.FB ={
        init:function(savedata,callback){
            $.ajax({
                url: '/dispatcher/',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    type:"zxfb",
                    action:"load"
                }
            })
            .done(function(data) {
                if (data.status == 1) {
                    $("body").append(data.data);
                    $(".win_zxfb").fadeIn(400);
                    $(".win_zxfb .win_smsmain .botton").click(function(event) {
                        $(".win_zxfb .win_smsmain .tip").html("");
                        $(".win_zxfb .win_smsmain .focus").removeClass("focus");
                        var _this = $(this);
                        _this.attr("disabled","disabled");
                        if(!App.validate.run($(".win_smsmain input[name=tel]").val(),"string")){
                            $(".win_zxfb .win_smsmain input[name=tel]").focus();
                            $(".win_zxfb .win_smsmain input[name=tel]").addClass('focus');
                            $(".win_zxfb .win_smsmain .tip").html("请填写手机号");
                            return false;
                        }

                        if(!App.validate.run($(".win_smsmain input[name=tel]").val(),"moblie")){
                            $(".win_zxfb .win_smsmain input[name=tel]").focus();
                            $(".win_zxfb .win_smsmain input[name=tel]").addClass('focus');
                            $(".win_zxfb .win_smsmain .tip").html("无效的手机号");
                            return false;
                        }

                        var json = savedata;
                        json["tel"] = $(".win_zxfb .win_smsmain input[name=tel]").val();
                        $.ajax({
                            type : "POST",
                            url : "/fb_order/",
                            dataType : "json",
                            data:json,
                            success : function(data){
                                if(data.status == 0){
                                    _this.attr("disabled",false);
                                    $(".win_zxfb .win_smsmain .tip").html(data.info);
                                }else{
                                    $(".win_zxfb").remove();
                                    if(typeof callback == "function"){
                                        callback();
                                    }
                                }
                            },
                            error:function(xhr){
                                _this.attr("disabled",false);
                                $(".win_zxfb .win_smsmain .tip").html("发送失败,请稍后再试！");
                            }
                        });
                    });
                }
            });
        }
    }


    App.addfavorite = {
        run:function(){
            var url = window.location;
             var title = document.title;
             var ua = navigator.userAgent.toLowerCase();
            if (ua.indexOf("360se") > -1) {
                 alert("由于360浏览器功能限制，请按 Ctrl+D 手动收藏！");
            } else if (ua.indexOf("msie 8") > -1) {
                    window.external.AddToFavoritesBar(url, title); //IE8
            } else if (document.all) {
                try {
                    window.external.addFavorite(url, title);
                } catch (e) {
                    alert('您的浏览器不支持,请按 Ctrl+D 手动收藏!');
                }

            } else if (window.sidebar) {
                window.sidebar.addPanel(title, url, "");
            } else {
                alert('您的浏览器不支持,请按 Ctrl+D 手动收藏!');
            }
        }
    }

    App.pop ={
        run:function(v,str){
            if(typeof v != "string" && typeof v != "object"){
                return false;
            }

            if(typeof v == "string"){
                v = $(v);
            }
            $(".tooltips").remove();
            var top = v.offset().top - v.height();
            var left = v.offset().left;
            var pop = $("<div></div>");
            pop.addClass('tooltips');
            pop.offset({top:top,left:left});
            var popin  = $("<div></div>");
            popin.addClass('tooltips-in')
            popin.appendTo(pop);
            var bg = $("<em></em>");
            bg.addClass('arrar_bg');
            var em = $("<em></em>");
            bg.appendTo(popin);
            em.appendTo(popin);
            var text = $("<p></p>");
            text.html(str);
            text.appendTo(popin)
            popin.appendTo(pop);
            $("body").append(pop);
            var t = setTimeout(function(){
                $(".tooltips").hide(function(){
                    clearTimeout(t);
                    $(this).remove();
                });
            }, 3000);
        },
        reset:function(){
             $(".tooltips").remove();
        }
    }

    var forbid = {
        init:function(options){
            var _this = this;
            var defaults = {
                rightClick:true,//右键
                copy:true,//复制
                Paste:true,//粘贴
                source:true,//源文件
                select:true//选择
            }
            var option =  $.extend({},defaults, options);

            if(!option.rightClick){
                document.oncontextmenu = function(event){
                    return false;
                }
            }

            if(!option.copy){
                document.body.ondragstart= function(){
                    return false;
                }
                document.body.oncopy = function (){
                    alert("您访问的页面,内容受保护,禁止复制！");
                    return false;
                }
            }

            if(!option.select){
                if(document.all)
                {
                   document.body.onselectstart= function(){return false;}; //for ie
                }
                else
                {
                   document.body.onmousedown= function(){return false;};

                   document.body.onmouseup= function(){return true;};

                }
            }
        }
    }

    App.getCookie = function (name) {
        var arr,reg = new RegExp("(^| )"+name+"=([^;]*)(;|$)");
        if(arr = reg.exec(document.cookie)){
            return unescape(arr[2]);
        }
        return null;
    }

    App.setCookie =  function (name,value,expires){
        var path ="";
        var date = new Date();
        if (typeof expires == "undefined") {
            expires = 30*60*1000;
        } else {
            expires = expires*1000;
        }
        date.setTime(date.getTime() + expires);
        var expires = "; expires=" + date.toGMTString();
        document.cookie = name + "=" + encodeURIComponent(value) + expires + ";domain=.qizuang.com;path=/";
    }

    $.fn.forbidmenu= function(method) {
        if(forbid[method]) {
            return forbid[method].apply(this, arguments);
        } else if( typeof(method) == 'object' || !method ) {
           return forbid.init.apply(this, arguments);
        } else {
            $.error( 'Method ' +  method + ' does not exist on forbidmenu' );
            return this;
        }
    }

    var sendOrderMethod = {
        init:function(options){
            var defaults = {
                callback:null,
                data:null
            }
            var _this = this;
            var options =  $.extend({},defaults, options);

             $.ajax({
                    url: '/fb_order/',
                    type: 'POST',
                    dataType: 'JSON',
                    data: options.data
                })
                .done(function(data) {
                    $("#safecode").val(data.data.safecode);
                            $("#safekey").val(data.data.safekey);
                    if (data.status == 1) {
                         if(typeof options.callback == "function"){
                            options.callback();
                         }
                    } else {
                        $.pt({
                            target: _this,
                            content: data.info,
                            width: 'auto'
                        });
                    }
                })
                .fail(function(xhr) {
                    $.pt({
                        target: _this,
                        content: '发布失败,请刷新页面！',
                        width: 'auto'
                    });
                });
        }
    }

    $.fn.sendOrder = function(method) {
        if(sendOrderMethod[method]) {
            return sendOrderMethod[method].apply(this, arguments);
        } else if( typeof(method) == 'object' || !method ) {
           return sendOrderMethod.init.apply(this, arguments);
        } else {
            $.error( 'Method ' +  method + ' does not exist on forbidmenu' );
            return this;
        }
    }


})(jQuery);

