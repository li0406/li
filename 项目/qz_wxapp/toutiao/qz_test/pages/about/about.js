// pages/about/about.js
import {getCompanyDetail} from '../../utils/api'
const app = getApp()
Page({
  data: {
    detailData:{},
    honor:[],
    jianjie:"",
    toView:'',
    tabData:[
      {
        text:"公司服务",
        active:true,
        toViewPos:'gsfw'
      },
      {
        text:"公司荣誉",
        active:false,
        toViewPos:'gsry'
      },
      {
        text:"公司介绍",
        active:false,
        toViewPos:'gsjs'
      }
    ]
  },
  onLoad: function (options) {
    this.getDetail()
  },
  getDetail:function(){
    getCompanyDetail(
      "/company/detail",
      {
        id:app.globalData.companyId,
        bm:app.globalData.bm,
        classtype:9
      }
    ).then(res=>{
      this.setData({
        detailData:res.detail,
        honor:res.honor,
        jianjie:res.detail.jianjie.join("。")
      })
    }).catch()
  },
  toCurrentPos:function(e){
    for(let i in this.data.tabData){
      this.setData({
        ['tabData['+i+'].active']:e.target.dataset.pos === this.data.tabData[i].toViewPos
      })
    }
    this.setData({
      toView:e.target.dataset.pos
    })  
  },
  toPreViewImage:function(e){
    let imgArry = this.data.honor.map((item,index)=>{
      return item.img
    })
    let currentUrl = e.currentTarget.dataset.url
      tt.previewImage({
        urls: imgArry,
        current: currentUrl
      });
  }
})