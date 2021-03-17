// pages/activity/activity.js
import {getComData} from '../../utils/api'
Page({
  data: {
    detail:{},
    cityInfo:{}
  },
  onLoad: function (options) {
    getComData(
      '/tt/v1/company/event_detail',
      {
        id:options.id,
        city_id:options.cid
      }
    ).then(res=>{
      if(res.error_code === 0){
        this.setData({
          detail:res.data
        })
        bdParse.bdParse()
      }
    })
  }
})