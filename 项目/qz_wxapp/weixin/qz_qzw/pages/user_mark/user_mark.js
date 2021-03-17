// pages/user_collect/user_collect.js
let app = getApp();
let apiUrl = app.getApiUrl();
let oImgUrl = app.getImgUrl();
let getPVNum = app.getPVNum();

Page({

  /**
   * 页面的初始数据
   */
  data: {
    userid: "",
    isHide: [true, true,true,true],
    isEmpty: [true, true,true,true],
    xgt:[],
    zxgl: [],
    spArr: [],
    zxgs:[],
    oImgUrl: oImgUrl,
    img_path:[],
    img_pathArr:[],
    loginUserId: '',
    currentTab: '',
    lingNum: '',
    // delBtnWidth: 180
    startX: 0, //开始坐标
    startY: 0,
    getPVNum:[],
    isUpdateScrollHeight: false,
    xgtPage:1,
    glPage:1,
    videoPage:1,
    companyPage:1,
    hasXgtData:1,
    hasGlData:1,
    hasVideoData:1,
    hasCompanyData:1,
  },
  /*** 滑动切换tab***/
  bindChange: function (e) {
    var that = this;
    that.setData({ currentTab: e.detail.current });
  },
  /*** 点击tab切换***/
  swichNav: function (e) {
    var that = this;
    that.setData({
      currentTab: e.currentTarget.dataset.current
    });
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(options) {
    let that = this;
    //swiper的高度计算
    wx.getSystemInfo({
      success: function (res) {
        that.setData({
          clientHeight: res.windowHeight
        });
      }
    });
    //获取效果图
    wx.request({
      url: apiUrl + '/appletcarousel/collect',
      data: {
        count: '15',
        userid: options.userId,
        classtype: '4',//效果图
        page: that.data.xgtPage
      },
      dataType: 'json',
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      success(res) {
        let xgt = res.data;
        that.data.xgt = that.data.xgt.concat(xgt)
        wx.request({
          url: apiUrl + '/appletcarousel/collect',
          data: {
            count: '15',
            userid: options.userId,
            classtype: '2',//案例图
            page: that.data.xgtPage
          },
          dataType: 'json',
          header: {
            'content-type': 'application/x-www-form-urlencoded'
          },
          success(res) {
            let empty = that.data.isEmpty;
            that.data.xgt = that.data.xgt.concat(res.data)
            for (let i = 0; i < that.data.xgt.length; i++) {
              that.data.xgt[i].pv = app.getPVNum();
            }
            if (that.data.xgt.length > 0) {
              empty[0] = true;
              that.setData({ xgt: that.data.xgt, isEmpty: empty })
            } else {
              empty[0] = false;
              that.setData({ xgt: that.data.xgt, isEmpty: empty })
            }
            wx.hideLoading();
          }
        })

      }
    });
    wx.showLoading({
      title: "加载中"
    });
    // 获取攻略
    wx.request({
      url: apiUrl + '/appletcarousel/collect',
      data: {
        count: '15',
        userid: options.userId,
        classtype: '10',//攻略
        page: that.data.glPage
      },
      dataType: 'json',
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      success(res) {
        that.data.isUpdateScrollHeight ? that.updateScrollHeight(res.data.length) : "";
        let empty = that.data.isEmpty;
        if (res.data.length > 0 && res.data.length < 15) {
          that.setData({
            hasGlData: 0
          });
        } else {
          that.setData({
            hasGlData: 1
          });
        }
        that.data.zxgl = that.data.zxgl.concat(res.data);
        if (res.data.length > 0) {
          empty[1] = true;
          that.setData({ zxgl: that.data.zxgl, isEmpty: empty })
        } else {
          empty[1] = false;
          that.setData({ zxgl: that.data.zxgl, isEmpty: empty })
        }
        wx.hideLoading();
      }
    });
    wx.showLoading({
      title: "加载中"
    });
    // 获取视频
    wx.request({
      url: apiUrl + '/appletcarousel/collect',
      data: {
        count: '15',
        userid: options.userId,
        classtype: '11',//视频
        page: that.data.videoPage
      },
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      success(res) {
        let empty = that.data.isEmpty;
        that.data.spArr = res.data.concat(that.data.spArr);
        if (res.data.length > 0) {
          empty[2] = true;
          that.setData({ spArr: that.data.spArr, isEmpty: empty })
        } else {
          empty[2] = false;
          that.setData({ spArr: that.data.spArr, isEmpty: empty })
        }
        wx.hideLoading();
      }
    });
    wx.showLoading({
      title: "加载中"
    });
    // 获取装修公司
    wx.request({
      url: apiUrl + '/appletcarousel/collect',
      data: {
        count: '15',
        userid: options.userId,
        classtype: '9',
        page: that.data.companyPage
      },
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      success(res) {
        let empty = that.data.isEmpty;
        that.data.zxgs = that.data.zxgs.concat(res.data);
        if (res.data.length > 0) {
          empty[3] = true;
          that.setData({ zxgs: that.data.zxgs, isEmpty: empty })
        } else {
          empty[3] = false;
          that.setData({ zxgs: that.data.zxgs, isEmpty: empty })
        }
        wx.hideLoading();
      }
    });
  },
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage(res) {
    if (res.from === 'button') {
      // 来自页面内转发按钮
      if (res.target.dataset.style == 'sp') {
        return {
          title: res.target.dataset.title,
          path: '' + res.target.dataset.id + '' + res.target.dataset.title,
          imageUrl: oImgUrl + res.target.dataset.imgsrc,
          success(res) {
            // 转发成功
          },
          fail(res) {
            // 转发失败
          }
        }
      } else if (res.target.dataset.style == 'zxgl') {
        return {
          title: res.target.dataset.title,
          path: '' + res.target.dataset.id,
          imageUrl: oImgUrl + res.target.dataset.imgsrc,
          success(res) {
            // 转发成功
          },
          fail(res) {
            // 转发失败
          }
        }
      }
    }
  },
  /**
     * 我的收藏tab切换
     */
  showModal: function (res) {
    let index = (res.currentTarget.dataset.index);
    let hideArr = [false, false, false,false];
    hideArr[index] = true;
    this.setData({ isHide: hideArr });
  },
  /**
  * 点击跳转到设计页面
  */
  toSheji() {
    wx.navigateTo({ url: '../zhuangxiusj/zhuangxiusj' });
  },
  //阻止下拉刷新
  onPullDownRefresh: function () {
    wx.stopPullDownRefresh()
  },
  //取消收藏
  toDel(e) {
    let id = e.currentTarget.dataset.id,
      classType = e.currentTarget.dataset.classtype,
      userId = e.currentTarget.dataset.userid,
      that = this;

    if (classType === "10") {
      wx.request({
        url: apiUrl + '/appletcarousel/editcollect',
        data: {
          userid: userId, // 用户ID
          classtype: '10', // 装修攻略
          classid: id
        },
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        success(res) {
          if (res.data.state == 1) {
            // 获取装修攻略
            wx.request({
              url: apiUrl + '/appletcarousel/collect',
              data: {
                count: '10',
                userid: userId,
                classtype: '10'//装修攻略
              },
              header: {
                'content-type': 'application/x-www-form-urlencoded'
              },
              success(res) {
                let empty = that.data.isEmpty;
                if (res.data.length > 0) {
                  empty[1] = true;
                  that.setData({ zxgl: res.data, isEmpty: empty })
                } else {
                  empty[1] = false;
                  that.setData({ zxgl: res.data, isEmpty: empty })
                }
                //收藏取消弹框
                wx.showToast({
                  title: '你已取消收藏',
                  icon: 'success',
                  duration: 1200
                });
                //下拉刷新
                wx.startPullDownRefresh();
                wx.stopPullDownRefresh();
              }
            });
          }
        }
      });
    } else if (classType === "11") {
      wx.request({
        url: apiUrl + '/appletcarousel/editcollect',
        data: {
          userid: userId, // 用户ID
          classtype: '11',
          classid: id
        },
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        success(res) {
          if (res.data.state == 1) {
            wx.request({
              url: apiUrl + '/appletcarousel/collect',
              data: {
                userid: userId,
                classtype: '11'
              },
              header: {
                'content-type': 'application/x-www-form-urlencoded'
              },
              success(res) {
                let empty = that.data.isEmpty;
                if (res.data.length > 0) {
                  empty[2] = true;
                  that.setData({ spArr: res.data, isEmpty: empty })
                } else {
                  empty[2] = false;
                  that.setData({ spArr: res.data, isEmpty: empty })
                }
                //收藏取消弹框
                wx.showToast({
                  title: '你已取消收藏',
                  icon: 'success',
                  duration: 1200
                });

                //下拉刷新
                wx.startPullDownRefresh();
                wx.stopPullDownRefresh();
              },
              fail: function () {
                // console.log(111)
              }
            });
          }
        }
      });
    } else if (classType === "4" || classType === "2") {
      wx.request({
        url: apiUrl + '/appletcarousel/editcollect',
        data: {
          userid: that.data.userid, // 用户ID
          classtype: classType,
          classid: id
        },
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        success(res) {
          if (res.data.state == 1) {
            wx.request({
              url: apiUrl + '/appletcarousel/collect',
              data: {
                userid: that.data.userid,
                classtype: classType
              },
              header: {
                'content-type': 'application/x-www-form-urlencoded'
              },
              success(res) {
                let xgt = res.data;
                classType = classType == "4"?"2":"4"
                wx.request({
                  url: apiUrl + '/appletcarousel/collect',
                  data: {
                    userid: that.data.userid, // 用户ID
                    classtype: classType,
                  },
                  header: {
                    'content-type': 'application/x-www-form-urlencoded'
                  },
                  success(res) {
                    xgt = xgt.concat(res.data);
                    let empty = that.data.isEmpty;
                    if (xgt.length > 0) {
                      empty[0] = true;
                      that.setData({ xgt: xgt, isEmpty: empty })
                    } else {
                      empty[0] = false;
                      that.setData({ xgt: xgt, isEmpty: empty })
                    }
                    //收藏取消弹框
                    wx.showToast({
                      title: '你已取消收藏',
                      icon: 'success',
                      duration: 1200
                    });

                    //下拉刷新
                    wx.startPullDownRefresh();
                    wx.stopPullDownRefresh();
                  }
                })
              },
              fail: function () {
                // console.log(111)
              }
            });
          }
        }
      });
    } else if (classType === "9") {
      wx.request({
        url: apiUrl + '/appletcarousel/editcollect',
        data: {
          userid: userId, // 用户ID
          classtype: '9', 
          classid: id
        },
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        success(res) {
          if (res.data.state == 1) {
            // 获取装修攻略
            wx.request({
              url: apiUrl + '/appletcarousel/collect',
              data: {
                count: '10',
                userid: userId,
                classtype: '9'
              },
              header: {
                'content-type': 'application/x-www-form-urlencoded'
              },
              success(res) {
                let empty = that.data.isEmpty;
                if (res.data.length > 0) {
                  empty[3] = true;
                  that.setData({ zxgs: res.data, isEmpty: empty })
                } else {
                  empty[3] = false;
                  that.setData({ zxgs: res.data, isEmpty: empty })
                }
                //收藏取消弹框
                wx.showToast({
                  title: '你已取消收藏',
                  icon: 'success',
                  duration: 1200
                });
                //下拉刷新
                wx.startPullDownRefresh();
                wx.stopPullDownRefresh();
              }
            });
          }
        }
      });
    }

  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function (option) {
    let that = this;
    that.setData({ lingNum: app.globalData.personNum });

    let viewVideoId = "";
    try {
      viewVideoId = wx.getStorageSync("viewVideoId");
      if (viewVideoId) {
        this.setData({
          vid: viewVideoId
        });

        wx.removeStorage({
          key: 'viewVideoId'
        })

        this.updateVideoPV();
      }
    } catch (e) {
      console.log(e);
    }
    
  },
  updateVideoPV: function () {
    let that = this;
    let vid = that.data.vid;
    let spArr = that.data.spArr
    wx.request({
      url: apiUrl + '/appletgonglue/getVideoDetail',
      data: {
        userid: that.data.userid,
        classid: that.data.vid,
        classtype: 11
      },
      header: { 'content-type': 'application/json' },
      success: function (res) {
        if (res.data.data.videoDetail) {
          for(var i = 0;i < spArr.length;i++){
            if (spArr[i].vid == vid){
              spArr[i].pv++;
            }
          }
          that.setData({
            spArr: spArr
          });
        }
      },
      fail: function () {
        console.log("error!!!");
      }
    })
  },
  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  },
  // 更新scrollContainerHeight的值
  updateScrollHeight: function (size) {
    if (size < 15) {
      this.setData({
        scrollContainerHeight: size * 240
      });
    }
    this.setData({
      isUpdateScrollHeight: false
    });
  },
  // 通过时间来判断点击事件和长按事件
  bindTouchStart: function (e) {
    this.startTime = e.timeStamp;
  },
  bindTouchEnd: function (e) {
    this.endTime = e.timeStamp;
  },
  bindlongtap:function (e) {
    let id = e.currentTarget.dataset.id,
      userId = e.currentTarget.dataset.userid,
      classid = e.currentTarget.dataset.classid,
      that = this;
    wx.showModal({
      title: '提示',
      content: '删除装修公司？',
      success: function (res) {
        if (res.confirm) {
          wx.request({
            url: apiUrl + '/appletcarousel/editcollect',
            data: {
              userid: userId, // 用户ID
              classtype: '9',
              classid: classid
            },
            header: {
              'content-type': 'application/x-www-form-urlencoded'
            },
            success(res) {
              if (res.data.state == 1) {
                wx.request({
                  url: apiUrl + '/appletcarousel/collect',
                  data: {
                    userid: userId,
                    classtype: '9'
                  },
                  header: {
                    'content-type': 'application/x-www-form-urlencoded'
                  },
                  success(res) {
                    let empty = that.data.isEmpty;
                    if (res.data.length > 0) {
                      empty[3] = true;
                      that.setData({ zxgs: res.data, isEmpty: empty })
                    } else {
                      empty[3] = false;
                      that.setData({ zxgs: res.data, isEmpty: empty })
                    }

                    //收藏取消弹框
                    wx.showToast({
                      title: '你已取消收藏',
                      icon: 'success',
                      duration: 1200
                    });
                  },
                  fail: function () {
                    // console.log(111)
                  }
                });
              }
            }
          });
        } else if (res.cancel) {
          console.log('用户点击取消')
        }
      }
    })

    
  },
  toCompanyDetail:function (e) {

    if (this.endTime - this.startTime < 350){
      let id = e ? e.currentTarget.dataset.classid : options.classid;
      let bm = e ? e.currentTarget.dataset.bm : options.bm;
      wx.navigateTo({
        url: '../company_detail/company_detail?id=' + id + '&bm=' + bm + '&classtype=2&userid=' + this.data.userid +'&p=1&pagecount=10'
      })
    }
  },
  toxgtDetail: function (e) {
    let id = e.currentTarget.dataset.id,
      classtype = e.currentTarget.dataset.type,
      pv = e.currentTarget.dataset.pv;

    wx.navigateTo({
      url: '../xgt-detail/index?id=' + id + '&type=' + classtype + '&pv=' + pv
    });
  },
  toPlayDetail: function (e) {
    let classid = e.currentTarget.dataset.classid;
    wx.navigateTo({
      url: '../play_detail/play_detail?id=' + classid
    });
    //将当前查看的文章id缓存起来，页面返回时需要更新该文章的pv以及收藏情况
    wx.setStorage({
      key: 'viewVideoId',
      data: classid,
    })
  },
  lowerXgt:function (e) {
    let that = this;
    let page = this.data.xgtPage;
    // 有数据就请求下一页，无数据就不再请求
    if (that.data.hasXgtData) {
      that.setData({
        xgtPage: ++page
      });
      wx.showLoading({
        title: "加载中"
      })
      // 获取攻略
      wx.request({
        url: apiUrl + '/appletcarousel/collect',
        data: {
          count: '15',
          userid: that.data.userid,
          classtype: '4',
          page: that.data.xgtPage
        },
        dataType: 'json',
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        success(res) {
          that.data.xgt = that.data.xgt.concat(res.data);
          wx.request({
            url: apiUrl + '/appletcarousel/collect',
            data: {
              count: '15',
              userid: that.data.userid,
              classtype: '2',//案例图
              page: that.data.xgtPage
            },
            dataType: 'json',
            header: {
              'content-type': 'application/x-www-form-urlencoded'
            },
            success(res) {
              that.data.isUpdateScrollHeight ? that.updateScrollHeight(res.data.length) : "";
              if (res.data.length > 0 && res.data.length < 15) {
                that.setData({
                  hasXgtData: 0
                });
              } else {
                that.setData({
                  hasXgtData: 1
                });
              }
              that.data.xgt = that.data.xgt.concat(res.data);
              that.setData({ xgt: that.data.xgt })
              wx.hideLoading();
            }
          })
        }
      });
    }
  },
  lowerGonglue:function (e) {
    let that = this;
    let page = this.data.glPage;
    // 有数据就请求下一页，无数据就不再请求
    if (that.data.hasGlData) {
      that.setData({
        glPage: ++page
      });
      wx.showLoading({
        title: "加载中"
      })
      // 获取攻略
      wx.request({
        url: apiUrl + '/appletcarousel/collect',
        data: {
          count: '15',
          userid: that.data.userid,
          classtype: '10',//攻略
          page: that.data.glPage
        },
        dataType: 'json',
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        success(res) {
          that.data.isUpdateScrollHeight ? that.updateScrollHeight(res.data.length) : "";
          if (res.data.length > 0 && res.data.length < 15) {
            that.setData({
              hasGlData: 0
            });
          } else {
            that.setData({
              hasGlData: 1
            });
          }
          that.data.zxgl = that.data.zxgl.concat(res.data);
          that.setData({ zxgl: that.data.zxgl})
          wx.hideLoading();
        }
      });
    }
  },
  lowerVideo:function (e) {
    let that = this;
    let page = this.data.videoPage;
    // 有数据就请求下一页，无数据就不再请求
    if (that.data.hasVideoData) {
      that.setData({
        videoPage: ++page
      });
      wx.showLoading({
        title: "加载中"
      })
      // 获取视频
      wx.request({
        url: apiUrl + '/appletcarousel/collect',
        data: {
          count: '15',
          userid: that.data.userid,
          classtype: '11',//攻略
          page: that.data.videoPage
        },
        dataType: 'json',
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        success(res) {
          that.data.isUpdateScrollHeight ? that.updateScrollHeight(res.data.length) : "";
          if (res.data.length > 0 && res.data.length < 15) {
            that.setData({
              hasVideoData: 0
            });
          } else {
            that.setData({
              hasVideoData: 1
            });
          }
          that.data.spArr = that.data.spArr.concat(res.data);
          that.setData({ spArr: that.data.spArr })
          wx.hideLoading();
        }
      });
    }
  },
  lowerCompany:function (e) {
    let that = this;
    let page = this.data.companyPage;
    // 有数据就请求下一页，无数据就不再请求
    if (that.data.hasCompanyData) {
      that.setData({
        companyPage: ++page
      });
      wx.showLoading({
        title: "加载中"
      })
      // 获取公司
      wx.request({
        url: apiUrl + '/appletcarousel/collect',
        data: {
          count: '15',
          userid: that.data.userid,
          classtype: '9',
          page: that.data.companyPage
        },
        dataType: 'json',
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        success(res) {
          that.data.isUpdateScrollHeight ? that.updateScrollHeight(res.data.length) : "";
          if (res.data.length > 0 && res.data.length < 15) {
            that.setData({
              hasCompanyData: 0
            });
          } else {
            that.setData({
              hasCompanyData: 1
            });
          }
          that.data.zxgs = that.data.zxgs.concat(res.data);
          that.setData({ zxgs: that.data.zxgs })
          wx.hideLoading();
        }
      });
    }
  }
})