// 城市初始化
var map = '',
    sContent = '',
    page = 1,
    v = '';

var city = GetChinese($("#f_y_cs option:selected").text());

map = new BMap.Map("container", {enableMapClick: false});
map.enableScrollWheelZoom();
map.disableDoubleClickZoom();//启用双击放大，默认启用。

// 获取列表
getList()
var infoWindowList = []


$('#search-con').focus(function () {
    if ($(this).val() == '请输入关键字...') {
        $(this).val('');
    }
});


$('#search-con').blur(function () {
    if ($(this).val() == '') {
        $(this).val('请输入关键字...');
    }
});


// 获取列表并生成对应地图
function getList(name) {
    infoWindowList = []

    $.ajax({
        url: '/zxdt/map',
        type: 'GET',
        dataType: 'JSON',
        data: {
            company: name,
            city: $("#f_y_cs option:selected").val(),
            p: page
        }
    }).done(function (data) {

        var List = data.data.list
        var pointList = [];
        // 创建对应信息窗口和标点
        for (var i = 0; i < List.length; i++) {
            var point = new BMap.Point(List[i].lng, List[i].lat);
            pointList.push(point);

            var numStr = '';
            var hpStr = '';
            (function () {
                if (List[i].case_num == 0) {
                    numStr = "<span>设计师:<i>" + List[i].designer_num + "</i>位</span>" +
                        "<span>工地:<i>" + List[i].site_num + "</i>家</span>"
                }
                if (List[i].designer_num == 0) {
                    numStr =
                        "<span>设计案例:<i>" + List[i].case_num + "</i>套</span>" +
                        "<span>工地:<i>" + List[i].site_num + "</i>家</span>"
                }
                if (List[i].site_num == 0) {
                    numStr =
                        "<span>设计案例:<i>" + List[i].case_num + "</i>套</span>" +
                        "<span>设计师:<i>" + List[i].designer_num + "</i>位</span>"
                }
                if (List[i].case_num == 0 && List[i].designer_num == 0) {
                    numStr =
                        "<span>工地:<i>" + List[i].site_num + "</i>家</span>"
                }
                if (List[i].case_num == 0 && List[i].site_num == 0) {
                    numStr =
                        "<span>设计师:<i>" + List[i].designer_num + "</i>位</span>"
                }
                if (List[i].designer_num == 0 && List[i].site_num == 0) {
                    numStr =
                        "<span>设计案例:<i>" + List[i].case_num + "</i>套</span>"
                }
                if (List[i].case_num == 0 && List[i].designer_num == 0 && List[i].site_num == 0) {
                    numStr = ''
                }
                if (List[i].case_num != 0 && List[i].designer_num != 0 && List[i].site_num != 0) {
                    numStr =
                        "<span>设计案例:<i>" + List[i].case_num + "</i>套</span>" +
                        "<span>设计师:<i>" + List[i].designer_num + "</i>位</span>" +
                        "<span>工地:<i>" + List[i].site_num + "</i>家</span>"
                }


                if (List[i].haoping_lv == 0) {
                    hpStr = ""
                } else {
                    hpStr = "<p><i>好评率：</i><span>" + Math.round((List[i].haoping_lv * 10000) / 100).toFixed(0) + '%' + "</span></p>"
                }

            })()
            if (List[i].intro.length > 55) {
                List[i].intro = List[i].intro.substring(0, 55) + '...'
            } else {
                List[i].intro = List[i].intro
            }


            if (List[i].type == 3) {
                // 信息窗口模板
                sContent = "<div class='zxdt-card'>" +
                    "<div class='zxdt-card-top clearfix'>" +
                    "<div class='company-img'>" +
                    "<img src='" + List[i].logo + "'>" +
                    "<span>进入店铺</span>" +
                    "</div>" +
                    "<div class='company-con'>" +
                    "<h2><span>" + List[i].company_name + "</span></h2>" +
                    hpStr +
                    "<p class='intro'><i>公司简介：</i>" + List[i].intro + "</p>" +
                    "<div class='num'>" +
                    numStr +
                    "</div>" +
                    "</div>" +
                    "</div>" +
                    "<div class='zxdt-card-bottom clearfix'>" +
                    "<div class='zxdt-info'>" +
                    "<p title=" + List[i].address + ">地址：" + List[i].address + "</p>" +
                    "<p>电话：<span>" + tel + "</span></p>" +
                    "</div>" +
                    "<div class='zxdt-btn'>" +
                    "<a target='_blank' href=" + '//' + List[i].bm + '.qizuang.com/baojia.html' + "><img src='/assets/sub/company/img/window-liang.png'>免费报价</a>" +
                    "<a href='" + url + "/qcc?src=qz-zqc&search=" + List[i].company_name + "' target='_blank'><img src='/assets/sub/company/img/window-cha.png'>装企查一下</a>" +
                    "</div>" +
                    "</div>" +
                    "</div>";
            } else {
                // 信息窗口模板
                sContent = "<div class='zxdt-card'>" +
                    "<div class='zxdt-card-top clearfix'>" +
                    "<div class='company-img'>" +
                    "<img src='" + List[i].logo + "'>" +
                    "<a target='_blank' href=" + '//' + List[i].bm + ".qizuang.com/company_home/" + List[i].company_id + ">进入店铺</a>" +
                    "</div>" +
                    "<div class='company-con'>" +
                    "<h2><a target='_blank' href=" + '//' + List[i].bm + ".qizuang.com/company_home/" + List[i].company_id + ">" + List[i].company_name + "</a></h2>" +
                    hpStr +
                    "<p class='intro'><i>公司简介：</i>" + List[i].intro + "</p>" +
                    "<div class='num'>" +
                    numStr +
                    "</div>" +
                    "</div>" +
                    "</div>" +
                    "<div class='zxdt-card-bottom clearfix'>" +
                    "<div class='zxdt-info'>" +
                    "<p title=" + List[i].address + ">地址：" + List[i].address + "</p>" +
                    "<p>电话：<span>" + tel + "</span></p>" +
                    "</div>" +
                    "<div class='zxdt-btn'>" +
                    "<a target='_blank' href=" + '//' + List[i].bm + '.qizuang.com/baojia.html' + "><img src='/assets/sub/company/img/window-liang.png'>免费报价</a>" +
                    "<a href='" + url + "/qcc?src=qz-zqc&search=" + List[i].company_name + "' target='_blank'><img src='/assets/sub/company/img/window-cha.png'>装企查一下</a>" +
                    "</div>" +
                    "</div>" +
                    "</div>";

            }
            addMarker(point, i);

        }

        v = map.getViewport(pointList);
        map.centerAndZoom(v.center, v.zoom)

        // 地图当前位置
        var str1 = '<div class="pos-i">' +
            '<span>当前位置：<a href="/">首页</a>><strong>' + city + '装修公司</strong></span>' +
            '</div>' +
            '<div class="zxdt-res">' +
            '<span><strong>装修地图</strong>（已为您找到了' + data.data.total_count + '个相关结果）</span>' +
            '</div>'
        $('#pos').html(str1);

        // 左侧列表
        var str = '';
        for (var i = 0; i < List.length; i++) {
            if (List[i].lat != '' && List[i].lng != '') {
                str += '<li onclick="changePoint(' + List[i].lat + ',' + List[i].lng + ',' + i + ')">' +
                    '<div><h3><span>' + (i + 1) + '</span>' + List[i].company_name + '</h3><a target="_blank" href="' + url + '/qcc?src=qz-zqc&search=' + List[i].company_name + '">查</a></div>' +
                    '<p>' + List[i].address + '</p>' +
                    '</li>';
            } else {
                str += '<li>' +
                    '<div><h3><span>' + (i + 1) + '</span>' + List[i].company_name + '</h3><a target="_blank" href="' + url + '/qcc?src=qz-zqc&search=' + List[i].company_name + '">查</a></div>' +
                    '<p>' + List[i].address + '</p>' +
                    '</li>';
            }
        }


        //放入页面的容器显示
        $('#list').html(str);

        $('#list').append(data.data.page);

        $(function () {
            for (var i = 0; i <= $('.page a').length; i++) {
                $('.page a').eq(i).attr("href", "javascript:;")
                $('.page a').eq(i).attr("id", "page" + i)
                $('.page a').eq(i).bind("click", {index: i}, clickHandler);
            }

            function clickHandler(event) {
                var i = event.data.index;
                var html = document.getElementById('page' + i).innerHTML
                console.log(html)
                if (html == '<i class="fa fa-angle-right" id="next"></i>') {
                    page += 1
                } else if (html == '<i class="fa fa-angle-left" id="prve"></i>') {
                    page -= 1
                } else {
                    if (html == '…'){
                        page = page
                    }else {
                        page = Number(document.getElementById('page' + i).innerText)
                    }
                }
                map.clearOverlays()
                infoWindowList = []
                getList()
            }
        });
        $('.list li').eq(0).addClass('active')
    })

}


