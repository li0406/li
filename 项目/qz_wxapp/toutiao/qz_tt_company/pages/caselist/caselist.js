import {getCaseData} from '../../utils/api'
const app = getApp()
Page({
  data: {
    total:false,
    categoryData:[],
    caseData:[],
    currentPage:1,
    pageNumber:1,
    navToView:'',
    typeMask:false,
    cityInfo:'',
    noresult:false
  },
  onLoad: function (options) {
    console.log(111,options)
    let parms={
      id:options.id,
      city_id:options.cid,
      p:options.p||1,
      category:options.category||'',
      class:options.type||''
    }
    this.setData({
      cityInfo:parms
    })
    this.getCaseListData(parms)
  },
  getCaseListData:function(parms){
    getCaseData(
      "/tt/v1/company/cases",
      parms
    ).then(res=>{
      if(res.error_code===0){
        this.setData({
          categoryData:res.data.category,
          noresult:res.data.list.list == null
        })
        this.setData({
          caseData:res.data.list.list,
          pageNumber:res.data.list.page.page_total_number,
          currentPage:res.data.list.page.page_current
        })
        this.setCurrentNav(parms.category)
      }
    }).catch(res=>{
      console.log(res)
    })
  },
  setCurrentNav:function(index){
    if(index){
      this.setData({
        ['categoryData['+(parseInt(index)-1)+'].active']:true,
        navToView:'category_'+ index
      })
    }else{
      this.setData({
       total:true
      })
    }
  },
  // 打开筛选按钮
  openType:function(){
    this.setData({
      typeMask:!this.data.typeMask
    })
  }
})