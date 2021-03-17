const app = getApp()

Page({
  data: {
    comName:app.globalData.compayName,
    bm:app.globalData.bm,
    showWin:false,
    orderType:0,
    toView:"",
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
  onLoad: function () {
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
    this.setData({
      showWin:true,
      orderType:e.target.dataset.index
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
  }
})
