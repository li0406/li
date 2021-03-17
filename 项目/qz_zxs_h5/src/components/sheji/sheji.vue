<template>
  <section class="bj-body">
    <div class="sj-banner">
      <img src="../../assets/img/sheji/banner.jpg">
    </div>
    <div class="out-box">
      <div class="adv-title">免费获取
        <span class="high-light">4套装修设计方案</span>
      </div>
      <input-list :formInfo="formInfo"></input-list>
      <div class="adv-title">专业设计师
        <span class="high-light">一对一服务</span>
      </div>
      <div class="swiper-container">
        <swiper :options="swiperOption">
          <!-- slides -->
          <swiper-slide>
            <div class="half-box-left">
              <div class="touxiang">
                <img src="../../assets/img/sheji/xulibo.jpg">
              </div>
              <div class="sheji-name">许立波</div>
              <div class="he-say">用心打造完美生活</div>
              <div class="he-detial">
                <span>9</span>年工作经验
              </div>
            </div>
            <div class="half-box-right">
              <div class="hisZuopin">
                <img src="../../assets/img/sheji/zp-xulibo.jpg">
              </div>
              <div class="hisZuopin">
                <img src="../../assets/img/sheji/zp-xulibo-1.jpg">
              </div>
            </div>
          </swiper-slide>
          <swiper-slide>
            <div class="half-box-left">
              <div class="touxiang">
                <img src="../../assets/img/sheji/shengjiawei.png">
              </div>
              <div class="sheji-name">盛加伟</div>
              <div class="he-say">打造个性私人住宅</div>
              <div class="he-detial">
                <span>9</span>年工作经验
              </div>
            </div>
            <div class="half-box-right">
              <div class="hisZuopin">
                <img src="../../assets/img/sheji/zp-sjw.jpg">
              </div>
              <div class="hisZuopin">
                <img src="../../assets/img/sheji/zp-sjw-1.jpg">
              </div>
            </div>
          </swiper-slide>
          <swiper-slide>
            <div class="half-box-left">
              <div class="touxiang">
                <img src="../../assets/img/sheji/jianghailin.png">
              </div>
              <div class="sheji-name">蒋海林</div>
              <div class="he-say">打造自然、舒适的生活理念</div>
              <div class="he-detial">
                <span>10</span>年设计经验
              </div>
            </div>
            <div class="half-box-right">
              <div class="hisZuopin">
                <img src="../../assets/img/sheji/zp-jhl.jpg">
              </div>
              <div class="hisZuopin">
                <img src="../../assets/img/sheji/zp-jhl-1.jpg">
              </div>
            </div>
          </swiper-slide>
          <!-- Optional controls -->
          <div class="swiper-button-prev" slot="button-prev">
            <i class="fa fa-chevron-left"></i>
          </div>
          <div class="swiper-button-next" slot="button-next">
            <i class="fa fa-chevron-right"></i>
          </div>
        </swiper>
      </div>
      <div class="adv-title">私人订制
        <span class="high-light">独特风格</span>
      </div>
      <div class="fg-table">
        <div class="fg-title">
          <p>装修</p>
          <span>前</span>
        </div>
        <div class="fg-img">
          <img src="../../assets/img/sheji/zx-berfore.jpg" alt>
        </div>
      </div>
      <br>
      <div class="fg-table">
        <div class="fg-img">
          <div class="fg-img">
            <img src="../../assets/img/sheji/zx-after.jpg" alt>
          </div>
        </div>
        <div class="fg-title">
          <p>装修</p>
          <span>后</span>
        </div>
      </div>
      <div class="adv-title">三步
        <span class="high-light">高效出图</span>
      </div>
      <img src="../../assets/img/sheji/gxct.jpg" class="single-img">
      <div class="adv-title">申请即送
        <span class="high-light">2项增值服务</span>
      </div>
      <p class="small-title">免费上门量房</p>
      <img src="../../assets/img/sheji/free-getSize.jpg" class="single-img">
      <p class="small-title">免费规划预算</p>
      <img src="../../assets/img/sheji/free-getYusan.jpg" class="single-img">
      <table class="small-title-bottom">
        <tr>
          <td>
            <span>4份设计</span>报价PK
          </td>
          <td>
            <span>正规</span>装修公司
          </td>
          <td style="text-align:center">
            <span>决绝</span>恶意增项
          </td>
        </tr>
      </table>
    </div>
    <m-tips ref="tips"/>
  </section>
