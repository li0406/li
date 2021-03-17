//index.js
//获取应用实例
const app = getApp();
const navActive = require('../../utils/util.js');
const fadan = require('../../utils/fadan.js');
const apiUrl = app.getApiUrl();
const imgUrl = app.getImgUrl();


Page({
  data: {
    userid : "",
    imgUrl: imgUrl,
    category : true, //视频还是攻略,true攻略，false视频
    articleData: [], // 搜索出的攻略数据
    videoData: [], // 搜索出的视频数据
    fa : "",
    currentArticlePage : 1, // 当前攻略页码
    currentVideoPage: 1, // 当前视频页码
    size : 15, // 条数，攻略15，视频6，默认15
    type : 10, // 类型，攻略10，视频11
    kyw : "", // 搜索关键词
    oldKyw : "", // 存储上一次搜索词
    articleHasData : 1, // 攻略是否还有数据标识,0表示无数据，1表示有数据
    videoHasData: 1, // 视频是否还有数据标识
    categoryArticleNoData: 1, // 该搜索词是否有数据，默认有数据
    categoryVideoNoData: 1, // 该搜索词是否有数据，默认有数据
    articleContainerHeight: "3600rpx",
    videoContainerHeight: "3168rpx",
    searchSize: 1, // 记录搜索次数,只有在触发搜索按钮时有用
    vaid : "", // 查看文章的id
    vvid : "", //查看视频的id
    showTab : false, // 是否显示攻略/视频选项卡
    articleTips: "我也是有底线的",
    videoTips : "我也是有底线的",
    lingNum: ''
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

  onLoad: function () {
    let userid = "";
    //发单引用
    fadan.fadan.init(this);

    //从缓存里获取userid，获取不到就从服务器获取
    userid = this.getStorage("userId");
    if (userid) {
      this.setData({
        userid: userid
      });
    } else {
      this.getUserid();
    }
  },
  // 更新文章/视频的pv数
  onShow : function(){
    let viewArticleId = "",
        viewVideoId = "";
    this.setData({ lingNum: app.globalData.fadanNum });
    viewArticleId = this.getStorage("viewArticleId");
    viewVideoId = this.getStorage("viewVideoId");
    // 如果点击的是文章，返回后就更新文章的pv
    if (viewArticleId) {
      this.setData({
        vaid: viewArticleId
      });
      this.removeStorage('viewArticleId');
      this.updateArticle();
    }
    // 如果点击的是视频，返回后就更新视频的pv
    if (viewVideoId) {
      this.setData({
        vvid: viewVideoId
      });
      this.removeStorage('viewVideoId');
      this.updateVideo();
    }
  },

  // 视频/攻略选项卡切换事件，点击触发
  switchCategory : function(){
    //视频还是攻略,true攻略，false视频
    let bool = this.data.category;
    this.setData({
      category : !bool
    });
    if (this.data.category){
      this.setData({
        type : 10
      });
    }else{
      this.setData({
        type: 11
      });
    }
  },

  
  // 实时更新输入的关键字
  bindKeyInput: function (e) {
    this.setData({
      kyw: e.detail.value.replace(/(^\s*)|(\s*$)/g,"")
    })
  },

  // 启动搜索
  search : function(){
    // 清空数据，启动搜索就意味着数据都是新的
    this.setData({
      articleData : [],
      videoData : [],
      currentArticlePage : 1,
      currentVideoPage : 1,
      searchSize : 1,
      currentArticlePage: 1, // 当前攻略页码
      currentVideoPage: 1, // 当前视频页码
      articleHasData: 1, // 攻略是否还有数据标识,0表示无数据，1表示有数据
      videoHasData: 1, // 视频是否还有数据标识
      categoryArticleNoData: 1, // 该搜索词是否有数据，默认有数据
      categoryVideoNoData: 1, // 该搜索词是否有数据，默认有数据
      articleContainerHeight: "3600rpx",
      videoContainerHeight: "3168rpx",
      searchSize: 1, // 记录搜索次数,只有在触发搜索按钮时有用
      vaid: "", // 查看文章的id
      vvid: "", //查看视频的id
      showTab: true, // 是否显示攻略/视频选项卡
      articleTips: "我也是有底线的",
      videoTips: "我也是有底线的"
    });

    this.getContentList(10);
  },

  // ajax获取数据
  getContentList : function(kind){
    let that = this,
        tempDataSet = null,
        currentPage = 1;
    currentPage = that.data.category ? that.data.currentArticlePage : that.data.currentVideoPage;
    wx.showLoading({
      title: "加载中"
    })
    // ajax获取内容列表
    wx.request({
      url: apiUrl + '/appletgonglue/homeSearch',
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      method: "GET",
      dataType: 'json',
      data: {
        page: currentPage,
        userid: that.data.userid,
        type: kind ? kind : that.data.type,
        keywords: that.data.kyw
      },
      success: function (res) {
        // 判断是否还有数据及决定使用哪种提示文字，无数据则禁止下拉
        that.hasData(res.data.data.length,kind);

        // 数据拼接，要注意拼接哪个数组
        if(kind){
          if (kind == 10) {
            tempDataSet = that.data.articleData.concat(res.data.data);
            that.setData({
              articleData: tempDataSet
            });
          } else {
            tempDataSet = that.data.videoData.concat(res.data.data);
            that.setData({
              videoData: tempDataSet
            });
          }
        }else{
          if (that.data.type == 10) {
            tempDataSet = that.data.articleData.concat(res.data.data);
            that.setData({
              articleData: tempDataSet
            });
          } else {
            tempDataSet = that.data.videoData.concat(res.data.data);
            that.setData({
              videoData: tempDataSet
            });
          }
        }
        
        wx.hideLoading();
        // 判断是否要启动视频搜索，仅用于点击了搜索按钮的情况
        if(kind && that.data.searchSize<2){
          that.getContentList(11);
          that.setData({
            searchSize : 2
          });
        }
      },
      fail: function () {
        console.log("error!!!!");
        wx.hideLoading();
      }
    })
  },

  // 更新文章查看后返回的pv及收藏状态
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

  // 更新文章查看后返回的pv及收藏状态
  updateVideo: function () {
    let vvid = this.data.vvid,
      videoList = this.data.videoData,
      pv = "",
      isCollect = 0,
      that = this,
      len = videoList.length;
    wx.request({
      url: apiUrl + '/appletgonglue/getVideoDetail',
      data: {
        userid: that.data.userid,
        id: this.data.vvid,
        classtype: '11'
      },
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        if (res.data.data.videoDetail) {
          pv = res.data.data.videoDetail.pv;
          isCollect = res.data.data.videoDetail.is_collect;
          for (let i = 0; i < len; i++) {
            if (videoList[i].id == vvid) {
              videoList[i].pv = pv;
              videoList[i].is_collect = isCollect;
            }
          }
          that.setData({
            videoData: videoList
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
    if(this.data.type==10){
      if (this.data.articleHasData){
        this.setData({
          currentArticlePage: ++currentPage
        });
        this.getContentList();
      }
    }else{
      if (this.data.videoHasData) {
        this.setData({
          currentVideoPage: ++currentPage
        });
        this.getContentList();
      }
    }

  },

  // 判断请求的关键字是否还有数据及是否请求到数据，用于判断下拉时，是否要发送请求，攻略和视频要分开判断
  hasData : function(len,kind){
    let hasData = "",
        size = 15,
        ch = 240;
    kind ? (kind == 10 ? size = 15 : size = 6 ) : (this.data.type == 10 ? size = 15 : size = 6);
    kind ? (kind == 10 ? ch = 240 : ch = 528) : (this.data.type == 10 ? ch = 240 : ch = 528);
    if(!len){
      // 无数据要么显示“底线”，要么显示“End”，要么显示搜索不到数据
      if(kind ? kind == 10 : this.data.type==10){
        // 当前是攻略
        if(this.data.articleData.length){
          this.setData({
            categoryArticleNoData : 1,
            articleHasData : 0
          });
        }else{
          this.setData({
            categoryArticleNoData : 0,
            articleHasData : 1,
            articleContainerHeight: "0"
          });
        }
      }else{
        // 当前是视频
        if (this.data.videoData.length) {
          this.setData({
            categoryVideoNoData : 1,
            videoHasData : 0
          });
        } else {
          this.setData({
            categoryVideoNoData : 0,
            videoHasData : 1,
            videoContainerHeight: ""
          });
        }
      }
    }else{
      // 有数据
      if (kind ? kind == 10 :this.data.type==10){
        if(!this.data.articleData.length && len < size){
          this.setData({
            articleContainerHeight : len * ch + "rpx",
            articleHasData: 0,
            articleTips : "End"
          });
        }
      }else{
        if (!this.data.videoData.length && len < size) {
          this.setData({
            videoContainerHeight : len * ch + "rpx",
            videoHasData : 0,
            videoTips : "End"
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
  toArticleDetail : function(e){
    let id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../article-detail/index?id='+id,
    })
    //将当前查看的文章id缓存起来，页面返回时需要更新该文章的pv
    wx.setStorage({
      key: 'viewArticleId',
      data: id,
    })
  },
  toVideoDetail : function(e){
    let id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../play_detail/play_detail?id=' + id,
    })
    //将当前查看的视频id缓存起来，页面返回时需要更新该文章的pv
    wx.setStorage({
      key: 'viewVideoId',
      data: id,
    })
  },
  toGetScheme : function(){
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
    if(!sn){
      return ;
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
  removeStorage : function(sn){
    if (!sn) {
      return;
    }
    wx.removeStorage({
      key: sn,
      success: function(res) {},
    })
  }

})
