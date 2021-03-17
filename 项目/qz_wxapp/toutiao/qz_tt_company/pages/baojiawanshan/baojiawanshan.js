// pages/baojiawanshan/baojoiawanshan.js
Page({
  data: {
    parms:{
      orderid:'',
      cs:'',
      tel:''
    }
  },
  onLoad: function (options) {
    this.setData({
      ['parms.orderid']:options.orderId||"2019090973437092",
      ['parms.cs']:options.cs||"110100",
      ['parms.tel']:options.tel||'18896701463'
    })
  },
  orderSuccess:function(){
    tt.navigateTo({
      url: '/pages/shejidone/shejidone' // 指定页面的url
    });
  }
})