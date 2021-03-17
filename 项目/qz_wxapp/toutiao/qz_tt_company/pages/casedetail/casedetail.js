import {getCaseDetail} from '../../utils/api'
Page({
  data: {
    title:"",
    fengge:"",
    leixing:"家装案例",
    mianji:"",
    boajia:'',
    cityInfo:{},
    detailData:{},
    imageShow:false,
    imgsView:[]
  },
  onLoad: function (options) {
    let parms = {
      id:options.id,
      city_id:options.cid
    }
    this.setData({
      cityInfo:parms
    })
    this.getData(parms)
  },
  getData:function(parms){
    getCaseDetail(
      '/tt/v1/company/case_detail',
      parms
    ).then(res=>{
      if(res.error_code === 0){
        this.setData({
          detailData:res.data
        })
      }
    })
  },
  toPreViewImage:function(e){
    let index = e.target.dataset.index
    let imgs = []
    if(this.data.detailData.img_path){
      imgs.push(this.data.detailData.img_path)
    }else{
      imgs.push(this.data.detailData.child[0])
    }
    for(let i in this.data.detailData.child){
      imgs.push(this.data.detailData.child[i])
    }
    let delItem = imgs.splice(index, 1)
    imgs = [delItem].concat(imgs)
    this.setData({
      imageShow:true,
      imgsView:imgs
    })
  },
  closeImage:function(){
    this.setData({
      imageShow:false
    })
  }
})