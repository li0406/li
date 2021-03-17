/*
 * @Author: your name
 * @Date: 2020-07-28 10:59:43
 * @LastEditTime: 2020-08-05 17:07:41
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: \qz_admin_centerd:\wamp64\www\qz_mobile\web\assets\mobile\company\js\zxdt.js
 */
var maplist = [] //地图列表
var inputVal = '' //搜索框的值
var pointList = []
var searchbul = true
var cityid = $('.sel-sty').val()

//附近装企
var citylist = []
var citypage = 1
var citypage_total_number = 1
var searchbuldet = true
var total_count = 0 //附近公司的总数

var bul = false
var first = true

var firstmsgdata
var checkdata
var seldata = ''

function domap(data_info, pointbul, par) {
  var map = new BMap.Map('allmap') // 创建Map实例
  var markerstyle = {
    color: '#fff',
    background: 'url(/assets/mobile/company/img/marker-bd.png) no-repeat center -2px',
    backgroundSize: '32px 32px',
    border: 'none',
    fontSize: '12px',
    width: '32px',
    textAlign: 'center',
    height: '32px',
    lineHeight: '25px',
    fontFamily: '微软雅黑'
  }
  for (var i = 0; i < data_info.length; i++) {
    var point = new BMap.Point(data_info[i].lng, data_info[i].lat)
    pointList.push(point)
    var sContent = data_info[i].company_name
    addMarker(data_info[i], point, i, sContent, markerstyle, -17, -26)
  }
  // var v = map.getViewport(pointList)
  // console.log(point, v.center)
  // map.centerAndZoom(v.center, 15);
  // map.centerAndZoom(v.center, v.zoom) // 初始化地图,设置中心点坐标和地图级别
  // 封装,创建标注
  function addMarker(onedata, point, index, sContent, markerstyle, x, y) {
    map.centerAndZoom(point, 11)
    if (!Boolean(point.lat && point.lng)) {
      return
    }
    // 根据坐标点生成覆盖物
    var marker = new BMap.Marker(point)
    var icon = marker.getIcon();
    marker.setShadow(icon);
    map.addOverlay(marker)

    var opts = {
      position: point, // 指定文本标注所在的地理位置
      offset: new BMap.Size(x, y), //设置文本偏移量
    }
    var label = new BMap.Label(index + 1, opts) // 创建文本标注对象
    label.setStyle(markerstyle)
    map.addOverlay(label)
    // var infoWindow = new BMap.InfoWindow(sContent)
    // 打开列表的第一个信息窗口
    // if (index === 0) {
    //   marker.openInfoWindow(infoWindow)
    // }
    marker.addEventListener('click', function () {
      // marker.openInfoWindow(infoWindow)
      // e.stopPropagation()
      event.stopPropagation()
      listitem(onedata, index)
    })
    map.addEventListener('click', function () {
      hides()
    })
  }
  if (pointbul) {
    addMarker(...par)
  }
}

//点击搜索
$('.search').click(function () {
  maplist = []
  citylist = []
  inputVal = $('.input').val() //获取用户输入的值
  cityid = $('.sel-sty').val()
  hides()
  bul = true
  resetlistparams()
  getlist()
})
//重置列表
function resetlistparams() {
  searchbul = true
  page = 1
  searchbuldet = true
  citypage = 1
}
//所有元素
//只有一条搜索结果

