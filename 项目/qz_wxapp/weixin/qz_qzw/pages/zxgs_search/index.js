//index.js
//获取应用实例
const app = getApp();
const navActive = require('../../utils/util.js');
const fadan = require('../../utils/fadan.js');
const apiUrl = app.getApiUrl();
const imgUrl = app.getImgUrl();


Page({
  data: {
    userid: "",
    imgUrl: imgUrl,
    category: true, //视频还是攻略,true攻略，false视频
    articleData: [], // 搜索出的攻略数据
    fa: "",
    currentArticlePage: 1, // 当前页码
    size: 15, // 条数
    type: 10, // 类型
    kyw: "", // 搜索关键词
    oldKyw: "", // 存储上一次搜索词
    articleHasData: 1, // 攻略是否还有数据标识,0表示无数据，1表示有数据
    categoryArticleNoData: 1, // 该搜索词是否有数据，默认有数据
    articleContainerHeight: "3600rpx",
    searchSize: 1, // 记录搜索次数,只有在触发搜索按钮时有用
    vaid: "", // 查看文章的id
    // articleTips: "我也是有底线的",
    lingNum: ''
  },
  onLoad: function () {
    let that = this
    //发单引用
    fadan.fadan.init(this);
    wx.getStorage({
      key: 'userId',
      success: function (res) {
        that.setData({
          userid: res.data
        })
      },
      fail: function () {
        app.newSq(that, function (res) {
          if(res===0){
            return
          }
          that.setData({
            userid: res.userId
          })
        })
      }
    })
    
  },
  // 更新文章/视频的pv数
  onShow: function () {
    let viewArticleId = "";
    this.setData({ lingNum: app.globalData.personNum });
    viewArticleId = this.getStorage("viewArticleId");


    if (viewArticleId) {
      this.setData({
        vaid: viewArticleId
      });
      this.removeStorage('viewArticleId');
      this.updateArticle();
    }
    
  },


  // 实时更新输入的关键字
  bindKeyInput: function (e) {
    this.setData({
      kyw: e.detail.value.replace(/(^\s*)|(\s*$)/g, "")
    })
  },

  // 启动搜索
  search: function () {
    // 清空数据，启动搜索就意味着数据都是新的
    this.setData({
      articleData: [],
      currentArticlePage: 1,
      searchSize: 1,
      currentArticlePage: 1, // 当前页码
      articleHasData: 1, // 是否还有数据标识,0表示无数据，1表示有数据
      categoryArticleNoData: 1, // 该搜索词是否有数据，默认有数据
      articleContainerHeight: "3600rpx",
      searchSize: 1, // 记录搜索次数,只有在触发搜索按钮时有用
      vaid: "", // 查看id
      // articleTips: "我也是有底线的",
    });

    this.getContentList(10);
  },

  // ajax获取数据
  getContentList: function (kind) {
    let that = this,
      tempDataSet = null,
      currentPage = 1;
    wx.showLoading({
      title: "加载中"
    })
    // ajax获取内容列表
    wx.request({
      url: apiUrl + '/qizuang/search?',
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      method: "GET",
      dataType: 'json',
      data: {
        p: currentPage,
        userid: that.data.userid,
        type: 9,
        keywords: that.data.kyw
      },
      success: function (res) {
        // 判断是否还有数据及决定使用哪种提示文字，无数据则禁止下拉
        that.hasData(res.data.data.length, kind);

        // 数据拼接，要注意拼接哪个数组
        if (kind) {
            tempDataSet = that.data.articleData.concat(res.data.data);
            that.setData({
              articleData: tempDataSet
            });
          
        } else {
            tempDataSet = that.data.articleData.concat(res.data.data);
            that.setData({
              articleData: tempDataSet
            });
        }

        wx.hideLoading();
        
      },
      fail: function () {
        console.log("error!!!!");
        wx.hideLoading();
      }
    })
  },

  // 更新后返回的pv及收藏状态
  updateArticle: function () {
    let vaid = this.data.vaid,
      articleList = this.data.articleData,
      pv = "",
      isCollect = 0,
      that = this,
      len = articleList.length;
    wx.request({
      url: apiUrl + '/appletcarousel/details',
      data: {
        userid: that.data.userid,
        id: this.data.vaid,
        classtype: '10'
      },
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        if (res.data.article) {
          pv = res.data.article.pv;
          isCollect = res.data.article.is_collect;
          for (let i = 0; i < len; i++) {
            if (articleList[i].id == vaid) {
              articleList[i].pv = pv;
              articleList[i].is_collect = isCollect;
            }
          }
          that.setData({
            articleData: articleList
          });
        }
      },
      fail: function () {
        console.log("error!!!");
      }
    })
  },


  // 加载更多数据
  loadMoreData: function () {
    let currentPage = 1;
    currentPage = this.data.category ? this.data.currentArticlePage : this.data.currentVideoPage;
    // 有数据就请求下一页，无数据就不再请求
    if (this.data.type == 10) {
      if (this.data.articleHasData) {
        this.setData({
          currentArticlePage: ++currentPage
        });
        this.getContentList();
      }
    } else {
      if (this.data.videoHasData) {
        this.setData({
          currentVideoPage: ++currentPage
        });
        this.getContentList();
      }
    }

  },

  // 判断请求的关键字是否还有数据及是否请求到数据，用于判断下拉时，是否要发送请求
  hasData: function (len, kind) {
    let hasData = "",
      size = 15,
      ch = 270;
    kind ? (kind == 10 ? size = 15 : size = 6) : (this.data.type == 10 ? size = 15 : size = 6);
    kind ? (kind == 10 ? ch = 270 : ch = 528) : (this.data.type == 10 ? ch = 270 : ch = 528);
    if (!len) {
      // 无数据要么显示“底线”，要么显示“End”，要么显示搜索不到数据
      if (kind ? kind == 10 : this.data.type == 10) {
        // 当前是攻略
        if (this.data.articleData.length) {
          this.setData({
            categoryArticleNoData: 1,
            articleHasData: 0
          });
        } else {
          this.setData({
            categoryArticleNoData: 0,
            articleHasData: 1,
            articleContainerHeight: "0"
          });
        }
      } else {
        // 当前是视频
        if (this.data.videoData.length) {
          this.setData({
            categoryVideoNoData: 1,
            videoHasData: 0
          });
        } else {
          this.setData({
            categoryVideoNoData: 0,
            videoHasData: 1,
            videoContainerHeight: ""
          });
        }
      }
    } else {
      // 有数据
      if (kind ? kind == 10 : this.data.type == 10) {
        if (!this.data.articleData.length && len < size) {
          this.setData({
            articleContainerHeight: len * ch + "rpx",
            articleHasData: 0,
            // articleTips: "End"
          });
        }
      } else {
        if (!this.data.videoData.length && len < size) {
          this.setData({
            videoContainerHeight: len * ch + "rpx",
            videoHasData: 0,
            // videoTips: "End"
          });
        }
      }
    }
  },

  //跳转
  back: function () {
    wx.navigateBack({
    });
  },
  toCompanyDetail: function (e) {
    let id = e ? e.currentTarget.dataset.id : options.id;
    let bm = e ? e.currentTarget.dataset.bm : options.bm;
    wx.navigateTo({
      url: '../company_detail/company_detail?id=' + id + '&bm=' + bm + '&classtype=9&userid=' + this.data.userid + '&p=1&pagecount=10'
    });

    wx.setStorage({
      key: 'viewArticleId',
      data: id,
    })
  },

  toGetScheme: function () {
    wx.navigateTo({
      url: '../zhuangxiusj/zhuangxiusj',
    })
  },
  /* 
    * 公共函数：获取指定名称的缓存(同步获取)
    * @params sn 缓存名称
  */
  getStorage: function (sn) {
    let value = "";
    if (!sn) {
      return;
    }
    try {
      value = wx.getStorageSync(sn);
    } catch (e) {
      console.log(e);
    }
    return value;
  },
  /* 
    * 公共函数：移除指定的缓存(异步方式)
    * @params sn 缓存名称
  */
  removeStorage: function (sn) {
    if (!sn) {
      return;
    }
    wx.removeStorage({
      key: sn,
      success: function (res) { },
    })
  },
  onShareAppMessage: function (ops) {
    if (ops.from === 'button') {
      // 来自页面内转发按钮
      console.log(ops.target)
    }
    return {
      title: '齐装网装修家居',
      path: 'pages/zxgs_search/zxgs_search',
      success: function (res) { },
      fail: function (res) { }
    }
  },

})
