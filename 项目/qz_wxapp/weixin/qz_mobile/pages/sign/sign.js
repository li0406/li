// pages/sign/sign.js0
import {
  getSignList,
  signlist,
  signHandle
} from "../../utils/api.js"
const app = getApp();
const util = require('../../utils/util.js');
const dateObj = new Date();
function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
  wx.showModal({
      title: title,
      content: content,
      showCancel: true,
      success: function (res) {
          if (res.confirm) {
              confirm();
          } else if (res.cancel) {

          }
      }
  });
}
Page({

  /**
   * 页面的初始数据
   */
  data: {
    curCity: '不限',
    cs: '',
    createTime: '2010-10-10',
    nowTime: util.formatDate(new Date()),
    start: '',
    end: '',
    // tab
    fen: [{
        id: '',
        name: '不限'
      },
      {
        id: 1,
        name: '分单'
      },
      {
        id: 2,
        name: '赠单'
      }
    ],
    fen_index: -1,
    yu: [{
        id: '',
        name: '不限'
      },
      {
        id: 0,
        name: '审核中'
      },
      {
        id: 1,
        name: '已签约'
      },
      {
        id: 2,
        name: '继续跟踪'
      }
    ],
    yu_index: -1,
    fenId: '',
    yuId: '',
    // 请求数据
    list: [],
    pageCurrent: '0',
    pageTotalNumber: '0',
    parms: {
      cs: '',
      qdstatus: '',
      fw: '',
      start: '',
      end: '',
      search: '',
      p: 1,
      psize: 10,
    },
    noResult: false,
    page: false,
    pageNumber: [],
    popup: true, // 弹窗显示
    infoStatus: 0, // 审核弹窗选项
    popupCompanyName: '', //审核弹窗公司名称
    popupStatus: '', //审核弹窗状态名称
    popupId: '', //审核弹窗状态名称
    isListTo: false,
    showXibao:false,
    componyName:false,
    shareImg:'',
    windowWidth:'' ,
    windowHeight:'',
    mt:''
    
  },
  //城市选择
  toCity: function() {
    let city = this.data.curCity;
    wx.navigateTo({
      url: '../city/city?needArea=1&curCity=' + city
    })
  },
  //搜索框
  searchWord: function(e) {
    let that = this;
    let value = e.detail.value;

    that.setData({
      ['parms.search']: value,
      ['parms.p']: 1
    })
    that.getSignList(that.data.parms);
  },
  // 监听输入
  watchPassWord: function(event) {},
  //选择时间
  dateChange: function(e) {
    var keys = e.currentTarget.dataset.time;
    var obj = {};
    obj[keys] = e.detail.value;
    if (keys == 'start' && this.data.end != '' && e.detail.value > this.data.end) {
      obj.end = e.detail.value;
    }
    this.setData(obj);
    let that = this
    if (that.data.start != '' && that.data.end != '') {
      let start = that.data.start
      let end = that.data.end
      that.setData({
        ['parms.start']: start,
        ['parms.end']: end,
        ['parms.p']: 1
      })
      that.getSignList(that.data.parms);
    }
  },
  // 选项卡
  bindPickerChange_fen: function(e) {
    let id = this.data.fen[e.detail.value].id
    this.setData({
      fen_index: e.detail.value,
      fenId: id
    })
    let that = this;
    that.setData({
      ['parms.fw']: id,
      ['parms.p']: 1
    })
    that.getSignList(that.data.parms);
  },
  bindPickerChange_yu: function(e) {
    let id = this.data.yu[e.detail.value].id
    this.setData({
      yu_index: e.detail.value,
      yuId: id
    })
    let that = this;
    that.setData({
      ['parms.qdstatus']: id,
      ['parms.p']: 1
    })
    that.getSignList(that.data.parms);
  },
  //列表数据
  getSignList: function(parms) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function(res) {
        let token = res.data.token;
        getSignList('/v1/orders/qd',
          parms, {
            'content-type': 'application/json',
            'token': token
          }
        ).then(res => {
          if (res.error_code == 0) {
            //獲取頁嗎
            let totalNumber = res.data.page.page_total_number;
            let pageArr = [];
            for (let i = 0; i < totalNumber; i++) {
              pageArr.push(i + 1)
            }
            let datalist = res.data.list
            if (datalist.length < 1) {
              that.setData({
                list: [],
                page: false,
                noResult: true
              })
              return false
            }
            datalist.forEach((item, index) => {
              item.url = '../signdetail/signdetail?id=' + item.id + '&status=' + item.qiandan_status + '&type_fw=' + item.type_fw
            })
            let timestamp = res.data.timestamp
            if (!Array.isArray(timestamp)){
              var n = timestamp * 1000
              var date = new Date(n)
              //年  
              var Y = date.getFullYear();
              //月  
              var M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1);
              //日  
              var D = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();
              let timeStart = Y + '-' + M + '-' + D
              that.setData({
                timeStart: timeStart,
                start: timeStart,
                ['parms.qdstart']: timeStart
              })
            }
            that.setData({
              list: datalist,
              pageCurrent: res.data.page.page_current,
              pageTotalNumber: res.data.page.page_total_number,
              pageNumber: pageArr,
              noResult: false,
              page: true
            })
            if (res.data.page.total_number < 10) {
              that.setData({
                page: false
              })
            } else {
              that.setData({
                page: true
              })
            }
          } else {
            wx.showToast({
              title: res.error_msg,
              duration: 2000,
              icon: 'none'
            })
            that.setData({
              list: [],
              page: false,
              noResult: true,
              noInternet: false
            })
          }

        }).catch(function(error) {
          that.setData({
            list: [],
            page: false,
            noResult: false,
            noInternet: true
          })
        })
      },
      fail: function(res) {
        wx.showToast({
          title: '请登陆',
          icon: 'none',
          duration: 2000
        })
        setTimeout(function() {
          wx.navigateTo({
            url: '../login/login'
          })
        }, 2000)

      }
    })
  },
  prevBtn: function(e) {
    let that = this;
    let p = that.data.pageCurrent;
    if (p <= 1) {
      p = 1;
      return;
    } else {
      p = p - 1;
    }
    that.setData({
      ['parms.p']: p
    })
    that.getSignList(that.data.parms)

  },
  nextBtn: function(e) {
    let that = this;
    let p = that.data.pageCurrent;
    let max = that.data.pageTotalNumber;
    if (p >= max) {
      p = max;
      return;
    } else {
      p = p + 1;
    }
    that.setData({
      ['parms.p']: p
    })
    that.getSignList(that.data.parms)
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })

  },
  // 网络故障
  toCustom: function() {
    wx.navigateTo({
      url: '../sign/sign'
    })
  },

  // 分页
  toPage: function(e) {
    let that = this;
    let p = e.detail.value;
    p = Number(p) + 1;
    that.setData({
      ['parms.p']: p
    })
    that.getSignList(that.data.parms);
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
  },
  // 取消
  cancel(e){
    const that = this
    const id = e.currentTarget.dataset.id
    wx.showModal({
      title: '提示',
      content: '确定要取消签单？',
      success(res) {
        if (res.confirm) {
          wx.getStorage({
            key: 'info',
            success: function(res) {
              let token = res.data.token;
              signHandle('/v1/orders/qdcacel', {
                id: id
              }, {
                'content-type': 'application/json',
                'token': token
              }).then(res => {
                if (res.error_code == 0) {
                  wx.showToast({
                    title: '已取消该签单',
                    duration: 1000
                  })
                  that.getSignList(that.data.parms)
                }
              })
            }
          })
        } else if (res.cancel) {
          console.log('用户点击取消')
        }
      }
    })
  },
  // 查看
  toDetail(e){
    const url = e.currentTarget.dataset.url
    wx.navigateTo({
      url: url //实际路径要写全
    })
  },
  // 返点跳转
  rebates(e){
    const id = e.currentTarget.dataset.id
    if(this.data.isListTo){
      let pages = getCurrentPages(); //获取当前页面js里面的pages里的所有信息。
      let prevPage = pages[ pages.length - 2 ]; //prevPage 是获取上一个页面的js里面的pages的所有信息。 -2 是上一个页面，-3是上上个页面以此类推。
      prevPage.setData({  // 将我们想要传递的参数在这里直接setData。上个页面就会执行这里的操作。
        ['parms.order_id']: id,
        isListTo: true
      })
    //上一个页面内执行setData操作，将我们想要的信息保存住。当我们返回去的时候，页面已经处理完毕。
    //最后就是返回上一个页面。
      wx.navigateBack({
        delta: 1  // 返回上一级页面。
      })
    }else{
      wx.navigateTo({
        url: '../addsReport/addsReport?orderId='+id
      })
    }
  },
  // 弹窗
  hidePopup(flag = true) {
    this.setData({
      "popup": flag,
      infoStatus: 0 
    });
  },
  // 审核
  showPopup(e) {
    const name = e.currentTarget.dataset.name
    const status = e.currentTarget.dataset.status
    const id = e.currentTarget.dataset.id
    this.hidePopup(false);
    this.setData({
      popupCompanyName: name,
      popupStatus: status,
      popupId: id
    })
  },
  signStatus(e) {
    let status = e.detail.value
    this.setData({
      infoStatus: status
    })
  },
  report: function() {
    let that = this
    let infoStatus = that.data.infoStatus;
    if (!infoStatus) {
      wx.showToast({
        title: '请选择状态',
        icon: 'none',
        duration: 2000
      })
      return false
    } else {
      let id = that.data.popupId
      let infoStatus = that.data.infoStatus
      wx.getStorage({
        key: 'info',
        success: function(res) {
          let token = res.data.token;
          signHandle('/v1/orders/qdup', {
            id: id,
            qiandan_status: infoStatus
          }, {
            'content-type': 'application/json',
            'token': token
          }).then(res => {
            if (res.error_code == 0) {
              wx.showToast({
                title: '已保存',
                duration: 1000,
                icon: 'none'
              })
              that.getSignList(that.data.parms)
              that.hidePopup(true)
            }else{
              wx.showToast({
                title: 'res.error_msg',
                icon: 'none',
                duration: 1000
              })
            }
          })
        },
        fail: function(res) {
          wx.showToast({
            title: '请登陆',
            icon: 'none',
            duration: 2000
          })
          setTimeout(function() {
            wx.navigateTo({
              url: '../login/login'
            })
          }, 2000)

        }
      })
    }
  },
  onLoad: function(options) {
    var year = dateObj.getFullYear()
    var month = dateObj.getMonth() + 1
    var date = dateObj.getDate()
    let that = this
    let windowWidth = wx.getSystemInfoSync().windowWidth
    let windowHeight = wx.getSystemInfoSync().windowHeight
    let mt = windowHeight - windowWidth*1.608
    this.setData({
      timeStart: '开始时间',
      end: year + '-' + util.formatNumber(month) + '-' + util.formatNumber(date),
      ['parms.end']: year + '-' + util.formatNumber(month) + '-' + util.formatNumber(date) 
    })
    that.setData({
      windowWidth,
      windowHeight,
      mt
    })
    if (Object.keys(options).length != 0) {
      if(options.type == 8){
        that.setData({
          yu_index: 2,
          ['parms.qdstatus']: 1,
          isListTo: true
        })
      }else{
        that.setData({
          ['parms.search']: options.id,
          ['parms.start']: options.start,
          start: options.start,
          end: options.end,
          ['parms.end']: options.end
        })
      }
    }
  },
  xibao(e){
    const name = e.currentTarget.dataset.name;
    this.setData({
      componyName : name,
      showXibao : true
    })
    this.createdCode()
  },
  hideXibao(){
    this.setData({
      showXibao : false
    })
  },
  createdCode() {
    let that = this;
    const ctx = wx.createCanvasContext('shareImg');    //绘图上下文
    const name = that.data.componyName;     //绘图的标题  需要处理换行
    const windowWidth = wx.getSystemInfoSync().windowWidth
    const windowHeight = windowWidth*1.608
    const mt = wx.getSystemInfoSync().windowHeight - windowHeight
    // canvas 背景颜色设置不成功，只好用白色背景图
    ctx.drawImage('/img/xibao-bj2.png', 0, 0, windowWidth, windowHeight)
    // 绘制标题
    ctx.font = 'normal 30px sans-serif'
    ctx.setTextAlign('center')
    ctx.setFillStyle('#fff')
    ctx.fillText(name, windowWidth*.5, windowHeight*.61 )
    ctx.draw()
    setTimeout(() => {
      that.saveImgData()
    },300)
  },
  saveImgData(){
    let that = this;
    wx.canvasToTempFilePath({
      x: 0,
      y: 0,
      canvasId: 'shareImg',
      success: function (res) {
        let shareImg = res.tempFilePath;
        that.setData({
          shareImg: shareImg
        })
      },
      fail: function (res) {
      }
    })
  },
  saveImg() {
    let that = this;
    that.saveImgData()
    // 获取用户是否开启用户授权相册
    alertViewWithCancel('提示','喜报保存到相册？',function(){
      wx.getSetting({
        success(res) {
          // 如果没有则获取授权
          if (!res.authSetting['scope.writePhotosAlbum']) {
            wx.authorize({
              scope: 'scope.writePhotosAlbum',
              success() {
                wx.saveImageToPhotosAlbum({
                  filePath: that.data.shareImg,
                  success() {
                    wx.showToast({
                      title: '保存成功'
                    })
                  },
                  fail() {
                    wx.showToast({
                      title: '保存失败',
                      icon: 'none'
                    })
                  }
                })
              },
              fail() {
              // 如果用户拒绝过或没有授权，则再次打开授权窗口
              //（ps：微信api又改了现在只能通过button才能打开授权设置，以前通过openSet就可打开，下面有打开授权的button弹窗代码）
                that.setData({
                  openSet: true
                })
              }
            })
          } else {
            // 有则直接保存
            wx.saveImageToPhotosAlbum({
              filePath: that.data.shareImg,
              success() {
                wx.showToast({
                  title: '保存成功'
                })
              },
              fail() {
                wx.showToast({
                  title: '保存失败',
                  icon: 'none'
                })
              }
            })
          }
        }
      })
    })
    
  },
  // 授权
  cancleSet() {
    this.setData({
      openSet: false
    })
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {
    let that = this;
    that.setData({
      ['parms.cs']: that.data.cs
    });
    that.getSignList(that.data.parms);
    if(that.data.list != ''){
      that.setData({
        noResult:false
      })
    }
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function() {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function() {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {

  }
})