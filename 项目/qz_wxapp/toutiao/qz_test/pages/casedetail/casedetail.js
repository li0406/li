import {getCaseDetail} from '../../utils/api'
Page({
  data: {
    imageArray:[],
    title:"",
    fengge:"",
    leixing:"家装案例",
    mianji:"",
    boajia:''
  },
  onLoad: function (options) {
    let parms = {
      id:options.id
    }
    this.getData(parms)
  },
  getData:function(parms){
    getCaseDetail('/xgtdetail',
      parms
    ).then(res=>{
      if(res.status===1){
        this.setData({
          imageArray:res.data.imgs,
          title:res.data.title,
          fengge:res.data.fengge,
          mianji:res.data.mianji,
          baojia:res.data.zcost
        })
      }
    })
  },
  toPreViewImage:function(e){
    let imgArry = this.data.imageArray.map((item,index)=>{
      return item.img_path
    })
    let currentUrl = e.currentTarget.dataset.url
      tt.previewImage({
        urls: imgArry,
        current: currentUrl
      });
  }
})