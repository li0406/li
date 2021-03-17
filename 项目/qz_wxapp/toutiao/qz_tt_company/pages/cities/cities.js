// pages/cities/cities.js
import {getChoseCityData} from '../../utils/api'
Page({
  data: {
    cityName:"定位中",
    cityData:{},
    toCityView:'',
    cityText:'请选择城市',
    cid:'',
    bm:'',
    isHide:true
  },
  onLoad: function (options) {
    this.setData({
      cityName:options.cname
    })
    this.getCityData()
  },
  getCityData:function(){
    getChoseCityData().then(res=>{
      if(res.err_code===0){
        let cityData = res.data
        let temArray = []
        for (var i in cityData.accordCity) {
          temArray.push(cityData.accordCity[i])
        }
        cityData.accordCity = temArray
        this.setData({
          cityData:cityData
        })
      }
    })
  },
  toFenlei:function(e){
    let target = e.target.dataset.fl
    this.setData({
      toCityView:"fl_"+target
    })
  },
  selectHandle: function () {
    this.setData({
      isHide: false,
      hasOpen:true
    })
  },
  closeSelect: function (res) {
    this.setData({
      isHide: true,
      cid:  res.detail[0],
      cityText: res.detail[1].split(" ")[2]
    })
  },
  enterIndex:function(){
    for(let i in this.data.cityData.allCity){
      for(let j in this.data.cityData.allCity[i].child){
        if(this.data.cityData.allCity[i].child[j].cid == this.data.cid){
          this.setData({
            bm:this.data.cityData.allCity[i].child[j].bm
          })
        }
      }
    }
    tt.navigateTo({
      url: '/pages/index/index?cid='+this.data.cid+"&bm="+this.data.bm+"&cname="+this.data.cityText  
    });
  }
})