function searchList(name) {

    infoWindowList = []
    if ($("#f_y_cs option:selected").val() == '') {
        alert('请选择您要搜索的城市！')
        getList()
        return
    }

    if (name == '' || name == '请输入关键字...') {
        alert('请输入您要搜索的关键字！')
        getList()
        return
    }

    map.clearOverlays()

    $.ajax({
        url: '/zxdt/map',
        type: 'GET',
        dataType: 'JSON',
        data: {
            company: name,
            city: $("#f_y_cs option:selected").val(),
            p: page
        }
    }).done(function (data) {

        var List = data.data.list
        var pointList = [];
        // 创建对应信息窗口和标点
        for (var i = 0; i < List.length; i++) {
            var point = new BMap.Point(List[i].lng, List[i].lat);
            pointList.push(point);

            var numStr = '';
            var hpStr = '';
            (function () {
                if (List[i].case_num == 0) {
                    numStr = "<span>设计师:<i>" + List[i].designer_num + "</i>位</span>" +
                        "<span>工地:<i>" + List[i].site_num + "</i>家</span>"
                }
                if (List[i].designer_num == 0) {
                    numStr =
                        "<span>设计案例:<i>" + List[i].case_num + "</i>套</span>" +
                        "<span>工地:<i>" + List[i].site_num + "</i>家</span>"
                }
                if (List[i].site_num == 0) {
                    numStr =
                        "<span>设计案例:<i>" + List[i].case_num + "</i>套</span>" +
                        "<span>设计师:<i>" + List[i].designer_num + "</i>位</span>"
                }
                if (List[i].case_num == 0 && List[i].designer_num == 0) {
                    numStr =
                        "<span>工地:<i>" + List[i].site_num + "</i>家</span>"
                }
                if (List[i].case_num == 0 && List[i].site_num == 0) {
                    numStr =
                        "<span>设计师:<i>" + List[i].designer_num + "</i>位</span>"
                }
                if (List[i].designer_num == 0 && List[i].site_num == 0) {
                    numStr =
                        "<span>设计案例:<i>" + List[i].case_num + "</i>套</span>"
                }
                if (List[i].case_num == 0 && List[i].designer_num == 0 && List[i].site_num == 0) {
                    numStr = ''
                }
                if (List[i].case_num != 0 && List[i].designer_num != 0 && List[i].site_num != 0) {
                    numStr =
                        "<span>设计案例:<i>" + List[i].case_num + "</i>套</span>" +
                        "<span>设计师:<i>" + List[i].designer_num + "</i>位</span>" +
                        "<span>工地:<i>" + List[i].site_num + "</i>家</span>"
                }


                if (List[i].haoping_lv == 0) {
                    hpStr = ""
                } else {
                    hpStr = "<p><i>好评率：</i><span>" + Math.round((List[i].haoping_lv * 10000) / 100).toFixed(0) + '%' + "</span></p>"
                }

            })()
            if (List[i].intro.length > 55) {
                List[i].intro = List[i].intro.substring(0, 55) + '...'
            } else {
                List[i].intro = List[i].intro
            }


            if (List[i].type == 3) {
                // 信息窗口模板
                sContent = "<div class='zxdt-card'>" +
                    "<div class='zxdt-card-top clearfix'>" +
                    "<div class='company-img'>" +
                    "<img src='" + List[i].logo + "'>" +
                    "<span>进入店铺</span>" +
                    "</div>" +
                    "<div class='company-con'>" +
                    "<h2><span>" + List[i].company_name + "</span></h2>" +
                    hpStr +
                    "<p class='intro'><i>公司简介：</i>" + List[i].intro + "</p>" +
                    "<div class='num'>" +
                    numStr +
                    "</div>" +
                    "</div>" +
                    "</div>" +
                    "<div class='zxdt-card-bottom clearfix'>" +
                    "<div class='zxdt-info'>" +
                    "<p title=" + List[i].address + ">地址：" + List[i].address + "</p>" +
                    "<p>电话：<span>" + tel + "</span></p>" +
                    "</div>" +
                    "<div class='zxdt-btn'>" +
                    "<a target='_blank' href=" + '//' + List[i].bm + '.qizuang.com/baojia.html' + "><img src='/assets/sub/company/img/window-liang.png'>免费报价</a>" +
                    "<a href='" + url + "/qcc?src=qz-zqc&search=" + List[i].company_name + "' target='_blank'><img src='/assets/sub/company/img/window-cha.png'>装企查一下</a>" +
                    "</div>" +
                    "</div>" +
                    "</div>";
            } else {
                // 信息窗口模板
                sContent = "<div class='zxdt-card'>" +
                    "<div class='zxdt-card-top clearfix'>" +
                    "<div class='company-img'>" +
                    "<img src='" + List[i].logo + "'>" +
                    "<a target='_blank' href=" + '//' + List[i].bm + ".qizuang.com/company_home/" + List[i].company_id + ">进入店铺</a>" +
                    "</div>" +
                    "<div class='company-con'>" +
                    "<h2><a target='_blank' href=" + '//' + List[i].bm + ".qizuang.com/company_home/" + List[i].company_id + ">" + List[i].company_name + "</a></h2>" +
                    hpStr +
                    "<p class='intro'><i>公司简介：</i>" + List[i].intro + "</p>" +
                    "<div class='num'>" +
                    numStr +
                    "</div>" +
                    "</div>" +
                    "</div>" +
                    "<div class='zxdt-card-bottom clearfix'>" +
                    "<div class='zxdt-info'>" +
                    "<p title=" + List[i].address + ">地址：" + List[i].address + "</p>" +
                    "<p>电话：<span>" + tel + "</span></p>" +
                    "</div>" +
                    "<div class='zxdt-btn'>" +
                    "<a target='_blank' href=" + '//' + List[i].bm + '.qizuang.com/baojia.html' + "><img src='/assets/sub/company/img/window-liang.png'>免费报价</a>" +
                    "<a href='" + url + "/qcc?src=qz-zqc&search=" + List[i].company_name + "' target='_blank'><img src='/assets/sub/company/img/window-cha.png'>装企查一下</a>" +
                    "</div>" +
                    "</div>" +
                    "</div>";

            }
            addMarker(point, i);

        }

        v = map.getViewport(pointList);
        map.centerAndZoom(v.center, v.zoom)

        // 地图当前位置
        var str1 = '<div class="pos-i">' +
            '<span>当前位置：<a href="/">首页</a>><strong>' + city + '装修公司</strong></span>' +
            '</div>' +
            '<div class="zxdt-res">' +
            '<span><strong>装修地图</strong>（已为您找到了' + data.data.total_count + '个相关结果）</span>' +
            '</div>'
        $('#pos').html(str1);

        // 左侧列表
        var str = '';
        for (var i = 0; i < List.length; i++) {
            if (List[i].lat != '' && List[i].lng != '') {
                str += '<li onclick="changePoint(' + List[i].lat + ',' + List[i].lng + ',' + i + ')">' +
                    '<div><h3><span>' + (i + 1) + '</span>' + List[i].company_name + '</h3><a target="_blank" href="' + url + '/qcc?src=qz-zqc&search=' + List[i].company_name + '">查</a></div>' +
                    '<p>' + List[i].address + '</p>' +
                    '</li>';
            } else {
                str += '<li>' +
                    '<div><h3><span>' + (i + 1) + '</span>' + List[i].company_name + '</h3><a target="_blank" href="' + url + '/qcc?src=qz-zqc&search=' + List[i].company_name + '">查</a></div>' +
                    '<p>' + List[i].address + '</p>' +
                    '</li>';
            }
        }


        //放入页面的容器显示
        $('#list').html(str);

        $('#list').append(data.data.page);

        $(function () {
            for (var i = 0; i <= $('.page a').length; i++) {
                $('.page a').eq(i).attr("href", "javascript:;")
                $('.page a').eq(i).attr("id", "page" + i)
                $('.page a').eq(i).bind("click", {index: i}, clickHandler);
            }
        });

        function clickHandler(event) {
            var i = event.data.index;
            var html = document.getElementById('page' + i).innerHTML
            console.log(html)
            if (html == '<i class="fa fa-angle-right" id="next"></i>') {
                page += 1
            } else if (html == '<i class="fa fa-angle-left" id="prve"></i>') {
                page -= 1
            } else {
                if (html == '…'){
                    page = page
                }else {
                    page = Number(document.getElementById('page' + i).innerText)
                }
            }
            map.clearOverlays()
            infoWindowList = []
            getList()
        }

    })
}



