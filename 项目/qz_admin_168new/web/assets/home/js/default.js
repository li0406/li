//城市联动
(function($){
    if(typeof mobile == "undefined"){
        mobile = {}
    }

    mobile.citys = {
           citys:$("select[name='cs']"),
           quyu:$("select[name='qy']"),
           init:function(data,info,prefix,cs,qy){
                    var _this = this;
                    var citys = _this.citys;
                    if(typeof cs != "undefined" && cs != null){
                       citys = $("select[name="+cs+"]");
                    }

                    if(typeof qy != "undefined" && qy != null){
                        _this.quyu = $("select[name="+qy+"]");
                    }

                    if(typeof data  == "string"){
                         data = JSON.parse(data);
                    }

                    if(typeof info  == "string"){
                         info = JSON.parse(info);
                    }

                    _this.quyu.find("option").remove();
                    citys.find("option").remove("option");
                    //初始化城市
                    var first = $("<option selected='selected'></option>");
                    first.html("选城市");
                    first.val('000001');
                    citys.prepend(first);
                    //初始化区域
                    var first = $("<option selected='selected'></option>");
                    first.html("选区域");

                    //first.val(0);
                    _this.quyu.prepend(first);

                    for(var i in data){
                        var option = $("<option></option>");
                        if(typeof prefix != "undefined" && prefix == true){
                            option.html(data[i].cname.split(' ')[1]);
                        }else{
                            option.html(data[i].cname);
                        }
                        option.attr("data-id",i);
                        option.val(data[i].cid);
                        if(typeof info != "undefined"&& info != null){
                            if(typeof info.cid != "undefined"){
                                if(data[i].cid == info.cid){
                                    option.attr("selected","selected");
                                    _this.changed(data[i],data[i].cid,info);
                                }
                            }else{
                                if(data[i].cname.indexOf($.trim(info.city)) > 0){
                                    option.attr("selected","selected");
                                    _this.changed(data[i],data[i].cid,info);
                                }
                            }

                        }
                        citys.append(option);
                    }
                    citys.change(function(event) {
                        var i = $(this).find("option:selected").attr("data-id");
                        if (typeof(i)!="undefined") {
                            _this.changed(data[i],$(this).val(),info,prefix);
                        }

                    });

           },
           changed:function(data,cid,info,prefix){
                var _this = this;
                _this.quyu.find("option").remove();
                var first = $("<option selected='selected'></option>");
                first.html("选区域");
                first.val('');
                _this.quyu.prepend(first);
                var child = data["child"];
                var other = null;
                for(var i = 0;i < child.length;i++){
                    var option = $("<option></option>");
                    if(typeof prefix != "undefined" && prefix == true){
                        option.html(child[i].qz_area.split(' ')[1]);
                    }else{
                        option.html(child[i].qz_area);
                    }

                    option.val(child[i].qz_areaid);
                    if(i == 0){
                        other = child[i];
                    }

                    // if(parseInt(child[i].orders) > parseInt(other.orders)){
                    //     _this.quyu.find("option").removeAttr('selected');
                    //     other = child[i];
                    //     option.attr("selected","selected");
                    // }

                    if(typeof info != "undefined" && info != null ){
                        if(child[i].qz_area.indexOf($.trim(info.quyu)) > 0){
                            _this.quyu.find("option").removeAttr('selected');
                            option.attr("selected","selected");
                        }
                    }
                    _this.quyu.append(option);
                }
           }
    }
})(jQuery);

