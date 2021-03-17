// pages/designteam/designteam.js
const app = getApp()
import {designTeamlist} from '../../utils/api'
Page({
  data: {
    infoList:[],
    totalPage: '',
    nomore: 2, //1加载中 2加载完成 3没数据了
    parms:{
      company_id: '',
      page:1
    }
  },
  designTeamlist:function(){
    let that = this
    designTeamlist('/v1/company/designer/list',that.data.parms).then(res => {
      if(res.error_code == '0'){
        let infoList = that.data.infoList
        infoList = infoList.concat(res.data.list)
        that.setData({
          infoList:infoList,
          totalPage: res.data.page.page_total_number
        })
      }
    })
  },
  onLoad: function (options) {
    let id = options.id
    this.setData({
      ['parms.company_id']:id
    })
    this.designTeamlist()
  },
  toDesignDetail:function(e){
    let id = e.currentTarget.dataset.id
    let num = e.currentTarget.dataset.num
    tt.navigateTo({
      url: '../designdetail/designdetail?id=' + id + '&num=' + num // 指定页面的url
    });
  },
  toSheJi:function(){
    tt.navigateTo({
      url: '../sheji/sheji' // 指定页面的url
    });
  },
  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {
    var that = this;
    // 显示加载图标
    tt.showLoading({
      title: '请求中，请耐心',
    })
    // 页数+1
    that.setData({
      ['parms.page']: that.data.parms.page + 1
    })
    if (that.data.parms.page > that.data.totalPage) {
      tt.showToast({
        title: '没有更多数据了~', // 内容
        duration: 2000,
        icon: 'none',
        success: (res) => {
          
        }
      });
      //tt.hideLoading()
    } else {
      that.designTeamlist()
    }
  }
})