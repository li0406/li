// index.js
let app = getApp();
let apiUrl = app.getApiUrl();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    infoList:[],
    topImg: '',
    itemId:''
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    // 验证登录
    app.getUserInfo(function (userInfo) {});
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
      let json = [];
      let prevJson = [];
      let cityJson = [];
      let areaJson = [];
      let cityUrl;
      wx.getStorage({
          key: 'cityJson',
          success: function (res) {
              if (!res.data){
                  wx.request({
                      url: apiUrl + '/zxjt/getcityurl',
                      header: {
                          'content-type': 'application/json'
                      },
                      success: function (res) {
                          cityUrl = res.data.data.replace("/common/js", "");
                          let cityUrlArr = cityUrl.split(':');
                          let host = cityUrlArr[1].split('.');
                          host[0] = host[0] + 's';
                          cityUrlArr[1] = host.join('.');
                          let cityUrlStr = cityUrlArr[0] + 's:' + cityUrlArr[1]
                          wx.request({
                              url: cityUrlStr, // + 'common/js/rlpca20170721143503.js',
                              header: {
                                  'content-type': 'application/json'
                              },
                              success: function (res) {
                                  let str = res.data.replace("var rlpca = ", "");
                                  json = JSON.parse(str), prevJson = [], cityJson = [], areaJson = [];
                                  json.shift();
                                  // 删除空省份/城市/区域
                                  for (let i = 0; i < json.length; i++) {
                                      json[i].children.shift()
                                      for (var j = 0; j < json[i].children.length; j++) {
                                          json[i].children[j].children.shift()
                                      }
                                  };
                                  // 筛选省份名称+id
                                  for (let i = 0; i < json.length; i++) {
                                      prevJson.push({ id: json[i].id, text: json[i].text });
                                  }
                                  // 筛选第一省的第一个城市名称+id
                                  for (let j = 0; j < json[0].children.length; j++) {
                                      cityJson.push({ id: json[0].children[j].id, text: json[0].children[j].text })
                                  }
                                  // 筛选第一省/第一个城市/第一个区域名称+id
                                  for (let k = 0; k < json[0].children[0].children.length; k++) {
                                      areaJson.push({ id: json[0].children[0].children[k].id, text: json[0].children[0].children[k].text })
                                  }
                                  wx.setStorage({
                                      key: 'cityJson',
                                      data: { prev: prevJson, city: cityJson, area: areaJson, json: json },
                                  })
                              }
                          })
                      }
                  })
              }
              
          },
          // 获取缓存失败
          fail: function () {
              // ajax请求数据并且数据本地缓存存储
              wx.request({
                  url: apiUrl + '/zxjt/getcityurl',
                  header: {
                      'content-type': 'application/json'
                  },
                  success: function (res) {
                      cityUrl = res.data.data.replace("/common/js", "");
                      let cityUrlArr = cityUrl.split(':');
                      let host = cityUrlArr[1].split('.');
                      host[0] = host[0] + 's';
                      cityUrlArr[1] = host.join('.');
                      let cityUrlStr = cityUrlArr[0] + 's:' + cityUrlArr[1]
                      wx.request({
                          url: cityUrlStr, // + 'common/js/rlpca20170721143503.js',
                          header: {
                              'content-type': 'application/json'
                          },
                          success: function (res) {
                              let str = res.data.replace("var rlpca = ", "");
                              json = JSON.parse(str), prevJson = [], cityJson = [], areaJson = [];
                              json.shift();
                              // 删除空省份/城市/区域
                              for (let i = 0; i < json.length; i++) {
                                  json[i].children.shift()
                                  for (var j = 0; j < json[i].children.length; j++) {
                                      json[i].children[j].children.shift()
                                  }
                              };
                              // 筛选省份名称+id
                              for (let i = 0; i < json.length; i++) {
                                  prevJson.push({ id: json[i].id, text: json[i].text });
                              }
                              // 筛选第一省的第一个城市名称+id
                              for (let j = 0; j < json[0].children.length; j++) {
                                  cityJson.push({ id: json[0].children[j].id, text: json[0].children[j].text })
                              }
                              // 筛选第一省/第一个城市/第一个区域名称+id
                              for (let k = 0; k < json[0].children[0].children.length; k++) {
                                  areaJson.push({ id: json[0].children[0].children[k].id, text: json[0].children[0].children[k].text })
                              }
                              wx.setStorage({
                                  key: 'cityJson',
                                  data: { prev: prevJson, city: cityJson, area: areaJson, json: json },
                              })
                          }
                      })
                  }
              })
          }
      });
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    let that = this;
    // 首页设置缓存
    let indexInfo = app.getNewStorage('indexInfo');
    if (indexInfo) {
      that.setData({ infoList: indexInfo, topImg: indexInfo[0].img, itemId: indexInfo[0].id })
    } else {
      wx.request({
        url: apiUrl + '/zxjt/index',
        header: {
          'content-type': 'application/json'
        },
        dataType: 'json',
        success: function (res) {
          app.setNewStorage('indexInfo', res.data.videoList, 100);
          that.setData({ infoList: res.data.videoList, topImg: res.data.videoList[0].img, itemId: res.data.videoList[0].id })
        }
      });
    }
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
  
  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {
  
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {
  
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
  
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  
  },
  /**
   * 滑动切换banner
   */
  EventHandle:function(event){
    var count = event.detail.current;

    this.data.topImg = this.data.infoList[count].img

    this.setData({ topImg: this.data.topImg, itemId: this.data.infoList[count].id})
    
  },
  /**
   * 跳转到搜索页面
   */
  toSearchPage:function(){
    wx.navigateTo({
      url: '../search/search'
    })
  },

  /**
   * 点击跳转到播放详情页
   */
  toDetailPlay: function () {
    wx.navigateTo({
      url: '../detail_play/detail_play?id='+this.data.itemId,
      success: function (res) { },
      fail: function (res) { },
      complete: function (res) { },
    })
  }
})