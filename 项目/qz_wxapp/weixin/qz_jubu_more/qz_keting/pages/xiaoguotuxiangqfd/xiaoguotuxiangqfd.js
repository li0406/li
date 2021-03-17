// pages/xiaoguotuxiangqfd/xiaoguotuxiangqfd.js

const app = getApp()
let apiUrl = app.getApiUrl(),
  apid = app.getAPPid(),
  oImgUrl = app.getImgUrl();
function getLocalTime(nS) {
  return new Date(parseInt(nS) * 1000).toLocaleString().replace(/年|月/g, "-").replace(/日/g, " ").replace('/', '-').replace('/', '-').split(' ')[0];
}
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
Page({

  /**
   * 页面的初始数据
   */
  data: {
    circular:true,
    viewqieh:false,
    inputphone: '',
    inputfangan: '',
    inputname: '',
    mji: "",
    json: [],
    shoucqh:true,
    shujv:null,
    shujvimg:null,
    shujuscpd:null,
    pvshuju:null,
    wz:null,
    fg: null,
    hx: null,
    ys: null,
    xiangqingid:null,
    shoucpanduan:null,
    userId:null,
    biaoti:'',
    Idex: 1,
    changdu:'',
    prevCityAreaId:'',
    selectText: '',
    selectTextDefault: '请选择城市',
    valuecity: [0, 0, 0],
    val: [],
    prev: [],
    city: [],
    area: [],
    prevIndex: '0',
    cityIndex: '0',
    areaIndex: '0',
    isHideCity: true,
    personName: '',
    telNumber: '',
    mianJi2: '',
    telNumber2: '',
    tanchuang: true,
    tanchuang2: true,
    emptyNameValue: '',
    emptyPhoneValue: '',
    emptyMianjiValue:'',
    isColor:false,

  },

  EventHandle: function (event) {
    // console.log(event)
    // console.log(this.data.shujv)
    var count = event.detail.current;
    this.data.Idex = count + 1;
    this.data.biaoti = this.data.shujv[count].title;
    this.setData({ Idex: this.data.Idex });
    this.setData({ biaoti: this.data.biaoti });
    wx.setNavigationBarTitle({ title: this.data.biaoti })
  },



  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var self = this;
    console.log(options)
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
        userid: self.data.userId,
        fengge: options.fengg,
        location: options.leibwsj,
        color: options.yanse,
 		classtype:'8'
      },
      header: {
        'Content-Type': 'application/json'
      },
      success: function (res) {
        console.log(res)
        self.setData({
          changdu: res.data.info.length,
          shujv: res.data.info,
          shujvimg: res.data.info[0].img_path,
          shujuscpd: res.data.info[0].is_collect,
          wz: res.data.info[0].wz,
          fg: res.data.info[0].fg,
          hx: res.data.info[0].hx,
          ys: res.data.info[0].ys,
          pvshuju:res.data.info[0].pv,
          biaoti: res.data.info[0].title || '',
          shoucpanduan: res.data.info[0].is_collect
        })
        wx.setNavigationBarTitle({ title: self.data.biaoti })
        console.log(self.data.shujuscpd)
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

  toSeji:function(){
    wx.navigateTo({
        url:'../zhuangxiusj/zhuangxiusj'
    })
  },

  toBaojia: function () {
      wx.navigateTo({
          url: '../zhuangxiubj/zhuangxiubj'
      })
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


  selectHandle() {
    this.setData({ isHideCity: false });
  },

  /**
 * 城市选择滑动
 */
  bindChange: function (e) {
    let that = this;
    let cityJson = [];
    let areaJson = [];
    let val = e.detail.value;
    let prev = val[0];
    let city = val[1];
    let area = val[2];
    wx.getStorage({
      key: 'cityJson',
      success: function (res) {
        let json = res.data.json
        // 滑动省份获取城市
        for (let j = 0; j < json[prev].children.length; j++) {
          cityJson.push({ id: json[prev].children[j].id, text: json[prev].children[j].text });
        }
        // 滑动城市获取区域
        for (let k = 0; k < json[prev].children[city].children.length; k++) {
          areaJson.push({ id: json[prev].children[city].children[k].id, text: json[prev].children[city].children[k].text });
        }
        that.setData({ city: cityJson, area: areaJson, prevIndex: prev, cityIndex: city, areaIndex: area });
      }
    });

  },
  closebtn() {
    this.setData({ isHideCity: true });
  },
  /**
   * 城市选择
   */
  okbtn() {
    let that = this;
    let prevInfo = that.data.prev;
    let cityInfo = that.data.city;
    let areaInfo = that.data.area;

    let prevId = prevInfo[that.data.prevIndex].id;
    let cityId = cityInfo[that.data.cityIndex].id;
    let areaId = areaInfo[that.data.areaIndex].id;

    let prevText = prevInfo[that.data.prevIndex].text;
    let cityText = cityInfo[that.data.cityIndex].text;
    let areaText = areaInfo[that.data.areaIndex].text;

    let prevCityAreaId = prevId + ',' + cityId + ',' + areaId;
    let selectText = prevText + ' ' + cityText + ' ' + areaText;
    this.setData({ isHideCity: true, isColor: true, selectText: selectText, prevCityAreaId: prevCityAreaId, areaId: areaId, serverVal: areaText, selectTextDefault: '' });
  },


  getName: function (e) {
    this.setData({ personName: e.detail.value });
  },
  getPhone: function (e) {
    this.setData({ telNumber: e.detail.value });
  },

  getMianji2: function (e) {
    this.setData({ mianJi2: e.detail.value });
  },
  getPhone2: function (e) {
    this.setData({ telNumber2: e.detail.value });
  },

  getSheJi: function () {
    let regu = "^[a-zA-Z\u4e00-\u9fa5]+$";
    let re = new RegExp(regu);

    let that = this;
    if (that.data.selectText.length < 1) {
      that.setData({ selectTextDefault: '请选择城市' })
      alertViewWithCancel("提示", "请选择您的所在地区", function () { });
      return;
    } else {
      that.setData({ selectTextDefault: '' })
    }
    if (that.data.personName.length < 1) {
      alertViewWithCancel("提示", "请输入您的称呼", function () {
        that.setData({ boolName: true });
      });
      return;
    } else if (that.data.personName.search(re) == -1) {
      alertViewWithCancel("提示", "用户名不能为数字", function () {
        that.setData({ boolName: true });
      });
      return;
    }
    if (that.data.telNumber.length < 1) {
      alertViewWithCancel("提示", "请输入手机号", function () { });
      return;
    } else {
      var reg = new RegExp("^(13|14|15|17|18)[0-9]{9}$");
      if (!reg.test(that.data.telNumber)) {
        alertViewWithCancel("提示", "请填写正确的手机号码", function () { });
        return false;
      }
    }

    if (that.data.src) {
      wx.request({

        url: apiUrl + '/zxjt/submit_order_v2/?src=' + that.data.src,
        data: {
          name: that.data.personName,
          tel: that.data.telNumber,
          cs: that.data.prevCityAreaId
        },
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        method: "POST",
        success: function (res) {
          that.setData({
            tanchuang: true,
            emptyNameValue: '',
            emptyPhoneValue: '',
          })
          alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () { });
        }
      });
    } else {
      wx.request({
        url: apiUrl + '/zxjt/submit_order_v2/?src=' + app.globalData.sourceMark,
        data: {
          name: that.data.personName,
          tel: that.data.telNumber,
          cs: that.data.prevCityAreaId
        },
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        method: "POST",
        success: function (res) {
          that.setData({
            tanchuang: true,
            emptyNameValue: '',
            emptyPhoneValue: '',
          })
          alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () { });
        }
      });
    }

  },

  getSheJi2: function () {
    let regu = "^[a-zA-Z\u4e00-\u9fa5]+$";
    let re = new RegExp(regu);

    let that = this;
    if (that.data.selectText.length < 1) {
      that.setData({ selectTextDefault: '请选择城市' })
      alertViewWithCancel("提示", "请选择您的所在地区", function () { });
      return;
    } else {
      that.setData({ selectTextDefault: '' })
    }
    if (that.data.mianJi2.length < 1) {
      alertViewWithCancel("提示", "请输入您的房屋面积", function () {
        that.setData({ boolName: true });
      });
      return;
    } else if (isNaN(that.data.mianJi2)) {
      alertViewWithCancel("提示", "房屋面积为数字", function () {
        that.setData({ boolName: true });
      });
      return;
    }
    if (that.data.telNumber2.length < 1) {
      alertViewWithCancel("提示", "请输入手机号", function () { });
      return;
    } else {
      var reg = new RegExp("^(13|14|15|17|18)[0-9]{9}$");
      if (!reg.test(that.data.telNumber2)) {
        alertViewWithCancel("提示", "请填写正确的手机号码", function () { });
        return false;
      }
    }

    if (that.data.src) {
      wx.request({

        url: apiUrl + '/zxjt/submit_order_v2/?src=' + that.data.src,
        data: {
          name: that.data.mianJi2,
          tel: that.data.telNumber2,
          cs: that.data.prevCityAreaId
        },
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        method: "POST",
        success: function (res) {
          that.setData({
            tanchuang2: true,
            emptyMianjiValue: '',
            emptyPhoneValue: '',
          })
          alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () { });
        }
      });
    } else {
      wx.request({
        url: apiUrl + '/zxjt/submit_order_v2/?src=' + app.globalData.sourceMark,
        data: {
          name: that.data.mianJi2,
          tel: that.data.telNumber2,
          cs: that.data.prevCityAreaId
        },
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        method: "POST",
        success: function (res) {
          that.setData({
            tanchuang2: true,
            emptyMianjiValue: '',
            emptyPhoneValue: '',
          })
          alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () { });
        }
      });
    }

  },

  Guanbi: function () {
    let that = this;
    that.setData({
      tanchuang: true,
      emptyNameValue: '',
      emptyPhoneValue: '',
    })
  },

  Guanbi2: function () {
    let that = this;
    that.setData({
      tanchuang2: true,
      emptyMianjiValue: '',
      emptyPhoneValue: '',
    })
  },

  Tanchuang: function () {
    let that = this;
    that.setData({
      tanchuang: false,
    })
  },

  Tanchuang2: function () {
    let that = this;
    that.setData({
      tanchuang2: false,
    })
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

  Name: function (e) {
    this.setData({ inputname: e.detail.value })
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
    let oldVal = that.data.val;
    that.setData({ val: val })
    wx.getStorage({
      key: 'cityJson',
      success: function (res) {
        let json = res.data.json
        // 滑动省份获取城市
        if (oldVal[0] != prev && oldVal[1] == city && oldVal[2] == area) {
          city = 0; area = 0;
          that.setData({ valuecity: [prev, 0, 0] })
        } else if (oldVal[0] == prev && oldVal[1] != city && oldVal[2] == area) {
          area = 0;
          that.setData({ valuecity: [prev, city, 0] })
        } else if (oldVal[0] == prev && oldVal[1] == city && oldVal[2] != area) {

        }
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

  selectHandle: function () {
    this.setData({ isHideCity: false })
  },


  // 装修设计表单提交1
  formSubmit: function (e) {
    let that = this;
    // console.log("提交表单");
    console.log(e);
    var city = this.data.prevCityAreaId;
    var name = e.currentTarget.dataset.name;
    var tel = e.currentTarget.dataset.phone;
    console.log(city)

    if (city.length < 1) {
      that.setData({
        xzcity: '请选择城市',
      })
      alertViewWithCancel("提示", "请选择您的所在地区", function () {

      });
      return;
    } else {
      that.setData({
        xzcity: '',
      })
    }
    if (name.length < 1) {
      alertViewWithCancel("提示", "请输入您的称呼", function () {

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
    console.log(this.data.prevCityAreaId)
    if (that.data.src) {
      wx.request({
        url: apiUrl + '/zxjt/submit_order_v2/?src=' + that.data.src,
        data: {
          name: name,
          tel: tel,
          cs: city,
        },
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        method: "POST",
        success: function (res) {
          // console.log(res)
          alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () {
            console.log("点击确定按钮");
          });
        }
      });
    } else {

      wx.request({
        url: apiUrl + '/zxjt/submit_order_v2/?src=' + app.globalData.sourceMark,
        data: {
          name: name,
          tel: tel,
          cs: city,
        },
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        method: "POST",
        success: function (res) {
          // console.log(res)
          alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () {
            console.log("点击确定按钮");
          });
        }
      });
    }
  },

  xgtxqfdsc:function(e){
    console.log(e)
    let that=this;
    let id = e.currentTarget.dataset.id;

    if (that.data.userId) {
            wx.request({
                url: apiUrl + '/appletcarousel/editcollect',
                data: {
                    userid: that.data.userId,
                    classtype: '8', // 装修效果图
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
                        // let arr = that.data.data;
                        // arr[index].is_collect = 1;
                        that.setData({ shujuscpd: '1'});
                    }
                }
            });
        }else {
      app.getLoginAgain(function (e) {
        wx.login({
          success: function (l) {
            if (l.code) {
              wx.request({
                url: apiUrl + '/login',
                data: {
                  appid: apid,
                  code: l.code,
                  name: e.nickName,
                  logo: e.avatarUrl
                },
                header: {
                  'content-type': 'application/x-www-form-urlencoded'
                },
                method: "POST",
                dataType: 'json',
                success: function (i) {
                  that.setData({ userId: i.data.data })
                  wx.request({
                    url: apiUrl + '/appletcarousel/editcollect',
                    data: {
                      userid: i.data.data,
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
                        // let arr = that.data.data;
                        // arr[index].is_collect = 1;
                        that.setData({ shujuscpd: '1' });
                      }
                    }
                  });
                }
              });
            }
          }
        })
      });
    }

  },


  delxgtxqfdsc: function (e) {
    console.log(e)
    let that = this;
    let id = e.currentTarget.dataset.id;

    if (that.data.userId) {
      wx.request({
        url: apiUrl + '/appletcarousel/editcollect',
        data: {
          userid: that.data.userId,
          classtype: '8', // 装修效果图
          classid: id
        },
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        method: "GET",
        success: function (res) {
          if (res.data.state == 1) {
            wx.showToast({
              title: '取消收藏',
              icon: 'success',
              duration: 1200
            });
            that.setData({ shujuscpd: '0' });
          }
        }
      });
    } else {
      app.getLoginAgain(function (e) {
        wx.login({
          success: function (l) {
            if (l.code) {
              wx.request({
                url: apiUrl + '/login',
                data: {
                  appid: apid,
                  code: l.code,
                  name: e.nickName,
                  logo: e.avatarUrl
                },
                header: {
                  'content-type': 'application/x-www-form-urlencoded'
                },
                method: "GET",
                dataType: 'json',
                success: function (i) {
                  wx.request({
                    url: apiUrl + '/appletcarousel/editcollect',
                    data: {
                      userid: i.data.data,
                      classtype: '8', // 装修攻略
                      classid: id
                    },
                    header: {
                      'content-type': 'application/x-www-form-urlencoded'
                    },
                    method: "GET",
                    success: function (res) {
                      if (res.data.state == 1) {
                        wx.showToast({
                          title: '取消收藏',
                          icon: 'success',
                          duration: 1200
                        });
                        that.setData({ shujuscpd: '0' });
                      }
                    }
                  });
                }
              });
            }
          }
        })
      });
    }

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