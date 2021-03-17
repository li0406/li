//index.js
//获取应用实例
const app = getApp();
const navActive = require('../../utils/util.js');
const fadan = require('../../utils/fadan.js');
const apiUrl = app.getApiUrl();
const imgUrl = app.getImgUrl();
let levelOneNav = [{ id: 0, text: "收房验房", cate: "shoufang", lc: 89 }, { id: 1, text: "找装修公司", cate: "gongsi", lc: 90 }, { id: 2, text: "设计与预算", cate: "shejiyusuan", lc: 91 }, { id: 3, text: "装修选材", cate: "xuancai", lc: 92 }, { id: 4, text: "拆改", cate: "chagai", lc: 94 }, { id: 5, text: "水电", cate: "shuidian", lc: 95 }, { id: 6, text: "防水", cate: "fangshui", lc: 96 }, { id: 7, text: "泥瓦", cate: "niwa", lc: 97 }, { id: 8, text: "木工", cate: "mugong", lc: 98 }, { id: 9, text: "油漆", cate: "youqi", lc: 99 }, { id: 10, text: "检测验收", cate: "jianche", lc: 102 }, { id: 11, text: "后期配饰", cate: "peishi", lc: 103 }, { id: 12, text: "装修保养", cate: "baoyang", lc: 104 }, { id: 13, text: "家居生活", cate: "jjsh", lc: 141 }];