var labelList = []

// 自定义函数,创建标注
function addMarker(point, index) {
    if (!Boolean(point.lat && point.lng)) {
        return
    }
    // 根据坐标点生成覆盖物
    var marker = new BMap.Marker(point);
    map.addOverlay(marker)

    var opts = {
        position: point,    // 指定文本标注所在的地理位置
        offset: new BMap.Size(-17, -28)    //设置文本偏移量
    }
    var label = new BMap.Label((index + 1), opts);  // 创建文本标注对象
    label.setStyle({
        color: "#fff",
        background: "url(/assets/sub/company/img/marker-bd.png) no-repeat center -2px",
        backgroundSize: "32px 32px",
        border: "none",
        fontSize: "12px",
        width: "32px",
        textAlign: "center",
        height: "32px",
        lineHeight: "25px",
        fontFamily: "微软雅黑",
    });
    map.addOverlay(label);

    labelList.push(label)
    var infoWindow = new BMap.InfoWindow(sContent);

    infoWindowList.push(sContent)
    // 打开列表的第一个信息窗口
    if (index === 0) {
        marker.openInfoWindow(infoWindow);
    }

    marker.addEventListener("click", function () {
        marker.openInfoWindow(infoWindow);
        label.setZIndex(9999999)
        $('.list li').eq(index).addClass('active').siblings().removeClass('active')
    })

}


