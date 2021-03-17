const app = getApp(),
      apiUrl = app.getApiUrl(),
      oImgUrl = app.getImgUrl();

Page({
  data: {
    isHide: [true,false,false],
    isEmpty:[true,true,true],
    xgtList : null,
    companyList : null,
    articleList : null,
    userId : "",
    oImgUrl : oImgUrl,
    currentPath : "",//当前需要跳转的路径
    currentTabIndex : 0,//当前tab索引
    currentShareTitle : ""
  },
  /**
   * 我的收藏tab切换
  */
  showModal: function (e) {
      let index = e.currentTarget.dataset.index;
      let hideArr = [false,false,false];
      hideArr[index] = true;
      this.setData({ 
        isHide: hideArr,
        currentTabIndex : index
      });
  },
  onLoad : function(){

  },
  onShow : function(){
      let userId = '',
          that = this;
      app.getUserInfo(function (res) {
          that.setData({
              userId : res.userId
          });
          if (res.userId) {
              // 获取装修效果图
              my.httpRequest({
                  url: apiUrl + '/appletcarousel/collect',
                  data: {
                      count: '20',
                      userid: res.userId,
                      classtype: '8'//效果图
                  },
                  header: {
                      'content-type': 'application/json'
                  },
                  success: function (res) {
                      let empty = that.data.isEmpty;

                      if (res.data.length > 0) {
                          empty[0] = true;
                          that.setData({ 
                            xgtList: res.data, 
                            isEmpty: empty 
                          })
                      } else {
                          empty[0] = false;
                          that.setData({ 
                            xgtList: res.data, 
                            isEmpty: empty 
                          })
                      }
                  }
              });
              // 获取装修公司
              my.httpRequest({
                  url: apiUrl + '/appletcarousel/collect',
                  data: {
                      count: '20',
                      userid: res.userId,
                      classtype: '9'//装修公司
                  },
                  header: {
                      'content-type': 'application/json'
                  },
                  success: function (res) {
                      //console.log(res);
                      let empty = that.data.isEmpty;
                      if (res.data.length > 0) {
                          empty[1] = true;
                          that.setData({ 
                            companyList: res.data, 
                            isEmpty: empty 
                          })
                      } else {
                          empty[1] = false;
                          that.setData({ 
                            companyList: res.data, 
                            isEmpty: empty 
                          })
                      }
                  }
              });
              // 获取装修攻略
              my.httpRequest({
                  url: apiUrl + '/appletcarousel/collect',
                  data: {
                      count: '20',
                      userid: res.userId,
                      classtype: '10'//装修攻略
                  },
                  header: {
                      'content-type': 'application/json'
                  },
                  success: function (res) {
                      //console.log(res);
                      let empty = that.data.isEmpty;
                      if (res.data.length > 0) {
                          empty[2] = true;
                          that.setData({ 
                            articleList: res.data, 
                            isEmpty: empty 
                          })
                      } else {
                          empty[2] = false;
                          that.setData({ 
                            articleList: res.data, 
                            isEmpty: empty 
                          })
                      }

                  }
              });
          } else {
              app.getLoginAgain(function (res) {
                    that.setData({
                        userId : res.userId
                    });
                  // 获取装修效果图
                  my.httpRequest({
                      url: apiUrl + '/appletcarousel/collect',
                      data: {
                          count: '10',
                          userid: res.userId,
                          classtype: '8'//效果图
                      },
                      header: {
                          'content-type': 'application/json'
                      },
                      success: function (res) {
                          let empty = that.data.isEmpty;

                          if (res.data.length > 0) {
                              empty[0] = true;
                              that.setData({ 
                                xgtList: res.data, 
                                isEmpty: empty 
                              })
                          } else {
                              empty[0] = false;
                              that.setData({ 
                                xgtList: res.data, 
                                isEmpty: empty 
                              })
                          }
                      }
                  });
                  // 获取装修公司
                  my.httpRequest({
                      url: apiUrl + '/appletcarousel/collect',
                      data: {
                          count: '10',
                          userid: res.userId,
                          classtype: '9'//装修公司
                      },
                      header: {
                          'content-type': 'application/json'
                      },
                      success: function (res) {
                          let empty = that.data.isEmpty;
                          if (res.data.length > 0) {
                              empty[1] = true;
                              that.setData({ 
                                companyList: res.data, 
                                isEmpty: empty 
                              })
                          } else {
                              empty[1] = false;
                              that.setData({ 
                                companyList: res.data, 
                                isEmpty: empty 
                              })
                          }
                      }
                  });
                  // 获取装修攻略
                  my.httpRequest({
                      url: apiUrl + '/appletcarousel/collect',
                      data: {
                          count: '10',
                          userid: res.userId,
                          classtype: '10'//装修攻略
                      },
                      header: {
                          'content-type': 'application/json'
                      },
                      success: function (res) {
                          let empty = that.data.isEmpty;
                          if (res.data.length > 0) {
                              empty[2] = true;
                              that.setData({ 
                                articleList: res.data, 
                                isEmpty: empty 
                              })
                          } else {
                              empty[2] = false;
                              that.setData({ 
                                articleList: res.data, 
                                isEmpty: empty 
                              })
                          }

                      }
                  });
              })
              that.setData({ 
                isEmpty: [false, false, false] 
              })
          }
      });
    },
  toXgtDetail : function(e){
    let id = e.currentTarget.dataset.id,
        title = e.currentTarget.dataset.title;
    my.navigateTo({
        url: '../xgt-detail/xgt-detail?id=' + id + '&title=' + title
    });
  },
  toArticleDetail : function(e){
    let id = e.currentTarget.dataset.id;
    my.navigateTo({
        url: '../article-detail/index?id='+id
    })
  },
  toCompanyDetail : function(e){
    let id = e.currentTarget.dataset.id,
        star = e.currentTarget.dataset.star,
        anlicount = e.currentTarget.dataset.anlicount,
		bm = e.currentTarget.dataset.bm;
    my.navigateTo({
        url : "../zxCompany-detail/index?id=" + id + "&star=" + star + "&anlicount=" + anlicount + "&bm=" + bm,
    })
  },
  /**
   * 点击跳转到效果图
   */
  toXgtList:function(){
    my.navigateTo({
        url: '../xgt/xgt'
    })
  },
  /**
   * 点击跳转到装修公司
   */
  toCompany: function () {
    my.navigateTo({
        url: '../zxCompany/zxCompany'
    })
  },
  /**
   * 点击跳转到装修攻略
   */
  toArticleList: function () {
      my.navigateTo({
        url: '../article-center/index'
      })
  },
  // 删除某项收藏
  delCollect : function(e){
    let id = e.currentTarget.dataset.id,
            type = e.currentTarget.dataset.type,
            userId = e.currentTarget.dataset.userid,
            that = this;
    if (type === 'xgt'){
        my.httpRequest({
            url: apiUrl + '/appletcarousel/editcollect',
            data: {
                userid: userId, // 用户ID
                classtype: '8', // 装修效果图
                classid: id
            },
            header: {
                'content-type': 'application/json'
            },
            success: function (res) {
                if(res.data.state==1){
                    // 获取装修效果图
                    my.httpRequest({
                        url: apiUrl + '/appletcarousel/collect',
                        data: {
                            count: '10',
                            userid: userId,
                            classtype: '8'//效果图
                        },
                        header: {
                            'content-type': 'application/json'
                        },
                        success: function (res) {
                            let empty = that.data.isEmpty;

                            if (res.data.length > 0) {
                                empty[0] = true;
                                that.setData({ xgtList: res.data, isEmpty: empty })
                            } else {
                                empty[0] = false;
                                that.setData({ xgtList: res.data, isEmpty: empty })
                            }
                        }
                    });
                }
            }
        });
    }else if(type === 'company'){
        my.httpRequest({
            url: apiUrl + '/appletcarousel/editcollect',
            data: {
                userid: userId, // 用户ID
                classtype: '9', // 装修公司
                classid: id
            },
            header: {
                'content-type': 'application/json'
            },
            success: function (res) {
                if(res.data.state == 1){
                    // 获取装修公司
                    my.httpRequest({
                        url: apiUrl + '/appletcarousel/collect',
                        data: {
                            userid: userId,
                            classtype: '9'//装修公司
                        },
                        header: {
                            'content-type': 'application/json'
                        },
                        success: function (res) {
                            let empty = that.data.isEmpty;
                            if (res.data.length > 0) {
                                empty[1] = true;
                                that.setData({ companyList: res.data, isEmpty: empty })
                            } else {
                                empty[1] = false;
                                that.setData({ companyList: res.data, isEmpty: empty })
                            }
                        }
                    });
                }

            }
        });
    }else if(type === 'article'){
        my.httpRequest({
            url: apiUrl + '/appletcarousel/editcollect',
            data: {
                userid: userId, // 用户ID
                classtype: '10', // 装修攻略
                classid: id
            },
            header: {
                'content-type': 'application/json'
            },
            success: function (res) {
                if(res.data.state == 1){
                    // 获取装修攻略
                    my.httpRequest({
                        url: apiUrl + '/appletcarousel/collect',
                        data: {
                            userid: userId,
                            classtype: '10'//装修攻略
                        },
                        header: {
                            'content-type': 'application/json'
                        },
                        success: function (res) {
                            let empty = that.data.isEmpty;
                            if (res.data.length > 0) {
                                empty[2] = true;
                                that.setData({ articleList: res.data, isEmpty: empty })
                            } else {
                                empty[2] = false;
                                that.setData({ articleList: res.data, isEmpty: empty })
                            }
                        }
                    }); 
                }

            }
        });
    }
  }, 
    /**
    * 重写分享函数，不重写，分享功能无效
    */
    onShareAppMessage: function (e) {
        //这里的e是个空对象，无法接受自定义参数，微信则可以
        if(!my.canIUse('button.open-type.share')){
            my.alert({
                content : "您的支付宝版本较低，请升级"
            });
            return;
        }
        return {
            title: this.data.currentShareTitle,
            path : this.data.currentPath,
            success: function (res) {
                // 转发成功
            },
            fail: function (res) {
                // 转发失败
            }
        }
    },
    //支付宝bug，分享无法获得参数，所以要手动获取参数
    getShareArgs : function(e){
        if(this.data.currentTabIndex == 0){
            this.setData({
                currentShareTitle : e.currentTarget.dataset.title,
                currentPath : "pages/xgt-detail/xgt-detail?id=" + e.currentTarget.dataset.id + "&title=" + e.currentTarget.dataset.title,
            });
        }
        if(this.data.currentTabIndex == 1){
            this.setData({
                currentShareTitle : e.currentTarget.dataset.title,
                currentPath : "pages/zxCompany-detail/index?id=" + e.currentTarget.dataset.id + "&star=" + e.currentTarget.dataset.star + "&anlicount=" + e.currentTarget.dataset.anlicount,
            });
        }
        if(this.data.currentTabIndex == 2){
            this.setData({
                currentShareTitle : e.currentTarget.dataset.title,
                currentPath : "pages/article-detail/index?id="+e.currentTarget.dataset.id
            });
        }
    }

});
