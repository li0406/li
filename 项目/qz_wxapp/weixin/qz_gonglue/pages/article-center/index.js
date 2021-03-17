//index.js
//获取应用实例
const app = getApp();
const navActive = require('../../utils/util.js');
const fadan = require('../../utils/fadan.js');
const apiUrl = app.getApiUrl();
const imgUrl = app.getImgUrl();
let levelOneNav = [{ id: 0, text: "热门", cate: "" }, { id: 1, text: "准备阶段", cate: "zhunbei" }, { id: 2, text: "施工阶段", cate: "shigong" }, { id: 3, text: "入住阶段", cate: "ruzhu" }, { id: 4, text: "装修风格", cate: "fg" }, { id: 5, text: "局部装修", cate: "jubu" }, { id: 6, text: "建材选购", cate: "xcdg" }, { id: 7, text: "软装选购", cate: "xcdg" }, { id: 8, text: "家具选购", cate: "xcdg" }, { id: 9, text: "电器选购", cate: "xcdg" }, { id: 10, text: "家居风水", cate: "fs" }];

let levelTwoNav = {
  0: [{ id: 1, text: "收房验房", cate: "shoufang" }, { id: 2, text: "找装修公司", cate: "gongsi" }, { id: 3, text: "设计与预算", cate: "shejiyusuan" }, { id: 4, text: "装修选材", cate: "xuancai" }],
  1: [{ id: 1, text: "拆改", cate: "chagai" }, { id: 2, text: "水电", cate: "shuidian" }, { id: 3, text: "防水", cate: "fangshui" }, { id: 4, text: "泥瓦", cate: "niwa" }, { id: 5, text: "木工", cate: "mugong" }, { id: 6, text: "油漆", cate: "youqi" }],
  2: [{ id: 1, text: "检测验收", cate: "jianche" }, { id: 2, text: "后期配饰", cate: "peishi" }, { id: 3, text: "装修保养", cate: "baoyang" }, { id: 4, text: "家居生活", cate: "jjsh" }],
  3: [{ id: 1, text: "地中海风格", cate: "dzh" }, { id: 2, text: "田园风格", cate: "ty" }, { id: 3, text: "欧式风格", cate: "os" }, { id: 4, text: "现代简约风格", cate: "xdjy" }, { id: 5, text: "中式风格", cate: "zhsh" }, { id: 6, text: "东南亚风格", cate: "dnyfg" }, { id: 7, text: "美式风格", cate: "ms" }, { id: 8, text: "混沌风格", cate: "hd" }, { id: 9, text: "日韩风格", cate: "rh" }],
  4: [{ id: 1, text: "客厅装修", cate: "keting" }, { id: 2, text: "卧室装修", cate: "woshi" }, { id: 3, text: "厨房装修", cate: "chufang" }, { id: 4, text: "卫浴装修", cate: "weiyu" }, { id: 5, text: "阳台装修", cate: "yangtai" }, { id: 6, text: "餐厅装修", cate: "canting" }, { id: 7, text: "书房装修", cate: "shufang" }, { id: 8, text: "儿童房装修", cate: "ertongfang" }, { id: 9, text: "空间利用装修", cate: "kongjian" }, { id: 10, text: "色调搭配", cate: "sediao" }, { id: 11, text: "精品样板", cate: "yangban" }],
  5: [{ id: 1, text: "地板", cate: "diban" }, { id: 2, text: "油漆涂料", cate: "youqituliao" }, { id: 3, text: "灯具", cate: "dengju" }, { id: 4, text: "门窗", cate: "menchuang" }, { id: 5, text: "瓷砖", cate: "cizhuan" }, { id: 6, text: "橱柜", cate: "chugui" }, { id: 7, text: "卫浴", cate: "weiyushebei" }, { id: 8, text: "吊顶", cate: "diaoding" }, { id: 9, text: "五金", cate: "wujin" }, { id: 10, text: "暖气设备", cate: "nuanqi" }],
  6: [{ id: 1, text: "床上用品", cate: "csyp" }, { id: 2, text: "装饰画", cate: "zhuangshihua" }, { id: 3, text: "植物", cate: "zhiwu" }, { id: 4, text: "十字绣", cate: "shizixiu" }, { id: 5, text: "地毯", cate: "ditan" }, { id: 6, text: "窗帘", cate: "chuanglian" }, { id: 7, text: "墙纸", cate: "qiangzhi" }],
  7: [{ id: 1, text: "家具", cate: "jiaju" }, { id: 2, text: "儿童家具", cate: "ertongjiaju" }],
  8: [{ id: 1, text: "办公设备", cate: "bangong" }, { id: 2, text: "家电电器", cate: "jydq" }, { id: 3, text: "厨卫电器", cate: "cwdq" }, { id: 4, text: "数码产品", cate: "shuma" }],
  9: [{ id: 1, text: "客厅风水", cate: "ktfs" }, { id: 2, text: "卧室风水", cate: "wsfs" }, { id: 3, text: "厨房风水", cate: "cffs" }, { id: 4, text: "卫生间风水", cate: "wsjfs" }, { id: 5, text: "办公室风水", cate: "bgsfs" }, { id: 6, text: "财运风水", cate: "cyfs" }, { id: 7, text: "其他风水", cate: "qtfs" }]
};


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
    leftView : "" // 定位的id
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
    console.log(options);
    let userid = "";
    //从缓存里获取userid，获取不到就从服务器获取
    try{
      userid = wx.getStorageSync("userId");
      if(userid){
        this.setData({
          userid : userid
        });
      }else{
        this.getUserid();
      }
    }catch(e){
      this.getUserid();
    }
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
    console.log(e.detail.scrollLeft);
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
      category = e ? e.currentTarget.dataset.category : options.category;
    
    // 从首页点击进来的时候需要定位到目标选项卡位置，定位是通过id来确定的
    if(options){
      this.setData({
        leftView: category,
        //scrollLeft:id*60
      });
    }

    if (parseInt(cate)==1){
      //设置一级栏目选中状态并清除二级栏目的选中状态
      this.setData({
        currentlevelOneIndex: id,
        currentlevelTwoIndex : 0
      });
      e ? this.getLevelTwoNav(e) : this.getLevelTwoNav(options);
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
      scrollContainerHeight: "3600rpx"
    });
    //处理一级分类，“热门”选项卡的情况
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
  getLevelTwoNav : function(e){
    let id = e.id ? e.id : e.currentTarget.dataset.id,
        levelTwoNavs = null;
    if(id>0){
      levelTwoNavs = levelTwoNav[parseInt(id)-1];
    }
    // 填充二级栏目
    this.setData({
      levelTwoNav: levelTwoNavs
    });
  },
  // 获取攻略列表
  getContentList : function(e){
    let id = "",
        cateType = "",
        category = "",
        that = this,
        tempDataSet = [];
    // 从data选项中获取数据，下拉加载时无法获取到事件e
    cateType = that.data.cateType;
    category = that.data.category;
    // 处理非点击加载数据的情况，如初次打开页面，下拉加载数据,此时没有事件参数
    if(e){
      if (e.currentTarget.dataset.id){
        cateType = e.currentTarget.dataset.type;
      }
      category = e.currentTarget.dataset.category;
    }
    wx.showLoading({
      title : "加载中"
    })
    // ajax获取内容列表
    wx.request({
      url: apiUrl +'/appletgonglue/getGonglueList',
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      method: "GET",
      dataType: 'json',
      data : {
        type: category=="shizixiu"? "1":cateType, // type等于2的情况下十字绣无数据，1的情况下就有
        category: category,
        page : that.data.currentPage,
        userid : that.data.userid
      },
      success : function(res){
        // 点击选项卡的时候可能出现只有几条数据的情况，此时要更新scroll-view容器的高度，以免底线提示文字无法显示在可视区域
        that.data.isUpdateScrollHeight ? that.updateScrollHeight(res.data.data.length) : "";
        // 底线提示处理：只有请求的数据量大于0小于15条才能显示有底线
        if (res.data.data.length > 0 && res.data.data.length < 15 ){
          that.setData({
            hasData : 0
          });
        }else{
          that.setData({
            hasData: 1
          });
        }
        // 没有数据提示处理：只有查找的数据以及articleData中都没有数据，才能判定当前分类没有数据
        if (parseInt(res.data.data.length) == 0 && parseInt(that.data.articleData.length) == 0){
          that.setData({
            categoryNoData : 0
          });
        }else{
          that.setData({
            categoryNoData: 1
          });
        }
        tempDataSet = that.data.articleData.concat(res.data.data);
        that.setData({
          articleData: tempDataSet
        });
        wx.hideLoading();
      },
      fail:function(){
        console.log("error!!!!");
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
        console.log("error!!!");
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
  //跳转
  toSearch: function () {
    wx.navigateTo({
      url: '../search/index'
    })
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
  toGetScheme: function () {
    wx.navigateTo({
      url: '../zhuangxiusj/zhuangxiusj',
    })
  },
  // 收藏和取消收藏,0标识收藏，1表示取消收藏
  collectAction : function(e){
    let id = e.currentTarget.dataset.id,
        index = e.currentTarget.dataset.index,
        articleData = this.data.articleData;
    articleData[index].is_collect = 1;

    this.data.userid ? "" : this.getUserid();

    // 发送ajax请求添加收藏
    this.sendCollectToServer(id,0);

    // 更改收藏状态
    this.setData({
      articleData: articleData
    });
    
  },
  cancelCollectAction : function(e){
    let id = e.currentTarget.dataset.id,
        index = e.currentTarget.dataset.index,
        articleData = this.data.articleData;
    articleData[index].is_collect = 0;
    // 发送ajax请求取消收藏
    this.sendCollectToServer(id, 1);
    // 更改收藏状态
    this.setData({
      articleData: articleData
    });
  },
  sendCollectToServer : function(id,flag){
    let ajaxMethod = "POST",
        collectMsg = "收藏成功";
    if(flag){
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
          title : "网络异常，请稍后重试"
        });
      }
    })
  }
})
