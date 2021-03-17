//index.js
import { getUser,getMywork } from "../../utils/api.js"
const app = getApp();
Page({
  data: {
    deptName: '',
    roleName: '',
    user:'',
    list:[]
  },
  //事件处理函数
  onLoad: function () {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getUser('/v1/user/info',{},{
          'content-type': 'application/x-www-form-urlencoded',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            let deptName = res.data.info.dept_name;
            if (deptName == null) {
              deptName = '齐装网'
            }
            that.setData({
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
      }
    })
  },
  onShow: function () {

  },
  toDetail: function (e) {
    let status = e.currentTarget.dataset.status;
    let id = e.currentTarget.dataset.id;
    if (status == 0){
      wx.navigateTo({
        url: '../signdetail/signdetail?id=' + id + '&status=' + status
      })
    } else if (status == null) {
      wx.navigateTo({
        url: '../orderdetail/orderdetail?id=' + id +'&type_fw='+''
      })
    }else {
      return
    }
  },
  toCustom: function (e) {
    wx.navigateTo({
      url: '../custom/custom'
    })
  },
  toOrder: function (e) {
    wx.navigateTo({
      url: '../order/order'
    })
  },
  toSign: function (e) {
    wx.navigateTo({
      url: '../sign/sign'
    })
  }
})
