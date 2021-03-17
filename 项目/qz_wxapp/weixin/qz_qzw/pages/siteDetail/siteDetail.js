const app = getApp();
const auth = require('../../utils/util.js');
const raterpx = 750.0 / wx.getSystemInfoSync().windowWidth;
const rate = function(rpx) {
  return rpx / raterpx
};
const zxsApi = app.getZxsApiUrl();
const apiUrl = app.getApiUrl();

function reversePeople(array) {
  let newArr = [];
  for (let i = array.length - 1; i >= 0; i--) {
    newArr[newArr.length] = array[i];
  }
  return newArr;
}

function getLocalTime(nS) {
  Date.prototype.toLocaleString = function() {
    return this.getFullYear() + "年" + (this.getMonth() + 1) + "月" + this.getDate() + "日";
  };
  var d = new Date(nS * 1000);
  var t = d.toLocaleString();
  return t;
}

function getDayTime(nS) {
  Date.prototype.toLocaleString = function() {
    return this.getFullYear() + "-" + (this.getMonth() + 1) + "-" + this.getDate();
  };
  var d = new Date(nS * 1000);
  var t = d.toLocaleString();
  return t;
}
Page({
  data: {
    classify: [{
      name: '开工大吉',
      step: 2
    }, {
      name: '主体拆改',
      step: 3
    }, {
      name: '水电整改',
      step: 4
    }, {
      name: '防水施工',
      step: 5
    }, {
      name: '泥瓦工程',
      step: 6
    }, {
      name: '木工进场',
      step: 7
    }, {
      name: '厨卫吊顶',
      step: 8
    }, {
      name: '油漆粉刷',
      step: 9
    }, {
      name: '铺贴壁纸',
      step: 10
    }, {
      name: '成品安装',
      step: 11
    }, {
      name: '保洁收尾',
      step: 12
    }, {
      name: '家具进场',
      step: 13
    }, {
      name: '家电进场',
      step: 14
    }, {
      name: '家居配饰',
      step: 15
    }, {
      name: '交付工程',
      step: 16
    }, {
      name: '竣工',
      step: 17
    }],
    step: 14,
    flag: false,
    pop: true,
    isCreate: false, //海报字段
    isShow: false,
    wgShow: false, //围观,
    ylShow: false, //有礼 
    checkValue: true,
    adShow: false, //广告,
    adValue: true,
    page: 1,
    nomore: 2,
    infoList: [],
    jwt_token: '',
    live_id: 1,
    is_fans: 0,
    company_id: ''
  },
  // 围观
  wguan(e) {
    let is_fans = e.currentTarget.dataset.fan;
    let that = this;
    let id = that.data.live_info.id
    let token = wx.getStorageSync('jwt_token')
    that.setData({
      wgflag: false
    })

    if (is_fans == 0) {
      wx.getStorage({
        key: 'jwt_token',
        success: function(res) {


          wx.request({
            url: zxsApi + '/business/worksite/user/live_fans',
            method: 'POST',
            data: {
              live_id: id
            },
            header: {
              'content-type': 'application/x-www-form-urlencoded',
              token: token
            },
            success: function(res) {
              if (res.data.error_code == 0) {
                let wxoff = res.data.data.wxofficial_show
                if (wxoff == 1) {
                  that.setData({
                    wgShow: !that.data.wgShow,
                  })
                } else {
                  that.setData({
                    is_fans: 1
                  })
                }
              }
            }
          })
        },
        fail: function(res) {
          wx.redirectTo({
            url: '../login/login?live_id=' + id
          })
        }
      })
    } else {
      wx.showModal({
        title: '提示',
        content: '是否要取消围观？取消后将无法收到该现场的装修动态',
        success(res) {
          if (res.confirm) {
            wx.showLoading({
              title: '取消中',
            })
            wx.getStorage({
              key: 'jwt_token',
              success: function(res) {
                let token = res.data
                wx.request({
                  url: zxsApi + '/business/worksite/user/live_fans',
                  method: 'POST',
                  data: {
                    live_id: that.data.live_id
                  },
                  header: {
                    'content-type': 'application/x-www-form-urlencoded',
                    'token': token
                  },
                  success: function(res) {
                    if (res.data.error_code == 0) {
                      wx.hideLoading()
                      that.setData({
                        is_fans: 0
                      }, function() {
                        wx.showToast({
                          title: '已取消围观',
                          icon: 'none'
                        })
                      })
                    } else {
                      wx.showToast({
                        title: res.data.error_msg,
                        icon: 'none'
                      })
                    }
                  }
                });
              },
              fail: function(res) {
                wx.redirectTo({
                  url: '../login/login?live_id=' + id
                })
              }
            })
          } else if (res.cancel) {}
        }
      })
    }
  },
  reguan(e) {
    let that = this;
    let id = that.data.live_info.id
    wx.getStorage({
      key: 'jwt_token',
      success: function(res) {
        let token = res.data
        wx.request({
          url: zxsApi + '/business/worksite/user/live_fans',
          method: 'POST',
          data: {
            live_id: that.data.live_id
          },
          header: {
            'content-type': 'application/x-www-form-urlencoded',
            'token': token
          },
          success: function(res) {
            if (res.data.error_code == 0) {
              that.setData({
                is_fans: 1
              }, function() {
                wx.showToast({
                  title: '已围观',
                  icon: 'none'
                })
              })
            } else {
              wx.showToast({
                title: res.data.error_msg,
                icon: 'none'
              })
            }
          }
        });
      },
      fail: function(res) {
        wx.redirectTo({
          url: '../login/login?live_id=' + id
        })
      }
    })
  },
  catchwg() {
    this.setData({
      wgShow: false
    })
  },
  stopWg() {},
  // 有礼
  yli() {
    this.setData({
      ylShow: !this.data.ylShow
    })
  },
  catchyl() {
    this.setData({
      ylShow: false
    })
  },
  checkboxChange: function(e) {
    let that = this;
    if (that.data.checkValue == true) {
      that.setData({
        checkValue: false
      })
    } else {
      that.setData({
        checkValue: true
      })
    }
  },
  Phone: function(e) {
    this.setData({
      tel: e.detail.value
    })
  },
  ylFd() {
    let that = this;
    let tel = that.data.tel;
    let check = that.data.checkValue;
    let cs = that.data.live_info.cs;
    let qx = that.data.live_info.qx;
    if (tel == undefined) {
      app.showToast('请输入您的手机号码');
      return;
    } else {
      var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
      if (!reg.test(tel)) {
        app.showToast('请填写正确的手机号码');

        return false;
      }
    }
    if (!check) {
      app.showToast('请勾选我已阅读并同意齐装网的《免责声明》！');
      return;
    }
    wx.showLoading({
      title: '预约中',
    })
    wx.request({
      url: apiUrl + '/fb_order?src=' + app.globalData.sourceMark + '&gdt_vid=' + app.globalData.gdt_vid + "&aid=" + app.globalData.aid
          + "&click_id=" + app.globalData.click_id,
      data: {
        tel: tel,
        cs: cs,
        qx: qx,
        source: 20010846,
        gdt_vid: app.globalData.gdt_vid,
        aid: app.globalData.aid,
        click_id: app.globalData.click_id,
      },
      header: {
        'content-type': 'application/x-www-form-urlencoded',
      },
      method: "POST",
      success: function(res) {
        wx.hideLoading()
        if (res.data.status == 1) {
          that.setData({
            ylShow: false
          }, function() {
            wx.showToast({
              title: '报价成功,稍后客服会与您联系',
              icon: 'none'
            })
          });
        } else {
          app.showToast(res.data.info);
        }
      }
    });

  },
  // 广告发单
  adfd() {
    this.setData({
      adShow: !this.data.adShow
    })
  },
  catchad() {
    this.setData({
      adShow: false
    })
  },
  adChange: function(e) {
    let that = this;
    if (that.data.adValue == true) {
      that.setData({
        adValue: false
      })
    } else {
      that.setData({
        adValue: true
      })
    }
  },
  adPhone: function(e) {
    this.setData({
      adtel: e.detail.value
    })
  },
  adFd() {
    let that = this;
    let tel = that.data.adtel;
    let check = that.data.adValue;
    let cs = that.data.live_info.cs;
    let qx = that.data.live_info.qx;
    if (tel == undefined) {
      app.showToast('请输入您的手机号码');
      return;
    } else {
      var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
      if (!reg.test(tel)) {
        app.showToast('请填写正确的手机号码');

        return false;
      }
    }
    if (!check) {
      app.showToast('请勾选我已阅读并同意齐装网的《免责声明》！');
      return;
    }
    wx.showLoading({
      title: '预约中',
    })
    wx.request({
      url: apiUrl + '/fb_order?src=' + app.globalData.sourceMark + '&gdt_vid=' + app.globalData.gdt_vid + "&aid=" + app.globalData.aid
          + "&click_id=" + app.globalData.click_id,
      data: {
        tel: tel,
        cs: cs,
        qx: qx,
        source: 20010846,
        gdt_vid: app.globalData.gdt_vid,
        aid: app.globalData.aid,
        click_id: app.globalData.click_id,
      },
      header: {
        'content-type': 'application/x-www-form-urlencoded',
      },
      method: "POST",
      success: function(res) {
        wx.hideLoading()
        if (res.data.status == 1) {
          that.setData({
            adShow: false
          }, function() {
            wx.showToast({
              title: '预约成功,稍后客服会与您联系',
              icon: 'none'
            })
          });
        } else {
          app.showToast(res.data.info);
        }
      }
    });

  },

  // 分享
  share: function() {
    this.setData({
      flag: !this.data.flag
    })
  },
  pHb: function() {
    this.setData({
      flag: false
    })
    app.showLoading('正在生成...')
    const writing = this.data.writing
    // console.log(writing.code, this.data.wgCode)
    // writing.code = this.data.wgCode
    this.draw('poster', 600, 822, writing).then(res => {
      setTimeout(() => {
        app.hideLoading();
        this.setData({
          isCreate: true,
          isShow: true
        })
      }, 100)
    }, err => {
      setTimeout(() => {
        app.hideLoading();
        app.showToast('生成海报失败');
      }, 300)
    })
  },
  catchtap: function(callback) {
    this.setData({
      isShow: false
    })
    setTimeout(() => {
      this.setData({
        isCreate: false
      })
      if (callback && typeof callback == "function") {
        callback();
      }
    }, 400)
  },
  drawText(options) {
    var allRow = Math.ceil(options.ctx.measureText(options.str).width / options.maxWidth);
    var count = allRow >= options.maxLine ? options.maxLine : allRow,
      endPos = 0;
    options.ctx.setFillStyle(options.style ? options.style : '#000000');
    options.ctx.setFontSize(options.fontSize ? options.fontSize : rate(20));
    for (var j = 0; j < count; j++) {
      var nowStr = options.str.slice(endPos),
        rowWid = 0,
        y = options.y + (count == 1 ? 0 : j * options.height);
      if (options.ctx.measureText(nowStr).width > options.maxWidth) {
        for (var m = 0; m < nowStr.length; m++) {
          rowWid += options.ctx.measureText(nowStr[m]).width;
          if (rowWid > options.maxWidth) {
            if (j === options.maxLine - 1) {
              options.ctx.fillText(nowStr.slice(0, m - 1) + '...', options.x, y);
            } else {
              options.ctx.fillText(nowStr.slice(0, m), options.x, y);
            }
            endPos += m;
            break;
          }
        }
      } else {
        options.ctx.fillText(nowStr.slice(0), options.x, y);
      }
    }
    if (options.bold) {
      options.ctx.font = 'normal bold 40px sans-serif';
    }
  },
  roundRectColor(context, x, y, w, h, r) {
    context.save();
    context.setFillStyle("#F5F5F5");
    context.setStrokeStyle('#F5F5F5')
    context.setLineJoin('round');
    context.setLineWidth(r);
    context.strokeRect(x + r / 2, y + r / 2, w - r, h - r);
    context.fillRect(x + r, y + r, w - r * 2, h - r * 2);
    context.stroke();
    context.closePath();
  },
  draw: function(canvas, cavW, cavH, writing) {
    return new Promise((resolve, reject) => {
      if (!writing || !canvas) {
        reject();
        return;
      }
      var ctx = wx.createCanvasContext(canvas);
      ctx.clearRect(0, 0, rate(cavW), rate(cavH));
      let promise1 = new Promise(function(resolve, reject) {
        wx.getImageInfo({
          src: writing.bigImage,
          success: function(res) {
            resolve(res.path);
          },
          fail: function(err) {
            reject(err);
          }
        })
      });
      let promise2 = new Promise(function(resolve, reject) {
        if (writing.avatar == '' || !writing.avatar) {
          resolve('');
          return;
        }
        wx.getImageInfo({
          src: writing.avatar,
          success: function(res) {
            resolve(res.path);
          },
          fail: function(fail) {
            resolve('');
          }
        })
      });
      let promise3 = new Promise(function(resolve, reject) {
        wx.getImageInfo({
          src: writing.code,
          success: function(res) {
            resolve(res.path);
          },
          fail: function(err) {
            reject(err);
          }
        })
      });
      Promise.all(
        [promise1, promise2, promise3]
      ).then(res => {
        ctx.setFillStyle('white');
        ctx.fillRect(0, 0, rate(cavW), rate(cavH));
        ctx.drawImage(res[0], 0, 0, rate(600), rate(338));
        this.drawText({
          ctx: ctx,
          str: writing.site,
          maxLine: 1,
          maxWidth: rate(560),
          x: rate(29),
          y: rate(440),
          height: rate(36),
          style: '#999999'
        })
        ctx.drawImage(res[1], rate(20), rate(455), rate(560), rate(120));
        if (writing.hx.length > 6) {
          this.drawText({
            ctx: ctx,
            str: '户型',
            maxWidth: rate(150),
            maxLine: 1,
            x: rate(44),
            y: rate(506),
            fontSize: rate(22),
            style: '#333333',
            height: rate(36),
          })
        } else {
          this.drawText({
            ctx: ctx,
            str: '户型',
            maxWidth: rate(120),
            maxLine: 1,
            x: rate(80),
            y: rate(506),
            fontSize: rate(22),
            style: '#333333',
            height: rate(36),
          })
        }
        this.drawText({
          ctx: ctx,
          str: '面积',
          maxWidth: rate(120),
          maxLine: 1,
          x: rate(204),
          y: rate(506),
          fontSize: rate(22),
          style: '#333333',
          height: rate(36),
        })
        this.drawText({
          ctx: ctx,
          str: '风格',
          maxWidth: rate(120),
          maxLine: 1,
          x: rate(320),
          y: rate(506),
          fontSize: rate(22),
          style: '#333333',
          height: rate(36),
        })
        this.drawText({
          ctx: ctx,
          str: '造价',
          maxWidth: rate(120),
          maxLine: 1,
          x: rate(458),
          y: rate(506),
          fontSize: rate(22),
          style: '#333333',
          height: rate(36),
        })

        ctx.drawImage(res[2], rate(29), rate(600), rate(80), rate(80));
        this.drawText({
          ctx: ctx,
          str: "识别二维码查看现场详情",
          maxWidth: rate(300),
          x: rate(128),
          y: rate(628),
          fontSize: rate(24),
          style: '#666666'
        })
        this.drawText({
          ctx: ctx,
          str: writing.form,
          maxWidth: rate(460),
          maxLine: 1,
          x: rate(128),
          y: rate(668),
          fontSize: rate(24),
          style: '#000000',
          height: rate(36),
        })
        this.roundRectColor(ctx, rate(30), rate(705), rate(540), rate(98), rate(8));
        this.drawText({
          ctx: ctx,
          str: '邀请好友和家人一起围观装修现场实时跟进装修进度，把关装修质量',
          maxWidth: rate(344),
          maxLine: 2,
          x: rate(128),
          y: rate(744),
          fontSize: rate(22),
          style: '#999999',
          height: rate(36),
        })
        this.drawText({
          ctx: ctx,
          str: writing.name,
          maxLine: 1,
          maxWidth: rate(560),
          x: rate(29),
          y: rate(400),
          height: rate(36),
          fontSize: rate(34),
          style: '#000000',
          bold: true
        })
        if (writing.hx.length == 8) {
          this.drawText({
            ctx: ctx,
            str: writing.hx,
            maxWidth: rate(150),
            maxLine: 1,
            x: rate(44),
            y: rate(546),
            fontSize: rate(24),
            style: '#FF5353',
            height: rate(36),
          })
        } else {
          this.drawText({
            ctx: ctx,
            str: writing.hx,
            maxWidth: rate(120),
            maxLine: 1,
            x: rate(80),
            y: rate(546),
            fontSize: rate(24),
            style: '#FF5353',
            height: rate(36),
          })
        }

        this.drawText({
          ctx: ctx,
          str: writing.mj,
          maxWidth: rate(120),
          maxLine: 1,
          x: rate(204),
          y: rate(546),
          fontSize: rate(24),
          style: '#FF5353',
          height: rate(36),
        })
        this.drawText({
          ctx: ctx,
          str: writing.fg,
          maxWidth: rate(120),
          maxLine: 1,
          x: rate(320),
          y: rate(546),
          fontSize: rate(24),
          style: '#FF5353',
          height: rate(36),
        })
        this.drawText({
          ctx: ctx,
          str: writing.zj,
          maxWidth: rate(120),
          maxLine: 1,
          x: rate(458),
          y: rate(546),
          fontSize: rate(24),
          style: '#FF5353',
          height: rate(36),
        })
        ctx.draw(false, () => {
          wx.canvasToTempFilePath({
            canvasId: 'poster',
            fileType: 'png',
            success: res => {
              this.setData({
                poster: res.tempFilePath
              })
              resolve();
            },
            fail: err => {
              reject();
            }
          })
        });
      }, err => {
        reject();
      })
    })
  },
  btnCreate: function(obj) {
    app.showLoading('正在保存...')
    wx.saveImageToPhotosAlbum({
      filePath: this.data.poster,
      success: res => {
        app.hideLoading();
        this.catchtap(() => {
          wx.showToast({
            title: '保存成功'
          })
        });
      },
      fail: err => {
        app.hideLoading();
        this.catchtap(() => {
          wx.showToast({
            title: '保存失败',
            icon: 'none'
          })
        });
      }
    });
  },
  onLoad: function(options) {
    let live_id = options.live_id ? options.live_id : 1
    let actfrom = options.actfrom ? options.actfrom : ''
    let that = this
    let token = wx.getStorageSync('jwt_token')
    that.setData({
      jwt_token: token,
      live_id: live_id,
      actfrom: actfrom
    })
    that.getDetail()
    that.getCode()
  },
  onShow: function() {
    let that = this;
    let sign_one = (Date.parse(new Date())) / 1000,
      miceA = (new Date(getDayTime(sign_one))) / 1000,
      miceB = (new Date(getDayTime(sign_one))) / 1000 + 86400;
    let lastDay = wx.getStorageSync('day');
    if (lastDay < miceA) {
      wx.removeStorageSync('day')
    }
    if (lastDay) {
      if (lastDay == sign_one) {
        that.setData({
          wgflag: true
        })
      } else {
        that.setData({
          wgflag: false
        })
      }
    } else {
      //用户首次打开
      that.setData({
        wgflag: true
      })
      wx.setStorage({
        key: "day",
        data: sign_one
      })
    }
  },
  // 登陆后
  codeIf(token) {
    let that = this;
    let id = that.data.live_id
    wx.request({
      url: zxsApi + '/business/worksite/user/live_fans',
      method: 'POST',
      data: {
        live_id: id
      },
      header: {
        'content-type': 'application/x-www-form-urlencoded',
        token: token
      },
      success: function(res) {
        if (res.data.error_code == 0) {
          that.setData({
            wxoff: res.data.data.wxofficial_show
          })
        }
      }
    })
  },
  // 关闭第一次围观阴影
  wgBg() {
    this.setData({
      wgflag: false
    })
  },
  getCode() {
    let that = this;

    wx.request({
      url: zxsApi + '/business/worksite/applet_user/live_wxqrcode',
      method: 'POST',
      data: {
        live_id: that.data.live_id
      },
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      success: function(res) {
        if (res.data.error_code == 0) {
          that.setData({
            wgCode: res.data.data.url
          })
        }
      }
    });
  },
  getDetail() {
    let obj = this.data;
    let that = this

    if (obj.nomore != 2) {
      return
    }
    that.setData({
      nomore: 1
    })
    if (obj.page == 1) {
      wx.showLoading({
        title: '加载中',
      })
    }

    wx.request({
      url: zxsApi + '/business/worksite/applet_user/live_detail',
      data: {
        live_id: that.data.live_id,
        page: that.data.page,
        limit: 3,
      },
      header: {
        'token': that.data.jwt_token
      },
      success(res) {
        let datas = res.data;
        let infoTotal = datas.data.info.page.total_number;
        if (obj.page == 1) {
          wx.hideLoading()
          that.setData({
            infoList: []
          })
        }
        if (datas.error_code == 0) {
          let live_info = datas.data.live_info
          let owner = (live_info.yz_name).substring(0, 1)
          let sex = live_info.sex ? live_info.sex : '先生'
          let recomTotal = datas.data.recommend_total_number
          that.setData({
            company_id: live_info.company_id,
            owner: owner + sex,
            recomTotal: recomTotal
          })
          let step = datas.data.last_step.step;
          let recommend = datas.data.recommend;

          let infoList = that.data.infoList;
          infoList = infoList.concat(datas.data.info.list);

          infoList.forEach((item, index) => {
            if (item.media_list) {
              item.meiaLen = item.media_list.length
            }
          })
          recommend.forEach((item, index) => {
            if (item.fans) {
              item.fanLen = item.fans.length
              item.fans = reversePeople((item.fans).slice(0, 6))
            }
            if (item.media_list) {
              item.meiaLen = item.media_list.length
            }
            let sex = item.sex ? item.sex : '先生'
            item.owner = item.yz_name.substring(0, 1) + sex
          })
          if (datas.data.live_info.fans) {
            let fans = reversePeople((datas.data.live_info.fans).slice(0, 10))
            let fansLen = datas.data.live_info.fans.length
            that.setData({
              fans: fans,
              fansLen: fansLen
            })
          }
          let huName = live_info.order_type == 2 ? (live_info.huxing_other_name != null ? live_info.huxing_other_name : '实用户型') : (live_info.huxing_name != null ? live_info.huxing_name : '实用户型')
          let qrcode_url = zxsApi + '/' + live_info.qrcode_url
          that.setData({
            live_info: live_info,
            //粉丝
            is_fans: live_info.is_fans,
            // 海报字段
            writing: {
              bigImage: 'http://staticqn.qizuang.com/custom/20200109/FtcEr5gEGfUEyPJvO7M56jZ3ItgQ',
              code: qrcode_url,
              avatar: 'http://staticqn.qizuang.com/custom/20200110/FheDAQZZijMpvZKDXP8MSA-Fnonx',
              name: owner + sex + '新家的装修现场',
              site: live_info.city_name + '  ' + live_info.area_name + '  ' + live_info.xiaoqu,
              form: '来自【' + live_info.jc + '】',
              hx: huName,
              mj: live_info.mianji ? live_info.mianji + '㎡' : '96' + '㎡',
              fg: live_info.fengge_name ? live_info.fengge_name : '宜家宜居',
              zj: live_info.qiandan_jine + '万'
            },
            step: step,
            // 推荐字段
            recommend: recommend,
            // 动态
            infoList: infoList,
            infoTotal: infoTotal,
            recomLen: recommend.length
          })
          if (that.data.actfrom == 1 && live_info.is_fans == 0) {
            that.reguan()
          }
          if (datas.data.info.list.length < 3) {
            that.setData({
              nomore: 3
            })
          } else {
            that.setData({
              nomore: 2,
              page: that.data.page + 1
            })
          }
        } else {
          that.setData({
            nomore: 3
          })
          wx.showToast({
            title: res.data.error_msg,
            icon: 'none'
          })
        }
      }

    })
  },
  moreState() {
    this.getDetail()
  },
  // 点赞
  doLike(e) {
    let id = e.currentTarget.dataset.id
    let like = e.currentTarget.dataset.like

    if (like == 0) {
      let that = this;
      let live_id = that.data.live_info.id
      wx.getStorage({
        key: 'jwt_token',
        success: function(res) {
          let token = res.data
          wx.request({
            url: zxsApi + '/business/worksite/user/info_like',
            method: 'POST',
            data: {
              info_id: id
            },
            header: {
              'content-type': 'application/x-www-form-urlencoded',
              'token': token
            },
            success: function(res) {
              if (res.data.error_code == 0) {
                let infoList = that.data.infoList
                infoList.forEach((item, index) => {
                  if (item.id == id) {
                    item.is_like = 1
                    item.like = item.like + 1
                  }
                })
                that.setData({
                  infoList: infoList
                })
              } else {
                wx.showToast({
                  title: res.data.error_msg,
                  icon: 'none'
                })
              }
            }
          });
        },
        fail: function(res) {
          wx.redirectTo({
            url: '../login/login?live_id=' + live_id
          })
        }
      })
    } else {
      wx.showToast({
        title: '你已经点过赞了喔',
        icon: 'none'
      })
    }

  },
  videoClick(e) {
    let url = e.currentTarget.dataset.url
    wx.navigateTo({
      url: '../siteVideo/siteVideo?url=' + url
    });
  },
  dllClick(e) {
    let id = e.currentTarget.dataset.id
    let tid = e.currentTarget.dataset.tid
    let that = this
    let infoList = that.data.infoList
    var imglist = infoList.find(function(imglist) {
      return imglist.id == id
    })
    let swiperData = imglist.media_list
    let startNum = swiperData[0].id
    let currentNum = (tid - startNum) + 1

    that.setData({
      swiperData: swiperData,
      swiperLen: swiperData.length,
      currentIndex: currentNum,
      largeView: !that.data.largeView
    })
  },
  tuClick(e) {
    let id = e.currentTarget.dataset.id
    let tid = e.currentTarget.dataset.tid
    let that = this
    let recommend = that.data.recommend
    var imglist = recommend.find(function(imglist) {
      return imglist.id == id
    })
    let swiperData = imglist.media_list
    let startNum = swiperData[0].id
    let currentNum = (tid - startNum) + 1

    that.setData({
      swiperData: swiperData,
      swiperLen: swiperData.length,
      currentIndex: currentNum,
      largeView: !that.data.largeView
    })
  },
  getLargeImage() {
    this.setData({
      largeView: !this.data.largeView
    })
  },
  swiperChange(e) {
    this.setData({
      currentIndex: e.detail.current + 1
    })
  },
  onShareAppMessage: function(res) {
    this.setData({
      flag: false
    })
  },
  // 新增
  toExample: function(e) {
    let id = this.data.company_id;
    wx.navigateTo({
      url: '../allSite/allSite?id=' + id
    });
  },
  toSitedetail(e) {
    let id = e.currentTarget.dataset.id
    wx.navigateTo({
      url: '../siteDetail/siteDetail?live_id=' + id
    });
  }
})