function hides() {
  $('.sea-foot').hide()
  //详细信息
  $('.msg-foot').hide()
  //没有信息的时候
  $('.div-msg-foot').hide()
  //较低的列表
  $('.detaillist').hide()
  //较高的列表
  $('.list').hide()
}
hides()
var page = 1
var page_total_number = 1
//移动端-地图搜索接口
function getlist() {
  $.ajax({
    url: '/zxdt/map',
    type: 'get',
    data: {
      company: inputVal, //装修公司名称
      // company: '苏州兰陵装饰有限公司',
      // company: '苹果',
      city: cityid, //城市id
      // city: '320200',
      p: page, //页码
      limit: '', //每页显示个数(可能用不到)
    },
    success: function (res, status, xhr) {
      if (res.error_code === 0) {
        maplist.push(...res.data.list)
        page_total_number = res.data.page.page_total_number
        if (maplist == '') {
          getcitypoint()
          longlistdom()
          return
        }
        if (first || (maplist != '' && maplist.length == 1)) {
          domap(maplist, false)
          onedatalist(maplist[0])
          listitem(maplist[0], 0)
        }
        first = false
        longlistdom()
      } else {
        alert(res.error_code)
      }
    },
    fail: function () {},
  })
}
getlist()
//附近装企列表
function nearbylist() {
  var sev = `<div>—附近还有${total_count}家优质装修公司—</div>`
  $('.sev').html(sev)
  //较低的列表
  $('.shortlist').empty()
  for (var f = 0; f < citylist.length; f++) {
    var company_name_short
    var address_short
    if (citylist[f].company_name.length > 12) {
      company_name_short = citylist[f].company_name.substring(0, 12) + '...'
    } else {
      company_name_short = citylist[f].company_name
    }
    if (citylist[f].address.length > 15) {
      address_short = citylist[f].address.substring(0, 15) + '...'
    } else {
      address_short = citylist[f].address
    }
    var shortlist = `<li class="flex spa-aro pad bor-b">
            <div class="ars-msg">
              <div class="font34 bold">${company_name_short}</div>
              <div class="col999 flex mart">
                <div class="address"></div>
                <div>
                  ${address_short}
                </div>
              </div>
            </div>
            <div class="ret short-search" onclick="listitem(citylist[${f}],${f})">
              <div class="tex-ali">
                <img class="search-icon" src="/assets/mobile/company/img/search-icon.png" alt="">
              </div>
              <div class="mart col-D92B2B">搜查</div>
            </div>
          </li>`
    $('.shortlist').append(shortlist)
  }
  $('.detaillist').show()
}
$('.li-list').scroll(function () {
  var scrollTopdet = $(this).scrollTop()
  var ks_areadet = $(this).innerHeight()
  var nScrollHightdet = 0 //滚动距离总长(注意不是滚动条的长度)
  nScrollHightdet = $(this)[0].scrollHeight - 1
  if (scrollTopdet + ks_areadet >= nScrollHightdet) {
    // alert('到底了')
    searchbuldet = false
    var loaddet
    if (citypage_total_number > citypage) {
      citypage += 1
      getcitylist()
      loaddet = `<div>加载中...</div>`
      $('.loaddet').html()
    } else {
      loaddet = `<div class="totop">
              <img class="topping" src="/assets/mobile/company/img/topping.png" alt="">
            </div>
            <div>我也是有底线的哦～</div>`
    }
    $('.loaddet').html(loaddet)
    backtop()
  }
  // if ($(this).scrollTop() == 0) {
  //   alert('到顶部了')
  // }
})

