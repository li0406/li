// pages/xiaoguotuxiangqfd/xiaoguotuxiangqfd.js

//显示带取消按钮的消息提示框
function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
  wx.showModal({
    title: title,
    content: content,
    showCancel: false,
    success: function (res) {
      if (res.confirm) {
        confirm();
      }
    }
  });
}

let app = getApp();
let apiUrl = app.getApiUrl();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    viewqieh:false,
    inputphone: '',
    inputfangan: '',
    mji: "",
    prev: [],
    city: [],
    area: [],
    prevIndex: '0',
    cityIndex: '0',
    areaIndex: '0',
    json: [],
    isHideCity: true,
    xzcity: '请选择城市',
    selectText: '',
    shoucqh:true,
    shujvimg:null,
    pvshuju:null,
    xiangqingid:null,
    shoucpanduan:null,
    userId:null,
    biaoti:''
  
  },
  EventHandle: function (event) {
    var count = event.detail.current;
    this.data.topImg = data.videoList[count].img
    this.data.topName = data.videoList[count].name
    this.data.topGanwu = data.videoList[count].ganwu
    this.data.topGingyan = data.videoList[count].jingyan
    this.data.topImage = data.videoList[count].image
    this.data.topTupian = data.videoList[count].tupian
    this.setData({ topName: this.data.topName })
    this.setData({ topImg: this.data.topImg })
    this.setData({ topGanwu: this.data.topGanwu })
    this.setData({ topGingyan: this.data.topGingyan })
    this.setData({ topImage: this.data.topImage })
    this.setData({ topTupian: this.data.topTupian })
  },



  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var self = this;
    let title = options.title;
    wx.setNavigationBarTitle({
        title: title,
    })
    self.setData({
      xiangqingid: options.id
    })
    app.getUserInfo(function (res) {
        self.setData({ userId: res.userId });
    })
    wx.request({
      url: apiUrl + '/appletcarousel/meituDetails',
      data: {
        id: options.id,
        userid: self.data.userId
      },
      header: {
        'Content-Type': 'application/json'
      },
      success: function (res) {
        self.setData({
          shujvimg: res.data.info[0].img_path,
          pvshuju:res.data.info[0].pv,
          biaoti: res.data.info[0].title || '',
          shoucpanduan: res.data.info[0].is_collect
        })
        wx.setNavigationBarTitle({ title: self.data.biaoti })
      },
      fail: function () {
        console.log('error!!!!!!!!!!!!!!')
      }
    })

    let tiShi = app.getNewStorage('tiShi')||'';
    if (tiShi==="false"){
       self.setData({
         viewqieh:true
       })
    }else{
      self.setData({
        viewqieh:false
      })
    }
  },

  quchuyy: function () {
    let that = this;
    that.setData({
      viewqieh: true,
    });
    let tiShi = app.getNewStorage('tiShi')
    if(!tiShi){
      app.setNewStorage('tiShi', 'false', 86400)
    }

  },

  xiangqye: function () {
    var that=this;
    wx.navigateTo({
      url: '../xiaoguotuxiangq/xiaoguotuxiangq?id=' + that.data.xiangqingid
    });
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
  
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    let that = this;
    let json = [];
    let prevJson = [];
    let cityJson = [];
    let areaJson = [];
    let cityUrl;

    // 获取缓存城市数据
    wx.getStorage({
      key: 'cityJson',
      success: function (res) {
        let cityJsonNew = res.data;
        that.setData({ prev: cityJsonNew.prev, city: cityJsonNew.city, area: cityJsonNew.area });
        //   that.setData({ json: cityJsonNew.json })
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
                that.setData({ prev: prevJson, city: cityJson, area: areaJson, json: json });
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

  Mianji: function (e) {
    this.setData({ mji: e.detail.value })
    if (parseInt(e.detail.value) > 10000) {
        alertViewWithCancel("提示", "请输入小于10000的数字", function () { });
    }
  },
  inputPhone: function (e) {
    this.setData({ inputphone: e.detail.value })
  },

  // 城市选择滑动
  bindChange: function (e) {
    let that = this;
    let cityJson = [];
    let areaJson = [];
    const val = e.detail.value;
    // let json = that.data.json;
    let prev = val[0];
    let city = val[1];
    let area = val[2];
    wx.getStorage({
      key: 'cityJson',
      success: function (res) {
        let json = res.data.json
        // 滑动省份获取城市
        for (let j = 0; j < json[prev].children.length; j++) {
          cityJson.push({ id: json[prev].children[j].id, text: json[prev].children[j].text })
        }
        // 滑动城市获取区域
        for (let k = 0; k < json[prev].children[city].children.length; k++) {
          areaJson.push({ id: json[prev].children[city].children[k].id, text: json[prev].children[city].children[k].text })
        }
        that.setData({ city: cityJson, area: areaJson, prevIndex: prev, cityIndex: city, areaIndex: area })
      }
    });
  },

  closebtn: function () {
    this.setData({ isHideCity: true });
  },

  okbtn: function () {
    let that = this;
    let prevInfo = that.data.prev;
    let cityInfo = that.data.city;
    let areaInfo = that.data.area;

    let prevId = prevInfo[that.data.prevIndex].id
    let cityId = cityInfo[that.data.cityIndex].id
    let areaId = areaInfo[that.data.areaIndex].id

    let prevText = prevInfo[that.data.prevIndex].text
    let cityText = cityInfo[that.data.cityIndex].text
    let areaText = areaInfo[that.data.areaIndex].text

    let prevCityAreaId = prevId + ',' + cityId + ',' + areaId
    let selectText = prevText + ' ' + cityText + ' ' + areaText;
    console.log(prevCityAreaId + selectText);
    this.setData({ xzcity: '', isHideCity: true, selectText: selectText, prevCityAreaId: prevCityAreaId });
    console.log(selectText)

  },
  selectHandle: function () {
    this.setData({ isHideCity: false })
  },


  // 装修设计表单提交1
  formSubmit: function (e) {
    let that = this;
    // console.log("提交表单");
    console.log(e);
    var city = this.data.prevCityAreaId;
    var bjmj = e.currentTarget.dataset.mianji;
    var tel = e.currentTarget.dataset.phone;

    if (that.data.selectText.length < 1) {
      that.setData({
        xzcity: '选择城市',
      })
      alertViewWithCancel("提示", "请选择您的所在地区", function () {

      });
      return;
    } else {
      that.setData({
        xzcity: '',
      })
    }
    if (bjmj.length < 1) {
      alertViewWithCancel("提示", "请输入您的房屋面积", function () {

      });
      return;
    }

    if (tel.length < 1) {
      alertViewWithCancel("提示", "请输入手机号", function () {

      });
      return;
    } else {
      var reg = new RegExp("^(13|14|15|17|18)[0-9]{9}$");
      if (!reg.test(tel)) {
        alertViewWithCancel("提示", "请填写正确的手机号码", function () {
        });
        console.log(" ^_^!");
        return false;
      }
    }
    // c

    if (that.data.src) {
      wx.request({
        url: apiUrl + '/zxjt/submit_order/?src=' + that.data.src,
        data: {
          mianji: bjmj,
          tel: tel,
          cs: city,
        },
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        method: "POST",
        success: function (res) {
          // console.log(res)
          alertViewWithCancel("提示", "提交成功，请注意接听电话", function () {
            console.log("点击确定按钮");
          });
        }
      });
    } else {

      wx.request({
        url: apiUrl + '/zxjt/submit_order/?src=lljs-wxxcx',
        data: {
          mianji: bjmj,
          tel: tel,
          cs: city,
        },
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        method: "POST",
        success: function (res) {
          // console.log(res)
          alertViewWithCancel("提示", "提交成功，请注意接听电话", function () {
            console.log("点击确定按钮");
          });
        }
      });
    }
  },

  qiehuanshouc:function(e){
    let that=this;
    let id = e.currentTarget.dataset.id;

    wx.request({
      url: apiUrl + '/appletcarousel/editcollect',
      data: {
        userid: that.data.userId,
        classtype: '8', // 装修攻略
        classid: id
      },
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      method: "POST",
      success: function (res) {
        if (res.data.state == 1) {
          wx.showToast({
            title: '收藏成功',
            icon: 'success',
            duration: 1200
          });

          wx.request({
            url: apiUrl + '/appletcarousel/meituDetails',
            data: {
              id: that.data.xiangqingid,
              userid: that.data.userId
            },

            header: {
              'content-type': 'application/json'
            },
            success: function (res) {
              wx.showLoading({
                title: '页面加载中',
              });
              setTimeout(function () {
                wx.hideLoading()
              }, 1200);
              that.setData({
                shujvimg: res.data.info[0].img_path,
                shoucangid: res.data.info[0].id,
                pvshuju: res.data.info[0].pv,
                biaoti: res.data.info[0].title,
                shoucpanduan: res.data.info[0].is_collect
              })
              wx.setNavigationBarTitle({ title: that.data.biaoti })
            },
          });


        }
      }
    })

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function (res) {
      if (res.from === 'button') {
          // 来自页面内转发按钮
          res.target.dataset.title = res.target.dataset.title || '此图片没有标题 ^_^!'
      }
      return {
          title: res.target.dataset.title,
          success: function (res) {
              // 转发成功
          },
          fail: function (res) {
              // 转发失败
          }
      }
  },
  
})