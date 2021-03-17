import { getMeituCategory, queryMeituList } from "../../utils/api.js"
const app = getApp()
let apiUrl = app.getApiUrl();
Page({
  data: {
    shareInfo:'',
    selectHeader:[
      {
        text:'风格',
        active:false,
        tabIndex:0
      },
      {
        text:'户型',
        active:false,
        tabIndex:1
      },
      {
        text:'局部',
        active:false,
        tabIndex:2
      },
      {
        text:'颜色',
        active:false,
        tabIndex:3
      }
    ],
    scrollTop:0,
    bodyData:[],
    category:'',
    lowerInfo:'',
    hideMask:true,
    colors:[],
    huxing:[],
    jubu:[],
    fengge:[],
    parms:{
      page:1,
      wz:'',
      fg:'',
      hx:'',
      ys:''
    },
    getMore:0
  },
  onLoad: function () {
    let that = this;
    this.getCategory()
    this.getDatas()
  },
  getCategory:function(){
    getMeituCategory('/tt/v1/meitu/category').then(res=>{
      if(res.error_code === 0){
        this.setData({
          fengge:res.data.fengge,
          huxing:res.data.huxing,
          jubu:res.data.location,
          colors:res.data.color
        })
      }
    })
  },
  getQuery:function(e){
    let type = e.currentTarget.dataset.type
    let id = e.currentTarget.dataset.id
    let index = e.currentTarget.dataset.index
    let tempParms = this.data.parms
    tempParms.page = 1
    tempParms[type]=id
    for(let i in this.data.selectHeader){
      this.setData({
        ['selectHeader['+i+'].active']:false
      })
    }
    this.setData({
      bodyData:[],
      parms:tempParms,
      scrollTop:0,
      hideMask:!this.data.hideMask,
      ['selectHeader['+index+'].text']:e.currentTarget.dataset.text.length>3?e.currentTarget.dataset.text.substring(0,3)+"...":e.currentTarget.dataset.text,
    }, function(){
      this.getDatas()
    })
  },
  getDatas:function(){
    let that = this
    this.setData({
      getMore:0
    })
    queryMeituList(
      '/tt/v1/meitu/list',
      this.data.parms
    ).then(res=>{
      if(res.error_code===0){
        let oldData = this.data.bodyData
        let temGetMore = 0
        if(res.data.list.length<15||res.data.page.page_current==21){
          // 加载完成
          temGetMore = 2
        }
        if(res.data.list.length==0 && res.data.page.page_current==0){
          // 筛选无结果
          temGetMore = 1
        }
        oldData = oldData.concat(res.data.list)
        that.setData({
          bodyData:oldData,
          ['parms.page']:res.data.page.page_current+1,
          getMore:temGetMore
        })
      } else{
        tt.showToast({
          title: "加载失败，请稍后重试",
          icon: 'none'
        });
      }
    },res=>{
      tt.showToast({
        title: "加载失败，请稍后重试",
        icon: 'none'
      });
    })
  },
  lower:function (e) {
    if(this.data.getMore!=0){
      return
    }
    this.getDatas()
  },
  openCategory:function(e){
    for(let i in this.data.selectHeader){
      if(i == e.currentTarget.dataset.index) {
        this.setData({
          ['selectHeader['+i+'].active']:!this.data.selectHeader[e.currentTarget.dataset.index].active || this.data.hideMask
        })
      }else{
        this.setData({
          ['selectHeader['+i+'].active']:false,
        })
      }
    }
    if(this.data.selectHeader[e.currentTarget.dataset.index].active){
      this.setData({
        hideMask:!this.data.selectHeader[e.currentTarget.dataset.index].active
      })
    }else{
      this.setData({
        hideMask:true
      })
    }
  },
  closeMask:function(e){
    this.setData({
      hideMask:!this.data.hideMask
    })
  },
  onShareAppMessage:function(options){
  }
})