function onedatalist(onedata) {
  var company_name
  var address
  var addresslessdom
  if (onedata.company_name.length > 12) {
    company_name = onedata.company_name.substring(0, 12) + '...'
  } else {
    company_name = onedata.company_name
  }
  if (onedata.address.length > 25) {
    address = onedata.address.substring(0, 25) + '...'
  } else {
    address = onedata.address
  }
  firstmsgdata = onedata
  var seaFoot = `<div onclick="seeonedata(firstmsgdata)">
  <span class="font34 bold mar-bot">${company_name}</span>
    <div class="address-less-dom">
      
    </div>
    <div class="comments">
      <p class="stars">
      </p>
    </div>
    <div class="flex mar-top font24 labels">
    </div>
  </div>`
  $('.sea-foot').html(seaFoot)
  if (address != '') {
    addresslessdom = `<div class="col999 mar-bot">
      <div class="address"></div>
        ${address}
      </div>`
    $('.address-less-dom').html(addresslessdom)
  }
  $('.stars').empty()
  for (var t = 0; t < onedata.star; t++) {
    var star = `<span class="star"></span>`
    $('.stars').append(star)
  }
  $('.stars').empty()
  for (var t = 0; t < onedata.star; t++) {
    var star = `<span class="star"></span>`
    $('.stars').append(star)
  }
  $('.labels').empty()
  if (onedata.case_num != 0) {
    var case_num = `<div class="label-sty">${onedata.case_num}个精选案例</div>`
    $('.labels').append(case_num)
  }
  if (onedata.site_num != 0) {
    var site_num = `<div class="label-sty">${onedata.site_num}在建工地</div>`
    $('.labels').append(site_num)
  }
  if (onedata.designer_num != 0) {
    var designer_num = `<div class="label-sty">${onedata.designer_num}名设计师</div>`
    $('.labels').append(designer_num)
  }
  $('.sea-foot').show()
}
//列表dom
function longlistdom() {
  //搜索列表为空
  if (maplist == '') {
    //没有信息的时候
    var diMmsgFoot = `<img class="def-pag" src="/assets/mobile/company/img/def-pag.png" alt="">
      <div>抱歉，未能搜索到该公司</div>`
    $('.div-msg-foot').html(diMmsgFoot)
    $('.div-msg-foot').show()
    getcitylist() //附近装企
  }
  //搜索列表只有1条数据的时候
  if (maplist != '' && maplist.length == 1) {
    onedatalist(maplist[0])
  }
  //搜索列表有多条数据的时候
  if (maplist != '' && maplist.length > 1) {
    $('.longlist').empty()
    for (var o = 0; o < maplist.length; o++) {
      var company_name_long
      var address_long
      if (maplist[o].company_name.length > 12) {
        company_name_long = maplist[o].company_name.substring(0, 12) + '...'
      } else {
        company_name_long = maplist[o].company_name
      }
      if (maplist[o].address.length > 25) {
        address_long = maplist[o].address.substring(0, 25) + '...'
      } else {
        address_long = maplist[o].address
      }
      var longlist = `<li class="flex spa-aro pad bor-b">
            <div class="ars-msg">
              <div class="font34 bold">${company_name_long}</div>
              <div class="col999 flex mart">
                <div class="address"></div>
                <div>
                  ${address_long}
                </div>
              </div>
            </div>
            <div class="ret long-search" onclick="listitem(maplist[${o}],${o})">
              <div class="tex-ali">
                <img class="search-icon" src="/assets/mobile/company/img/search-icon.png" alt="">
              </div>
              <div class="mart col-D92B2B">搜查</div>
            </div>
          </li>`
      $('.longlist').append(longlist)
    }
    if (bul) {
      $('.list').show()
    }
  }
}

$('.li-list-y').scroll(function () {
  var scrollTop = $(this).scrollTop()
  var ks_area = $(this).innerHeight()
  var nScrollHight = 0 //滚动距离总长(注意不是滚动条的长度)
  nScrollHight = $(this)[0].scrollHeight - 1
  if (scrollTop + ks_area >= nScrollHight) {
    // alert("到底了")
    searchbul = false
    var load
    if (page_total_number > page) {
      page += 1
      getlist()
      load = `<div>加载中...</div>`
      $('.load').html()
    } else {
      load = `<div class="totop">
            <img class="topping" src="/assets/mobile/company/img/topping.png" alt="">
          </div>
          <div>我也是有底线的哦～</div>`
    }
    $('.load').html(load)
    backtop()
  }
  // if ($(this).scrollTop() == 0) {
  //   alert("到顶部了");
  // }
})

