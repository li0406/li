
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
            if(typeof cs == "undefined" ){
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

    App.singleCity = {
        init:function(t,d,id){
            if(typeof t == "undefined"){
                return false;
            }
            var option = $("<option value=''>城市</option>");
            option.appendTo($(t));
            for(var i in d['shen']){
                var option = $("<option ></option>");
                option.val(d['shen'][i].id);
                option.html(d['shen'][i].cname);
                if(typeof id != "undefined" && id == d['shen'][i].id){
                    option.attr("selected","selected");
                }
                option.appendTo($(t));
            }

        }
    }

    App.validateForm = {
        init:function(o,value,type,length){
            console.log(o);
           // var flag = App.validate.run(value,type,length);
           // if(!flag){

           // }

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
                case 'casemianji':
                        flag =  _this.validate_casemianji(value);
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
        validate_casemianji:function(value){
            //验证小数点2位
            var reg = /^[0-9]{1,5}(\.[0-9]{1,2})?$/i;
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
            var reg = /^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,50}$/;
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

function openwindow(text,callback){
    $('.c-windowbg').remove();
    $('body').append(
       '<div class="c-windowbg"><div class="c-windowbox"><i class="shutdown icon-remove"></i><div class="window-info"> ' + text + '</div><button class="btn-yes">确定</button><button class="btn-no">取消</button></div></div>'
    );

    $('.c-windowbg').find('.shutdown').on('click',function(){
        $('.c-windowbg').remove();
    });

    $('.c-windowbg').find('.btn-yes').on('click',function(){
        if (typeof callback == "function") {
            callback();
        }else{
            $('.c-windowbg').remove();
        }
    });

    $('.c-windowbg').find('.btn-no').on('click',function(){
        $('.c-windowbg').remove();
    });
};

function modal(text,title,callback){
    $('.c-windowbg').remove();
    $('body').append(
       '<div class="c-windowbg"><div class="c-windowbox" style="max-width: 500px;font-size:15px">'+title+'<i class="shutdown icon-remove"></i><div class="window-info"> ' + text + '</div><button class="btn-yes">确定</button></div></div>'
    );
    $('.c-windowbg').find('.shutdown').on('click',function(){
        $('.c-windowbg').remove();
    });
    $('.c-windowbg').find('.btn-yes').on('click',function(){
        if (typeof callback == "function") {
            callback();
        }else{
            $('.c-windowbg').remove();
        }
    });
    $('.c-windowbg').find('.btn-no').on('click',function(){
        $('.c-windowbg').remove();
    });
};


function postStandard(title,type,callback){
    $('.c-windowbg').remove();
	
	if(type == 'baike'){
		var content = '<p>1、描述标准：</p><p>&nbsp; 描述文字不可出现电话号码、QQ号、网址等信息内容。</p><p>2、标题标准： &nbsp;&nbsp;</p><p>&nbsp; 所有词条的条目必须以名词命名，不可使用文章的标题。</p><p>3、正文内容标准：</p><p>（1）词条需分段，词条应包含三个（含）以上的小标题，其要与百科条目相关，内容无广告，不可脱离百科主题，请用正式的中文词句撰写。</p><p>（2）正文排版要清晰，首行要缩进。</p><p>4、缩略图上传： &nbsp;</p><p>&nbsp; 所选用的缩略图一定要与百科条目相关，其图片要清晰、无水印以及完整性。</p>';
	}
	if(type == 'riji'){
		var content = '<p><strong>一、日记编辑标准</strong></p><p>1、日记内容编辑标准</p><p>（1）日记主题：</p><p>&nbsp; &nbsp;每篇日记必须要有主题，其主题要与装修有相关性，然而一个主题下面不可出现相同日记标题与内容。</p><p>（2）日记标题：</p><p>&nbsp; &nbsp;每篇日记必须要有标题，其标题要与装修有相关性。</p><p>（3）日记内容：</p><p>&nbsp; &nbsp;日记内容要与日记标题有相关性。日记内容不可以出现电话、QQ、网址等信息，不可有诋毁之意，不得出现违反国家相关法律的内容，不得有相同内容出现，其插入的图片要无水印、无电话号码、QQ号、网址等信息，不可上传广告活动之类图片。</p><p>（4）账号使用：</p><p>&nbsp; 一个账号下面不可出现相同日记主题、标题、内容的日记（涉及到任意一项都不可以）。</p><p>&nbsp; 注：编辑一个完整的日记:日记主题+日记标题+内容&nbsp;</p><p>2、日记评论编辑标准</p><p>&nbsp; &nbsp;不可以出现电话、QQ、网址等信息，不可有诋毁之意，不得出现违反国家相关法律的内容。&nbsp;</p>';
	}

    $('body').append(
       '<div class="c-windowbg"><div class="c-windowbox" style="max-width:600px;font-size:15px">'+title+'<i class="shutdown icon-remove"></i><div style="text-align:left;padding:10px">'+ content +'<p><br></p><p style="color:#FF0000">友情提示：</p><p style="color:#FF0000">&nbsp; 频道编辑人员会对发布内容进行审核。如有违规内容，编辑人员将会对其进行删除处理，如仍然违规，给予警告提醒，情节严重者，将设置其账号不得发表任何信息（禁言）3~7小时。感谢您的配合~！</p></div></div></div>'
    );
    $('.c-windowbg').find('.shutdown').on('click',function(){
        $('.c-windowbg').remove();
    });
    $('.c-windowbg').find('.btn-no').on('click',function(){
        $('.c-windowbg').remove();
    });
};


