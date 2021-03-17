// 百度地图API功能,浏览器获取城市名称,ajax传入城市名称
var areaid = $("input[name=hide_city_id]").val();
if (typeof areaid == "undefined" || areaid == "") {
    var geolocation = new BMap.Geolocation();
    geolocation.getCurrentPosition(function(r){
        if(this.getStatus() == BMAP_STATUS_SUCCESS){
                //ajax请求获取$cityinfo
                $.ajax({
                    url: "/getCityInfoByName/",
                    type: 'GET',
                    data:{
                        cityname : r.address.city
                    },
                    dataType: 'json',
                })
                .done(function(result) {
                    if(result.status == 1){
                        $("button[id *= 'showCityPicker']").html('<i class="fa fa-map-marker"></i>'+result.info.provincefull+' '+result.info.name+' '+result.info.cityarea);
                        $("input[name=province]").attr('data-id',result.info.pid);
                        $("input[name=city]").attr('data-id',result.info.id);
                        $("input[name=area]").attr('data-id',result.info.areaid);
                    }else{
                        //window.location.href = "http://m.qizuang.com/city/";//获取地址失败，跳转到城市选择页面
                        // $("#showCityPicker2").html('<i class="fa fa-map-marker"></i>'+'请选择您所在的区域');
                        $("button[id *= 'showCityPicker']").html('<i class="fa fa-map-marker"></i>'+'请选择您所在的区域');
                        $("input[name=province]").attr('data-id','');
                        $("input[name=city]").attr('data-id','');
                        $("input[name=area]").attr('data-id','');
                    }
                })
                .fail(function(xhr) {
                    //window.location.href = "http://m.qizuang.com/city/";//获取地址失败，跳转到城市选择页面
                    // $("#showCityPicker2").html('<i class="fa fa-map-marker"></i>'+'请选择您所在的区域');
                    $("button[id *= 'showCityPicker']").html('<i class="fa fa-map-marker"></i>'+'请选择您所在的区域');
                    $("input[name=province]").attr('data-id','');
                    $("input[name=city]").attr('data-id','');
                    $("input[name=area]").attr('data-id','');
                });
        }else{
            $("button[id *= 'showCityPicker']").html('<i class="fa fa-map-marker"></i>'+'请选择您所在的区域');
            $("input[name=province]").attr('data-id','');
            $("input[name=city]").attr('data-id','');
            $("input[name=area]").attr('data-id','');
        }
    },{enableHighAccuracy: true})
}

    //关于状态码
    //BMAP_STATUS_SUCCESS   检索成功。对应数值“0”。
    //BMAP_STATUS_CITY_LIST 城市列表。对应数值“1”。
    //BMAP_STATUS_UNKNOWN_LOCATION  位置结果未知。对应数值“2”。
    //BMAP_STATUS_UNKNOWN_ROUTE 导航结果未知。对应数值“3”。
    //BMAP_STATUS_INVALID_KEY   非法密钥。对应数值“4”。
    //BMAP_STATUS_INVALID_REQUEST   非法请求。对应数值“5”。
    //BMAP_STATUS_PERMISSION_DENIED 没有权限。对应数值“6”。(自 1.1 新增)
    //BMAP_STATUS_SERVICE_UNAVAILABLE   服务不可用。对应数值“7”。(自 1.1 新增)
    //BMAP_STATUS_TIMEOUT   超时。对应数值“8”。(自 1.1 新增)