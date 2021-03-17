const app = getApp(),
      apiUrl = app.getApiUrl();

Page({
  data: {
    articleList : [],
    urlstr : "",
    articleCount : 10,
    userId : ""
  },
  onLoad : function(options){
    let that = this,
        userId = '';
    my.setNavigationBar({
        title: options.urlstrname
    });

    // app.getUserInfo(function (res) { 
	// 	console.log(res.userId)
    //     that.setData({ 
    //         userId: res.userId
    //     }); 
    // })

    that.setData({ 
      urlstr: options.urlstr
    })
  },
  onShow : function(options){
    let that = this;
	let userid = ''
	if(app.globalData.userInfo && app.globalData.userInfo.userId) {
		userid = app.globalData.userInfo.userId
	}
    // 获取文章列表
    my.httpRequest({
        url: apiUrl + '/gonglue/' + that.data.urlstr,
        data: {
            userid : userid || that.data.userId,
            count: that.data.articleCount 
        },
        header: {
            'content-type': 'application/json'
        },
        success: function (res) {
            that.setData({ 
              articleList: res.data 
            })
        }
    });
  },
  loadMoreArticle : function(){
    let that = this,
        count = parseInt(that.data.articleCount);
        count+=10;
	let userid = ''
	if(app.globalData.userInfo && app.globalData.userInfo.userId) {
		userid = app.globalData.userInfo.userId
	}
    my.httpRequest({
        url: apiUrl + '/gonglue/' + that.data.urlstr,
        data: {
            userid : userid || that.data.userId,
            count: count 
        },
        header: {
            'content-type': 'application/json'
        },
        success: function (res) {
            my.showLoading({
                content: '数据加载中',
            });
            setTimeout(function () {
                my.hideLoading()
            }, 1200);
            that.setData({ 
              articleList: res.data, 
              articleCount: count
            });
        }
    });
  },
  toArticleDetail : function(e){
    let id = e.currentTarget.dataset.id;
    my.navigateTo({
        url: '../article-detail/index?id='+id
    })
  },
  collectAction : function(e){
    let id = e.currentTarget.dataset.id,
        index = e.currentTarget.dataset.index,
        that = this,
        userId = '';
    if (that.data.userId){
        my.httpRequest({
            url: apiUrl + '/appletcarousel/editcollect',
            data: {
                userid: that.data.userId,
                classtype: '10', // 装修攻略
                classid: id
            },
            header: {
                'content-type': 'application/x-www-form-urlencoded'
            },
            method: "POST",
            success: function (res) {
                if (res.data.state == 1) {
                    my.showToast({
                        content: '收藏成功',
                        type: 'success',
                        duration: 1200
                    });
                    let artList = that.data.articleList
                    artList[index].is_collect=1
                    that.setData({ 
                        articleList: artList
                    })
                }
            }
        });
    }else{
        app.getLoginAgain(function (res) {
            that.setData({
                userId : res.userId
            });
            if(that.data.userId){
                my.httpRequest({
                    url: apiUrl + '/appletcarousel/editcollect',
                    data: {
                        userid: that.data.userId,
                        classtype: '10', // 装修攻略
                        classid: id
                    },
                    header: {
                        'content-type': 'application/x-www-form-urlencoded'
                    },
                    method: "POST",
                    success: function (res) {
                        if (res.data.state == 1) {
                            my.showToast({
                                content: '收藏成功',
                                icon: 'success',
                                duration: 1200
                            });
                            let artList = that.data.articleList
                            artList[index].is_collect = 1
                            that.setData({ articleList: artList })
                        }
                    }
                });
            }
        });
    }
  },
    cancelCollectAction:function(e){
        let id = e.currentTarget.dataset.id,
            index = e.currentTarget.dataset.index,
            that = this;
        if (that.data.userId) {
            my.httpRequest({
                url: apiUrl + '/appletcarousel/editcollect',
                data: {
                    userid: that.data.userId, // 用户ID
                    classtype: '10', // 装修攻略
                    classid: id
                },
                header: {
                    'content-type': 'application/json'
                },
                success: function (res) {
                    if(res.data.state ==1){
                        my.showToast({
                            content: '你已取消收藏',
                            icon: 'success',
                            duration: 1200
                        });
                        let artList = that.data.articleList
                        artList[index].is_collect=0
                        that.setData({ 
                            articleList: artList
                        })
                    }
                }
            });
        }
    }


});
