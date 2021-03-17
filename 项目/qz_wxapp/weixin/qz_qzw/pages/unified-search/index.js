const app = getApp();
const apiUrl = app.getApiUrl();
const fadan = require('../../utils/fadan.js');
Page({
  data: {
    userid: '',
    hasTab:false,
    keyword:"",
    imgUrl: app.getImgUrl(),
    lingNum:0,
    scrollVideoHeight: '1455rpx',
    scrollHeight:"",
    tabBox:[
      {
        title:"效果图",
        isActive:true,
        hasData:true,
        resultData:[],
        dataType:8,
        sort: [{ sort: "hot", isSelect: true }, { sort: "time", isSelect: false }],
        p:1,
        toRequest: false,
        singleHeight:350,
        height: "1455rpx",
        advHeight:223,
        num:2,
        advNum:6,
        underLine:false,
        lock: false
      }, {
        title: "攻略",
        isActive: false,
        hasData: true,
        resultData: [],
        dataType:10,
        sort: [{ sort: "time", isSelect: true },{ sort: "hot", isSelect: false }],
        p:1,
        toRequest: true,
        singleHeight:200,
        height: "1455rpx",
        advHeight:223,
        num:1,
        advNum: 5,
        underLine: false,
        lock: false
      }, {
        title: "装修公司",
        isActive: false,
        hasData: true,
        resultData: [],
        dataType:9,
        sort: [{ sort: "hot", isSelect: true }, { sort: "time", isSelect: false }],
        p:1,
        toRequest: true,
        singleHeight:248,
        height: "1455rpx",
        advHeight:0,
        underLine: false,
        num:1,
        lock: false
      }, {
        title: "视频",
        isActive: false,
        hasData: true,
        dataType:11,
        resultData: [],
        sort: [{ sort: "time", isSelect: true }, { sort: "hot", isSelect: false }],
        p:1,
        toRequest: true,
        singleHeight:500,
        height: "1455rpx",
        advHeight:223,
        underLine: false,
        advNum: 2,
        num:1,
        lock: false
      }
    ],
  },
  onLoad: function (options) {
    let that=this;
    wx.getSystemInfo({
      success:function(e){
        that.setData({
          scrollHeight: e.windowHeight-100
        });
      }
    });
    wx.getStorage({
      key: 'userId',
      success: function (res) {
        that.setData({
          userid: res.data
        })
      },
      fail: function () {
        app.newSq(that, function (res) {
          if(res==0){
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
  },
  getSearchData:function(parms,index,sort){
    
    let that=this;
    wx.showLoading({
      title: "加载中"
    });
      wx.request({
        url: apiUrl + '/qizuang/search',
        data: parms,
        dataType: "json",
        success:function(res){
          let dataArray = "tabBox[" + index + "].resultData";
          let noData = "tabBox[" + index + "].hasData";
             //只在搜索情况下
          if (sort =="search"){
            that.setData({
              [dataArray]: []
            });
            if(res.data.status==1){
              if (index == 3) {
                for (let m = 0; m < res.data.data.length; m++) {
                  res.data.data[m].time = app.changeTime(res.data.data[m].time);
                }
              }
              that.setData({
                [dataArray]: res.data.data,
                [noData]: true
              });
              if (res.data.data.length < 15) {
                that.setData({
                  ["tabBox[" + index + "].lock"]: true,
                  ["tabBox[" + index + "].underLine"]: true
                });
              }
              
            }else{
              that.setData({
                [noData]: false
              });
            }

            
          }
          if (sort=="once"||sort=="sort"){ //筛选或者重新排序
            that.setData({
              [dataArray]:[]
            });
            if (res.data.status == 1){
              if (index == 3) {
                for (let m = 0; m < res.data.data.length; m++) {
                  res.data.data[m].time = app.changeTime(res.data.data[m].time);
                }
              }
              that.setData({
                [dataArray]: res.data.data,
                [noData]: true
              });
              if (res.data.data.length<15){
                that.setData({
                  ["tabBox[" + index + "].lock"]: true,
                  ["tabBox[" + index + "].underLine"]: true
                });
              }
            }else{
              that.setData({
                [noData]: false
              });
            }
          } else if (sort=="down"){//下拉加载情况
            if(that.data.tabBox[index].lock){
              that.setData({
                ["tabBox[" + index + "].underLine"]: true
              });
              return false
            }
            if(res.data.status==1){
              let gloabaLength = that.data.tabBox[index].resultData.length;
              let newLength = res.data.data.length;
              if (index == 3) {
                for (let m = 0; m < res.data.data.length; m++) {
                  res.data.data[m].time = app.changeTime(res.data.data[m].time);
                }
              }
              for (let n = gloabaLength; n < gloabaLength + newLength; n++) {
                let pushData = "tabBox[" + index + "].resultData[" + n + "]";
                that.setData({
                  [pushData]: res.data.data[n % 15]
                });
              }
           
            }else{
              that.setData({
                ["tabBox[" + index + "].lock"]: true,
                ["tabBox[" + index + "].underLine"]:true
              });
            }
          }
          wx.hideLoading();
        }
    
      })
   
  },
  searchWord:function(e){
    this.setData({
      keyword:e.detail.value
    });
    let that = this;
    let searchData = {
      "type": that.data.tabBox[0].dataType,
      "sort": that.data.tabBox[0].sort[0].sort,
      "keywords": that.data.keyword,
      "p":1
    }
    that.setData({
      ["tabBox[0].p"]:1,
      ["tabBox[0].hasData"]:true
    });
    for (let j = 0; j < this.data.tabBox.length; j++){
      let resetRequest = "tabBox[" + j + "].toRequest";
      let dataArray = "tabBox[" + j + "].isActive";
      if(j==0){
        that.setData({
          [dataArray]: true,
          [resetRequest]:false
        });
      }else{
        that.setData({
          [dataArray]: false,
          [resetRequest]: true,
          hasTab:true
        });
      }
    }
    that.getSearchData(searchData, 0, "search")
  },
  changeType:function(e){
    let Type=e.target.dataset.type;
    let that=this;
    for (let j = 0; j < this.data.tabBox.length;j++){
      let dataArray = "tabBox[" + j + "].isActive";
      let resetRequest = "tabBox[" + j + "].toRequest";
      if (this.data.tabBox[j].dataType==Type){
        if (this.data.tabBox[j].toRequest){
          let searchData = {
            "type": Type,
            "sort": that.data.tabBox[j].sort[0].sort,
            "keywords": that.data.keyword,
            "p": 1
          }
          that.setData({
            ["tabBox["+j+"].hasData"]:true
          })
          that.getSearchData(searchData, j,"once");
        }
        that.setData({
          [dataArray]:true,
          [resetRequest]:false
        });
      }else{
        that.setData({
          [dataArray]: false
        });
      }
    }
  },
  changeSort:function(e){
    let sortArray = e.target.dataset.sort.split("-");
    let changeItem = "tabBox[" + sortArray[0] +"].sort[0].isSelect";
    let changeItem2 = "tabBox[" + sortArray[0] + "].sort[1].isSelect";
    if (sortArray[1] == this.data.tabBox[sortArray[0]].sort[0].sort){
      this.setData({
        [changeItem]: true,
        [changeItem2]:false
      });
    }else{
      this.setData({
        [changeItem]: false,
        [changeItem2]: true
      });
    }
    let parms={
      "type": this.data.tabBox[sortArray[0]].dataType,
      "sort": sortArray[1],
      "keywords": this.data.keyword,
      "p": 1
    }
    this.getSearchData(parms, sortArray[0], "sort")
  },
  lowerXgt:function(e){//下拉加载效果图
    if(!this.data.tabBox[0].underLine){
      this.setData({
        ["tabBox[0].p"]: this.data.tabBox[0].p + 1,
      });
      let parms = {
        "p": this.data.tabBox[0].p,
        "sort": this.data.tabBox[0].sort[0].isSelect ? "hot" : "time",
        "type": this.data.tabBox[0].dataType,
        "keywords": this.data.keyword
      }
      this.getSearchData(parms, 0, "down")
    }
    
  },
  lowerGonglue: function (e) {//下拉加载效果图
    if (!this.data.tabBox[1].underLine) {
      this.setData({
        ["tabBox[1].p"]: this.data.tabBox[1].p + 1,
      });
      let parms = {
        "p": this.data.tabBox[1].p,
        "sort": this.data.tabBox[1].sort[0].isSelect ? "hot" : "time",
        "type": this.data.tabBox[1].dataType,
        "keywords": this.data.keyword
      }
      this.getSearchData(parms, 1, "down")
    }
    
  },
  lowerCompany:function(e){//下拉加载公司
    if (!this.data.tabBox[2].underLine) {
      this.setData({
        ["tabBox[2].p"]: this.data.tabBox[2].p + 1,
      });
      let parms = {
        "p": this.data.tabBox[2].p,
        "sort": this.data.tabBox[2].sort[0].isSelect ? "hot" : "time",
        "type": this.data.tabBox[2].dataType,
        "keywords": this.data.keyword
      }
      this.getSearchData(parms, 2,"down")
    }
  },
  lowerVideo:function(e){//下拉加载视频
    if (!this.data.tabBox[3].underLine) {
      this.setData({
        ["tabBox[3].p"]: this.data.tabBox[3].p + 1,
      });
      let parms = {
        "p": this.data.tabBox[3].p,
        "sort": this.data.tabBox[3].sort[0].isSelect ? "hot" : "time",
        "type": this.data.tabBox[3].dataType,
        "keywords": this.data.keyword
      }
      this.getSearchData(parms, 3, "down")
    }
  },
  toCompanyDetail:function(e){
    let id = e ? e.currentTarget.dataset.id : options.id;
    let bm = e ? e.currentTarget.dataset.bm : options.bm;
    wx.navigateTo({
      url: '../company_detail/company_detail?id=' + id + '&bm=' + bm + '&classtype=9&userid=' + this.data.userid + '&p=1&pagecount=10'
    });
  },
  onShow:function(){ 
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
      path: 'pages/unified-search/unified-search',
      success: function (res) { },
      fail: function (res) { }
    }
  },
})