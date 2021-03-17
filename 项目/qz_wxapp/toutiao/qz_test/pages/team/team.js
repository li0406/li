// pages/team/team.js
import {getDesigner,getDesignerCase} from '../../utils/api'
const app = getApp()
Page({
  data: {
    currentPage:1,
    totalPageNumber:1,
    dataList:[],
    id:app.globalData.companyId
  },
  onLoad: function (options) {
    this.setData({
      currentPage:options.p||1
    })
    this.getList()
  },
  getList:function(){
    getDesigner(
      '/company/team',
      {
        bm:app.globalData.bm,
        id:app.globalData.companyId,
        p:this.data.currentPage,
        pagecount:8
      }
    ).then(res=>{
      if(res.error_code===0){
        for(let i in res.data){
          res.data[i].cases=[]
          this.getCases(res.data[i].uid,i)
        }
        this.setData({
          dataList:res.data,
          totalPageNumber:2
        })
      }
    })
  },
  getCases:function(id,index){
    getDesignerCase(
      '/company/designer',
      {
        id:id,
        p:1,
        pagecount:3
      }
    ).then(res=>{
      this.setData({
        ['dataList['+index+'].cases']:res.cases
      })
    }).catch(res=>{
      console.log(res)
    })
  },
  toDetail:function(e){
    let data = JSON.stringify(e.currentTarget.dataset.info)
    tt.setStorageSync('designerInfo', data);
    tt.navigateTo({
      url: '/pages/blog/blog?id='+e.currentTarget.dataset.info.uid 
    });
  }
})