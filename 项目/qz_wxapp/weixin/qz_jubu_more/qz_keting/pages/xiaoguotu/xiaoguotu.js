const app = getApp()
let apiUrl = app.getApiUrl(),
  syxgtPX = app.getJubusyxgtpx(),
  apid = app.getAPPid(),
  xcxTitle = app.gettitle(),
  oImgUrl = app.getImgUrl(),
  jubuxgtType = app.getJubuxgtType();
function getLocalTime(nS) {
  return new Date(parseInt(nS) * 1000).toLocaleString().replace(/年|月/g, "-").replace(/日/g, " ").replace('/', '-').replace('/', '-').split(' ')[0];
}
function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
  wx.showModal({
    title: title,
    content: content,
    showCancel: false,
    success: function (res) {
      if (res.confirm) {
        confirm();
      }
    }
  });
}
Page({
    data: {
        syxgtPX: syxgtPX,
        xcxTitle: xcxTitle,
        data: [],//数据
        data2: [],
        house_space: 4,//风格
        house_section: jubuxgtType,//户型
        house_color: 12,//颜色
        page: 1,//当前页码
        tabTxt: ['卫生间', '中式', '纯情白色'],//tab文案
        tab: [true, true, true],
        scrollTop: 0,
        iidex: null,
        filterfengge: null,
        filterjvbu: null,
        filteryanse: null,
        moren: false,
        chushi: 20,
        fgnumber: 20,
        huxingnumber: 20,
        colornumber: 20,
        fgbc: 4,
        hxbc: jubuxgtType, //获取户型数据 如 卫生间 阳台 具体的对应表见说明文档
        ysbc: 12,
        mrshujv: null,
        shoucpanduan: true,
        userInfo: null,
        classtype: null,
        userId: null,
        selectText: '',
        selectTextDefault: '请选择城市',
        valuecity: [0, 0, 0],
        val: [],
        prev: [],
        city: [],
        area: [],
        prevIndex: '0',
        cityIndex: '0',
        areaIndex: '0',
        isHideCity: true,
        personName: '',
        telNumber: '',
        tanchuang: true,
        emptyNameValue: '',
        emptyPhoneValue: '',
        isColor:false,



    },


    xiaoguotuxq: function (e) {
        let id = e.currentTarget.dataset.id,
          fengg = e.currentTarget.dataset.fg,
          yanse = e.currentTarget.dataset.ys,
          leibwsj = e.currentTarget.dataset.wz,
            title = e.currentTarget.dataset.title;
        wx.navigateTo({
          url: '../xiaoguotuxiangqfd/xiaoguotuxiangqfd?id=' + id + '&title=' + title + '&fengg=' + fengg + '&yanse=' + yanse + '&leibwsj=' + leibwsj
        });
    },

    onReady: function (options) {
       let that=this;

       that.getFilter();
       let tabTxtnow = that.data.tabTxt;
       tabTxtnow[0] = that.data.xcxTitle;
       that.setData({
         tabTxt: tabTxtnow
       })
        /**
    * 获取城市缓存数据
    */
        wx.getStorage({
          key: 'cityJson',
          success: function (res) {
            let cityJsonNew = res.data;
            that.setData({ prev: cityJsonNew.prev, city: cityJsonNew.city, area: cityJsonNew.area });
          },
          // 获取缓存失败
          fail: function () {
            // ajax请求数据并且数据本地缓存存储
            wx.request({
              url: apiUrl + '/zxjt/getcityurl',
              header: {
                'content-type': 'application/json'
              },
              success: function (res) {
                cityUrl = res.data.data.replace("/common/js", "");
                let cityUrlArr = cityUrl.split(':');
                let host = cityUrlArr[1].split('.');
                host[0] = host[0] + 's';
                cityUrlArr[1] = host.join('.');
                let cityUrlStr = cityUrlArr[0] + 's:' + cityUrlArr[1] // s:
                wx.request({
                  url: cityUrlStr, // + 'common/js/rlpca20170721143503.js',
                  header: {
                    'content-type': 'application/json'
                  },
                  success: function (res) {
                    let str = res.data.replace("var rlpca = ", "");
                    json = JSON.parse(str), prevJson = [], cityJson = [], areaJson = [];
                    json.shift();
                    // 删除空省份/城市/区域
                    for (let i = 0; i < json.length; i++) {
                      json[i].children.shift()
                      for (var j = 0; j < json[i].children.length; j++) {
                        json[i].children[j].children.shift()
                      }
                    };
                    // 筛选省份名称+id
                    for (let i = 0; i < json.length; i++) {
                      prevJson.push({ id: json[i].id, text: json[i].text });
                    }
                    // 筛选第一省的第一个城市名称+id
                    for (let j = 0; j < json[0].children.length; j++) {
                      cityJson.push({ id: json[0].children[j].id, text: json[0].children[j].text })
                    }
                    // 筛选第一省/第一个城市/第一个区域名称+id
                    for (let k = 0; k < json[0].children[0].children.length; k++) {
                      areaJson.push({ id: json[0].children[0].children[k].id, text: json[0].children[0].children[k].text })
                    }
                    that.setData({ prev: prevJson, city: cityJson, area: areaJson });
                    // 设置缓存
                    wx.setStorage({
                      key: 'cityJson',
                      data: { prev: prevJson, city: cityJson, area: areaJson, json: json },
                    });
                  }
                })
              }
            })
          }
        });


    },


    onShow: function () {

      //初始化数据
      var that = this;
     

      app.getUserInfo(function (res) {
        that.setData({ userId: res.userId });
      })
      console.log(that.data.userId)
      wx.request({
        url: apiUrl + '/appletcarousel/meitujubu',
        data: {
          count: that.data.chushi,
          userid: that.data.userId,
          fengge: that.data.fgbc,
          location: that.data.hxbc,
          color: that.data.ysbc,
          location_wz:that.data.syxgtPX,

        },
        header: {
          'Content-Type': 'application/json'
        },
        success: function (res) {
          console.log(res)
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
    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function () {

    },
    // 选项卡
    filterTab: function (e) {
        var data = [true, true, true], index = e.currentTarget.dataset.index;
        data[index] = !this.data.tab[index];
        this.setData({
            tab: data,
        })
    },
    // 获取筛选项
    getFilter: function () {
        var that = this;
        wx.request({
            url: apiUrl + '/appletcarousel/meitujubu',
            data:{
              fengge: that.data.fgbc,
              userid: that.data.userId,
              location: that.data.hxbc,
              color: that.data.ysbc,
              count: that.data.huxingnumber,
              location_wz: that.data.syxgtPX,
            },
            header: {
                'Content-Type': 'application/json'
            },
            success: function (res) {
                 console.log(res)
                that.setData({
                    filterfengge: res.data.attribute.fengge,
                    // filterhuxing: res.data.attribute.huxing,
                    filterjvbu: res.data.attribute.location,
                    filteryanse: res.data.attribute.color,
                });

            },
            fail: function () {
                // console.log('error!!!!!!!!!!!!!!')
            }
        })
    },


    // 筛选项点击操作
    filter: function (e) {
        var that = this, id = e.currentTarget.dataset.id, iidex = e.currentTarget.dataset.index, txt = e.currentTarget.dataset.txt, tabTxt = this.data.tabTxt;
        that.setData({
            fgnumber: 20,
            // kongnumber: 20,
            huxingnumber: 20,
            colornumber: 20,
        })
        switch (iidex) {
            case '0':
                tabTxt[0] = txt;
                that.setData({
                    house_section: id,
                    page: 1,
                    data: [],
                    tab: [true, true, true],
                    tabTxt: tabTxt,
                    iidex: 0,
                    moren: true,
                    hxbc: id

                });
                wx.request({
                    url: apiUrl + '/appletcarousel/meitujubu',
                    data: {
                        fengge: that.data.fgbc,
                        userid: that.data.userId,
                        // huxing: that.data.kjbc,
                        location: that.data.hxbc,
                        color: that.data.ysbc,
                        count: that.data.huxingnumber,
                        location_wz: that.data.syxgtPX,
                    },
                    header: {
                        'Content-Type': 'application/json'
                    },
                    success: function (res) {
                        console.log(res)
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
                    tab: [true, true, true],
                    tabTxt: tabTxt,
                    iidex: 1,
                    moren: true,
                    fgbc: id
                });
                wx.request({
                    url: apiUrl + '/appletcarousel/meitujubu',
                    data: {
                        fengge: that.data.fgbc,
                        userid: that.data.userId,
                        location: that.data.hxbc,
                        color: that.data.ysbc,
                        location_wz: that.data.syxgtPX,
                        // count: that.data.huxingnumber
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
                    house_color: id,
                    page: 1,
                    data: [],
                    tab: [true, true, true],
                    tabTxt: tabTxt,
                    iidex: 2,
                    moren: true,
                    ysbc: id
                });

                wx.request({
                    url: apiUrl + '/appletcarousel/meitujubu',
                    data: {
                        fengge: that.data.fgbc,
                        userid: that.data.userId,
                        // huxing: that.data.kjbc,
                        location: that.data.hxbc,
                        color: that.data.ysbc,
                        count: that.data.colornumber,
                        location_wz: that.data.syxgtPX,
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


    // //下拉加载
    downLoad: function () {
        wx.showToast({
            title: '加载中...',
            icon: 'loading',
            duration: 2000
        });
        var that = this;
        if (that.data.moren == false) {
            let kka = that.data.chushi + 10
            wx.request({
                url: apiUrl + '/appletcarousel/meitujubu',
                data: {
                    count: kka,
                    userid: that.data.userId,
                    fengge: that.data.fgbc,
                    location: that.data.hxbc,
                    color: that.data.ysbc,
                    location_wz: that.data.syxgtPX,
                },
                header: {
                    'Content-Type': 'application/json'
                },
                success: function (res) {
                    console.log(res)
                    that.setData({
                        data: res.data.info.list,
                        chushi: kka
                    })

                },
                fail: function () {
                    // console.log('error!!!!!!!!!!!!!!')
                }
            })
        }
        if (that.data.iidex == 0) {
            let kkb = that.data.huxingnumber + 10
            wx.request({
                url: apiUrl + '/appletcarousel/meitujubu',

                data: {
                  fengge: that.data.fgbc,
                  location: that.data.hxbc,
                  color: that.data.ysbc,
                  userid: that.data.userId,
                  count: kkb,
                  location_wz: that.data.syxgtPX,
                },
                header: {
                    'Content-Type': 'application/json'
                },
                success: function (res) {

                  if (res.data.info.list < 1) {
                    that.setData({
                      data: that.data.mrshujv,
                      // huxingnumber: kkb,
                      // scrollTop: 0
                    })
                  } else {
                    that.setData({
                      data: res.data.info.list,
                      huxingnumber: kkb,
                      // scrollTop: 0
                    })
                  }


                },
                fail: function () {
                    // console.log('error!!!!!!!!!!!!!!')
                }


            })
        }

        else if (that.data.iidex == 1) {
            let kkd = that.data.fgnumber + 10
            wx.request({
                url: apiUrl + '/appletcarousel/meitujubu',

                data: {
                  fengge: that.data.fgbc,
                  location: that.data.hxbc,
                  color: that.data.ysbc,
                  userid: that.data.userId,
                  count: kkd,
                  location_wz: that.data.syxgtPX,
                },
                header: {
                    'Content-Type': 'application/json'
                },
                success: function (res) {

                  if (res.data.info.list < 1) {
                    that.setData({
                      data: that.data.mrshujv,
                      // fgnumber: kkd,
                      // scrollTop: 0
                    })
                  } else {
                    that.setData({
                      data: res.data.info.list,
                      fgnumber: kkd,
                      // scrollTop: 0
                    })
                  }
                },
                fail: function () {
                    // console.log('error!!!!!!!!!!!!!!')
                }


            })
        }
        else if (that.data.iidex == 2) {
            let kke = that.data.colornumber + 10
            wx.request({
                url: apiUrl + '/appletcarousel/meitujubu',

                data: {
                  fengge: that.data.fgbc,
                  location: that.data.hxbc,
                  color: that.data.ysbc,
                  userid: that.data.userId,
                  count: kke,
                  location_wz: that.data.syxgtPX,
                },
                header: {
                    'Content-Type': 'application/json'
                },
                success: function (res) {
                    // console.log(res)
                    if (res.data.info.list < 1) {
                      that.setData({
                        data: that.data.mrshujv,
                        // scrollTop: 0
                      })
                    } else {
                      that.setData({
                        data: res.data.info.list,
                        colornumber: kke,
                        // scrollTop: 0
                      })
                    }

                },
                fail: function () {
                    // console.log('error!!!!!!!!!!!!!!')
                }


            })
        }



    },


    selectHandle() {
      this.setData({ isHideCity: false });
    },

    /**
   * 城市选择滑动
   */
    bindChange: function (e) {
      let that = this;
      let cityJson = [];
      let areaJson = [];
      let val = e.detail.value;
      let prev = val[0];
      let city = val[1];
      let area = val[2];
      let oldVal = that.data.val;
      that.setData({ val: val })
      wx.getStorage({
        key: 'cityJson',
        success: function (res) {
          let json = res.data.json
          // 滑动省份获取城市
          if (oldVal[0] != prev && oldVal[1] == city && oldVal[2] == area) {
            city = 0; area = 0;
            that.setData({ valuecity: [prev, 0, 0] })
          } else if (oldVal[0] == prev && oldVal[1] != city && oldVal[2] == area) {
            area = 0;
            that.setData({ valuecity: [prev, city, 0] })
          } else if (oldVal[0] == prev && oldVal[1] == city && oldVal[2] != area) {

          }
          for (let j = 0; j < json[prev].children.length; j++) {
            cityJson.push({ id: json[prev].children[j].id, text: json[prev].children[j].text });
          }
          // 滑动城市获取区域
          for (let k = 0; k < json[prev].children[city].children.length; k++) {
            areaJson.push({ id: json[prev].children[city].children[k].id, text: json[prev].children[city].children[k].text });
          }
          that.setData({ city: cityJson, area: areaJson, prevIndex: prev, cityIndex: city, areaIndex: area });
        }
      });

    },
    closebtn() {
      this.setData({ isHideCity: true });
    },
    /**
     * 城市选择
     */
    okbtn() {
      let that = this;
      let prevInfo = that.data.prev;
      let cityInfo = that.data.city;
      let areaInfo = that.data.area;

      let prevId = prevInfo[that.data.prevIndex].id;
      let cityId = cityInfo[that.data.cityIndex].id;
      let areaId = areaInfo[that.data.areaIndex].id;

      let prevText = prevInfo[that.data.prevIndex].text;
      let cityText = cityInfo[that.data.cityIndex].text;
      let areaText = areaInfo[that.data.areaIndex].text;

      let prevCityAreaId = prevId + ',' + cityId + ',' + areaId;
      let selectText = prevText + ' ' + cityText + ' ' + areaText;
      this.setData({ isHideCity: true, isColor: true, selectText: selectText, prevCityAreaId: prevCityAreaId, areaId: areaId, serverVal: areaText, selectTextDefault: '' });
    },


    getName: function (e) {
      this.setData({ personName: e.detail.value });
    },
    getPhone: function (e) {
      this.setData({ telNumber: e.detail.value });
    },
    getSheJi: function () {
      let regu = "^[a-zA-Z\u4e00-\u9fa5]+$";
      let re = new RegExp(regu);

      let that = this;
      if (that.data.selectText.length < 1) {
        that.setData({ selectTextDefault: '请选择城市' })
        alertViewWithCancel("提示", "请选择您的所在地区", function () { });
        return;
      } else {
        that.setData({ selectTextDefault: '' })
      }
      if (that.data.personName.length < 1) {
        alertViewWithCancel("提示", "请输入您的称呼", function () {
          that.setData({ boolName: true });
        });
        return;
      } else if (that.data.personName.search(re) == -1) {
        alertViewWithCancel("提示", "用户名不能为数字", function () {
          that.setData({ boolName: true });
        });
        return;
      }
      if (that.data.telNumber.length < 1) {
        alertViewWithCancel("提示", "请输入手机号", function () { });
        return;
      } else {
        var reg = new RegExp("^(13|14|15|17|18)[0-9]{9}$");
        if (!reg.test(that.data.telNumber)) {
          alertViewWithCancel("提示", "请填写正确的手机号码", function () { });
          return false;
        }
      }

      if (that.data.src) {
        wx.request({

          url: apiUrl + '/zxjt/submit_order_v2/?src=' + that.data.src,
          data: {
            name: that.data.personName,
            tel: that.data.telNumber,
            cs: that.data.prevCityAreaId
          },
          header: {
            'content-type': 'application/x-www-form-urlencoded'
          },
          method: "POST",
          success: function (res) {
            that.setData({
              tanchuang: true,
              emptyNameValue: '',
              emptyPhoneValue: '',
            })
            alertViewWithCancel("提示", "提交成功，请注意接听电话", function () { });
          }
        });
      } else {
        wx.request({
          url: apiUrl + '/zxjt/submit_order_v2/?src=' + app.globalData.sourceMark,
          data: {
            name: that.data.personName,
            tel: that.data.telNumber,
            cs: that.data.prevCityAreaId
          },
          header: {
            'content-type': 'application/x-www-form-urlencoded'
          },
          method: "POST",
          success: function (res) {
            that.setData({
              tanchuang: true,
              emptyNameValue: '',
              emptyPhoneValue: '',
            })
            alertViewWithCancel("提示", "提交成功，请注意接听电话", function () { });
          }
        });
      }

    },

    Guanbi: function () {
      let that = this;
      that.setData({
        tanchuang: true,
        emptyNameValue: '',
        emptyPhoneValue: '',
      })
    },

    Tanchuang: function () {
      let that = this;
      that.setData({
        tanchuang: false,
      })
    },


    scqiehuan: function (e) {
      let that = this,

        id = e.currentTarget.dataset.id,
        index = e.currentTarget.dataset.index;
      if (that.data.userId) {
        wx.request({
          url: apiUrl + '/appletcarousel/editcollect',
          data: {
            userid: that.data.userId,
            classtype: '8', // 装修效果图
            classid: id
          },
          header: {
            'content-type': 'application/x-www-form-urlencoded'
          },
          method: "POST",
          success: function (res) {
            console.log(res)
            if (res.data.state == 1) {
              wx.showToast({
                title: '收藏成功',
                icon: 'success',
                duration: 1200
              });
              let arr = that.data.data;
              arr[index].is_collect = 1;
              that.setData({ data: arr });
            }
          }
        });
      } else {
        app.getLoginAgain(function (e) {
          wx.login({
            success: function (l) {
              console.log(l)
              if (l.code) {
                wx.request({
                  url: apiUrl + '/login',
                  data: {
                    appid: apid,
                    code: l.code,
                    name: e.nickName,
                    logo: e.avatarUrl
                  },
                  header: {
                    'content-type': 'application/x-www-form-urlencoded'
                  },
                  method: "POST",
                  dataType: 'json',
                  success: function (i) {
                    console.log(i)
                    that.setData({ userId: i.data.data })
                    wx.request({
                      url: apiUrl + '/appletcarousel/editcollect',
                      data: {
                        userid: i.data.data,
                        classtype: '8', // 装修效果图
                        classid: id
                      },
                      header: {
                        'content-type': 'application/x-www-form-urlencoded'
                      },
                      method: "POST",
                      success: function (res) {
                        if (res.data.state == 1) {
                          wx.showToast({
                            title: '收藏成功',
                            icon: 'success',
                            duration: 1200
                          });
                          let arr = that.data.data;
                          arr[index].is_collect = 1;
                          that.setData({ data: arr })
                        }
                      }
                    });
                  }
                });
              }
            }
          })
        });
      }
    },

    delscqiehuan: function (e) {
      let that = this,

        id = e.currentTarget.dataset.id,
        index = e.currentTarget.dataset.index;
      if (that.data.userId) {
        wx.request({
          url: apiUrl + '/appletcarousel/editcollect',
          data: {
            userid: that.data.userId,
            classtype: '8', // 装修效果图
            classid: id
          },
          header: {
            'content-type': 'application/x-www-form-urlencoded'
          },
          method: "GET",
          success: function (res) {
            console.log(res)
            if (res.data.state == 1) {
              wx.showToast({
                title: '取消收藏',
                icon: 'success',
                duration: 1200
              });
              let arr = that.data.data;
              arr[index].is_collect = 0;
              that.setData({ data: arr });
            }
          }
        });
      } else {
        app.getLoginAgain(function (e) {
          wx.login({
            success: function (l) {
              console.log(l)
              console.log(l)
              if (l.code) {
                wx.request({
                  url: apiUrl + '/login',
                  data: {
                    appid: apid,
                    code: l.code,
                    name: e.nickName,
                    logo: e.avatarUrl
                  },
                  header: {
                    'content-type': 'application/x-www-form-urlencoded'
                  },
                  method: "GET",
                  dataType: 'json',
                  success: function (i) {
                    console.log(i)
                    that.setData({ userId: i.data.data })
                    wx.request({
                      url: apiUrl + '/appletcarousel/editcollect',
                      data: {
                        userid: i.data.data,
                        classtype: '8', // 装修效果图
                        classid: id
                      },
                      header: {
                        'content-type': 'application/x-www-form-urlencoded'
                      },
                      method: "GET",
                      success: function (res) {
                        if (res.data.state == 1) {
                          wx.showToast({
                            title: '取消收藏',
                            icon: 'success',
                            duration: 1200
                          });
                          let arr = that.data.data;
                          arr[index].is_collect = 0;
                          that.setData({ data: arr })
                        }
                      }
                    });
                  }
                });
              }
            }
          })
        });
      }
    },

    toBaojia: function () {
        wx.navigateTo({
            url: '../zhuangxiubj/zhuangxiubj'
        })
    },
    toSearch: function () {
        wx.navigateTo({
            url: '../searchxgt/searchxgt?id=1'
        })
    }

})