// components/order.js
import {fadanHandle} from '../../utils/api'
const app = getApp()
Component({
  data: {
    winType: [
      {
        title: '预约量房',
        smallText: '免费上门，全方位了解装修需求'
      },
      {
        title: '预约设计',
        smallText: '免费设计，为您量身定制'
      },
      {
        title: '免费报价',
        smallText: '报价详细透明，杜绝恶意新增'
      }
    ],
    isHide: true,
    cityId:'',
    selectText:'请选择您所在的城市',
    tel:'',
    hasChecked:true,
    wait:false
  },
  properties: {
    orderType: { // 属性名
      type: Number, // 类型（必填），目前接受的类型包括：String, Number, Boolean, Object, Array, null（表示任意类型）
      value: 0 // 属性初始值（必填）
    },
    showWin:{
      type:Boolean,
      value:false
    },
   
  },
  methods: {
    selectHandle: function () {
      this.setData({
        isHide: false
      })
    },
    closeSelect: function (res) {
      this.setData({
        isHide: true,
        cityId: res.detail[0],
        selectText: res.detail[1]
      })
    },
    getValue:function(e){
      this.setData({
        tel:e.detail.value
      })
    },
    changeCheck:function(e){
      this.setData({
        hasChecked:!this.data.hasChecked
      })
    },
    closeWin:function(){
      this.setData({
        showWin:false
      })
    },
    submitOrder:function(){
      let reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
      if(this.data.tel === ""){
        tt.showToast({
          title: '请输入您的手机号',
          icon:'none'
        });
        return
      }
      if(!reg.test(this.data.tel)){
         tt.showToast({
          title: '请输入11位手机号码',
          icon:'none'
        });
        return
      }
      if(!this.data.hasChecked){
        tt.showToast({
          title: '请选择阅读齐装网的免责声明',
          icon:'none'
        });
        return
      }
      this.setData({
        wait:true
      })
      fadanHandle(
        'https://mpapi.qizuang.com/v1/fb?src='+app.globalData.sourceMark,
        {
          cs:this.data.cityId,
          tel:this.data.tel,
          src:app.globalData.sourceMark,
          source:'19070416'
        }
      ).then(res=>{
        this.setData({
          wait:false,
          showWin:false
        })
        if(res.error_code===0){
          tt.showToast({
            title:"预约成功"
          })
        }
      }).catch(res=>{
        this.setData({
          wait:false
        })
        tt.showToast({
          title:"网络错误，请稍后重试",
          icon:"none"
        })
      })
    }
  }
})