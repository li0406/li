// pages/company_detail/company_detail.js
const app = getApp();
const util = require('../../utils/util.js');
const fadan = require('../../utils/fadan.js');
const apiUrl = app.getApiUrl(); 
const imgUrl = app.getImgUrl();
const zxsApi = app.getZxsApiUrl();
const WxParse = require("../../wxParse/wxParse.js");
const collect = require('../../utils/collectTool.js');

function getLocalTime(nS) {
  Date.prototype.toLocaleString = function() {
    return this.getFullYear() + "-" + (this.getMonth() + 1) + "-" + this.getDate() + "  " + this.getHours() + ":" + this.getMinutes() + ":" + this.getSeconds();
  };
  var d = new Date(nS * 1000);
  var t = d.toLocaleString();
  return t;
}

function reversePeople(array) {
  let newArr = [];
  for (let i = array.length - 1; i >= 0; i--) {
    newArr[newArr.length] = array[i];
  }
  return newArr;
}
var time = 0;
var touchDot = 0; //触摸时的原点
var interval = "";
var flag_hd = true;

Page({
  /**
   * 页面的初始数据
   */
  data: {
    userid: "",
    imgUrl: imgUrl,
    star: [1],
    isHide: [true],
    isMore: [true],
    shouqi: false,
    companyArr: null,
    casesArr: [],
    teamArr: [],
    commentArr: [],
    timeArr: [],
    timeNum: [],
    honorArr: [],
    honor: true,
    cases: true,
    team: true,
    comments: true,
    introduce: true,
    companyData: [],
    scrollContainerWidth: "",
    scrollHonorWidth: "",
    find: false,
    toDesigner: false,
    infoList:[]
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    let that = this;
    fadan.fadan.init(that, 4, {
      cityInput: true,
      addressInput: false,
      phoneInput: true,
      nameInput: true,
      areaInput: false,
      btnText: "马上获取",
    });
    wx.request({
      url: apiUrl + '/company/detail',
      data: {
        id: options.id,
        bm: options.bm,
        classtype: '9',
        userid: options.userid,
        p: '1',
        pagecount: '10',
      },
      header: {
        'content-type': 'application/json'
      },
      success: function(res) {
        let arr = [];
        for (let i = 0; i < res.data.comments.length; i++) {
          that.data.timeNum[i] = getLocalTime(res.data.comments[i].time);
          arr[i] = {
            "timeNum": that.data.timeNum[i]
          }
          res.data.comments[i].timeNum = arr[i].timeNum;
        }
        let cases = (res.data.cases).slice(0, 6);
        let comments = (res.data.comments).slice(0, 3);
        let good = Math.ceil((res.data.detail.good) / 20);
        for (let i = 0; i < good; i++) {
          that.data.star[i] = i;
        };
        that.setData({
          companyArr: res.data.detail,
          star: that.data.star,
          casesArr: cases,
          teamArr: res.data.team,
          commentArr: comments,
          honorArr: res.data.honor,
          scrollDesignerWidth: res.data.team.length * 190,
          scrollHonorWidth: res.data.honor.length * 610,
          yuan: -100
        });
        if (res.data.cases.length < 6) {
          that.setData({
            scrollContainerWidth: res.data.cases.length * 411 + 'rpx',
          })
        } else {
          that.setData({
            scrollContainerWidth: '2484rpx',
          })
        }
        if (res.data.cases.length == 0) {
          that.setData({
            cases: false
          })
        }
        if (res.data.team.length == 0) {
          that.setData({
            team: false
          })
        }
        if (res.data.comments.length == 0) {
          that.setData({
            comments: false
          })
        }
        if (res.data.detail.jianjie.length == 0) {
          that.setData({
            introduce: false
          })
        }
        if (res.data.honor.length == 0) {
          that.setData({
            honor: false
          })
        }

        var str = res.data.detail.jianjie.join("");
        if (str.length < 85) {
          that.setData({
            isMore: false
          })
        };

        var bool = [that.data.cases, that.data.team, that.data.comments, that.data.introduce, that.data.honor];
        var a = 0;
        for (var i = 0; i < bool.length; i++) {
          if (bool[i] || bool[i] && bool[i + 1]) {
            a++;
          }
        }
        if (a == 0 || a == 1) {
          that.setData({
            find: true
          })
        }
        collect.collect.collectDetailInit(that, "companyArr"); //收藏引用
      }
    });

    wx.request({
      url: zxsApi + '/business/worksite/applet_user/live_list',
      data: {
        company_id: options.id,
        media: 1,
        fans: 1,
        page: 1,
        limit: '3'
      },
      header: {
        token: ''
      },
      success(res) {
        if (res.data.error_code == 0 && res.statusCode == 200) {
          let infoList = (res.data.data.info).slice(0, 3)
          infoList.forEach((item, index) => {
            if (item.fans) {
              item.fanLen = item.fans.length
              item.fans = reversePeople((item.fans).slice(0,6))
            }
            let sex = item.sex ? item.sex : '先生'
            item.owner = item.yz_name.substring(0, 1) + sex
          })
          that.setData({
            infoList: infoList
          })
        } else {
          wx.showToast({
            title: res.data.error_msg,
            icon: 'none'
          })
        }
      }

    })
  },
  onShow: function() {
    flag_hd = true; //重新进入页面之后，可以再次执行滑动切换页面代码
    clearInterval(interval); // 清除setInterval
    time = 0;
  },

  onPageScroll: function(e) {
    this.setData({
      find: true
    })
  },
  toSite: function(){
    let id = this.data.companyArr.id;
    wx.navigateTo({
      url: '../allSite/allSite?id=' + id
    });
  },
  look: function(e) {
    this.setData({
      isHide: false,
      isMore: false,
      shouqi: true
    })
  },
  stop: function(e) {
    this.setData({
      isHide: true,
      isMore: true,
      shouqi: false
    })
  },

  toExample: function(e) {
    let id = this.data.companyArr.id;
    let bm = this.options.bm;
    wx.navigateTo({
      url: '../example/example?id=' + id + '&bm=' + bm + '&p=1&pagecount=15'
    });
  },
  toDesignerDetail: function(e) {
    let id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../designer_detail/designer_detail?id=' + id + '&p=1&pagecount=10'
    });
  },
  toComments: function(e) {
    let id = this.data.companyArr.id;
    wx.navigateTo({
      url: '../comments/comments?id=' + id + '&p=1&pagecount=10'
    });
  },
  toDesigner: function(e) {
    let id = this.data.companyArr ? this.data.companyArr.id : '';
    let bm = this.options.bm;
    wx.navigateTo({
      url: '../designer/designer?id=' + id + '&bm=' + bm + '&p=1&pagecount=10'
    });
  },
  toExampleDetail: function(e) {
    let id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../xgt-detail/index?id=' + id + '&type=2&pv=' + app.getPVNum()
    });
  },


  scroll: util.throttle(function(e) {
    let id = this.data.companyArr.id;
    let bm = this.options.bm;
    let that = this;
    let left = e.detail.scrollLeft;
    if (left > 500) {
      wx.navigateTo({
        url: '../designer/designer?id=' + id + '&bm=' + bm + '&p=1&pagecount=10'
      })
    }
  }, 2000),
  onShareAppMessage: function(ops) {
    if (ops.from === 'button') {
      // 来自页面内转发按钮
      console.log(ops.target)
    }
    return {
      title: '齐装网装修家居',
      path: 'pages/company_detail/company_detail',
      success: function(res) {},
      fail: function(res) {}
    }
  }
})