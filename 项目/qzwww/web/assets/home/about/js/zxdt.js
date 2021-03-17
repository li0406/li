// 城市初始化
var map = '',
    sContent = '',
    v = '';
var labelList = []

map = new BMap.Map("allmap", {enableMapClick: false});
map.enableScrollWheelZoom();
map.disableDoubleClickZoom();//启用双击放大，默认启用。

// 获取列表
getList()


// 获取列表并生成对应地图
function getList() {
  infoWindowList = []
  var List = [
    {
      "address": "公司地址：苏州工业园区金鸡湖大道1355号国际科技园1期6栋",
      "href": "https://www.qizuang.com",
      "cname": "苏州总部",
      "lng": "120.676745",
      "lat": "31.300896"
    },
    {
      "address": "公司地址：重庆市江北区建新南路1号中信大厦22-6",
      "href": "https://cq.qizuang.com/",
      "cname": "重庆分公司",
      "lng": "106.540784",
      "lat": "29.578046"
    },
    {
      "address": "公司地址：成都市成华区玉双路2号6层",
      "href": "https://cd.qizuang.com",
      "cname": "成都分公司",
      "lng": "104.103967",
      "lat": "30.659772"
    },
    {
      "address": "公司地址：安徽省滁州市琅琊区琅琊街道",
      "href": "https://chuzhou.qizuang.com",
      "cname": "滁州分公司",
      "lng": "118.329013",
      "lat": "32.313987"
    }
  ]
  var pointList = [];
  // 创建对应信息窗口和标点
  for (var i = 0; i < List.length; i++) {
    var point = new BMap.Point(List[i].lng, List[i].lat);
    pointList.push(point);
    var hpStr = '';
    // 信息窗口模板
    sContent = "<div id='comdes'>" +
    "<span class='zong'>"+ List[i].cname +"</span>" +  "<br>" +
    "服务热线：<span class='tel'>400-6969-336</span>" + "<br>" + List[i].address +
    "<a href="+List[i].href+">查看网站</a> "
        +"</div>";

    addMarker(point, i);
  }
  map.centerAndZoom(new BMap.Point(120.676745, 31.300896), 6);
  
  // 地图当前位置
  var str1 = '<div class="pos-i">' +
      '<span>当前位置：<a href="/">首页</a>><strong>' + '装修公司</strong></span>' +
      '</div>' +
      '<div class="zxdt-res">' +
      '<span><strong>装修地图</strong>（已为您找到了个相关结果）</span>' +
      '</div>'
  $('#pos').html(str1);
  
  // 左侧列表
  var str = '';
  for (var i = 0; i < List.length; i++) {
    if (List[i].lat != '' && List[i].lng != '') {
      str += '<li onclick="changePoint(' + List[i].lat + ',' + List[i].lng + ',' + i + ')">' +
          '<div><h3>' + List[i].cname + '</h3></div>' + '<span>' + '服务热线：400-6969-336' + '</span>'+
          '<p>' + List[i].address + '</p>' +
          '</li>' + '<div class="line"></div>';
    } else {
      str += '<li>' +
          '<div><h3><span>' + '服务热线：400-6969-336' + '</span>' + List[i].cname + '</h3></div>' +
          '<p>' + List[i].address + '</p>' +
          '</li>';
    }
  }
  //放入页面的容器显示
  $('#list').html(str);
  $('#list li').eq(0).addClass('active')
}
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
    $('#list li').eq(index).addClass('active').siblings().removeClass('active')
    setTimeout(function(){
      map.centerAndZoom(new BMap.Point(point.lng, point.lat), 17);
    }, 500);
  })
}

// 改变地图坐标
function changePoint(lat, lng, i) {
  if (lat != '' && lng != '') {
    map.panTo(new BMap.Point(lng, lat));
    var infoWindowBox = new BMap.InfoWindow(infoWindowList[i], {offset: new BMap.Size(0, -27)});
    map.openInfoWindow(infoWindowBox, new BMap.Point(lng, lat));
    setTimeout(function(){
      map.centerAndZoom(new BMap.Point(lng, lat), 17);
    }, 500);
    $('#list li').eq(i).addClass('active').siblings().removeClass('active')
    for (j = 0; j < labelList.length; j++) {
      labelList[j].setZIndex(5555)
    }
    labelList[i].setZIndex(999999)
  } else {
  }
}

