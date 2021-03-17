//index.js
//获取应用实例
const app = getApp(); 
const fadan = require('../../utils/fadan.js');
const apiUrl = app.getAppApiUrl();
const imgUrl = app.getImgUrl();
const WxParse = require("../../wxParse/wxParse.js");

function getTime(timestamp) {
  let date = new Date(timestamp * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
  let Y = date.getFullYear() + '-';
  let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
  let D = date.getDate() < 10 ? "0" + date.getDate() :date.getDate();
  return Y + M + D;
}

Page({
  data: {
    userid: "",
    imgUrl: imgUrl,
    articleId: "",
    articleDetail: null,
    recommendArticleData: null,
    fa: "",
    hasApprove: false // 是否点过赞了，默认未点赞
  },
  onLoad: function (options) {
    let that = this;
    this.setData({
      articleId: options.id
    });
    that.getArticleDetail();
    that.getRecommedList();
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
  onShow: function () {

  },
  getArticleDetail: function () {
    let that = this;
    // 获取文章详情
    tt.request({
      url: apiUrl + '/appletcarousel/details',
      data: {
        id: that.data.articleId,
        classtype: '10'
      },
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        res.data.article.addtime = getTime(res.data.article.addtime);
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
        tt.setNavigationBarTitle({
          title: res.data.article.title
        });

      }
    });
  },
  getRecommedList: function () {
    let that = this;
    //获取推荐文章 
    tt.request({
      url: apiUrl + '/appletcarousel/detailsRecommendTT',
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



  onShareAppMessage: function () {

  },
  //跳转
  toArticleDetail: function (e) {
    let id = e.currentTarget.dataset.id;
    tt.navigateTo({
      url: '../article-detail/index?id=' + id
    })
  },
  toSheJi: function () {
    tt.navigateTo({
      url: '../sheji/sheji',
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
