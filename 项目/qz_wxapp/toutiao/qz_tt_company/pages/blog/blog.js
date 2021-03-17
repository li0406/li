// pages/blog/blog.js
import {getDesigner} from '../../utils/api'
Page({
  data: {
    currentPage:1,
    pageNumber:1,
    pagecount:10,
    tabInfo:false,
    detailInfo:{},
    cityInfo:{}
  },
  onLoad: function (options) {
    this.getInfo(options)
    this.setData({
      cityInfo:options
    })
  },
  getInfo:function(options){
    getDesigner(
      '/tt/v1/company/designer_detail',
      {
        id:options.id,
        city_id:options.cid,
        p:options.p||1
      }
    ).then(res=>{
       if(res.error_code === 0){
        this.setData({
          detailInfo:res.data,
          currentPage:res.data.cases.page.page_current,
          pageNumber:res.data.cases.page.page_total_number
        })
       }
    })
  },
  changTab:function(e){
    this.setData({
      tabInfo:e.target.dataset.index==1
    })
  }
})