function seeonedata(onedata) {
  seldata = onedata
  var company_namedet
  var advertisement
  var addressdet
  var introdet
  var comprosty
  var kouhao
  var addressmanydom
  if (onedata.company_name.length > 12) {
    company_namedet = onedata.company_name.substring(0, 12) + '...'
  } else {
    company_namedet = onedata.company_name
  }
  if (onedata.kouhao != '' && onedata.kouhao.length > 20) {
    advertisement = onedata.kouhao.substring(0, 20) + '...'
  } else {
    advertisement = onedata.kouhao
  }
  if (onedata.address.length > 25) {
    addressdet = onedata.address.substring(0, 25) + '...'
  } else {
    addressdet = onedata.address
  }
  if (onedata.intro.length > 70) {
    introdet = onedata.intro.substring(0, 70) + '...'
  } else {
    introdet = onedata.intro
  }
  checkdata = onedata
  var msgFoot = `<div class="detailed">
      <div class="flex-msg">
        <div class="div-img" id="headerPic" onclick="previewpic()">
          <img class="det-img" src="${onedata.logo}" alt="">
        </div>
        <div>
          <div class="font34 mar-bot-s flex">
            <div class="bold" onclick="tocitydet(checkdata)">${company_namedet}</div>
            <div class="ticket" onclick="tocheck(checkdata)">查</div>
          </div>
          <div class="kouhao">
            
          </div>
          <div class="comments">
            <p class="starsdet">
            </p>
          </div>
        </div>
      </div>
      <div class="flex mar-top mar-bot font24 labelsdet">
      </div>
      <div class="address-many-dom">
        
      </div>
      <div class="comprosty">
        
      </div>
      <div class="flex">
        <div class="bold font-30">电话：</div>
        <div class="tel bold">
          400-6969-336
        </div>
      </div>
    </div>`
  $('.msg-foot').html(msgFoot)
  $('.msg-foot').html(msgFoot)
  if (advertisement != '') {
    kouhao = `<div class="col999 mar-bot-s">
              ${advertisement}
            </div>`
    $('.kouhao').html(kouhao)
  }
  if (addressdet != '') {
    addressmanydom = `<div class="col999">
          <div class="address"></div>
          ${addressdet}
        </div>`
    $('.address-many-dom').html(addressmanydom)
  }
  if (introdet != '') {
    comprosty = `<div class="sketch-tb bold font-30">公司简介</div>
        <div class="col999 mar-bot introdet">
          ${introdet}
        </div>`
    $('.comprosty').html(comprosty)
  }
  $('.starsdet').empty()
  for (var t = 0; t < onedata.star; t++) {
    var stardet = `<span class="star"></span>`
    $('.starsdet').append(stardet)
  }
  $('.labelsdet').empty()
  if (onedata.case_num != 0) {
    var case_numdet = `<div class="label-sty">${onedata.case_num}个精选案例</div>`
    $('.labelsdet').append(case_numdet)
  }
  if (onedata.site_num != 0) {
    var site_numdet = `<div class="label-sty">${onedata.site_num}在建工地</div>`
    $('.labelsdet').append(site_numdet)
  }
  if (onedata.designer_num != 0) {
    var designer_numdet = `<div class="label-sty">${onedata.designer_num}名设计师</div>`
    $('.labelsdet').append(designer_numdet)
  }
  citylist = []
  getcitylist() //附近装企
  $('.sea-foot').hide()
  $('.msg-foot').show()
  $('.detaillist').show()
}
//长列表的搜索 地图定位
function listitem(onedata, index) {
  var firstpoint = new BMap.Point(onedata.lng, onedata.lat)
  var changemarkerstyle = {
    color: '#fff',
    background: 'url(/assets/mobile/company/img/marker-spot.png) no-repeat center -2px',
    backgroundSize: '34px 34px',
    border: 'none',
    fontSize: '12px',
    width: '34px',
    textAlign: 'center',
    height: '34px',
    lineHeight: '25px',
    fontFamily: '微软雅黑',
  }
  var par = [
    onedata,
    firstpoint,
    index,
    onedata.company_name,
    changemarkerstyle,
    -18,
    -27,
  ]
  if (citylist != '') {
    domap(citylist, true, par)
  } else {
    domap(maplist, true, par)
  }

  // addMarker(
  //   onedata,
  //   firstpoint,
  //   index,
  //   onedata.company_name,
  //   changemarkerstyle,
  //   -18,
  //   -27
  // )
  // maplist = [onedata]
  onedatalist(onedata)
  if (first) {
    btnoff()
  } else {
    $('.input').val(onedata.company_name)
    btnon()
  }
  // $('.list').hide()
  // $('.div-msg-foot').hide()
  // $('.detaillist').hide()
  // $('.msg-foot').hide()
  hides()
  $('.sea-foot').show()
}
//置顶
function backtop() {
  $('.totop').click(function () {
    $('.li-list').animate({
        scrollTop: 0,
      },
      300
    )
    $('.li-list-y').animate({
        scrollTop: 0,
      },
      300
    )
  })
}

