<template>
  <section class="success-box">
    <div class="res-box">
      <img src="../../assets/img/sheji/fd-success.png" class="res-img"/>
      <div class="res-title">
        获取成功
      </div>
      <p class="res-text">【齐装网】给小主请安！小齐已经接到您的装修圣旨，正马不停蹄赶来您的身边，并在第一时间与您联系，请注意接听哦！</p>
      <div class="res-link">
        如有疑问致电<span><a :href="'tel:'+tel">{{tel}}</a></span>
      </div>
      <div class="bottom-distance">

      </div>
    </div>
  </section>
</template>
<script>
import globalData from '../../utils/globalData.js'
export default {
  name: 'shejidone',
  data () {
    return {
      tel: ''
    }
  },
  created () {
    this.tel = globalData.tel400
    setInterval(() => {
      this.callNative()
    }, 200)
  },
  methods: {
    callNative () {
      this.$bridge.callNative('UI_onBackClick', '000', function (res) {})
      this.$bridge.registWebNew('JS_onBackClick', function (data, nativeCallBack) {
        nativeCallBack(-1)
      })
    }
  }
}
</script>
<style scoped>
.success-box{
  height: calc(100% - 50px);
  display: table;
  width: 100%;
}
.res-box{
  display: table-cell;
  vertical-align: middle;
  text-align: center
}
.res-img{
  width:15%
}
.res-title{
  font-size: 0.18rem;
  background: linear-gradient(to bottom, #ff5353, #fcb165);
  -webkit-background-clip: text;
  color: transparent;
  padding-top:0.05rem;
}
.res-text{
  padding:0px 15px;
  padding-top:0.1rem;
  color:#999;
  font-size:0.12rem;
  padding-bottom:0.12rem;
}
.res-link span{
  color:#ff5353;
  padding-left:5px;
}
.bottom-distance{
  height: 2rem;
}
</style>
