// pages/team/team.js
import {getDesigner,getDesignerCase} from '../../utils/api'
const app = getApp()
Page({
  data: {
    currentPage:1,
    totalPageNumber:1,
    dataList:[],
    id:app.globalData.companyId,
    cityInfo:{
      cid:'',
      id:''
    }
  },
  onLoad: function (options) {
    this.setData({
      currentPage:options.p||1,
      cityInfo:options,
      id:options.id
    })
    this.getList(options)
  },
  getList:function(options){
    getDesigner(
      '/tt/v1/company/designer',
      {
        cid:options.cid,
        id:options.id,
        p:this.data.currentPage
      }
    ).then(res=>{
      if(res.error_code===0){
        this.setData({
          dataList:res.data.team,
          totalPageNumber:res.data.page.page_total_number,
          currentPage:res.data.page.page_current
        })
      }
    })
  }
})