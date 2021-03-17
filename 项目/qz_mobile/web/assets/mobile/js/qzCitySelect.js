// 城市数据优化
rlpca.shift();
for(var i=0;i<rlpca.length;i++){
    rlpca[i].children.shift()
    for(var j=0;j<rlpca[i].children.length;j++){
          rlpca[i].children[j].children.shift()
    }
};
// 城市选择
var selectQz = {
    init:function(option){
        option = option || {};
        option.province = parseInt(option.province) || null;
        option.city = parseInt(option.city) || null;
        option.area = parseInt(option.area) || null;
        /*插入DOM
        *qz_Area         ----------- 三级城市父节点
        *qz_provinceArea ----------- 省份
        *qz_cityArea     ----------- 地级市
        *qz_currentArea  ----------- 区域
        */

        if(!$('.qz_Area')){
            $('<div class="qz_Area"><div class="qz_provinceArea"></div><div class="qz_cityArea"></div><div class="qz_currentArea"></div></div>').appendTo($('body'));
        }else{
            $('.qz_Area').each(function(index, el) {
                $(this).remove();
            });
            $('<div class="qz_Area"><div class="qz_provinceArea"></div><div class="qz_cityArea"></div><div class="qz_currentArea"></div></div>').appendTo($('body'));
        }
        // 判断有没有获取到GEO定位省份ID、地级市ID、区域ID
        if(option.province && option.city && option.area){// 如果GEO定位到、就按照定位到的ID数据排序初始化
            for(var j = 0;j<rlpca.length;j++){
                if(rlpca[j].id == option.province){// 判断定位省份
                    for(var k = 0;k<rlpca[j].children.length;k++){
                        if(rlpca[j].children[k].id == option.city){// 判断定位地级市
                            for(var l = 0;l<rlpca[j].children[k].children.length;l++){
                                if(rlpca[j].children[k].children[l].id == option.area){// 判断定位区域
                                    var j1=j,k1=k,l1=l;
                                    selectQz.createLeft(rlpca,j);
                                    selectQz.createCenter(rlpca[j].children,0,k);
                                    selectQz.createRight(rlpca[j].children[k].children,0,0,l);
                                }
                            }
                        }
                    }
                }
            }
            selectQz.events(j1,k1,l1);
        }else{// 如果GEO没有定位到按照数据排序初始化
            selectQz.createLeft(rlpca);
            selectQz.createCenter(rlpca[0].children,0);
            selectQz.createRight(rlpca[0].children[0].children,0,0);
            selectQz.events();
        }
        // 遮罩层
        if($('.qz_cityMask').length<1){
            $('<div class="qz_cityMask"></div>').insertAfter(".qz_Area");
        }

    },
    createLeft:function(data,j){
        var html = [];
        html.push("<ul>");
        $.each(data,function(index,obj){
            html.push('<li class="qz_province_li" province-id="'+obj.id+'" data-id="'+index+'"><span>'+obj.text+'</span></li>');
        });
        html.push("</ul>");
        html = html.join('');
        $(".qz_provinceArea").html(html);
        var jroll = new JRoll(".qz_provinceArea", {id: "scroller"});// 初始化省份列可以滚动
        var height = $(".qz_provinceArea").find('li').eq(0).outerHeight();// 获取单个li的高度
        if(j){
            $(".qz_provinceArea").find('li').eq(j).addClass('qz_active');// 标记定位省份样式
            jroll.scrollTo(0, -j*height, 0, true);// 滚动到定位省份
        }else{
            $(".qz_provinceArea").find('li:first').addClass('qz_active');// 如果没有定位到省份，默认标记第一个省份样式
        }
    },
    createCenter:function(data,parentid,k){
        var html = [];
        html.push("<ul>");
        $.each(data,function(index,obj){
            html.push('<li class="qz_city_li" city-id="'+obj.id+'" data-parentid="'+parentid+'" data-id="'+index+'"><span>'+obj.text+'</span></li>');
        });
        html.push("</ul>");
        html = html.join('');
        $(".qz_cityArea").html(html);
        var jroll1 = new JRoll(".qz_cityArea", {id: "scroller1"}); // 初始化地级市列可以滚动
        var height = $(".qz_cityArea").find('li').eq(0).outerHeight();// 获取单个li的高度
        if(k){
            jroll1.scrollTo(0, -k*height, 0, true);// 滚动到定位地级市
            $(".qz_cityArea").find('li').eq(k).addClass('qz_active');// 标记定位地级市样式
        }else{
            $(".qz_cityArea").find('li:first').addClass('qz_active')// 如果没有定位到地级市，默认标记第一个地级市样式
        }
    },
    createRight:function(data,rootid,parentid,l){
        var html = [];
        html.push("<ul>");
        $.each(data,function(index,obj){
            html.push('<li class="qz_area_li" area-id="'+obj.id+'" data-rootid="'+rootid+'" data-parentid="'+parentid+'" data-id="'+index+'"><span>'+obj.text+'</span></li>');
        });
        html.push("</ul>");
        html = html.join('');
        $(".qz_currentArea").html(html);
        var jroll2 = new JRoll(".qz_currentArea", {id: "scroller2"});// 初始化区域列可以滚动
        var height = $(".qz_currentArea").find('li').eq(0).outerHeight();// 获取单个li的高度
        if(l >= 0){
            jroll2.scrollTo(0, -l*height, 0, true);// 滚动到定位区域
            $(".qz_currentArea").find('li').eq(l).addClass('qz_active');// 标记定位区域样式
        }
    },
    events:function(j,k,l){
        // j 定位到的省份 k 定位到的地级市 l 定位到的区域
        //绑定一级联动点击事件
        $("body").on("click",".qz_province_li",function(){
            var id = $(this).data("id");
            jroll = new JRoll(".qz_provinceArea", {id: "scroller"});
            $(this).siblings().removeClass('qz_active');
            $(this).addClass('qz_active');
            selectQz.createCenter(rlpca[id].children,id); //创建二级联动
            selectQz.createRight(rlpca[id].children[0].children,id,0); //创建三级联动
            j = id;
        });
        //绑定二级联动点击事件
        $("body").on("click",".qz_city_li",function(){
            var parentid = $(this).data("parentid");
            jroll1 = new JRoll(".qz_cityArea", {id: "scroller1"});
            $(this).siblings().removeClass('qz_active');
            $(this).addClass('qz_active');
            var id = $(this).data("id");
            k = id;
            if(j){
                selectQz.createRight(rlpca[j].children[id].children,parentid,id); //创建三级联动
            }else{
                j = parentid;
                selectQz.createRight(rlpca[parentid].children[id].children,parentid,id); //创建三级联动
            }
        });
        //绑定三级联动点击事件
        $("body").on("click",".qz_area_li",function(){
            var rootid = $(this).data("rootid");
            jroll2 = new JRoll(".qz_currentArea", {id: "scroller2"});
            $(this).siblings().removeClass('qz_active');
            $(this).addClass('qz_active');
            var parentid = $(this).data("parentid");
            var id = $(this).data("id");
            $('.qz_cityMask').hide();
            $('.qz_Area').animate({right: "-800px"}, 800,function(){
                $('.qz_Area').hide();
            });
            // 重新获取赋值---省市区
            if(j == rootid && k != parentid){
                k = 0;
                $('#showCityPicker2').html('<i class="fa fa-map-marker"></i> '+
                    rlpca[rootid].text+" "+
                    rlpca[rootid].children[k].text+" "+
                    rlpca[rootid].children[k].children[id].text).css('color',"#666");

                $('#showCityPicker10').html('<i class="fa fa-map-marker"></i> '+
                    rlpca[rootid].text+" "+
                    rlpca[rootid].children[k].text+" "+
                    rlpca[rootid].children[k].children[id].text).css('color',"#666");

                $('#showCityPicker11').html('<i class="fa fa-map-marker"></i> '+
                    rlpca[rootid].text+" "+
                    rlpca[rootid].children[k].text+" "+
                    rlpca[rootid].children[k].children[id].text).css('color',"#666");

                $('#showCityPicker9').html('<i class="map-marker fr"></i> '+
                    rlpca[rootid].text+" "+
                    rlpca[rootid].children[k].text+" "+
                    rlpca[rootid].children[k].children[id].text).css('color',"#666");

                $('#showCityPicker3').html('<i class="fa fa-map-marker"></i> '+
                    rlpca[rootid].text+" "+
                    rlpca[rootid].children[k].text+" "+
                    rlpca[rootid].children[k].children[id].text).css('color',"#666");

                $('#showCityPicker4').html('<i class="fa fa-map-marker"></i> '+
                    rlpca[rootid].text+" "+
                    rlpca[rootid].children[k].text+" "+
                    rlpca[rootid].children[k].children[id].text).css('color',"#333");

                    $('#showCityPicker5').html('<i class="fa fa-map-marker"></i> '+
                        rlpca[rootid].text+" "+
                        rlpca[rootid].children[k].text+" "+
                        rlpca[rootid].children[k].children[id].text).css('color',"#333");

            }else{
                if(!j && !k){
                    $('#showCityPicker2').html('<i class="fa fa-map-marker"></i> '+
                        rlpca[rootid].text+" "+
                        rlpca[rootid].children[parentid].text+" "+
                        rlpca[rootid].children[parentid].children[id].text).css('color',"#666");

                    $('#showCityPicker10').html('<i class="fa fa-map-marker"></i> '+
                        rlpca[rootid].text+" "+
                        rlpca[rootid].children[parentid].text+" "+
                        rlpca[rootid].children[parentid].children[id].text).css('color',"#666");

                    $('#showCityPicker11').html('<i class="fa fa-map-marker"></i> '+
                        rlpca[rootid].text+" "+
                        rlpca[rootid].children[parentid].text+" "+
                        rlpca[rootid].children[parentid].children[id].text).css('color',"#666");

                    $('#showCityPicker9').html('<i class="map-marker fr"></i> '+
                        rlpca[rootid].text+" "+
                        rlpca[rootid].children[parentid].text+" "+
                        rlpca[rootid].children[parentid].children[id].text).css('color',"#666");

                    $('#showCityPicker3').html('<i class="fa fa-map-marker"></i> '+
                        rlpca[rootid].text+" "+
                        rlpca[rootid].children[parentid].text+" "+
                        rlpca[rootid].children[parentid].children[id].text).css('color',"#666");

                    $('#showCityPicker4').html('<i class="fa fa-map-marker"></i> '+
                        rlpca[rootid].text+" "+
                        rlpca[rootid].children[parentid].text+" "+
                        rlpca[rootid].children[parentid].children[id].text).css('color',"#333");

                    $('#showCityPicker5').html('<i class="fa fa-map-marker"></i> '+
                        rlpca[rootid].text+" "+
                        rlpca[rootid].children[parentid].text+" "+
                        rlpca[rootid].children[parentid].children[id].text).css('color',"#333");
                }else{
                    $('#showCityPicker2').html('<i class="fa fa-map-marker"></i> '+
                        rlpca[j].text+" "+
                        rlpca[j].children[k].text+" "+
                        rlpca[j].children[k].children[id].text).css('color',"#666");

                    $('#showCityPicker10').html('<i class="fa fa-map-marker"></i> '+
                        rlpca[j].text+" "+
                        rlpca[j].children[k].text+" "+
                        rlpca[j].children[k].children[id].text).css('color',"#666");

                    $('#showCityPicker11').html('<i class="fa fa-map-marker"></i> '+
                        rlpca[j].text+" "+
                        rlpca[j].children[k].text+" "+
                        rlpca[j].children[k].children[id].text).css('color',"#666");

                    $('#showCityPicker9').html('<i class="map-marker fr"></i> '+
                        rlpca[j].text+" "+
                        rlpca[j].children[k].text+" "+
                        rlpca[j].children[k].children[id].text).css('color',"#666");

                    $('#showCityPicker3').html('<i class="fa fa-map-marker"></i> '+
                        rlpca[j].text+" "+
                        rlpca[j].children[k].text+" "+
                        rlpca[j].children[k].children[id].text).css('color',"#666");

                    $('#showCityPicker4').html('<i class="fa fa-map-marker"></i> '+
                        rlpca[j].text+" "+
                        rlpca[j].children[k].text+" "+
                        rlpca[j].children[k].children[id].text).css('color',"#333");

                    $('#showCityPicker5').html('<i class="fa fa-map-marker"></i> '+
                        rlpca[j].text+" "+
                        rlpca[j].children[k].text+" "+
                        rlpca[j].children[k].children[id].text).css('color',"#333");

                }
            }
            // 隐藏域赋值
            $('input[name=province]').attr('data-id', $(".qz_provinceArea").find(".qz_active").attr("province-id"));
            $('input[name=city]').attr('data-id', $(".qz_cityArea").find(".qz_active").attr("city-id"));
            $('input[name=area]').attr('data-id', $(".qz_currentArea").find(".qz_active").attr("area-id"));
        });

        $('#showCityPicker2').on('click',function(){
            $('.qz_Area').show().stop().animate({right: 0}, 300);
            $('.qz_cityMask').fadeIn(100);
            jroll = new JRoll(".qz_provinceArea", {id: "scroller"});
            jroll1 = new JRoll(".qz_cityArea", {id: "scroller1"});
            jroll2 = new JRoll(".qz_currentArea", {id: "scroller2"});
        });

        $('#showCityPicker10').on('click',function(){
            $('.qz_Area').show().stop().animate({right: 0}, 300);
            $('.qz_cityMask').fadeIn(100);
            jroll = new JRoll(".qz_provinceArea", {id: "scroller"});
            jroll1 = new JRoll(".qz_cityArea", {id: "scroller1"});
            jroll2 = new JRoll(".qz_currentArea", {id: "scroller2"});
        });

        $('#showCityPicker11').on('click',function(){
            $('.qz_Area').show().stop().animate({right: 0}, 300);
            $('.qz_cityMask').fadeIn(100);
            jroll = new JRoll(".qz_provinceArea", {id: "scroller"});
            jroll1 = new JRoll(".qz_cityArea", {id: "scroller1"});
            jroll2 = new JRoll(".qz_currentArea", {id: "scroller2"});
        });

        $('#showCityPicker9').on('click',function(){
            $('.qz_Area').show().stop().animate({right: 0}, 300);
            $('.qz_cityMask').fadeIn(100);
            jroll = new JRoll(".qz_provinceArea", {id: "scroller"});
            jroll1 = new JRoll(".qz_cityArea", {id: "scroller1"});
            jroll2 = new JRoll(".qz_currentArea", {id: "scroller2"});
        });

        $('#showCityPicker3').on('click',function(){
            $('.qz_Area').show().stop().animate({right: 0}, 300);
            $('.qz_cityMask').fadeIn(100);
            jroll = new JRoll(".qz_provinceArea", {id: "scroller"});
            jroll1 = new JRoll(".qz_cityArea", {id: "scroller1"});
            jroll2 = new JRoll(".qz_currentArea", {id: "scroller2"});
        });

        $('body').on('click','#showCityPicker4',function(){
            $('.qz_Area').show().stop().animate({right: 0}, 300);
            $('.qz_cityMask').fadeIn(100);
            jroll = new JRoll(".qz_provinceArea", {id: "scroller"});
            jroll1 = new JRoll(".qz_cityArea", {id: "scroller1"});
            jroll2 = new JRoll(".qz_currentArea", {id: "scroller2"});


        });

        $('body').on('click','#showCityPicker5',function(){
            $('.qz_Area').show().stop().animate({right: 0}, 300);
            $('.qz_cityMask').fadeIn(100);
            jroll = new JRoll(".qz_provinceArea", {id: "scroller"});
            jroll1 = new JRoll(".qz_cityArea", {id: "scroller1"});
            jroll2 = new JRoll(".qz_currentArea", {id: "scroller2"});


        });

        $('body').on('touchend','.qz_cityMask',function(){
            $(this).fadeOut(100).hide();
            $('.qz_Area').stop().animate({right: "-800px"}, 300,function(){
                $('.qz_Area').hide();
            });
            return false;
        });
    }
}