</template>
<script>
import mTips from '../common/mTips.vue' // 引入tips 提示框
import inputList from '../common/inputList.vue'
import 'swiper/dist/css/swiper.css'
import { swiper, swiperSlide } from 'vue-awesome-swiper'
import { sheji } from '@/api/api'
export default {
  name: 'sheji',
  components: {
    inputList,
    swiper,
    swiperSlide,
    mTips
  },
  data () {
    return {
      addText: '',
      formInfo: {
        source: 19011620,
        inputArray: [
          {
            name: 'mianji',
            placeholder: '请输入您的面积',
            type: 'number',
            danwei: '㎡',
            value: '',
            tips: [
              '请填写您的房屋面积^_^!',
              '请填写正确的房屋面积^_^!',
              '房屋面积请输入1-10000之间的数字^_^!'
            ]
          },
          {
            name: 'xiaoqu',
            placeholder: '填写小区名称以便准确匹配',
            type: 'text',
            value: '',
            tips: ['请填写您的小区以便精准匹配^_^!']
          },
          {
            name: 'tel',
            placeholder: '请输入您的手机号码获取装修设计方案',
            type: 'tel',
            maxlength: 11,
            value: '',
            tips: ['您填写的手机号码为空^_^!', '请填写正确的手机号码^_^!']
          }
        ],
        buttonText: '立即获取',
        isCity: true,
        callBack: function (parms, parent, src) {
          sheji(parms, src).then((res) => {
            if (res.data.error_code === 0) {
              location.href = '/shejidone'
            } else {
              parent.$refs.tips.tipsFadeIn({
                text: res.data.error_msg
              })
            }
          })
        }
      },
      swiperOption: {
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev'
        }
      }
    }
  },
  created () {
    this.formInfo.source = this.$globalFun.setSource(this.$route.query.source, 'sheji')
    setInterval(() => {
      this.callNative()
    }, 200)
  },
  methods: {
    getCityVlaue (city) {
      this.addText = city[0].name + ' ' + city[1].name + ' ' + city[2].name
    },
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
.sj-banner {
  width: 100%;
}
.sj-banner img {
  width: 100%;
  vertical-align: top;
}
.bj-body {
  background: #f5f5f5;
}
.adv-title {
  text-align: center;
  font-size: 0.2rem;
  margin: 0.25rem auto 0.15rem auto;
}
.adv-title span {
  font-weight: bold;
  padding-left: 5px;
}
.out-box {
  margin: 0px 15px;
}
.fg-table {
  display: table;
  height: 1.2rem;
}
.fg-table div {
  display: table-cell;
  vertical-align: middle;
  text-align: center;
}
.fg-title {
  width: 2%;
}
.fg-img {
  width: 90%;
}
.fg-img img {
  width: 90%;
}
.fg-title span {
  color: #ff5353;
}
.single-img {
  width: 100%;
  margin: 20px 0px;
}
.small-title {
  text-align: center;
  padding: 8px 0px;
  font-size: 0.16rem;
}
.small-title-bottom {
  font-size: 0.11rem;
  width: 100%;
  margin-top: -20px;
  text-align: left;
  padding-bottom: 0.2rem;
  table-layout: fixed;
}
.small-title-bottom span {
  color: #ff5353;
}
.half-box-left {
  width: 45%;
  float: left;
}
.half-box-left {
  width: 45%;
  float: left;
}
.half-box-right {
  width: 55%;
  float: right;
}
.touxiang {
  width: 1rem;
  height: 1rem;
  overflow: hidden;
  border-radius: 0.5rem;
  margin: 0.15rem auto;
}
.touxiang img {
  width: 100%;
  height: 100%;
  margin: 0px auto;
}
.sheji-name {
  margin: 0.11rem auto;
  font-size: 0.2rem;
  text-align: center;
}
.he-say {
  margin: 0.08rem auto;
  text-align: center;
  color: #727272;
  font-size: 0.125rem;
}
.he-detial {
  margin: 0.1rem auto;
  text-align: center;
}
.he-detial span {
  color: #ff5353;
  font-size: 0.15rem;
  padding-right: 5px;
}
.hisZuopin {
  height: 1rem;
  margin: 0.14rem auto;
  overflow: hidden;
  text-align: center;
}
.swiper-button-next,
.swiper-container-rtl .swiper-button-prev {
  background: none;
  color: #ff5353;
  font-size: 0.2rem;
  right: 0px;
}
.swiper-button-prev,
.swiper-container-rtl .swiper-button-next {
  background: none;
  color: #ff5353;
  font-size: 0.2rem;
}
.swiper-button-prev:focus{
  outline: none;
}
.swiper-button-next:focus{
  outline: none;
}
</style>
