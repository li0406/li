// pages/blog/blog.js
import {getDesignerCase} from '../../utils/api'
Page({
  data: {
    currentPage:1,
    pageNumber:1,
    pagecount:10,
    caseData:[],
    tabInfo:false,
    designer:''
  },
  onLoad: function (options) {
    this.getInfo(options)
  },
  getInfo:function(options){
    getDesignerCase(
      '/company/designer?id=310194&p=1&pagecount=5',
      {
        id:options.id,
        p:options.p||1,
        pagecount:10
      }
    ).then(res=>{
       this.setData({
         caseData:res.cases
       })
    })
    tt.getStorage({
      key: 'designerInfo', // 缓存数据的key
      success: (res) => {
        this.setData({
          designer:JSON.parse(res.data)
        })
        console.log(this.data.designer)
      }
    });
  },
  changTab:function(e){
    this.setData({
      tabInfo:e.target.dataset.index==1
    })
  }
})