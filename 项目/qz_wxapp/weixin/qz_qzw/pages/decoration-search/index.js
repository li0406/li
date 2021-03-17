// pages/decoration-search/index.js

const app = getApp()
const apiUrl = app.getApiUrl();
const fadan = require('../../utils/fadan.js');

Page({
  data: {
    tabActive:true,
    getImgUrl: app.getImgUrl(),
    meituData:[],
    hasSearch:false,
    noXgt:false,
    noCases:false,
    fd:"",
    lingNum: app.globalData.personNum,
    caseData:[],
    once:0,
    xgtParms: {
      p: 1,
      count: 20,
      keyword: "",
      wz: "",
      fg: "",
      ys: ""
    },
    caseParms: {
      p: 1,
      count: 20,
      keyword: "",
      hx: "",
      fg: "",
      jg: '',
      bm: "sz"
    },
    xgtHeight: "",
    caseHeight: "",
    caseUnderLine:false,
    xgtUnderLine:false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    //发单引用
    let that=this;
    fadan.fadan.init(this, 2, {
      cityInput: true,
      addressInput: false,
      phoneInput: true,
      nameInput: true,
      areaInput: false,
      btnText: "马上获取"
    });
    wx.getSystemInfo({
      success: function (res) { 
        that.setData({
          xgtHeight: res.windowHeight-100,
          caseHeight: res.windowHeight-100
        })
      }
    });
  },
  searchWord:function(e){
    var value = e.detail.value;
    this.setData({
      ["xgtParms.keyword"]: value,
      ["caseParms.keyword"]: value,
      hasSearch:true,
      meituData:[],
      caseData:[],
      tabActive:true,
      once: 0
    });
    this.getXgtData(this.data.xgtParms,false);
   
  },
  getXgtData(parms,flag){
    let that=this;
    wx.showLoading({
      title: '数据加载中',
    });
    wx.request({
      url: apiUrl + "/index/meitu",
      data: parms,
      header: {
        'content-type': 'application/json' // 默认值
      },
      success:function(res){
        wx.hideLoading()
        //直接搜索情况
        if(!flag){
          if (res.data.status == 1){
            that.setData({
              meituData: res.data.data.meitu,
              noXgt:false
            });
            if (res.data.data.meitu.length < 20) {
              that.setData({
                xgtUnderLine: true
              })
            }

          }else{
            that.setData({
              meituData:[],
              noXgt: true
            });
          }
        }else{
          if (res.data.status == 1){
            let gloabaLength = that.data.meituData.length;
            let insertDataList = res.data.data.meitu.length;
            for (let k = gloabaLength; k < (gloabaLength + insertDataList); k++) {
              let pushData = "meituData[" + k + "]";
              that.setData({
                [pushData]: res.data.data.meitu[k % 20]
              });
            }
            if (insertDataList < 20) {
              that.setData({
                xgtUnderLine: true
              })
            }
          }else{
            that.setData({
              xgtUnderLine: true
            })
          }
        }
      },
    })
  },
  getCaseData(parms,flag){
    let that = this;
    wx.showLoading({
      title: '数据加载中',
    });
    wx.request({
      url: apiUrl + "/index/xgt",
      data: parms,
      header: {
        'content-type': 'application/json' // 默认值
      },
      success: function (res) {
        wx.hideLoading()
        //直接搜索情况
        if(!flag){
          if(res.data.status==1){
            for (let m = 0; m < res.data.data.cases.length;m++){
              res.data.data.cases[m].pv = app.getPVNum();
            }
            that.setData({
              caseData: res.data.data.cases,
              noCases: false
            });
            if (res.data.data.cases.length < 20) {
              that.setData({
                caseUnderLine: true
              })
            }

          }else{
            that.setData({
              caseData:[],
              noCases: true
            });
          }
        }else{
          if (res.data.status == 1){
            let gloabaLength = that.data.caseData.length;
            let insertDataList = res.data.data.cases.length;
            for (let k = gloabaLength; k < (gloabaLength + insertDataList); k++) {
              res.data.data.cases[k % 20].pv = app.getPVNum();
              let pushData = "caseData[" + k + "]";
              that.setData({
                [pushData]: res.data.data.cases[k % 20]
              });
            }
            if (insertDataList <20) {
              that.setData({
                caseUnderLine: true
              })
            }
          }else{
            that.setData({
              caseUnderLine: false
            })
          }
        }
      },
    })
  },
  changeType: function (e) {
    
    if (this.data.once==0){
      this.getCaseData(this.data.caseParms, false)
    }
    this.setData({
      tabActive: e.target.dataset.type == "true",
      once:1
    });
    
    
    
  },
  lowerXgt:function(e){
    let page = "xgtParms.p";
    this.setData({
      [page]: this.data.xgtParms.p + 1
    });
    this.getXgtData(this.data.xgtParms, true)
  },
  lowerCase: function (e) {
    let page = "caseParms.p";
    this.setData({
      [page]: this.data.caseParms.p + 1
    });
    this.getCaseData(this.data.caseParms,true);

  },
  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.setData({
      lingNum: app.globalData.personNum
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

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function (ops) {
    if (ops.from === 'button') {
      // 来自页面内转发按钮
      console.log(ops.target)
    }
    return {
      title: '齐装网装修家居',
      path: 'pages/decoration-search/decoration-search',
      success: function (res) { },
      fail: function (res) { }
    }
  },
})