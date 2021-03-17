//index.js
//获取应用实例
const app = getApp();
const navActive = require('../../utils/util.js');
const fadan = require('../../utils/fadan.js');
const apiUrl = app.getApiUrl();
const imgUrl = app.getImgUrl();
const WxParse = require("../../wxParse/wxParse.js");
const collect = require('../../utils/collectTool.js');

function getLocalTime(nS) {
  return new Date(parseInt(nS) * 1000).toLocaleString().replace(/年|月/g, "-").replace(/日/g, " ").replace('/', '-').replace('/', '-').split(' ')[0];
}

Page({
  data: {
    userid : "",
    imgUrl: imgUrl,
    articleId : "",
    articleDetail : null,
    recommendArticleData: null,
    fa: "",
    hasApprove : false // 是否点过赞了，默认未点赞
  },
  // 获取userid
  getUserid: function () {
    let that = this;
    app.getUserInfo(function (res) {//授权
      wx.setStorage({
        key: 'userId',
        data: res.userId,
      });
      that.setData({
        userid: res.userId
      });
    });
  },
  onLoad: function (options) {
    
    let userid = "";
    //从缓存里获取userid，获取不到就从服务器获取
    try {
      userid = wx.getStorageSync("userId");
      if (userid) {
        this.setData({
          userid: userid
        });
      } else {
        this.getUserid();
      }
    } catch (e) {
      this.getUserid();
    }

    this.setData({
      articleId: options.id
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

    this.getArticleDetail();
    this.getRecommedList();
  },
  onShow : function(){
    // 判断这篇文章是否点过赞
    if (this.hasApprove()){
      this.setData({
        hasApprove : true
      });
    }
  },
  getArticleDetail : function(){
    let that = this;
    // 获取文章详情
    wx.request({
      url: apiUrl + '/appletcarousel/details',
      data: {
        userid: that.data.userid,
        id: that.data.articleId,
        classtype: '10'
      },
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        res.data.article.addtime = getLocalTime(res.data.article.addtime);
        let contents = res.data.article.content,
          a1 = '<a href="http://www.qizuang.com/zxbj/?src=gl-bj" rel="nofollow" target="_blank">&gt;&gt; 点此获取专业设计师免费量房设计</a>',
          a3 = '<a href="http://www.qizuang.com/zhaobiao/" rel= "nofollow" target= "_blank" >&gt;&gt; 点此获取专业设计师免费量房设计 < /a>',
          a4 = '<a href="http://www.qizuang.com/zxbj/?src=gl-bj" rel= "nofollow" target= "_blank" >&gt;&gt; 点此获取专业设计师免费量房设计 < /a>',
          a5 = '<a href="http://www.qizuang.com/zhaobiao/" rel="nofollow" target="_blank">&gt;&gt; 点此获取专业设计师免费量房设计</a>',
          a2 = '<a href="http://www.qizuang.com/zhaobiao/" rel="nofollow" target="_blank" style="text-decoration: underline; font-size: 14px; color: rgb(192, 0, 0);"><span style="font-size: 14px; color: rgb(192, 0, 0);">&gt;&gt; 点此获取专业设计师免费量房设计</span></a>',
          a6 = '<ahref= rel="nofollow" target="_blank" style="text-decoration:underline;font-size:14px;color:rgb(192,0,0);"><spanstyle="font-size:14px;color:rgb(192,0,0);">&gt;&gt;点此获取专业设计师免费量房设计</spanstyle="font-size:14px;color:rgb(192,0,0);"></ahref=>';
        if (contents.indexOf(a1) > 0) {
          contents = contents.split(a1)[0];
        } else if (contents.indexOf(a2) > 0) {
          contents = contents.split(a2)[0];
        } else if (contents.indexOf(a3) > 0) {
          contents = contents.split(a3)[0];
        } else if (contents.indexOf(a4) > 0) {
          contents = contents.split(a4)[0];
        } else if (contents.indexOf(a5) > 0) {
          contents = contents.split(a5)[0];
        } else if (contents.indexOf(a6) > 0) {
          contents = contents.split(a6)[0];
        }
        if (contents.indexOf("&gt;&gt; 点此获取专业设计师免费量房设计") > 0) {
          contents = contents.replace("&gt;&gt; 点此获取专业设计师免费量房设计", "");
        }
        if (contents.indexOf("&gt;&gt; 点此免费获得最新装修报价") > 0) {
          contents = contents.replace("&gt;&gt; 点此免费获得最新装修报价", "");
        }
        WxParse.wxParse('article_content', 'html', contents, that, 5)

        that.setData({
          articleDetail: res.data.article,
        });
        console.log(that.data.articleDetail);
        wx.setNavigationBarTitle({
          title: res.data.article.title
        });
        collect.collect.collectDetailInit(that, "articleDetail");//详情收藏引用
      }
    });
  },
  getRecommedList : function(){
    let that = this;
    //获取推荐文章 
    wx.request({
      url: apiUrl + '/appletcarousel/detailsRecommend',
      data: {
        id: this.data.articleId,
        order: 'realview',
        count: '5'
      },
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        console.log(res);
        that.setData({
          recommendArticleData: res.data
        });
      }
    });
  },
  // 收藏和取消收藏
  collectAction : function(e){
    let id = e.currentTarget.dataset.id,
        articleData = this.data.articleDetail;
    articleData.is_collect = 1;
    this.setData({
      articleDetail: articleData
    });
    // 发送ajax请求添加收藏
    this.sendCollectToServer(id, 0);

    // 提示收藏成功
    wx.showToast({
      title: '收藏成功',
      icon: 'success',
      duration: 1000
    })
  },
  cancelCollectAction : function(e){
    let id = e.currentTarget.dataset.id,
        articleData = this.data.articleDetail;
    articleData.is_collect = 0;
    this.setData({
      articleDetail: articleData
    });
    // 发送ajax请求取消收藏
    this.sendCollectToServer(id, 1);

    // 提示取消收藏
    wx.showToast({
      title: '你已取消收藏',
      icon: 'success',
      duration: 1000
    })
  },
  sendCollectToServer: function (id, flag) {
    let ajaxMethod = "POST",
      collectMsg = "收藏成功";
    if (flag) {
      ajaxMethod = "GET";
      collectMsg = "你已取消收藏";
    }
    // 收藏取消收藏发送到服务器
    wx.request({
      url: apiUrl + '/appletcarousel/editcollect',
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      method: ajaxMethod,
      data: {
        userid: this.data.userid,
        classtype: 10,
        classid: id
      },
      success: function (res) {
        wx.showToast({
          title: collectMsg,
          icon: 'success',
          duration: 1500
        })
      },
      fail: function () {
        wx.alert({
          title: "网络异常，请稍后重试"
        });
      }
    })
  },
  // 点赞
  approveAction : function(e){
    let id = e.currentTarget.dataset.id,
        articleData = this.data.articleDetail;
    // 先判断当前文章是否点赞过（点赞过的存放在缓存中），微信给每个小程序分配了10M的缓存空间
    // 不同小程序，不同用户缓存不通用，可以设置一个点赞数组
    if (this.hasApprove()){
      wx.showModal({
        title: '您已点过赞了',
        showCancel : false
      });
      this.setData({
        hasApprove: true
      })
    }else{
      // 发送ajax请求，将点赞同步到服务器
      this.sendApproveToServer();

      // 提示点赞成功
      wx.showModal({
        title: '点赞成功',
        showCancel: false
      })
    }
  },
  // 判断是否点过赞了，默认未点赞
  hasApprove : function(){
    let bool = false;
    let articleId = this.data.articleId;
    let approveArray = app.getNewStorage('articleApproves');
    if (approveArray){
      for (let i = 0; i < approveArray.length; i++) {
        if (approveArray[i] == articleId) {
          bool = true;
          break;
        }
      }
    }
    return bool;
    
  },
  sendApproveToServer : function(){
    let that = this,
        approveArray = app.getNewStorage('articleApproves') || [];
    console.log(approveArray);
    wx.request({
      url: apiUrl + '/appletcarousel/like',
      data: {
        id: that.data.articleId
      },
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        if (res.data.state === 1) {
          let articleDetail = that.data.articleDetail;
          articleDetail.likes = parseInt(articleDetail.likes) + 1;

          that.setData({
            articleDetail: articleDetail,
            hasApprove: true
          });
          approveArray.push(articleDetail.id);
          app.setNewStorage('articleApproves', approveArray);
        }
      }
    });
  },
  //跳转
  toSearch: function() {
    wx.navigateTo({
      url: '../search/index'
    })
  },
  toArticleDetail: function (e) {
    let id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../article-detail/index?id=' + id
    })
  },
  toGetScheme: function () {
    wx.navigateTo({
      url: '../zhuangxiusj/zhuangxiusj',
    })
  }
})