// 城市改变事件
$("#f_y_cs").change(function () {
    city = GetChinese($("#f_y_cs option:selected").text());
    // $('#search-con').val('')
    page = 1
    infoWindowList = []
    labelList = []
    map.clearOverlays()
    getList()
});

// 改变地图坐标
function changePoint(lat, lng, i) {
    if (lat != '' && lng != '') {
        map.panTo(new BMap.Point(lng, lat));
        var infoWindowBox = new BMap.InfoWindow(infoWindowList[i], {offset: new BMap.Size(0, -27)});
        map.openInfoWindow(infoWindowBox, new BMap.Point(lng, lat));
        $('.list li').eq(i).addClass('active').siblings().removeClass('active')

        for (j = 0; j < labelList.length; j++) {
            labelList[j].setZIndex(5555)
        }

        labelList[i].setZIndex(999999)

    } else {
        // console.log(123)

    }
}

// 搜索框改变事件，用于搜索框为空时重新请求列表
$("#search-con").bind("input propertychange change", function (event) {
    if ($('#search-con').val() == '') {
        getList()
    }
});

// 测距
var myDis = new BMapLib.DistanceTool(map);
tack.addEventListener("click", function () {
    myDis.open();  //开启鼠标测距
});


function getQueryVariable(variable) {
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split("=");
        if (pair[0] == variable) {
            return pair[1];
        }
    }
    return (false);
}

// 获取城市文字
function GetChinese(strValue) {
    if (strValue !== null && strValue !== '') {
        var reg = /[\u4e00-\u9fa5]/g;
        return strValue.match(reg).join('');
    }
    return '';
}

// 全屏
$('#full').click(function () {
    if ($('.map-box').hasClass('full')) {
        $('.map-box').removeClass('full')
    } else {
        $('.map-box').addClass('full')
    }
})

// 列表折叠
var ocStatus = 1;
$('#oc-btn').click(function () {
    if (ocStatus == 1){
        $('.company-list').css('width','0')
        $('#container').css('width','100%')
        $('#oc-btn span').css('transform','rotate(0deg)')
        ocStatus = 0
    }else {
        $('.company-list').css('width','350px')
        $('#container').css('width','calc(100% - 350px)')
        $('#oc-btn span').css('transform','rotate(180deg)')
        ocStatus = 1
    }
})

