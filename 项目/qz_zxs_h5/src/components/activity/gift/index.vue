<template>
  <section>
    <div class="health_img">
      <img src="http://staticqn.qizuang.com/custom/20200331/Fj5lvBSTvu_01rskxo6jBSe_qQQP" />
    </div>
  </section>
</template>
<script>
export default {
  name: 'gift',
  data () {
    return {
        endCall: false
    }
  },
  created () {
      let that = this
      that.changeHeader()
  },
  mounted () {
    document.title = '装修送好礼-齐装网7周年钜惠'
  },
  methods: {
      setShare () {
          let that = this
          // 分享成功，实际环境
          that.$bridge.callNative('base_share', {
              mShareUrl: 'http://zxs.h5.qizuang.com/activity/gift',
              mShareTitle: '装修送好礼-来自齐装APP的分享',
              mShareSubTitle: '齐装网7周年钜惠好礼等你来领取~',
              imagArray: ['http://staticqn.qizuang.com/custom/20191205/FpXm_VC3_gwrnD_eTE_HOBO5YN6Z']
          }, function (res) {

          })
      },
      changeHeader () {
          let that = this
          that.$bridge.callNative('header', {
              navTitle: '装修送好礼-齐装网7周年钜惠',
              customMenuList: [
                  {
                      text: '',
                      name: 'share',
                      menuStatus: '2',
                      textColor: '',
                      menuType: '2',
                      menuImageUrl: ''
                  }
              ]
          }, function (res) {
              if (res) {
                  that.endCall = true
              }
          })
          // 回退
          that.$bridge.callNative('UI_onBackClick', '000', function (res) {
              if (res) {
                  that.endCall = true
              }
          })
          that.$bridge.registWebNew('JS_onBackClick', function (data, nativeCallBack) {
              nativeCallBack(1)
          })
          // 右上角分享回调
          that.$bridge.registWebNew('JS_onNavClick', function (res, nativeCallBack) {
              let data = JSON.parse(res)
              if (data.key === 'share') {
                  that.setShare()
              }
          })
          if (!that.endCall) {
              setTimeout(function () {
                  that.changeHeader()
              }, 200)
          }
      }
  }
}
</script>
<style scoped>
  *{
    margin:0;
    padding:0;
  }
  .health_img img{
    width:100%;
  }
</style>
