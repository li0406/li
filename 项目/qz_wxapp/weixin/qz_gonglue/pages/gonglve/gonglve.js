//index.js
//获取应用实例
const app = getApp();
const navActive = require('../../utils/util.js');
const fadan = require('../../utils/fadan.js');
const collect=require('../../utils/collectTool.js');
const apiUrl = app.getApiUrl();
Page({
  data: {
    currentUrl:"",
    navList:"",
    autoPlay:false,
    userid:"",
    underLine:false,
    src:"",
    imgUrls: [{
      imgSrc: '../../img/banner01.jpg',
      href:'../article-center/index'
    }, {
      imgSrc: '../../img/banner02.jpg',
      href: '../article-detail/index?id=' + 67257
    },{
      imgSrc: '../../img/banner03.jpg',
      href: '../article-detail/index?id=' + 60517
    }
    ],
    smallTab: [[
      {
        name: "准备阶段",
        imgSrc: "../../img/zbjd.png",
        href: "../article-center/index?id=1&type=1&category=zhunbei&userid="
      }, {
        name: "施工阶段",
        imgSrc: "../../img/sgjd.png",
        href: "../article-center/index?id=2&type=1&category=shigong&userid="
      }, {
        name: "入住阶段",
        imgSrc: "../../img/rzjd.png",
        href: "../article-center/index?id=3&type=1&category=ruzhu&userid="
      }, {
        name: "装修风格",
        imgSrc: "../../img/zxfg.png",
        href: "../article-center/index?id=4&type=1&category=fg&userid="
      }, {
        name: "局部装修",
        imgSrc: "../../img/jbzx.png",
        href: "../article-center/index?id=5&type=1&category=jubu&userid=" 
      }, {
        name: "建材选购",
        imgSrc: "../../img/jcxg.png",
        href: "../article-center/index?id=6&type=1&category=xcdg&userid=" 
      }, {
        name: "软装选购",
        imgSrc: "../../img/rzxg.png",
        href: "../article-center/index?id=7&type=1&category=xcdg&userid="
      }, {
        name: "家具选购",
        imgSrc: "../../img/jjxg.png",
        href: "../article-center/index?id=8&type=1&category=xcdg&userid=" 
      }, {
        name: "电器选购",
        imgSrc: "../../img/dqxg.png",
        href: "../article-center/index?id=9&type=1&category=xcdg&userid=" 
      }, {
        name: "家居风水",
        imgSrc: "../../img/jjfs.png",
        href: "../article-center/index?id=10&type=1&category=fs&userid=" 
      }
    ], [{
      name: "装修前",
      imgSrc: "../../img/sj.png",
      href: "../gongluelist/gongluelist?id=1",
    }, {
      name: "装修中",
      imgSrc: "../../img/jbzx.png",
      href: "../gongluelist/gongluelist?id=2"
    }, {
      name: "装修后",
      imgSrc: "../../img/zxsm.png",
      href: "../gongluelist/gongluelist?id=3"
    },{
      name: "推荐",
      imgSrc: "../../img/qita.png",
      href:"../gongluelist/gongluelist?id=0"
    }]],
    indicatorDots: true,
    autoplay: true,
    interval: 5000,
    duration: 1000,
    tabChange:[true,false],
    startX:0,
    distanceX:0,
    moveFlag:0,
    outBoxWidth:0,
    hidden:false,
    pagationNum:[],
    sliderIndex:0,
    company_fadan:true,
    fd:"",
    dataList:[],
    pageNum:1,
    scrollHeight:2000,
  },
  onLoad: function (options) {
    let that=this;
    if (options.src) {
        that.setData({
          src: options.src
        });
      } else {
        wx.getStorage({
          key: 'src',
          success: function (res) {
            console.log("ssss")
            that.setData({
              src: res.data
            });
          },
        })
      }
    app.getUserInfo(function (res) {//授权
      wx.setStorage({
        key: 'userId',
        data: res.userId,
      });
    });
    wx.currentUrl = getCurrentPages()[getCurrentPages().length-1].route;//获取当前页面url
    this.setData({
      navList: navActive.activeNav(wx.currentUrl)
    });
    let len = (this.data.smallTab[0].length / 5) > 1 ? (this.data.smallTab[0].length / 5) : 0;
    for (let j = 0; j <len; j++) {
      let pageNum = "pagationNum[" + j + "]";
      if (j == 0) {
        this.setData({
          [pageNum]: true
        });
      } else {
        this.setData({
          [pageNum]: false
        });
      }
    }
    fadan.fadan.init(this, 0, {
      cityInput: true,
      addressInput: true,
      phoneInput: true,
      nameInput: false,
      areaInput: false,
      btnText: "获取推荐（1-4家星级装修公司）"
    });//发单引用
    collect.collect.collectInit(this,"dataList");//收藏引用
    let _this=this;
    setInterval(_this.autoPlayIcon,5000);
    
  },
  //加载数据
  onShow:function(){
    this.getDataList(0)
  },
  getDataList:function(pageNum){
    let that = this;
    app.getUserInfo(function (res) {//授权
      that.setData({
        userid:res.userId
      });
      wx.showLoading({
        title: '数据加载中',
      });
      wx.request({
        url: apiUrl + "/appletgonglue/articleandvideo?userid=" + res.userId + "&page=" + pageNum,
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        success: function (res) {
          let baseData = res.data.data.articleList;
          let insertData = res.data.data.videoList;
          let addHeight = baseData.length*167+insertData.length+229;
          if (res.data.data.articleList.length == 0 && res.data.data.videoList.length==0){
            that.setData({
              underLine: true
            });
          }else{
            for (let n = 0; n < baseData.length; n++) {
              baseData[n].type = 10;
            }
            for (let m = 0; m < insertData.length; m++) {
              insertData[m].type = 11;
            }
            for (let i = 0; i < insertData.length; i++) {
              baseData.splice((2 * i + i + 2), 0, insertData[i]);
            }
            let baseLength = baseData.length;
            let gloabaLength = that.data.dataList.length;
            for (let k = gloabaLength; k < baseLength * (pageNum + 1); k++) {
              let pushItem = "dataList[" + k + "]";
              that.setData({
                [pushItem]: baseData[k % 15]
              });
            }
            that.setData({
              pageNum: that.data.pageNum + 1,
              scrollHeight: that.data.scrollHeight + addHeight
            });
            wx.hideLoading()
          }
        }
      });
    });
  },
  lower: function () {
    this.getDataList(this.data.pageNum);
  },
  changTab: function (e) {
    let num = e.currentTarget.dataset.id;
    this.setData({
      pagationNum: []
    });
    for (let i = 0; i < 2; i++) {
      let tabitem = "tabChange[" + i + "]";
      if (i == num) {
        this.setData({
          [tabitem]: true
        });
        let len = (this.data.smallTab[i].length / 5) > 1 ? this.data.smallTab[i].length / 5 : 0;
        for (let j = 0; j < len; j++) {
          let pageNum = "pagationNum[" + j + "]";
          if (j == 0) {
            this.setData({
              [pageNum]: true
            });
          } else {
            this.setData({
              [pageNum]: false
            });
          }
        }
      } else {
        this.setData({
          [tabitem]: false
        });
      }
    }
  },
  moveSlider: function (e) {
    let moveX = e.touches[0].pageX;
    let distance = moveX - this.data.startX;
    this.setData({
      distanceX: distance
    });
  },
  getStarX: function (e) {
    var starX = e.touches[0].pageX;
    this.setData({
      startX: starX
    });
  },
  getDistance: function (e) {
    let pageNum1 = "pagationNum[" + 0 + "]";
    let pageNum2 = "pagationNum[" + 1 + "]";
    if (Math.abs(this.data.distanceX) > 20) {
      if (this.data.distanceX > 0) {//向右滑
        this.setData({
          moveFlag: 0,
          [pageNum1]: true,
          [pageNum2]: false
        });
      } else {//向左滑
        this.setData({
          moveFlag: -(wx.getSystemInfoSync().windowWidth - 20),
          [pageNum1]: false,
          [pageNum2]: true
        });
      }
    }
  },
  autoPlayIcon() {
    let pageNum1 = "pagationNum[" + 0 + "]";
    let pageNum2 = "pagationNum[" + 1 + "]";
    if (this.data.autoPlay) {
      this.setData({
        moveFlag: -(wx.getSystemInfoSync().windowWidth - 20),
        [pageNum1]: false,
        [pageNum2]: true
      });
      this.setData({
        autoPlay: false
      });
    } else {
      this.setData({
        moveFlag: 0,
        [pageNum1]: true,
        [pageNum2]: false
      });
      this.setData({
        autoPlay: true
      });
    }
  },
  toSearch: function () {
    wx.navigateTo({
      url: '../search/index'
    })
  },
  toArticleList: function (e) {
    let type = e.currentTarget.dataset.title;
    wx.navigateTo({
      url: '../article-list/index?title=' + type
    })
  }
})