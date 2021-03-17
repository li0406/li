//index.js
import { getUser, getMywork, miniDetails } from "../../utils/api.js"
import { STATIC_HOST } from '../../utils/config.js'
const app = getApp();
Page({
  data: {
    second_name: '',
    three_name: '',
    deptName: '',
    roleName: '',
    user: '',
    list: [],
    status: '',
    miniList:[]
  },
  //事件处理函数
  onLoad: function () {
    let that = this;
    let userInfo = wx.getStorageSync('info');
    let token = userInfo.token;
    app.globalData.token = token;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getUser('/v1/user/info', {}, {
          'content-type': 'application/x-www-form-urlencoded',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            let deptName = res.data.info.dept_name;
            let secondName = res.data.info.second_name;
            let threeName = res.data.info.three_name;
            if (threeName == null) {
              threeName = ''
            } else {
              threeName = threeName + '/'
            }
            if (secondName == null) {
              secondName = ''
            } else {
              secondName = secondName + '/'
            }
            if (deptName == null) {
              deptName = '齐装网'
            }

            that.setData({
              second_name: secondName,
              three_name: threeName,
              deptName: deptName,
              roleName: res.data.info.role_name,
              user: res.data.info.user
            })

          }
        })
        getMywork('/v1/user/job', {}, {
          'content-type': 'application/x-www-form-urlencoded',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            that.setData({
              list: res.data.list
            })
          }
        })
      },
      fail: function () {
        wx.redirectTo({
          url: '../login/login',
        })
      }
    })
  },
  onShow: function () {
    let that = this;
    that.miniDetails();
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getMywork('/v1/user/job', {}, {
          'content-type': 'application/x-www-form-urlencoded',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            that.setData({
              list: res.data.list
            })
          }
        })
      },
      fail: function () {
        wx.redirectTo({
          url: '../login/login',
        })
      }
    })
  },
  miniDetails: function (e) { 
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        miniDetails('/v1/appletmenu/menus',
          {tab:1}, {
            'content-type': 'application/json',
            'token': token
          }
        ).then(res => {
          if (res.error_code == 0) {
            let miniList = res.data.list
            miniList.forEach((item, index) => {
              item.icon = STATIC_HOST + '/' + item.icon
            })
            that.setData({
              miniList: miniList
            })
          } else {
            wx.showToast({
              title: res.error_msg,
              duration: 2000,
              icon: 'none'
            })
            that.setData({
            })
          }

        }).catch(function (error) {
          that.setData({
          })
        })
      },
      fail: function (res) {
        wx.showToast({
          title: '请登陆',
          icon: 'none',
          duration: 2000
        })
        setTimeout(function () {
          wx.navigateTo({
            url: '../login/login'
          })
        }, 2000)

      }
    })
  },
  toDetail: function (e) {
    let status = e.currentTarget.dataset.status;
    let id = e.currentTarget.dataset.id;
    if (status == 0) {
      wx.navigateTo({
        url: '../signdetail/signdetail?id=' + id + '&status=' + status
      })
    } else if (status == null) {
      wx.navigateTo({
        url: '../orderdetail/orderdetail?id=' + id + '&type_fw=' + ''
      })
    } else {
      return
    }
  },
  toItem: function (e) {
    let url = e.currentTarget.dataset.url
    wx.navigateTo({
      url: url
    })
  },
})
