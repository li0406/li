<template>
  <section id="zxkb-content" v-if ="showOrder">
    <div class="bg"></div>
    <div class="top-title" v-if ="showOrder">
      <div class="top-title-content" style="width:20px">
        <span style="font-size:0.27rem; font-weight:bold;">{{currentMonth}}</span>
      </div>
      <div class="top-title-content">
        <span style="font-weight:bold;">月{{cityInfo.address.city}}装修行情</span>
      </div>
    </div>
    <data-chart :city="cityInfo" v-if ="showOrder"></data-chart>
    <order-form :city="cityInfo" v-if ="showOrder"></order-form>
    <good-company :city="cityInfo" v-if ="showOrder"></good-company>
  </section>
</template>
<script>
import dataChart from './dataChart.vue'
import orderForm from './orderForm.vue'
import goodCompany from './goodCompany.vue'
import '../../../assets/css/zxkb.css'
export default {
  name: 'zxkb',
  components: {
    dataChart, orderForm, goodCompany
  },
  data () {
    return {
      cityInfo: {
        address: {
          province: '',
          city: ''
        },
        cs: ''
      },
      currentMonth: '',
      endCall: false,
      showOrder: false,
      interVal: null
    }
  },
  created () {
    this.interVal = setInterval(() => {
      if (!this.endCall) {
        this.setHeaderInfo()
      } else {
        clearInterval(this.interVal)
      }
    }, 200)
    if (process.env.NODE_ENV === 'development') {
      this.cityInfo.address.province = '江苏省'
      this.cityInfo.address.city = '苏州'
      this.cityInfo.cs = '320324'
      this.showOrder = true
    }
  },
  methods: {
    // setBaseInfo () {
    //   this.currentMonth = new Date().getMonth() + 1
    // },
    // 设置头部信息
    setHeaderInfo () {
      let that = this
      that.$bridge.callNative('header', {
        navTitle: '装修行情',
        customMenuList: [
          {
            text: '',
            name: '',
            menuStatus: '0',
            textColor: '',
            menuType: '0',
            menuImageUrl: ''
          }
        ]
      }, function (res) {
      })
      // 回退
      that.$bridge.callNative('UI_onBackClick', '000', function (res) {})
      // 回退的方法
      that.$bridge.registWebNew('JS_onBackClick', function (data, nativeCallBack) {
        nativeCallBack(1)
      })
      // 获取城市信息
      that.$bridge.callNative('base_userCity', '000', function (res) {
        if (res) {
          that.endCall = true
          let resJson = JSON.parse(res)
          that.cityInfo.address.province = resJson.provice_name
          that.cityInfo.address.city = resJson.city_name
          that.cityInfo.cs = resJson.city_id
          that.showOrder = true
        }
      })
    }
  }
}
</script>