Page({
  data: {
    userid : "",
    levelOneNav: null,
    levelTwoNav: null,
    currentlevelOneIndex : 0, // 用于控制一级分类选中状态
    currentlevelTwoIndex: 0, // 用于控制二级分类选中状态
    currentPage : 1, //当前页码
    articleData: [],
    hasData : 1, //是否还有数据标识
    categoryNoData : 1, //该分类下是否有数据
    cateType : "", // 一级分类标识
    category : "", // 二级分类标识
    fa: "",
    imgUrl: imgUrl,
    vid : "", //用户当前查看的文章id，用户返回时更新pv和收藏情况
    scrollContainerHeight : "3600rpx",
    isUpdateScrollHeight: false, //当前选项数据不足15条时要更新scrollContainerHeight的值，好让“有底线显示出来”
    isFixed : false, // 用户标签选项卡设置固定定位,false标识不需要，true表示需要
    scrollTop : "",
    scrollLeft : "", // 滚动选项横向定位
    leftView : "", // 定位的id
    lc:''
  },
  onLoad: function (options) {
    let that = this
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
    //发单引用
    fadan.fadan.init(this, 2, {
      cityInput: true,
      addressInput: false,
      phoneInput: true,
      nameInput: true,
      areaInput: false,
      btnText: "马上获取"
    });

    // 设置一级分类并请求数据
    this.setData({
      levelOneNav: levelOneNav
    });

    this.setTab(null,options);

    //this.getContentList();
  },
  // 用于某个文章pv和收藏更新
  onShow : function(){
    let viewArticleId = "";
    try {
      viewArticleId = wx.getStorageSync("viewArticleId");
      if (viewArticleId) {
        this.setData({
          vid: viewArticleId
        });
        
        wx.removeStorage({
          key: 'viewArticleId'
        })

        this.updateArticle();
      } 
    } catch (e) {
      console.log(e);
    }
  },
  //监听页面滚动
  onPageScroll : function(scrollObj){
  },
  scroll : function(e){
    if (e.detail.scrollTop>2){
      this.setData({
        isFixed : true
      });
    }else{
      this.setData({
        isFixed: false
      });
    }
  },
  // 设置当前栏目选中状态，一级类目点击，二级类目点击都分发到这里
  setTab : function(e,options){
    let id = e ? e.currentTarget.dataset.id : options.id, 
      cate = e ? e.currentTarget.dataset.type : options.type,
      category = e ? e.currentTarget.dataset.category : options.category,
      lc = e ? e.currentTarget.dataset.lc : options.lc;
    // 从首页点击进来的时候需要定位到目标选项卡位置，定位是通过id来确定的
    if(options){
      this.setData({
        leftView: category,
        currentlevelOneIndex: id,
      });
    }

    if (parseInt(cate)==1){
      //设置一级栏目选中状态并清除二级栏目的选中状态
      this.setData({
        currentlevelOneIndex: id,
        currentlevelTwoIndex : 0
      });
    }else{
      //设置二级栏目选中状态
      this.setData({
        currentlevelTwoIndex: id
      });
    }
    // 因为前台需要拼接数据，因此点击选项卡之后要清空数据列表，以免其他选项的数据被拼接
    this.setData({
      articleData : [],
      currentPage : 1,
      cateType : cate,
      category: category,
      isUpdateScrollHeight : true,
      hasData : 1,
      scrollContainerHeight: "3600rpx",
      lc:lc
    });
    //处理一级分类
    if (parseInt(cate) == 1 && parseInt(id) == 0) {
      this.setData({
        cateType: "",
      });
    }
    wx.pageScrollTo({
      scrollTop: 0
    })
    this.getContentList(e);
  },
  // 获取二级栏目
  menu: function (e) {
    // 填充二级栏目
    this.setData({
      isHide: true
    });
  },
  cancel: function (e) {
    this.setData({
      isHide: false
    })
  },
  // 获取攻略列表
  getContentList : function(e){
    let id = "",
        cateType = "",
        category = "",
        that = this,
        tempDataSet = [],
        lc = that.data.lc;

    // 从data选项中获取数据，下拉加载时无法获取到事件e
    cateType = that.data.cateType;
    category = that.data.category;
    // 处理非点击加载数据的情况，如初次打开页面，下拉加载数据,此时没有事件参数
    if(e){
      if (e.currentTarget.dataset.id){
        cateType = e.currentTarget.dataset.type;
      }     
    }
    wx.showLoading({
      title : "加载中"
    })
    // ajax获取内容列表
    wx.request({
      url: apiUrl +'/qizuang/liu/cheng?',
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      method: "GET",
      dataType: 'json',
      data : {
        lc:lc,
        p: that.data.currentPage,
        // userid : that.data.userid
      },
      success : function(res){
        // 点击选项卡的时候可能出现只有几条数据的情况，此时要更新scroll-view容器的高度，以免底线提示文字无法显示在可视区域
        that.data.isUpdateScrollHeight ? that.updateScrollHeight(res.data.list.length) : "";
        // 底线提示处理：只有请求的数据量大于0小于15条才能显示有底线
        if (res.data.list.length > 0 && res.data.list.length < 15 ){
          that.setData({
            hasData : 0
          });
        }else{
          that.setData({
            hasData: 1
          });
        }
        // 没有数据提示处理：只有查找的数据以及articleData中都没有数据，才能判定当前分类没有数据
        if (parseInt(res.data.list.length) == 0 && parseInt(that.data.articleData.length) == 0){
          that.setData({
            categoryNoData : 0
          });
        }else{
          that.setData({
            categoryNoData: 1
          });
        }
        tempDataSet = that.data.articleData.concat(res.data.list);
        that.setData({
          articleData: tempDataSet
        });
        wx.hideLoading();
      },
      fail:function(){
        wx.hideLoading();
      }
    })
  },
  // 更新文章查看后返回的pv及收藏状态
  updateArticle : function(){
    let vid = this.data.vid,
        articleList = this.data.articleData,
        pv = "",
        isCollect = 0,
        that = this,
        len = articleList.length;
    wx.request({
      url: apiUrl + '/appletcarousel/details',
      data: {
        userid: that.data.userid,
        id: this.data.vid,
        classtype: '10'
      },
      header: {
        'content-type': 'application/json'
      },
      success : function(res){
        if (res.data.article){
          //pv = res.data.article.pv;
          isCollect = res.data.article.is_collect;
          for(let i=0;i<len;i++){
            if (articleList[i].id == vid){
              //articleList[i].pv = pv;
              articleList[i].pv++;
              articleList[i].is_collect = isCollect;
            }
          }
          that.setData({
            articleData: articleList
          });
        }
      },
      fail : function(){
      }
    })
  },
  // 更新scrollContainerHeight的值
  updateScrollHeight : function(size){
    if(size<15){
      this.setData({
        scrollContainerHeight : size*240
      });
    }
    this.setData({
      isUpdateScrollHeight: false
    });
  },
  // 加载更多数据
  loadMoreData : function(){
    let page = this.data.currentPage;
    // 有数据就请求下一页，无数据就不再请求
    if (this.data.hasData){    
      this.setData({
        currentPage : ++page
      });
      this.getContentList();
    }
  },
  toArticleDetail : function(e){
    let id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../article-detail/index?id='+id
    })
    //将当前查看的文章id缓存起来，页面返回时需要更新该文章的pv以及收藏情况
    wx.setStorage({
      key: 'viewArticleId',
      data: id,
    })
  },
  onShareAppMessage: function (ops) {
    if (ops.from === 'button') {
    }
    return {
      title: '齐装网装修家居',
      path: 'pages/zhuangxiulc/zhuangxiulc',
      success: function (res) { },
      fail: function (res) { }
    }
  }

})
