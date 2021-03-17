const app = getApp(),
      apiUrl = app.getApiUrl();

Page({
  data: {
    userInfo : null,
    isHide : false,
    markInfoAll : null,
    markHide : false,
    loginText : "点击登录",
    version: null
  },
  onLoad : function(){
      this.setData({
          version: app.globalData.version
      })
    // my.showToast({
    //     type: 'none',
    //     content: '加载中...',
    //     duration: 2000,
    // });
  },
onShow : function(){
    var that = this;
    //调用应用实例的方法获取全局数据
    app.getUserInfo(function (userInfo) {
        // 判断是否拒绝登录
        that.setData({
          userId: userInfo.userId
        })
        if (!userInfo.userId) {
            that.setData({ 
              isHide: true, 
              userInfo: userInfo,
              loginText: '点击登录'
            })
        } else if (userInfo.userId) {
			console.log(userInfo)
            that.setData({ 
              userInfo: userInfo,
			  loginText: ''
            });
        }
    });
},
    toMyCollections : function(e){
        my.navigateTo({
            url: '../user-collects/index'
        })
    },
    toSetUp : function(e){
        my.navigateTo({
            url: '../user-set/index'
        })
    },
    register : function(){
        let that = this;
        app.getLoginAgain(function (res) {
            that.setData({
                userInfo: res,
                loginText: res.error == 'error' && res.userId ? "" : "点击登录"
            });
        })
    }
});
