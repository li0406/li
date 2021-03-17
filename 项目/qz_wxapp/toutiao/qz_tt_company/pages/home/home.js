const app = getApp()
import {getComData} from '../../utils/api'
const $time = require('../../utils/utils.js')
Page({
  data: {
    showWin:false,
    orderType:0,
    toView:"",
    cityInfo:{},
    isTips:false,
    totalData:{},
    quanInfo:'',
    tabFixd:false,
    viewSet:[
      {
        text:"真实案例",
        active:true,
        toViewClass:"zsal"
      },
      {
        text:"设计师",
        active:false,
        toViewClass:"sjs"
      },
      {
        text:"业主评价",
        active:false,
        toViewClass:"yzpj"
      },
      {
        text:"商家信息",
        active:false,
        toViewClass:"sjxx"
      }
    ]
  },
  onLoad: function (options) {
    this.setData({
      cityInfo: options
    })
    this.getCompanyData(options)
  },
  callPhone:function(){
    tt.makePhoneCall({
      phoneNumber: '400-6969-336',
      success (res) {
          console.log(`makePhoneCall调用成功${res}`);
      },
      fail (res) {
          console.log(`makePhoneCall调用失败`);
      }
    });
  },
  openWin:function(e){
    let quanId = e.target.dataset.id
    let quan = {}
    this.data.totalData.cardlist.card.forEach(item=>{
      if(item.record_id == quanId){
        quan = item
      }
    })
    this.setData({
      showWin:true,
      orderType:e.target.dataset.index,
      quanInfo:quan
    })
  },
  toViewPosition:function(e){
    for(let i in this.data.viewSet){
      this.setData({
        ['viewSet['+i+'].active']:e.target.dataset.pos === this.data.viewSet[i].toViewClass
      })
    }
    this.setData({
      toView:e.target.dataset.pos
    })
  },
  hasGetQuan:function(){
    for(let i in this.data.totalData.cardlist.card){
      if(this.data.totalData.cardlist.card[i].record_id == this.data.quanInfo.record_id){
        this.setData({
          ['totalData.cardlist.card['+i+'].hasLq']:true
        })
      }
    }
  },
  toTags:function(){
    this.setData({
      toView:"company_tags"
    })
  },
  getCompanyData:function(options){
    getComData(
      '/tt/v1/company/detail',
      {
        city_id:options.cid,
        id:options.id
      }
    ).then(res=>{
      if(res.error_code === 0){
        let listData = res.data
        listData.user.starNums = []
        for(let i = 0; i<listData.user.star;i++){
          listData.user.starNums.push(i)
        }
        if(listData.comments!=null){
          listData.comments.comments = res.data.comments.comments.map(function(item, obj){
            item.time = $time.formatTime(item.time, 'Y-M-D')
            item.rptxt_time = $time.formatTime(item.rptxt_time, 'Y-M-D')
            return item
          })
        }
        if(listData.cardlist.card.length!=0){
          listData.cardlist.card = res.data.cardlist.card.map(function(item, obj){
            item.start = $time.formatTime(item.start, 'Y-M-D')
            item.end = $time.formatTime(item.end, 'Y-M-D')
            item.hasLq = false
            return item
          })
        }
        this.setData({
          totalData: listData,
          isTips:res.data.activity
        })
      }
    })
  },
  closeTips:function(){
    this.setData({
      isTips:false
    })
  },
  showZhizhao:function(e){
    let arrays = []
    console.log(this.data.totalData.licence)
    for(let i in this.data.totalData.licence){
      if(this.data.totalData.licence[i]){
        arrays.push(this.data.totalData.licence[i])
      }
    }
    console.log(arrays)
    let currentUrl = arrays[0]
    tt.previewImage({
      urls: arrays,
      current: currentUrl
    });
  },
  toFixed:function(e){
    let scrollTop = e.detail.scrollTop
    let allHeight = e.detail.scrollHeight
    this.setData({
      tabFixd:scrollTop/allHeight>0.43
    })
  }
})
