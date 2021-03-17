const app = getApp();
var config = require('../../config');
const apiUrl = app.getApiUrl();
const fadan = require('../../utils/fadan.js');
var QQMapWX = require('../../utils/qqmap-wx-jssdk.min.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    tabActive:true,
    reasultXgtData:[],
    xgtLevel:[],
    caseLevel:[],
    reasultCaseData:[],
    xgtControll: [{ "gb": false, "id": 0, "active": false, "name": "局部" }, { "gb": false, "id": 0, "active": false, "name": "风格" }, { "gb": false, "id": 0, "active": false, "name": "色彩"}],
    caseControll: [{ "hx": false, "id": 0, "active": false, "name": "户型" }, { "fg": false, "id": 0, "active": false, "name": "风格" }, { "jg": false, "id": 0, "active": false, "name": "造价"}],
    caseXh:0,
    xgtXh:0,
    fd:"",
    noXgt:false,
    noCase:false,
    xgtUnderLine:false,
    caseUnderLine:false,
    imgUrl: app.getImgUrl(),
    xgtParms:{
      p:1,
      count:20,
      keyword:"",
      wz:"",
      fg:"",
      ys:""
    },
    caseParms:{
      p: 1,
      count: 20,
      keyword: "",
      hx: "",
      fg: "",
      jg: '',
      bm:"sz"
    },
    xgtHeight:"",
    caseHeight:"",
    scrollTop:0
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that=this;
    wx.getSystemInfo({
      success: function (res) {
        that.setData({
          xgtHeight: res.windowHeight-70,
          caseHeight: res.windowHeight-70
        })
      }
    });
    if (options.id){
      this.setData({
        ["xgtParms.fg"]:options.id
      });
      for(let m=0; m<3;m++){
        let activeM = "xgtControll[" + m + "].active";
        if(m==1){
          that.setData({
            [activeM]: true
          });
        }else{
          that.setData({
            [activeM]: false
          });
        }
      }
      //带参不下拉,不筛选
      this.getXgtData(this.data.xgtParms,options.id,"","",true);
    }else{
      //不带参,不下拉,不筛选
      this.getXgtData(this.data.xgtParms, "", "", "", true);
    }
    let qqmapsdk = new QQMapWX({
      key: config.service.qqMapKey
    });

    wx.getLocation({
      type: 'wgs84',
      success: function (res) {
        var latitude = res.latitude
        var longitude = res.longitude
        qqmapsdk.reverseGeocoder({
          location: {
            latitude: latitude,
            longitude: longitude
          },
          success: function (data) {
            let cityName = data.result.address_component.city;//获取当前城市名
            wx.request({
              url: apiUrl + '/getCityByCityName',
              data: {
                cityname: cityName
              },
              success: function (res) {
                if (res.data.status == 1) {
                  that.setData({
                    ["caseParms.bm"]: res.data.data.bm
                  });
                }
              }
            })
          }
        })
      }
    });
    //发单引用
    fadan.fadan.init(this, 2, {
      cityInput: true,
      addressInput: false,
      phoneInput: true,
      nameInput: true,
      areaInput: false,
      btnText: "马上获取"
    });
  },
  getXgtData: function (parms, id, screen, down,once){
    wx.showLoading({
      title: '数据加载中',
    });
    var that=this;
    wx.request({
      url: apiUrl + "/index/meitu", 
      data: parms,
      header: {
        'content-type': 'application/json' // 默认值
      },
      success: function (res) {
        wx.hideLoading();
        if (res.data.status == 1&&once) {
          res.data.data.wz.unshift({ "id": 0, "name": "不限", "isActive": true });
          res.data.data.fg.unshift({ "id": 0, "name": "不限", "isActive": true });
          res.data.data.ys.unshift({ "id": 0, "name": "不限", "isActive": true });
          for (let i = 1; i < res.data.data.wz.length; i++) {
            res.data.data.wz[i].isActive = false
          }
          for (let j = 1; j < res.data.data.fg.length; j++) {
            res.data.data.fg[j].isActive = false
          }
          for (let k = 1; k < res.data.data.ys.length; k++) {
            res.data.data.ys[k].isActive = false
          }
          that.setData({
            xgtLevel: [res.data.data.wz, res.data.data.fg, res.data.data.ys],
          });
        }
        if(id==""&&screen==""&&down==""){
          if(res.data.status==1){
            that.setData({
              reasultXgtData: res.data.data.meitu
            });
          }else{
            that.setData({
              noXgt: true
            })
          }
        }
        if (id != "" && screen == "" && down == ""){
          if (res.data.status == 1){
            that.setData({
              reasultXgtData: res.data.data.meitu,
              xgtLevel: [res.data.data.wz, res.data.data.fg, res.data.data.ys],
            });
            let activeName = "xgtControll[1].name";
            for(let l=0; l<that.data.xgtLevel[1].length;l++){
              let item = "xgtLevel[1][" + l +"].isActive";
              if(that.data.xgtLevel[1][l].id==id){
                that.setData({
                  [item]:true,
                  [activeName]: that.data.xgtLevel[1][l].name
                })
              }else{
                that.setData({
                  [item]: false
                })
              }
            }
          }else{
            that.setData({
              noXgt: true
            })
          }
        }
        if(id==""&&screen=="screen"&&down==""){
          if(res.data.status==1){
            that.setData({
              reasultXgtData: res.data.data.meitu
            });
          }else{
            that.setData({
              noXgt: true
            })
          }
        }
        if(down!=""){
          if(res.data.status==1){
            let gloabaLength = that.data.reasultXgtData.length;
            let insertDataList = res.data.data.meitu.length;
            for (let k = gloabaLength; k < (gloabaLength + insertDataList); k++) {
              let pushData = "reasultXgtData[" + k + "]";
              that.setData({
                [pushData]: res.data.data.meitu[k % 20]
              });
            }
            if (insertDataList < 20) {
              if (!that.data.xgtParms.screen) {
                that.setData({
                  xgtUnderLine: true
                })
              } 
            } 
          }else{
            that.setData({
              xgtUnderLine: true
            })
          }
        }

      }
    })
  },
  getCaseData: function (parms,id,screen,down,once){
    wx.showLoading({
      title: '数据加载中',
    });
    var that=this;
      wx.request({
        url: apiUrl + '/index/xgt',
        data: parms,
        header: {
          'content-type': 'application/json' // 默认值
        },
        success: function (res) {
          wx.hideLoading();
          if (res.data.status == 1 && once) {
            res.data.data.hx.unshift({ "id": 0, "name": "不限", "isActive": true });
            res.data.data.fg.unshift({ "id": 0, "name": "不限", "isActive": true });
            res.data.data.jg.unshift({ "id": 0, "name": "不限", "isActive": true });
            for (let m = 0; m < res.data.data.cases.length;m++){
              res.data.data.cases[m].pv = app.getPVNum();
            }
            for (let i = 1; i < res.data.data.hx.length; i++) {
              res.data.data.hx[i].isActive = false
            }
            for (let j = 1; j < res.data.data.fg.length; j++) {
              res.data.data.fg[j].isActive = false
            }
            for (let k = 1; k < res.data.data.jg.length; k++) {
              res.data.data.jg[k].isActive = false
            }
            for (let m = 0; m < res.data.data.cases.length; m++) {
              res.data.data.cases[m].pv = app.getPVNum();
            }
            that.setData({
              reasultCaseData: res.data.data.cases,
              caseLevel: [res.data.data.hx, res.data.data.fg, res.data.data.jg],
            });
          }
          if (id == "" && screen == "" && down == "") {
            if (res.data.status == 1) {
              for (let m = 0; m < res.data.data.cases.length; m++) {
                res.data.data.cases[m].pv = app.getPVNum();
              }
              that.setData({
                reasultCaseData: res.data.data.cases
              });
            } else {
              that.setData({
                noCase: true
              })
            }
          }
          if (id != "" && screen == "" && down == "") {
            if (res.data.status == 1) {
              for (let m = 0; m < res.data.data.cases.length; m++) {
                res.data.data.cases[m].pv = app.getPVNum();
              }
              that.setData({
                reasultCaseData: res.data.data.cases,
                caseLevel: [res.data.data.hx, res.data.data.fg, res.data.data.jg],
              });
              let activeName = "caseControll[1].name";
              for (let l = 0; l < that.data.caseLevel[1].length; l++) {
                let item = "caseLevel[1][" + l + "].isActive";
                if (that.data.caseLevel[1][l].id == id) {
                  that.setData({
                    [item]: true,
                    [activeName]: that.data.caseLevel[1][l].name
                  })
                } else {
                  that.setData({
                    [item]: false
                  })
                }
              }
            } else {
              that.setData({
                noCase: true
              })
            }
          }
          if (screen == "screen" && down == "") {
            if (res.data.status == 1) {
              for (let m = 0; m < res.data.data.cases.length; m++) {
                res.data.data.cases[m].pv = app.getPVNum();
              }
              that.setData({
                reasultCaseData: res.data.data.cases
              });
            } else {
              that.setData({
                noCase: true
              })
            }
          }
          if (down != "") {
            if (res.data.status == 1) {
              let gloabaLength = that.data.reasultCaseData.length;
              let insertDataList = res.data.data.cases.length;
              for (let m = 0; m < res.data.data.cases.length; m++) {
                res.data.data.cases[m].pv = app.getPVNum();
              }
              for (let k = gloabaLength; k < (gloabaLength + insertDataList); k++) {
                let pushData = "reasultCaseData[" + k + "]";
                that.setData({
                  [pushData]: res.data.data.cases[k % 20]
                });
              }
              if (insertDataList < 20) {
                if (!that.data.caseParms.screen) {
                  that.setData({
                    caseUnderLine: true
                  })
                }
              }
            } else {
              that.setData({
                caseUnderLine: true
              })
            }
          }
         
        }
      })
  },
  changeType:function(e){
    this.setData({
      tabActive: e.target.dataset.type=="true"
    });
    if (e.target.dataset.type == "false" && this.data.reasultCaseData.length==0){
      this.getCaseData(this.data.caseParms,"","","",true)
    
    }
  },
  choseXgtLevel: function (e) {
    let xgtItem = "xgtControll[" + e.target.dataset.num + "].gb";
    this.setData({
      [xgtItem]: !this.data.xgtControll[e.target.dataset.num].gb,
      xgtXh: e.target.dataset.num
    });
  //关闭其他二级分类
    for (let j = 0; j < this.data.xgtControll.length;j++){
      let xgtItem = "xgtControll[" +j+ "].gb";
      if (j != e.target.dataset.num){
        this.setData({
          [xgtItem]: false,
        });
      }
    }

  },
  choseCaseLevel:function(e){
    let caseItem = "caseControll[" + e.target.dataset.num + "].gb";
    this.setData({
      [caseItem]: !this.data.caseControll[e.target.dataset.num].gb,
      caseXh: e.target.dataset.num
    });
    //关闭其他二级分类
    for (let j = 0; j < this.data.caseControll.length; j++) {
      let caseItem = "caseControll[" + j + "].gb";
      if (j != e.target.dataset.num) {
        this.setData({
          [caseItem]: false,
        });
      }
    }
  },
  screenXgtFun:function(e){//效果图筛选
    let parms=e.target.dataset.type.split(",");
    //修改参数
    let choseType="";
    switch (parms[0]){
      case '0':
        choseType ="wz"
        break;
      case '1':
        choseType = "fg"
        break;
      case '2':
        choseType = 'ys'
    }
    
    this.setData({
      ['xgtParms.'+choseType]:parms[1],
      reasultXgtData:[],
      ["xgtParms.p"]: 1,
      noXgt:false,
      scrollTop:0
      
    });
    for (let m = 0; m < 3; m++) {
      let activeM = "xgtControll[" + m + "].active";
      let activeName = "xgtControll[" + m + "].name";
      if (m == parms[0]) {
        this.setData({
          [activeM]: true,
          [activeName]:parms[2]
        });
      } else {
        this.setData({
          [activeM]: false
        });
      }
    }
    for (let j = 0; j < this.data.xgtControll.length; j++) {
      let xgtItem = "xgtControll[" + j + "].gb";
      let choseItem = "xgtControll[" + j + "].active";
      this.setData({
        [xgtItem]: false,
      });
      if (parms[0]==j){
        this.setData({
          [choseItem]: true,
        });
      }else{
        this.setData({
          [choseItem]: false,
        });
      }
    }
    for (let j = 0; j < this.data.xgtLevel[parms[0]].length; j++){
      let itemActive = "xgtLevel[" + parms[0]+"]["+j+"].isActive";
      if (this.data.xgtLevel[parms[0]][j].id == parms[1]){
         this.setData({
           [itemActive]:true,
         });
      }else{
        this.setData({
          [itemActive]: false
        });
      }
    }
    
    //带参,筛选,不下拉
    this.getXgtData(this.data.xgtParms,"", "screen", "", false);
  },
  screenCaseFun:function(e){//案例筛选
    let parms = e.target.dataset.type.split(",");
    console.log(parms)
    //修改参数
    let choseType = "";
    switch (parms[0]) {
      case '0':
        choseType = "hx"
        break;
      case '1':
        choseType = "fg"
        break;
      case '2':
        choseType = 'jg'
    }

    this.setData({
      ['caseParms.' + choseType]: parms[1],
      reasultCaseData: [],
      noCase: false,
      scrollTop: 0
    });

    for (let m = 0; m < 3; m++) {
      let activeM = "caseControll[" + m + "].active";
      let activeName = "caseControll[" + m + "].name";
      if (m == parms[0]) {
        this.setData({
          [activeM]: true,
          [activeName]: parms[2]
        });
      } else {
        this.setData({
          [activeM]: false
        });
      }
    }
    for (let j = 0; j < this.data.caseControll.length; j++) {
      let xgtItem = "caseControll[" + j + "].gb";
      let choseItem = "caseControll[" + j + "].active";
      this.setData({
        [xgtItem]: false,
      });
      if (parms[0] == j) {
        this.setData({
          [choseItem]: true,
        });
      } else {
        this.setData({
          [choseItem]: false,
        });

    }
    }
   
    for (let j = 0; j < this.data.caseLevel[parms[0]].length; j++) {
      let itemActive = "caseLevel[" + parms[0] + "][" + j + "].isActive";
      if (this.data.caseLevel[parms[0]][j].id == parms[1]) {
        this.setData({
          [itemActive]: true,
        });
      } else {
        this.setData({
          [itemActive]: false
        });
      }
    }
    //带参,筛选,不下拉

    this.getCaseData(this.data.caseParms, parms[1], "screen", "", false);
 
  },
  lowerXgt:function(){
    let page = "xgtParms.p";
    this.setData({
      [page]: this.data.xgtParms.p + 1,
      ["xgtParms.screen"]: false
    }); 
    //不带参,不筛选,下拉
    this.getXgtData(this.data.xgtParms,"","","down",false)
  },
  lowerCase:function(){
    let page = "caseParms.p";
    this.setData({
      [page]: this.data.caseParms.p + 1,
      ["caseParms.screen"]: false
    });
    //不带参,不筛选,下拉
    this.getCaseData(this.data.caseParms, "", "", "down", false)
  },
  onShow: function () {
    this.setData({
      lingNum: app.globalData.personNum
    });
  }, 
  onShareAppMessage: function (ops) {
    if (ops.from === 'button') {
      // 来自页面内转发按钮
      console.log(ops.target)
    }
    return {
      title: '齐装网装修家居',
      path: 'pages/decoration-style/decoration-style',
      success: function (res) { },
      fail: function (res) { }
    }
  },
})