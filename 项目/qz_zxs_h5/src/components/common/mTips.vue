<template>
  <section>
    <div class="m-tips-mask" v-if="fadeIn">
      <div class="m-mask-box"><span :class="'m-tips tips_'+info.type">{{info.text}}</span></div>
    </div>
  </section>
</template>
<script>
export default{
  name: 'mTips',
  data () {
    return {
      fadeIn: false,
      info: {
        text: '',
        type: 'error'
      }
    }
  },
  methods: {
    tipsFadeIn (options) {
      let _this = this
      _this.fadeIn = true
      _this.info.text = options.text
      _this.info.type = options.type
      setTimeout(function () {
        _this.fadeIn = false
      }, 1500)
    },
    tipsFadeOut () {
      this.fadeIn = false
    }
  }
}
</script>
<style scoped>
  .tips_error{
    background:rgba(215,55,55,1) !important;
  }
  .m-tips-mask{
    position: fixed;
    z-index:9999;
    width:100%;
    height: 100%;
    left:0px;
    right:0px;
    top:0px;
    bottom:0px;
    display: table;
  }
  .m-tips-mask .m-mask-box{
    display: table-cell;
    color:#fff;
    vertical-align:middle;
    width:100%;
    text-align:center;
  }
 .m-tips-mask .m-mask-box span{
    background:rgba(0,0,0,0.8);
    padding:8px 10px;
    border-radius:5px;
    display:inline-block;
    margin:0px auto;
    min-width:200px;
  }
</style>
