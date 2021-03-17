import {
  getQiniuToken,
  getImg,
  getInfodetail,
  pastDetail
} from "../../utils/api.js"
import * as qiniu from '../../utils/qiniu.min.js'
const qiniuUploader = require("../../utils/qiniuUploader")
const app = getApp();
Page({
  data: {
    qiniutoken: '',
    imageObject: null,
    stage: [{
        id: '',
        name: '选择施工进行阶段'
      },
      {
        id: 2,
        name: '开工大吉'
      },
      {
        id: 3,
        name: '主体拆改'
      },
      {
        id: 4,
        name: '水电整改'
      },
      {
        id: 5,
        name: '防水施工'
      },
      {
        id: 6,
        name: '泥瓦工程'
      },
      {
        id: 7,
        name: '木工进场'
      },
      {
        id: 8,
        name: '厨卫吊顶'
      },
      {
        id: 9,
        name: '油漆粉刷'
      },
      {
        id: 10,
        name: '铺贴壁纸'
      },
      {
        id: 11,
        name: '成品安装'
      },
      {
        id: 12,
        name: '保洁收尾'
      },
      {
        id: 13,
        name: '家具进场'
      },
      {
        id: 14,
        name: '家电进场'
      },
      {
        id: 15,
        name: '家居配饰'
      },
      {
        id: 16,
        name: '交付工程'
      }
    ],
    is_sIndex: 0,
    zhen_index: 0,
    isHidePlaceholder: false,
    imgs: [], //本地图片地址数组
    pics: [],
    apics: [],
    plen: 0,
    tuUrl: '',
    fullScreen: false,
    infoVideo: true,
    parms: {
      imgs: [],
      video: '',
      step: '',
      content: '',
      id: '',
      live_id: '',
      media_type: ''
    },
    id: '',
    live_id: '',
    content: '',
    isClicked: false
  },
  // 选择会员类型
  selectMemberType: function(e) {
    let id = this.data.stage[e.detail.value].id
    let zhenid = e.detail.value
    let that = this
    that.setData({
      is_sIndex: id,
      zhen_index: zhenid,
      ['parms.step']: id
    })
  },
  // textarea 输入时触发
  getTextareaInput: function(e) {
    var that = this;
    if (e.detail.cursor > 0) {
      that.setData({
        isHidePlaceholder: true
      })
    } else {
      that.setData({
        isHidePlaceholder: false
      })
    }
    let content = e.detail.value
    that.setData({
      ['parms.content']: content
    })
  },

  //添加上传图片
  chooseImageTap: function() {
    var that = this;
    let plen = that.data.plen
    if (plen >= 9) {
      wx.showToast({
        title: '图片最多上传9张',
        icon: 'none',
        duration: 2000
      })
      return;
    }
    wx.showActionSheet({
      itemList: ['从相册中选择', '拍照'],
      itemColor: "#00000",
      success: function(res) {
        if (!res.cancel) {
          if (res.tapIndex == 0) {
            that.chooseWxImage('album')
          } else if (res.tapIndex == 1) {
            that.chooseWxImage('camera')
          }
        }
      }
    })
  },
  // 图片本地路径
  chooseWxImage: function(type) {
    let that = this;
    let apics = that.data.apics;
    wx.chooseImage({
      sizeType: ['original', 'compressed'],
      sourceType: [type],
      success: function(res) {
        let imgsrc = res.tempFilePaths;
        let jielen = 9 - Number(apics.length)　
        let slicePics = imgsrc.slice(0, jielen);
        wx.getStorage({
          key: 'info',
          success: function(res) {
            wx.showLoading({
              title: '上传中',
            })
            let token = res.data.token;
            for (var i = 0; i < slicePics.length; i++) {
              wx.uploadFile({
                url: 'https://zxs.api.qizuang.com' + '/business/worksite/applet/upload_img',
                header: {
                  token: token,
                  'content-type': 'application/x-www-form-urlencoded'
                },
                filePath: slicePics[i],
                name: 'file',
                formData: {
                  'prefix': 'sale_report',
                },
                success: function(res) {
                  var data = JSON.parse(res.data)
                  that.data.parms.imgs.push(data.data.img_path)
                  that.setData({
                    ['parms.media_type']: 1
                  })
                  let apics = that.data.parms.imgs
                  let plen = apics.length
                  that.setData({
                    apics: apics,
                    plen: plen
                  })
                  setTimeout(function () {
                    wx.hideLoading()
                  }, 1000)
                }
              })
            }
          }
        })
      }
    })
  },
  // 删除图片
  deleteImg: function(e) {
    var apics = this.data.apics;
    var imgs = this.data.parms.imgs;
    var index = e.currentTarget.dataset.index;
    imgs.splice(index, 1);
    this.setData({
      apics: imgs,
      ['parms.imgs']: imgs,
      plen: imgs.length
    })
  },
  // 预览图片
  previewImg: function(e) {
    //获取当前图片的下标
    var index = e.currentTarget.dataset.index;
    //所有图片
    var apics = this.data.apics;
    let arrpics = []
    apics.forEach((item, index)=>{
      item = 'https://zxsqn.qizuang.com/'+item
      arrpics.push(item)
    })
    wx.previewImage({
      //当前显示图片
      current: arrpics[index],
      //所有图片
      urls: arrpics
    })
  },

  // 上传视频(开始)
  chooseVideoTap: function() {
    var that = this;
    wx.showActionSheet({
      itemList: ['从相册中选择', '拍摄'],
      itemColor: "#00000",
      success: function(res) {
        if (!res.cancel) {
          if (res.tapIndex == 0) {
            that.uploadVideo('album')
          } else if (res.tapIndex == 1) {
            that.uploadVideo('camera')
          }
        }
      }
    })
  },
  // 上传视频
  uploadVideo: function(type) {
    this.initQiniu()
    var me = this;
    wx.chooseVideo({
      sourceType: [type],
      maxDuration: 60,
      camera: 'back',
      success(res) {
        wx.showLoading({
          title: '上传中',
        })
        var duration = res.duration;
        var tmpheight = res.height;
        var tmpwidth = res.width;
        var tmpVideoUrl = res.tempFilePath;
        var tmpCoverUrl = res.thumbTempFilePath;
        if (duration > 60) {
          wx.showToast({
            title: '视频不能超过60秒',
            icon: "none",
            duration: 2500
          })
        } else if (duration < 1) {
          wx.showToast({
            title: '视频长度不能小于1秒...',
            icon: "none",
            duration: 2500
          })
        } else {
          qiniuUploader.upload(tmpVideoUrl, (res) => {
              let vUrl = res.fileUrl
              let xUrl = 'https://video.qizuang.com/' + vUrl
              me.setData({
                videoUrl: xUrl,
                ['parms.video']: vUrl,
                ['parms.media_type']: 2,
                infoVideo: false
              });
              wx.hideLoading()
            }, (error) => {
              wx.showToast({
                title: '上传视频失败',
                icon: 'none',
                duration: 2000
              })
            },
            null,
            (progress) => {}, cancelTask => me.setData({
              cancelTask
            })
          );
        }
      }
    })
  },
  /**播放视屏 */
  play(e) {
    var videoContext = wx.createVideoContext('myvideo', this);
    videoContext.requestFullScreen();
    this.setData({
      fullScreen: true
    })
  },
  closeVideo() {
    var videoContext = wx.createVideoContext('myvideo', this);
    videoContext.exitFullScreen();
  },
  fullScreen(e) {
    var isFull = e.detail.fullScreen;
    this.setData({
      fullScreen: isFull
    })
  },
  deleteVideo() {
    this.setData({
      infoVideo: true,
      ['parms.video']: ''
    })
  },
  // 获取本地token
  getToken() {
    let that = this;
    wx.getStorage({
      key: 'info',
      success(res) {
        let token = res.data.token;
        getQiniuToken('/business/worksite/applet/video_token', {}, {
          'content-type': 'application/x-www-form-urlencoded',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            let qiniutoken = res.data
            that.setData({
              qiniutoken: qiniutoken
            })
          }
        })
      },
      fail: function() {
        // 再次执行获取token操作
        wx.showToast({
          title: '暂无登陆信息,请登陆',
          icon: 'none',
          duration: 1000,
          success: function() {
            setTimeout(function() {
              wx.navigateTo({
                url: '../login/login',
              })
            }, 2000);
          }
        })

      }
    })
  },
  // 初始化七牛相关参数
  initQiniu() {
    var options = {
      region: 'ECN',
      uptoken: this.data.qiniutoken,
      shouldUseQiniuFileName: false
    };
    qiniuUploader.init(options);
  },

  getInfodetail() {
    let that = this;
    let id = that.data.id
    let live_id = that.data.live_id
    wx.getStorage({
      key: 'info',
      success(res) {
        let token = res.data.token;
        getInfodetail('/business/worksite/applet/info_detail', {
          id: live_id
        }, {
          'content-type': 'application/x-www-form-urlencoded',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            if (res.data.info) {
              let info = res.data.info
              let media = info.media_list
              let xpics = [],
                ximgs = [],
                xvideoUrl = '',
                type = media[0].type ? media[0].type : '';
              if (type == 1) {
                media.forEach((item, index) => {
                  ximgs.push(item.url)
                  xpics.push(item.url)
                })
                that.setData({
                  is_sIndex: info.step,
                  content: info.content,
                  isHidePlaceholder: true,
                  apics: xpics,
                  plen: xpics.length,
                  ['parms.imgs']: ximgs,
                  ['parms.step']: info.step,
                  ['parms.content']: info.content,
                  ['parms.media_type']: type
                })
              }
              if (type == 2) {
                xvideoUrl = 'https://video.qizuang.com/' + media[0].url
                that.setData({
                  is_sIndex: info.step,
                  content: info.content,
                  isHidePlaceholder: true,
                  videoUrl: xvideoUrl,
                  infoVideo: false,
                  ['parms.video']: media[0].url,
                  ['parms.step']: info.step,
                  ['parms.content']: info.content,
                  ['parms.media_type']: type
                })
              }

            }
          } else {
            wx.showToast({
              title: res.error_msg,
              icon: 'none',
              duration: 1000
            })
          }
        })
      },
      fail: function() {
        wx.showToast({
          title: res.error_msg,
          icon: 'none',
          duration: 1000,
          success: function() {
            setTimeout(function() {
              wx.navigateTo({
                url: '../login/login',
              })
            }, 2000);
          }
        })
      }
    })
  },
  publish() {
    let that = this;
    let parms = that.data.parms
    if (parms.step == '') {
      wx.showToast({
        title: '请选择施工阶段',
        icon: 'none',
        duration: 2000
      })
      return
    }
    if (parms.content == '') {
      wx.showToast({
        title: '请填写施工描述',
        icon: 'none',
        duration: 2000
      })
      return
    }
    if (parms.imgs.length == 0 && parms.video == '') {
      wx.showToast({
        title: '请上传施工现场照片或视频',
        icon: 'none',
        duration: 2000
      })
      return
    }
    let xparms = {}
    if (parms.video == '') {
      xparms.media_url = parms.imgs
      xparms.content = parms.content
      xparms.live_id = parms.id
      xparms.id = parms.live_id
      xparms.step = parms.step
      xparms.media_type = parms.media_type
    } else {
      let str = parms.video
      xparms.media_url = str.split(",");
      xparms.content = parms.content
      xparms.live_id = parms.id
      xparms.id = parms.live_id
      xparms.step = parms.step
      xparms.media_type = parms.media_type
    }
    let allowLen = (that.data.apics).length
    if (allowLen > 0) {
      if (xparms.media_url.length == allowLen) {
        that.setData({
          isClicked: true
        })
      } else {
        wx.showToast({
          title: '正在上传',
          icon: 'none'
        })
        return false
      }
    } else {
      that.setData({
        isClicked: true
      })
    }
    wx.getStorage({
      key: 'info',
      success(res) {
        let token = res.data.token;
        pastDetail('/business/worksite/applet/info_save', xparms, {
          'content-type': 'application/x-www-form-urlencoded',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            let id = that.data.id
            wx.showToast({
              title: '发布成功',
              icon: 'none',
              duration: 2000
            })
            setTimeout(function() {
              wx.navigateBack({
                url: '../sgdetail/sgdetail?id=' + id,
              })
            }, 2000);
          } else {
            wx.showToast({
              title: res.error_msg,
              icon: 'none',
              duration: 2000
            })
          }
        })
      },
      fail: function() {
        wx.showToast({
          title: res.error_msg,
          icon: 'none',
          duration: 1000,
          success: function() {
            setTimeout(function() {
              wx.navigateTo({
                url: '../login/login',
              })
            }, 2000);
          }
        })
      }
    })
  },
  onLoad: function(options) {
    let id = options.id ? options.id : ''
    let live_id = options.live_id ? options.live_id : ''
    if (live_id > 0) {
      wx.setNavigationBarTitle({
        title: '修改施工信息'
      })
    }
    this.setData({
      ['parms.id']: id,
      ['parms.live_id']: live_id,
      id: id,
      live_id: live_id
    })
    this.getToken()
    this.getInfodetail()

    let phone = wx.getSystemInfoSync();
    let that = this;
    let system = (phone.system).slice(0, 3)
    if (system == 'iOS') {
      that.setData({
        detailcs: true
      });
    } else if (phone.platform == 'And') {
      that.setData({
        detailcs: false
      });

    }
  },
  onReady: function() {

  },
  onShow: function() {

  },
  playVideo: function() {
    let that = this;
    that.videoContext = wx.createVideoContext('myVideo');
    that.videoContext.play();
  },
})