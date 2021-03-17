<template>
    <div class="common-top">
        <div class="top">
            <div class="back" @click="toBack">
                <img src="../../assets/img/qz/share/back.png" alt="">
            </div>
            <div class="c-name">{{companyMesage.jc}}</div>
            <div class="share" @click="toShare">
                <img src="../../assets/img/qz/share/icon_share_black.png" alt="">
            </div>
        </div>
    </div>
</template>
<script>
export default {
  name: 'commonTop',
  props: ['companyMesage'],
  data () {
    return {
      id: ''
    }
  },
  created () {
    this.id = this.$route.query.id
  },
  methods: {
    toBack: function () {
      this.$bridge.callNative('UI_closeWebView', {}, function (res) {

      })
    },
    toShare: function () {
      let companyName = this.companyMesage.jc
      this.$bridge.callNative('base_share', {
        mShareUrl: 'https://' + document.domain + '/share-playbill?sharebtnid=qztopshare_qzbottomshare&id=' + this.id,
        mShareTitle: companyName + '-齐装APP',
        mShareSubTitle: '我发现了优质装修公司，推荐你也来看看吧！',
        mShareIconUrl: this.companyMesage.logo
      }, function (res) {

      })
    }
  }
}
</script>

<style scoped>
.top {
    background: #fff;
    height: 44px;
    width: 100%;
    line-height: 44px;
    position: fixed;
    top: 0;
}
.top .back {
    width: 10px;
    height: 18px;
    float: left;
    margin-left: 0.2rem;
    margin-top: 13px;
}
.top .back img {
    display: block;
    width: 100%;
    height: 100%;
}
.top .c-name {
    float: left;
    width: 70%;
    height: 100%;
    text-align: center;
    font-size: 0.162rem;
    color: #333;
    margin: 0 5%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.top .share {
    float: left;
    width: 20px;
    height: 18px;
    margin-top: 13px;
}
.top .share img {
    display: block;
    width: 100%;
    height: 100%;
}
</style>
