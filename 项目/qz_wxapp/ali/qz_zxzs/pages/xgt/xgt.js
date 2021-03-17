const app = getApp(),
      apiUrl = app.getApiUrl();

Page({
  data: {
    // 初始化数据
    data: [],//数据
    dataTwo: [],
    styleFilter : null,
    spaceFilter : null,
    layoutFiler : null,
    colorFilter : null,
    tabTxt: ['风格', '空间', '户型', '颜色'],
    tab: [true,true,true,true],
    house_style: 0,//风格
    house_space: 0,//空间
    house_layout: 0,//户型
    house_color: 0,//颜色
    page: 1,//当前页码,
    default : false,
    defaultNumber : 20,//默认显示条数
    styleNumber: 20, //风格默认显示条数
    spaceNumber: 20, //空间默认显示条数
    layoutNumber: 20, //布局默认显示条数
    colorNumber: 20, //颜色默认显示条数
    styleBc: null,
    spaceBc: null,
    layoutBc: null,
    colorBc: null,
    mrshujv: null,
    shoucpanduan: true,
    userInfo: null,
    classtype: null,
    userId: "",
    isHide: [true, true, true,true],
    scrollTop : 0,
    noMore : true, //是否有更多数据
    defaultCount : 20
  },
  onLoad : function(e){
	  let userid = ''
		if(app.globalData.userInfo && app.globalData.userInfo.userId) {
			userid = app.globalData.userInfo.userId
			this.setData({
				userId: userid
			})
		}
    this.getFilterItems();
  },
  onShow : function(){
    this.getXgtItemFirst();
  },
  getXgtItemFirst : function(){
    //初始化数据
    let that = this;
    //that.getFilterItems();
    // app.getUserInfo(function (res) { 
    //     that.setData({ 
    //         userId: res.userId
    //     }); 
    // })
    my.httpRequest({
        url: apiUrl + '/appletcarousel/meitu',
        data: {
            count: that.data.defaultCount,
            userid: that.data.userId
        },
        header: {
            'Content-Type': 'application/json'
        },
        success: function (res) {
            that.setData({
                data: res.data.info.list,
                mrshujv: res.data.info.list
            })

        },
        fail: function () {
            // console.log('error!!!!!!!!!!!!!!')
        }
    })
  },
  // 获取筛选项
  getFilterItems: function () {
      var that = this;
      my.httpRequest({
          url: apiUrl + '/appletcarousel/meitu',
          header: {
              'Content-Type': 'application/json'
          },
          success: function (res) {
              that.setData({
                  styleFilter: res.data.attribute.fengge,
                  spaceFilter: res.data.attribute.huxing,
                  layoutFiler: res.data.attribute.location,
                  colorFilter: res.data.attribute.color,
              });

          },
          fail: function () {
              // console.log('error!!!!!!!!!!!!!!')
          }
      })
  },
  // 筛选项点击操作
  xgtFilter : function(e){
    let that = this, 
        id = e.currentTarget.dataset.id, 
        index = e.currentTarget.dataset.index, 
        txt = e.currentTarget.dataset.txt, 
        tabTxt = this.data.tabTxt;
    that.setData({
        styleNumber: 20,
        spaceNumber: 20,
        layoutNumber: 20,
        colorNumber: 20,
    });
    switch (index) {
      case '0':
          tabTxt[0] = txt;
          that.setData({
              house_style: id,
              page: 1,
              data: [],
              tab: [true, true, true, true],
              tabTxt: tabTxt,
              index: 0,
              default: true,
              styleBc: id

          });
          my.httpRequest({
              url: apiUrl + '/appletcarousel/meitu',
              data: {
                  fengge: that.data.styleBc,
                  huxing: that.data.spaceBc,
                  location: that.data.layoutBc,
                  color: that.data.colorBc,
                  count: that.data.styleNumber
              },
              header: {
                  'Content-Type': 'application/json'
              },
              success: function (res) {
                  if (res.data.info.list.length < 1) {

                      that.setData({
                          data: that.data.mrshujv,
                          scrollTop: 0
                      })
                  } else {
                      that.setData({
                          data: res.data.info.list,
                          scrollTop: 0
                      })
                  }

              },
              fail: function () {
                  // console.log('error!!!!!!!!!!!!!!')
              }
          })

          break;
      case '1':
          tabTxt[1] = txt;
          that.setData({
              house_space: id,
              page: 1,
              data: [],
              tab: [true, true, true, true],
              tabTxt: tabTxt,
              index: 1,
              default: true,
              spaceBc: id,
          });

          my.httpRequest({
              url: apiUrl + '/appletcarousel/meitu',
              data: {
                  fengge: that.data.styleBc,
                  huxing: that.data.spaceBc,
                  location: that.data.layoutBc,
                  color: that.data.colorBc,
                  count: that.data.styleNumber
              },
              header: {
                  'Content-Type': 'application/json'
              },
              success: function (res) {
                  if (res.data.info.list < 1) {
                      that.setData({
                          data: that.data.mrshujv,
                          scrollTop: 0
                      })
                  } else {
                      that.setData({
                          data: res.data.info.list,
                          scrollTop: 0
                      })
                  }

              },
              fail: function () {
                  // console.log('error!!!!!!!!!!!!!!')
              }
          })

          break;
      case '2':
          tabTxt[2] = txt;
          that.setData({
              house_layout: id,
              page: 1,
              data: [],
              tab: [true, true, true, true],
              tabTxt: tabTxt,
              index: 2,
              default: true,
              layoutBc: id
          });
          my.httpRequest({
              url: apiUrl + '/appletcarousel/meitu',
              data: {
                  fengge: that.data.styleBc,
                  huxing: that.data.spaceBc,
                  location: that.data.layoutBc,
                  color: that.data.colorBc,
                  count: that.data.styleNumber
              },
              header: {
                  'Content-Type': 'application/json'
              },
              success: function (res) {
                  if (res.data.info.list < 1) {
                      that.setData({
                          data: that.data.mrshujv,
                          scrollTop: 0
                      })
                  } else {
                      that.setData({
                          data: res.data.info.list,
                          scrollTop: 0
                      })
                  }

              },
              fail: function () {
                  // console.log('error!!!!!!!!!!!!!!')
              }
          })
          break;
      case '3':
          tabTxt[3] = txt;
          that.setData({
              house_color: id,
              page: 1,
              data: [],
              tab: [true, true, true, true],
              tabTxt: tabTxt,
              index: 3,
              default: true,
              colorBc: id
          });

          my.httpRequest({
              url: apiUrl + '/appletcarousel/meitu',
              data: {
                  fengge: that.data.styleBc,
                  huxing: that.data.spaceBc,
                  location: that.data.layoutBc,
                  color: that.data.colorBc,
                  count: that.data.styleNumber
              },
              header: {
                  'Content-Type': 'application/json'
              },
              success: function (res) {
                  if (res.data.info.list < 1) {
                      that.setData({
                          data: that.data.mrshujv,
                          scrollTop: 0
                      })
                  } else {
                      that.setData({
                          data: res.data.info.list,
                          scrollTop: 0
                      })
                  }

              },
              fail: function () {
                  // console.log('error!!!!!!!!!!!!!!')
              }
          })

          break;
    }
  },
   // 选项卡
  filterTab: function (e) {
      var data = [true, true, true, true], 
          index = e.currentTarget.dataset.index;
      data[index] = !this.data.tab[index];
      this.setData({
          tab: data,
      })
    },
  //下拉加载
  loadMoreData : function(){
    let primaryLength = this.data.data.length,
        lastDataLength = 0;
    //console.log("原来的数据："+primaryLength);
    my.showToast({
        content: '加载中...',
        duration: 2000
    });
    let that = this;
    if (that.data.default == false) {
        let count = that.data.defaultNumber + 10;
        my.httpRequest({
            url: apiUrl + '/appletcarousel/meitu',
            data: {
                userid:that.data.userId,
                count: count
            },
            header: {
                'Content-Type': 'application/json'
            },
            success: function (res) {
                that.setData({
                    data: res.data.info.list,
                    defaultNumber: count
                })
                lastDataLength = res.data.info.list.length;
                that.hasMore(primaryLength,lastDataLength);
            },
            fail: function () {
                // console.log('error!!!!!!!!!!!!!!')
            }
        })
    }
    if (that.data.index == 0) {
      let count = that.data.styleNumber + 10;
      my.httpRequest({
          url: apiUrl + '/appletcarousel/meitu',
          data: {
              userid:that.data.userId,
              fengge: that.data.styleBc,
              huxing: that.data.spaceBc,
              location: that.data.layoutBc,
              color: that.data.colorBc,
              count: count,
          },
          header: {
              'Content-Type': 'application/json'
          },
          success: function (res) {

              that.setData({
                  data: res.data.info.list,
                  styleNumber: count
              })
              lastDataLength = res.data.info.list.length;
              that.hasMore(primaryLength,lastDataLength);
          },
          fail: function () {
              // console.log('error!!!!!!!!!!!!!!')
          }


      })
  } else if (that.data.index == 1) {
      let count = that.data.spaceNumber + 10;
      my.httpRequest({
          url: apiUrl + '/appletcarousel/meitu',

          data: {
              userid: that.data.userId,
              fengge: that.data.styleBc,
              huxing: that.data.spaceBc,
              location: that.data.layoutBc,
              color: that.data.colorBc,
              count: count,
          },
          header: {
              'Content-Type': 'application/json'
          },
          success: function (res) {
              that.setData({
                  data: res.data.info.list,
                  spaceNumber: count
              })
              lastDataLength = res.data.info.list.length;
              that.hasMore(primaryLength,lastDataLength);
          },
          fail: function () {
              // console.log('error!!!!!!!!!!!!!!')
          }


      })
  } else if (that.data.index == 2) {
      let count = that.data.layoutNumber + 10;
      my.httpRequest({
          url: apiUrl + '/appletcarousel/meitu',

          data: {
              userid: that.data.userId,
              fengge: that.data.styleBc,
              huxing: that.data.spaceBc,
              location: that.data.layoutBc,
              color: that.data.colorBc,
              count: count,
          },
          header: {
              'Content-Type': 'application/json'
          },
          success: function (res) {
              that.setData({
                  data: res.data.info.list,
                  layoutNumber: count
              })
              lastDataLength = res.data.info.list.length;
              that.hasMore(primaryLength,lastDataLength);
          },
          fail: function () {
              // console.log('error!!!!!!!!!!!!!!')
          }


      })
  } else if (that.data.index == 3) {
      let count = that.data.colorNumber + 10;
      my.httpRequest({
          url: apiUrl + '/appletcarousel/meitu',

          data: {
              userid: that.data.userId,
              fengge: that.data.styleBc,
              huxing: that.data.spaceBc,
              location: that.data.layoutBc,
              color: that.data.colorBc,
              count: count,
          },
          header: {
              'Content-Type': 'application/json'
          },
          success: function (res) {
              that.setData({
                  data: res.data.info.list,
                  colorNumber: count
              })
              lastDataLength = res.data.info.list.length;
              that.hasMore(primaryLength,lastDataLength);
          },
          fail: function () {
              // console.log('error!!!!!!!!!!!!!!')
          }


      })
    }
  },
  toXgtDetail : function(e){
    let id = e.currentTarget.dataset.id,
        title = e.currentTarget.dataset.title;
    my.navigateTo({
        url: '../xgt-detail/xgt-detail?id=' + id + '&title=' + title
    });
},
//判断是否有更多数据，没有则显示没有了
hasMore : function(primarySize,lastSize){
    //console.log("more");
    //console.log(primarySize+":"+lastSize);
    if(primarySize>=lastSize){
        // this.setData({
        //     noMore : false
        // });
        my.showToast({
            content: '没有了',
            duration: 3000
        });
    }
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
                classtype: '8', // 效果图
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
                    let data = that.data.data
                    data[index].is_collect=1
                    that.setData({ 
                        data: data
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
                        classtype: '8', // 效果图
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
                            let data = that.data.data
                            data[index].is_collect = 1
                            that.setData({ data: data })
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
        if (that.data.userId != '') {
            my.httpRequest({
                url: apiUrl + '/appletcarousel/editcollect',
                data: {
                    userid: that.data.userId, // 用户ID
                    classtype: '8', // 效果图
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
                        let data = that.data.data
                        data[index].is_collect=0
                        that.setData({ 
                            data: data
                        })
                    }
                }
            });
        }
    }

});
