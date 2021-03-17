//index.js
//获取应用实例
const app = getApp();
const utils = require('../../utils/util.js');
const fadan = require('../../utils/fadan.js');
const apiUrl = app.getApiUrl();
const imgUrl = app.getImgUrl();
const WxParse = require("../../wxParse/wxParse.js");
const collect = require('../../utils/collectTool.js');

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
  onLoad: function (options) {
    let that = this;
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
    wx.getStorage({
      key: 'userId',
      success: function(res) {
        that.setData({
          userid: res.data
        })
        that.getArticleDetail();
        that.getRecommedList();
      },
      fail:function(res) {
        app.newSq(that,function(data){
          if(data!==0){
            that.setData({
              userid: data.userId
            })
          }
          that.getArticleDetail();
          that.getRecommedList();
        })
       
      }
    });
    
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
        collect.collect.collectDetailInit(that, "articleDetail");//详情收藏引用
        res.data.article.addtime = utils.matTime(res.data.article.addtime, 'Y-M-D h:m:s');
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
        wx.setNavigationBarTitle({
          title: res.data.article.title
        });
       
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
        let data = (res.data).slice(0, 3);
        that.setData({
          recommendArticleData: data
        });
      }
    });
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
  onShareAppMessage: function () {

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
  },
  onShareAppMessage: function (ops) {
    if (ops.from === 'button') {
      // 来自页面内转发按钮
    }
    return {
      title: '齐装网装修家居',
      path: 'pages/article-detail/index',
      success: function (res) { },
      fail: function (res) { }
    }
  }
})
