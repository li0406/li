import { getMeituDetail } from "../../utils/api.js"
Page({
  data: {
    imageData:[],
    currentInde:1,
    imagsLength:0,
    prevId:'',
    nextId:'',
    startTime:'',
    domView:false
  },
  onLoad: function (options) {
    if(options.id){
      this.getDetail(options.id)
    }
  },
  getDetail:function(id){
    getMeituDetail(
      '/tt/v1/meitu/detail',
      {
        id:id
      }
    ).then(res=>{
      if(res.error_code===0){
        this.setData({
          imageData:res.data.now,
          imagsLength:res.data.now.child.length,
          prevId:res.data.prv.id,
          nextId:res.data.next.id,
          domView:true
        })
      }
    })
  },
  swiperChange:function(e){
    let currIndex = e.detail.current+1
    this.setData({
      currentInde:currIndex
    })
  },
  onShareAppMessage:function(options){
  }
})