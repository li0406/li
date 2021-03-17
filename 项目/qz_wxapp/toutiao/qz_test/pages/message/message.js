// pages/message/message.js
import {getComments} from '../../utils/api'
import $time  from '../../utils/utils.js'
const app = getApp()
Page({
  data: {
    dataList:[]
  },
  onLoad: function (options) {
    this.getCommentsData(options)
  },
  getCommentsData:function(options){
    getComments(
      "/company/comment",
      {
        id:app.globalData.companyId,
        bm:app.globalData.bm,
        p:options.p||1
      }
    ).then(res=>{
      if(res.error_code===0){
        res.data.forEach(function(item,index){
          item.update_time = item.update_time == null ? $time.formatTime(item.time, 'Y-M-D h:m:s') : $time.formatTime(item.update_time, 'Y-M-D h:m:s')
          item.time = $time.formatTime(item.time, 'Y-M-D h:m:s')
        })
        this.setData({
          dataList:res.data
        })
      }
    }).catch(res=>{

    });
  }
})
