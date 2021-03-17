// pages/message/message.js
import {getComments} from '../../utils/api'
const app = getApp()
Page({
  data: {
    dataList:{},
    cityInfo:{},
    totalNumber:0,
    currentPage:0,
    type:'',
    plsxData:[
      {
        name:'全部',
        id:'',
      },
      {
        name:'精选',
        id:1
      },
      {
        name:'最新点评',
        id:2
      }
    ]
  },
  onLoad: function (options) {
    this.setData({
      cityInfo:options
    })
    this.getCommentsData(options)
  },
  getCommentsData:function(options){
    getComments(
      "/tt/v1/company/comments",
      {
        id:options.id,
        city_id:options.cid,
        type:options.type||0,
        p:options.p||1
      }
    ).then(res=>{
      if(res.error_code===0){
        let newData = res.data
        let jieduan = newData.showjieduan
        let newSx = this.data.plsxData.concat(jieduan)
        if(newData.hadjingxuan==0){
          newSx.splice(1,1)
        }
        if(options.type){
          for(let i in newSx){
            if(newSx[i].id == options.type){
              newSx[i].active = true
            }
          }
        }else{
          newSx[0].active = true
        }
        // 头条setData 如果一次赋值多个，需要确保每个值都是正确的。
        this.setData({
           plsxData:newSx
        })
        newData.user.avgsjpf = this.dpFen(newData.user.avgsj)
        newData.user.avgfwpf = this.dpFen(newData.user.avgfw)
        newData.user.avgsgpf = this.dpFen(newData.user.avgsg)
        newData.info.comments.forEach(item=>{
          item.shortText = item.text.substring(0,100)
          item.showShort = item.text.length>100,
          item.avgcount = this.dafen2(item.avgcount)
        })
        this.setData({
          dataList:newData,
          totalNumber:res.data.info.page.page_total_number,
          currentPage:res.data.info.page.page_current
        })
      }
    }).catch(res=>{})
  },
  dpFen:function(num){
    if(num>9){
      return [1,1,1,1,1]
    }
    if(num>=8&&num<9){
      return [1,1,1,1,0]
    }
    if(num>=4&&num<8){
      return [1,1,1,0,0]
    }
    if(num>=2&&num<4){
      return [1,1,0,0,0]
    }
    if (num>0&&num<2) {
     return [1,0,0,0,0]
    }
    return [0,0,0,0,0]
  },
  dafen2:function(fen){
    let array = []
    for(let i = 1; i<6; i++){
      if(i<=fen){
        array.push(1)
      }else{
        array.push(0)
      }
    }
    return array
  },
  showQuanwen:function(e){
    let id = e.target.dataset.id
    console.log(this.data.dataList.info.comments)
    this.data.dataList.info.comments.forEach((item,index)=>{
      if(id == item.id){
        this.setData({
         ['dataList.info.comments['+index+'].showShort']:!this.data.dataList.info.comments[index].showShort
        })
      }
    })
  }
})
