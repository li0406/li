const app = getApp()
import {getFilterData,getCityInfoByName,getIndexCom} from '../../utils/api'
Page({
  data: {
    tabFixd:false,
    searchCityName:"",
    tuijian: [],
    companyFilterData:[
      {
        active:false,
        text:'服务区域',
        selected:false
      },
      {
        active:false,
        text:'公司规模',
        selected:false
      },
      {
        active:false,
        text:'服务保障',
        selected:false
      },
      {
        active:false,
        text:'智能排序',
        selected:false
      }
    ],
    searchParms:{
      keyword:'',
      area:'',
      gm:'',
      bz:'',
      orderby:'',
      p:'',
      cs:''
    },
    cityInfo:{},
    filterDatas:[],
    dataList:[],
    maskOpen:false,
    pageInfo:{
      pageNumber: 0,
      currentPage:1
    },
    noresult:false
  },
  onLoad: function (options) {
    //  若参数中有cid
    if(options.cid){
      this.setData({
        cityInfo:options,
        ['searchParms.keyword']:options.keyword||'',
        ['searchParms.area']:options.area||'',
        ['searchParms.gm']:options.gm||'',
        ['searchParms.bz']:options.bz||'',
        ['searchParms.order']:options.orderby||'',
        ['searchParms.cs']:options.cid,
        ['searchParms.p']:options.p||1
      })
      app.globalData.cityInfo = options
      this.getFilter()
      this.filterCityData()
    }else{
      this.getCity(options)
    }
  },
  getCity:function(options){
    app.getMyPosition((cityname) =>{
      getCityInfoByName(
      '/v1/city/getCityByCityName',
      {
        cityname:cityname
      }).then(res=>{
        console.log(res)
        if(res.status === 1){
          let info = {
            bm: res.data.bm,
            cid: res.data.cid,
            cname:res.data.name
          }
          this.setData({
            cityInfo: info,
            ['searchParms.keyword']:options.keyword||'',
            ['searchParms.area']:options.area||'',
            ['searchParms.gm']:options.gm||'',
            ['searchParms.bz']:options.bz||'',
            ['searchParms.order']:options.orderby||'',
            ['searchParms.cs']:info.cid,
            ['searchParms.p']:options.p||1
          })
          this.filterCityData()
          app.globalData.cityInfo = info
          this.getFilter()
        }
      })
    })
  },
  getFilter:function(){
    getFilterData(
      '/tt/v1/company/filter',
      {
        city_id: this.data.cityInfo.cid
      }
    ).then(res=>{
      if(res.error_code === 0){
        for (let i in res.data.area) {
          if (res.data.area[i].id === this.data.searchParms.area) {
             this.setData({
               ['companyFilterData[0].text']:res.data.area[i].name,
               ['companyFilterData[0].selected']:true
             })
          }
        }
        for (let i in res.data.size) {
          if (res.data.size[i].id === this.data.searchParms.gm) {
             this.setData({
               ['companyFilterData[1].text']:res.data.size[i].name,
               ['companyFilterData[1].selected']:true
             })
          }
        }
        for (let i in res.data.service) {
          if (res.data.service[i].id === this.data.searchParms.bz) {
             this.setData({
               ['companyFilterData[2].text']:res.data.service[i].name,
               ['companyFilterData[2].selected']:true
             })
          }
        }
        if (this.data.searchParms.order !== ''){
          let orderArray = ''
          if (this.data.searchParms.order === 'star') {
            orderArray = '口碑'
          }
          if (this.data.searchParms.order === 'shili') {
             orderArray = '综合实力'
          }
          if (this.data.searchParms.order === 'issale') {
             orderArray = '优惠'
          }
          this.setData({
            ['companyFilterData[3].text']:orderArray,
            ['companyFilterData[3].selected']:true
          })
        }else{
           this.setData({
            ['companyFilterData[3].text']:'智能排序',
            ['companyFilterData[3].selected']:false
          })
        }
        this.setData({
          filterDatas:res.data
        })  
      }
    })
  },
  getSearchName:function(e){
    this.setData({
      searchCityName:e.detail.value
    })
  },
  // 搜索装修公司
  searchCompany:function(e){
    tt.navigateTo({
      url:"/pages/index/index?keyword=" + this.data.searchCityName + "&cid="+this.data.cityInfo.cid+"&cname="+this.data.cityInfo.cname
    })
  },
  openFilter:function(e){
    let index = e.target.dataset.index;
    for(let i in this.data.companyFilterData){
      if (i!= index) {
        this.setData({
          ["companyFilterData["+i+"].active"]: false
        })
      }else{
        this.setData({
          ["companyFilterData["+i+"].active"]: !this.data.companyFilterData[i].active,
          maskOpen:!this.data.companyFilterData[i].active
        })
      }
    }
  },
  filterCityData:function(){
    getIndexCom(
      '/tt/v1/company/list',
      this.data.searchParms
    ).then(res => {
      if(res.error_code === 0){
        let list = res.data.list
        let recommend = res.data.recommend
        list.forEach((item)=>{
          item.starNums = []
          for(let num = 0; num <item.star; num++){
            item.starNums.push(num)
          }
        })
        if(recommend.length>0){
          recommend.forEach((item)=>{
            item.starNums = []
            for(let num = 0; num <item.star; num++){
              item.starNums.push(num)
            }
          })
        }
        
        this.setData({
          ['pageInfo.pageNumber']: res.data.page.page_total_number,
          ['pageInfo.currentPage']: res.data.page.page_current,
          dataList:list,
          tuijian:recommend,
          noresult:list.length==0
        })
      }
    })
  },
  closeMask:function(){
    this.setData({
      maskOpen:false
    })
  },
  // 发单成功回调
  orderSuccess:function(res){
    tt.navigateTo({
      url: '/pages/baojiawanshan/baojiawanshan' // 指定页面的url
    });
  },
   toFixed:function(e){
    let scrollTop = e.detail.scrollTop
    let allHeight = e.detail.scrollHeight
    this.setData({
      tabFixd:scrollTop/allHeight>0.1
    })
  }
})
