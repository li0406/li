// pages/about/about.js
import {getComData} from '../../utils/api'
const app = getApp()
Page({
  data: {
    detailData:{},
    honor:[],
    jianjie:"",
    toView:'',
    showMore:false,
    shortAbout:'',
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
    this.getDetail(options)
  },
  getDetail:function(options){
    getComData(
       '/tt/v1/company/detail',
      {
        city_id:options.cid,
        id:options.id
      }
    ).then(res=>{
      if(res.error_code===0){
        this.setData({
          detailData:res.data,
          showMore:res.data.user.about_jianjie.length>300,
          shortAbout:res.data.user.about_jianjie.length>300?res.data.user.about_jianjie.substring(0,299)+'...':''
        })
      }
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
    let imgArry = this.data.detailData.rongyu.img_path.map((item,index)=>{
      return item
    })
    let currentUrl = e.currentTarget.dataset.url
      tt.previewImage({
        urls: imgArry,
        current: currentUrl
      });
  },
  changeLength:function(){
    console.log(1)
    this.setData({
      showMore:!this.data.showMore
    })
  }
})