function getcitylist() {
  $.ajax({
    url: '/zxdt/map',
    type: 'get',
    data: {
      city: cityid, //城市id
      // city: '320200',
      p: citypage, //页码
      limit: '', //每页显示个数(可能用不到)
    },
    success: function (res, status, xhr) {
      if (res.error_code === 0) {
        if (seldata != '') {
          for (var n = 0; n < res.data.list.length; n++) {
            if (res.data.list[n].company_name != seldata.company_name) {
              citylist.push(res.data.list[n])
            }
          }
          total_count = res.data.total_count - 1
        } else {
          citylist.push(...res.data.list)
          total_count = res.data.total_count
        }
        citypage_total_number = res.data.page.page_total_number

        nearbylist() //添加列表dom
      } else {
        alert(res.error_code)
      }
    },
    fail: function () {},
  })
}
//清除搜索框的内容
function clearinput() {
  inputVal = $('.input').val('') //获取用户输入的值
  btnoff()
}

function btnoff() {
  $('.x').addClass('none')
  $('.x').removeClass('block')
  $('.btn').addClass('searchoff')
  $('.btn').removeClass('searchon')
  $('.btn').attr('disabled', true)
}

function btnon() {
  $('.x').addClass('block')
  $('.x').removeClass('none')
  $('.btn').addClass('searchon')
  $('.btn').removeClass('searchoff')
  $('.btn').attr('disabled', false)
}

function watchval() {
  if ($('.input').val() == '') {
    btnoff()
  } else {
    btnon()
  }
}

function goback() {
  window.history.go(-1)
}

function tocheck() {
  window.location.href = `/qcc?src=qz_zqc&search=${checkdata.company_name}`
}
$('.msg-foot').click(function () {
  hides()
  $('.sea-foot').show()
})

function getcitypoint() {
  // 百度地图API功能
  var alonemap = new BMap.Map("allmap");
  var city = $('.cityname').text()
  if (city != "") {
    alonemap.centerAndZoom(city, 11); // 用城市名设置地图中心点
  }
  alonemap.addEventListener('click', function () {
    hides()
  })
}

function tocitydet(onedata) {
  event.stopPropagation()
  if (onedata.type != 3) {
    window.location.href = `/${onedata.bm}/company_home/${onedata.company_id}`
  }
}
function previewpic(){
  // event.stopPropagation()
  // $('#headerPic').FlyZommImg({
  //   rollSpeed: 200, //切换速度
  //   miscellaneous: false, //是否显示底部辅助按钮
  //   closeBtn: true, //是否打开右上角关闭按钮
  //   hideClass: 'hideImg', //不需要显示预览的 class
  //   imgQuality: 'thumb', //图片质量类型 thumb 缩略图 original 默认原图
  //   slitherCallback: function (direction, DOM) { //左滑动回调 两个参数 第一个动向 'left,firstClick,close' 第二个 当前操作DOM
  //     setTimeout(function () {
  //       // 为了一开始居中显示
  //       $('.fly - zoom - box - img').css('width', '100 % ').css('height', 'auto').css('top', 0).css('bottom',
  //         0).css('margin', 'auto');
  //     }, 300)
  //   }
  // });
}