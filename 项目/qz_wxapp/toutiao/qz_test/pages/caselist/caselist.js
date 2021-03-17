import {getCaseData} from '../../utils/api'
const app = getApp()
Page({
  data: {
    categoryData:[
      {name:'现代简约',active:false},
      {name:'欧式风格',active:false},
      {name:'中式风格',active:false},
      {name:'美式风格',active:false},
      {name:'其他',active:false}
    ],
    bm:'',
    caseData:[],
    currentPage:1,
    pageNumber:1
  },
  onLoad: function (options) {
    let parms={
      bm:app.globalData.bm,
      id:app.globalData.companyId,
      p:options.p||1,
      pagecount:12
    }
    this.setData({
      currentPage:options.p||1,
      bm:app.globalData.bm
    })
    this.getCaseListData(parms)
  },
  getCaseListData:function(parms){
    getCaseData(
      "/company/caselist",
      parms
    ).then(res=>{
      if(res.error_code===0){
        this.setData({
          caseData:res.data,
          pageNumber:2
        })
      }
    }).catch(res=>{
      console.log(res)
    })
  }
})