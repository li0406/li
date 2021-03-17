// pages/journalDetail/journalDetail.js
import { getContactList, getJournalDetail } from "../../utils/api.js"
function getTime1(timestamp) {
  let date = new Date(timestamp * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
  let Y = date.getFullYear() + '-';
  let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
  let D = date.getDate() + ' ';
  let h = (date.getHours() < 10 ? '0' + (date.getHours()) : date.getHours()) + ':';
  let m = (date.getMinutes() < 10 ? '0' + (date.getMinutes()) : date.getMinutes());
  // let s = (date.getSeconds() < 10 ? '0' + (date.getSeconds()) : date.getSeconds());
  return Y + M + D + h + m;
}
function getTime2(timestamp) {
  let date = new Date(timestamp * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
  let Y = date.getFullYear() + '-';
  let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
  let D = date.getDate() + ' ';
  // let h = (date.getHours() < 10 ? '0' + (date.getHours()) : date.getHours()) + ':';
  // let m = (date.getMinutes() < 10 ? '0' + (date.getMinutes()) : date.getMinutes()) + ':';
  // let s = (date.getSeconds() < 10 ? '0' + (date.getSeconds()) : date.getSeconds());
  return Y + M + D;
}
Page({

  /**
   * 页面的初始数据
   */
  data: {
    tabActive: true,
    company_name: '',
    cname:'',
    aname:'',
    contact_name:'',
    contact_job:'',
    tel:'',
    wechat:'',
    qq:'',
    khStyle:'',
    khSource: ['请选择', '已签会员', '客服咨询转接', '会员转介绍', '后台注册公司0', '扫楼', '同行网站', '订单轰炸', '空间营销', '其他', '合作页面'],
    khSourceIndex: 0,
    address:'',
    intention:'',
    visit_style: '',
    visit_start_time: '',
    visit_end_time: '',
    qianyue_status: '',
    desc: '',
    visit_next_time: '',
    uname: '',
    pre_money:'',
    retainage_time:'',
    user_id:''
  },
  changeType: function (e) {
    this.setData({
      tabActive: e.currentTarget.dataset.type == "true"
    });
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let companyId = options.companyId;
    let visitId = options.visitId;
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getContactList('/v1/report/list_user', {
          company_id: companyId
        }, {
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          if(res.error_code == 0){
            console.log(res.data.is_new)
            that.setData({
              company_name: res.data.company_name,
              cname: res.data.cname,
              aname: res.data.aname,
              contact_name: res.data.contacts[0].name,
              contact_job: res.data.contacts[0].job,
              tel: res.data.contacts[0].tel,
              wechat: res.data.contacts[0].wx,
              qq: res.data.contacts[0].qq,
              khStyle: res.data.is_new,
              khSourceIndex: res.data.customer_source,
              address: res.data.address,
              intention: res.data.intention,
              user_id: res.data.user_id
            })
          }
        });
        getJournalDetail('/v1/report/list_visit',{
          company_id: companyId,
          page:1,
          page_count:10
        },{
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          if(res.error_code == 0){
            let visit_start_time = getTime1(res.data.list[0].visit_next_time);
            let visit_end_time = getTime1(res.data.list[0].visit_next_time);
            let visit_next_time = getTime2(res.data.list[0].visit_next_time);
            let retainage_time = getTime2(res.data.list[0].retainage_time);
            that.setData({
              visit_style: res.data.list[0].visit_style,
              visit_start_time: visit_start_time,
              visit_end_time: visit_end_time,
              qianyue_status: res.data.list[0].qianyue_status,
              desc: res.data.list[0].desc,
              pre_money: res.data.list[0].pre_money,
              visit_next_time: visit_next_time,
              uname: res.data.list[0].uname,
              retainage_time: retainage_time
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

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

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

  }
})