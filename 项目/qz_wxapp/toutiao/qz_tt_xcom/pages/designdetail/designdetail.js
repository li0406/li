// pages/designdetail/designdetail.js
const app = getApp()
import {designlist, designdetail} from '../../utils/api'
Page({
  data: {
    isShow:false,
    info: {},
    workList: [],
    totalPage: '',
    scrollTop:'',
    num: '',
    type:1,
    imgList:{
      title:'',
      huxing:'',
      case_attr:'',
      imgs:[]
    },
    parms:{
      designer_id: '',
      page:1
    }
  },
  designChange(res){
    let type = res.currentTarget.dataset.type
    this.setData({
      type:type
    })
    if(type == 1){
      this.setData({
        isShow:false,
        workList: [],
        ['parms.page']:1
      })
      this.designlist() 
    }else{
      this.setData({
        isShow:true
      })
      this.designdetail(this.data.parms.designer_id)
    }
  },
  designdetail:function(id){
    designdetail('/v1/company/designer/detail',
      {designer_id:id}).then(res => {
      if(res.error_code === '0'){
        this.setData({
          info:res.data
        })
      }else{
        
      }
    })
  },
  designlist:function(){
    designlist('/v1/company/designer/caselist',
    this.data.parms).then(res => {
      if(res.error_code == '0'){
        let workList = this.data.workList
        workList = workList.concat(res.data.list)
        this.setData({
          workList:workList,
          totalPage: res.data.page.page_total_number
        })
      }
    })
  },
  toImages:function(e){
    let id = e.currentTarget.dataset.id
    let obj = this.data.workList
    for(let i = 0; i < obj.length; i++){
      if(obj[i].id == id){
        this.setData({
          ['imgList.imgs']:obj[i].img_list,
          ['imgList.title']:obj[i].title,
          ['imgList.case_attr']:obj[i].case_attr,
          ['imgList.huxing']:obj[i].huxing
        })
      }
    }
    tt.navigateTo({
      url: '../openimages/openimages?imgList=' + JSON.stringify(this.data.imgList) + '&type=2' // 指定页面的url
    });
  },
  onLoad: function (options) {
    let id = options.id
    let case_num = options.num
    this.setData({
      workList: [],
      num: case_num,
      ['parms.page']:1,
      ['parms.designer_id']:id
    })
    this.designlist() 
    this.designdetail(this.data.parms.designer_id)
  },
  toSheJi:function(){
    tt.navigateTo({
      url: '../sheji/sheji' // 指定页面的url
    });
  },
  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {
    var that = this;
    if(that.data.type != 1){
      return
    }
    // 显示加载图标
    tt.showLoading({
      title: '请求中，请耐心',
    })
    // 页数+1
    that.setData({
      ['parms.page']: that.data.parms.page + 1
    })
    if (that.data.parms.page > that.data.totalPage) {
      tt.showToast({
        title: '没有更多数据了~',
        icon: 'none',
        duration: 2000
      })
      //tt.hideLoading()
    } else {
      that.designlist()
    }
  },
  onPageScroll:function(e){
    this.setData({
      scrollTop: e.scrollTop
    })
  }
})
