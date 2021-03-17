const app = getApp(),
    oImgUrl = app.getImgUrl(),
      apiUrl = app.getApiUrl();

var WxParse =require("../../wxParse/wxParse.js");

function getLocalTime(nS) {
    return new Date(parseInt(nS) * 1000).toLocaleString().replace(/年|月/g, "-").replace(/日/g, " ").replace('/', '-').replace('/', '-').split(' ')[0];
}
function formatDate(now) { 
     var year=now.getFullYear(); 
     var month=now.getMonth()+1; 
     var date=now.getDate(); 
     return year+"-"+month+"-"+date; 
} 
Page({
  data: {
    articleDetail : {},
    recommendArticleList : [],
    content : "",
    article_content : "",
    oImgUrl: oImgUrl,
    zan :true,
    userId : ""
  },
  onLoad : function(options){
    let that = this;
	let userid = ''
	if(app.globalData.userInfo && app.globalData.userInfo.userId) {
		userid = app.globalData.userInfo.userId
		this.setData({
			userId: userid
		})
	}
	// 删除授权
    // app.getUserInfo(function(res){
    //     that.setData({
    //         userId:res.userId
    //     })
    // });
    // 获取文章详情
    my.httpRequest({
        url: apiUrl+'/appletcarousel/details',
        data: {
            userid: that.data.userId,
            id: options.id,
            classtype:'10'
        },
        header: {
            'content-type': 'application/json'
        },
        success:function(res){
          let times = res.data.article.addtime
          var dx=new Date(times*1000);
          let xtimes = formatDate(dx)
          that.setData({ 
            xtimes: xtimes+''
          });
          res.data.article.addtime = xtimes;
          let contents = res.data.article.content,
              a1 = '<a href="http://www.qizuang.com/zxbj/?src=gl-bj" rel="nofollow" target="_blank">&gt;&gt; 点此获取专业设计师免费量房设计</a>',
              a3 = '<a href="http://www.qizuang.com/zhaobiao/" rel= "nofollow" target= "_blank" >&gt;&gt; 点此获取专业设计师免费量房设计 < /a>',
              a4 = '<a href="http://www.qizuang.com/zxbj/?src=gl-bj" rel= "nofollow" target= "_blank" >&gt;&gt; 点此获取专业设计师免费量房设计 < /a>',
              a5 = '<a href="http://www.qizuang.com/zhaobiao/" rel="nofollow" target="_blank">&gt;&gt; 点此获取专业设计师免费量房设计</a>',
              a2 = '<a href="http://www.qizuang.com/zhaobiao/" rel="nofollow" target="_blank" style="text-decoration: underline; font-size: 14px; color: rgb(192, 0, 0);"><span style="font-size: 14px; color: rgb(192, 0, 0);">&gt;&gt; 点此获取专业设计师免费量房设计</span></a>',
              a6 = '<ahref= rel="nofollow" target="_blank" style="text-decoration:underline;font-size:14px;color:rgb(192,0,0);"><spanstyle="font-size:14px;color:rgb(192,0,0);">&gt;&gt;点此获取专业设计师免费量房设计</spanstyle="font-size:14px;color:rgb(192,0,0);"></ahref=>';
          if ( contents.indexOf(a1) > 0){
              contents = contents.split(a1)[0];
          } else if(contents.indexOf(a2) > 0){
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
          if(contents.indexOf("&gt;&gt; 点此获取专业设计师免费量房设计")>0){
              contents = contents.replace("&gt;&gt; 点此获取专业设计师免费量房设计","");
          }
          if(contents.indexOf("&gt;&gt; 点此免费获得最新装修报价")>0){
              contents = contents.replace("&gt;&gt; 点此免费获得最新装修报价","");
          }
          WxParse.wxParse('article_content','html',contents,that,5)

          that.setData({ 
            articleDetail: res.data.article,
          });

          my.setNavigationBar({
              title: res.data.article.title
          });
        }
      });
    //获取推荐文章 
    my.httpRequest({
        url: apiUrl + '/appletcarousel/detailsRecommend',
        data: {
          id : options.id,
          order: 'realview',
          count:'9' 
        },
        header: {
            'content-type': 'application/json'
        },
        success: function (res) {
            that.setData({ 
              recommendArticleList: res.data
            });
        }
    });
    this.isZan(options.id);
  },
  //判断当前文章是否点过赞
  isZan : function(id){
    let that = this,
        user = app.getNewStorage('user'),
        len = Array.isArray(user) ? user.length : 0;
    for(let i=0;i<len;i++){
        if(parseInt(user[i])==id){
            that.setData({
                zan : false
            });
        }
    }
  },
  toArticleDetail : function(e){
    let id = e.currentTarget.dataset.id;
    my.navigateTo({
        url: '../article-detail/index?id='+id
    })
  },
//收藏   
  collect: function (e) {
      let that = this,
          id = e.currentTarget.dataset.id || that.data.articleDetail.id;
      //console.log(that.data.userId)
      if (that.data.userId) {
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
          success: function () {
            my.showToast({
              content: '收藏成功',
              icon: 'success',
              duration: 1200
            });
            
            //把是否收藏数据放到articleDetail中
            let detailsNow = that.data.articleDetail;
            detailsNow.is_collect = 1;
            that.setData({ 
                articleDetail: detailsNow 
            });
          }
        });
      } else {
          //先获取授权，再到齐装平台注册，最后再收藏
            app.getLoginAgain(function(res){
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
                        success: function () {
                            my.showToast({
                            content: '收藏成功',
                            icon: 'success',
                            duration: 1200
                            });
                            
                            //把是否收藏数据放到articleDetail中
                            let detailsNow = that.data.articleDetail;
                            detailsNow.is_collect = 1;
                            that.setData({ 
                                articleDetail: detailsNow 
                            });
                        }
                    });
                }
            })
      }

    },
    // 取消收藏
    cancelCollect : function(e){
        let that = this,
            id = e.currentTarget.dataset.id || that.data.articleDetail.id;
        if (that.data.userId) {
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
                method: "GET",
                success: function (res) {
                    my.showToast({
                        content: '你已取消收藏',
                        icon: 'success',
                        duration: 1200
                    });
                    let detailsNow = that.data.articleDetail;
                    detailsNow.is_collect = 0;
                    that.setData({ 
                        articleDetail: detailsNow 
                    });
                }
            });
        }
    },
    // 点赞
    approve : function(){
        let that = this,
            user = app.getNewStorage('user'),
            details = that.data.articleDetail,
            bool = true;
        if (user){
            for (let i = 0; i < user.length; i++) {
                if (user[i] == details.id){
                    bool = false;

                    that.setData({
                        zan:false
                    })
                    break;
                }else{
                    bool = true;
                }
            }
            if (bool) {
                my.httpRequest({
                    url: apiUrl+'/appletcarousel/like',
                    data:{
                        id: details.id
                    },
                    header: {
                        'content-type': 'application/json'
                    },
                    success:function(res){
                        if(res.data.state === 1){
                            let dianzanNow = that.data.articleDetail;
                            dianzanNow.likes = parseInt(dianzanNow.likes) + 1;

                            that.setData({ 
                                articleDetail: dianzanNow, 
                                zan: false
                            });
                            user.push(details.id);
                            app.setNewStorage('user', user);
                        }
                    }
                });
            } else {
                my.alert({
                    content: '您已经点过了'
                });
            }
        }else{
            let user = [];
            my.httpRequest({
                url: apiUrl + '/appletcarousel/like',
                data: {
                    id: details.id
                },
                header: {
                    'content-type': 'application/json'
                },
                success: function (res) {
                    if (res.data.state === 1) {
                        let dianzanNow = that.data.articleDetail;
                        dianzanNow.likes = parseInt(dianzanNow.likes) + 1;
                        that.setData({ 
                            articleDetail: dianzanNow, 
                            zan: false 
                        });
                        user.push(details.id);
                        app.setNewStorage('user', user);
                    }
                }
            });
        }
    },
    /**
    * 重写分享函数，不写则无效
    */
    onShareAppMessage: function (res) {
        if(!my.canIUse('button.open-type.share')){
            my.alert({
                content : "您的支付宝版本较低，请升级"
            });
            return;
        }
        return {
            title: this.data.articleDetail.title,
            //title: res.target.dataset.title,
            path : "pages/article-detail/index?id=" + this.data.articleDetail.id,
            success: function (res) {
                // 转发成功
            },
            fail: function (res) {
                // 转发失败
            }
        }
    },

});
