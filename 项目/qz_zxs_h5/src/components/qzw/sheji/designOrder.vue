<template>
  <section>
    <div class="sheji-fadan">
      <div class="top_border">
        深耕8载 比熟人更靠谱
      </div>
      <div class="sheji-icon">
        <div class="icon-list">
          <img src="../../../assets/img/qzw/icon5.png">
          <p>电话回访</p>
          <span class="icon-line"></span>
        </div>
        <div class="icon-list">
          <img src="../../../assets/img/qzw/icon6.png">
          <p>上门量房</p>
          <span class="icon-line"></span>
        </div>
        <div class="icon-list">
          <img src="../../../assets/img/qzw/icon7.png">
          <p>出设计图</p>
          <span class="icon-line"></span>
        </div>
        <div class="icon-list">
          <img src="../../../assets/img/qzw/icon8.png">
          <p>确认方案</p>
        </div>
      </div>
      <div class="fadan-box">
        <ul>
          <li>
            <div class="comm comm-left">
              <img class="left" src="../../../assets/img/qzw/map.png" />
              <span>所在城市</span>
            </div>
            <div class="comm comm-right" @click="openPicker">
              {{addrText}}
              <img class="more-city" src="../../../assets/img/qzw/more-city.png" />
            </div>
          </li>
          <li>
            <div class="comm comm-left">
              <img class="left" src="../../../assets/img/qzw/mianji.png" />
              <span>房屋面积</span>
            </div>
            <div class="comm comm-right" style="padding-right: 0.1rem;width: 64%;">
              <input type="number" v-model="formInfo.inputArray[0].value" name="mianji" class="sheji-tele" placeholder="请输入您的房屋面积" />
            </div>
          </li>
          <li>
            <div class="comm comm-left">
              <img class="left" src="../../../assets/img/qzw/phone.png" />
              <span>联系方式</span>
            </div>
            <div class="comm comm-right" style="padding-right: 0.1rem;width: 64%;">
              <input type="tel" maxlength="11" v-model="formInfo.inputArray[1].value" name="telephone" class="sheji-tele" placeholder="请输入您的联系方式" />
            </div>
          </li>
          <li class="input-box" id="shenming" style="border:none" @click="hidePicker">
            <label @click="isDisclaimer">
              <i class="fa fa-check" v-if="disclaimer"></i>
            </label>
            <span>我已阅读并同意齐装网的</span>
            <span href="javascript:void(0)" @click='toMianze' class="a"><span>《免责声明》</span></span>
          </li>
        </ul>
        <div class="sheji-btn" @click="submit">
          <img src="../../../assets/img/qzw/shejibtn.gif" />
        </div>
      </div>
    </div>
    <city-select ref='city' v-on:getCityVlaue="getCityVlaue"></city-select>
    <m-tips ref="tips"/>
  </section>
</template>
<script>
import mTips from '../../common/mTips.vue' // 引入tips 提示框
import citySelect from '../../common/citySelect.vue'
import {xhxsj, isFinishTask} from '@/api/api'
import router from "@/router";
export default {
  name: 'baojia',
  components: {
    citySelect,
    mTips
  },
  data () {
    return {
      addrText: '请选择您所在的城市',
      disclaimer: true,
      orderId: '',
      formInfo: {
        source: '',
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
            name: 'tel',
            placeholder: '请输入您的手机号获取报价结果',
            type: 'tel',
            maxlength: 11,
            value: '',
            tips: [
              '您填写的手机号码为空^_^!',
              '请填写正确的手机号码^_^!'
            ]
          }
        ],
        isCity: true,
        callBack: function (parms, parent, src) {
          xhxsj(parms, src).then((res) => {
            if (res.data.error_code === 0) {
              isFinishTask({
                phone: parms.tel,
                source: parms.source
              }).then(res => {
                // do nothing
                location.href = '/xhxsj-success'
              })
              //location.href = '/xhxsj-success'
            } else {
              parent.$refs.tips.tipsFadeIn({
                text: res.data.error_msg
              })
            }
          })
        }
      }
    }
  },
  created () {
    this.formInfo.source = this.$globalFun.setSource(this.$route.query.source, 'sheji')
  },
  methods: {
    isDisclaimer () {
      this.disclaimer = !this.disclaimer
    },
    toMianze () {
      router.push('/disclaimer')
    },
    openPicker () {
      this.$refs.city.openPicker()
    },
    hidePicker () {
      this.$refs.city.cancel()
    },
    getCityVlaue (city) {
      this.addrText = city[0].name + ' ' + city[1].name + ' ' + city[2].name
      this.cs = city[1].id
      this.qx = city[2].id
    },
    validateForm () {
      let that = this
      let yzData = that.formInfo.inputArray
      if (that.addrText === '请选择您所在的城市') {
        that.$refs.tips.tipsFadeIn({
          text: '请选择您所在的城市'
        })
        return false
      }
      for (let i in yzData) {
        // 验证面积
        if (yzData[i].name === 'mianji') {
          if (yzData[i].value === '') {
            that.$refs.tips.tipsFadeIn({
              text: yzData[i].tips[0]
            })
            return false
          }
          let reg = /^(-?\d+)(\.\d+)?$/
          if (!reg.test(yzData[i].value)) {
            that.$refs.tips.tipsFadeIn({
              text: yzData[i].tips[1]
            })
            return false
          }
          if (yzData[i].value < 1 || yzData[i].value > 10000) {
            that.$refs.tips.tipsFadeIn({
              text: yzData[i].tips[2]
            })
            return false
          }
        }
        // 验证号码
        if (yzData[i].name === 'tel') {
          let reg = new RegExp('^(13|14|15|16|17|18|19)[0-9]{9}$')
          if (yzData[i].value === '') {
            that.$refs.tips.tipsFadeIn({
              text: yzData[i].tips[0]
            })
            return false
          }
          if (!reg.test(yzData[i].value)) {
            that.$refs.tips.tipsFadeIn({
              text: yzData[i].tips[1]
            })
            return false
          }
        }
      }
      // 验证免责声明
      if (!this.disclaimer) {
        this.$refs.tips.tipsFadeIn({
          text: '请勾选我已阅读并同意齐装网的《免责声明》'
        })
        return false
      }
      return true
    },
    submit () {
      let that = this
      let from = that.$route.query.from
      let parms = {}
      if (that.validateForm()) {
        let yzData = that.formInfo.inputArray
        for (let i in yzData) {
          parms[yzData[i].name] = yzData[i].value
        }
        parms['cs'] = that.cs
        parms['qx'] = that.qx
        parms['source'] = that.formInfo.source
        if (from === 'app') {
          that.$bridge.callNative('getChannelSrc', {}, function (src) {
            that.formInfo.callBack(parms, that.$parent, src)
          })
        } else {
          that.formInfo.callBack(parms, that.$parent, 'zxs-h5')
        }
      }
    }
  }
}
</script>
<style scoped>
  img{
    width:100%;
    vertical-align: middle;
  }
  .sheji-fadan{
    width: 92%;
    margin: 0 auto;
    background: #fff;
    border-radius: 6px;
    position: relative;
    top: -0.24rem;
    padding-bottom: 0.16rem;
  }
  .sheji-fadan .top_border{
    width: 2.16rem;
    background: url(../../../assets/img/qzw/top_border.png) no-repeat;
    background-size: 100% 100%;
    position: relative;
    top: -0.1rem;
    margin: 0 auto;
    color: #fff;
    text-align: center;
    height: 0.32rem;
    line-height: 0.32rem;
    font-size: 0.15rem;
    font-weight: bold;
  }
  .sheji-icon{
    overflow: hidden;
  }
  .sheji-icon .icon-list{
    width:25%;
    display: inline-block;
    float: left;
    position: relative;
  }
  .icon-list img{
    display: block;
    width: 0.218rem;
    height: 0.218rem;
    margin:0 auto;
  }
  .icon-list p{
    text-align: center;
    font-size: 12px;
    margin-top:6px;
    color:#666;
  }
  .sheji-icon .icon-line{
    display: inline-block;
    width: 10px;
    height: 0;
    position: absolute;
    border-top: 1px #666 solid;
    right: -5px;
    bottom: 7px;
  }
  .sheji-fadan .fadan-box{
    margin: 0 0.125rem;
    margin-top: 0.24rem;
  }
  .fadan-box ul li{
    height: 0.428rem;
    background: #F3F3F3;
    margin-bottom: 0.172rem;
    font-size: 0.12rem;
  }
  .fadan-box ul li div.comm{
    display: inline-block;
    position: relative;
  }
  .fadan-box ul li div.comm-left{
    width: 30%;
  }
  .fadan-box ul li div.comm-right{
    width: 59%;
    line-height: 0.428rem;
    text-align: right;
    padding-right: 0.22rem;
  }
  .fadan-box ul li div img.left{
    display: inline-block;
    width: 0.125rem;
    height: 0.125rem;
    position: absolute;
    top: 0;
    bottom: 0;
    left: 8px;
    margin: auto;
  }
  .fadan-box .comm-right .more-city{
    display: inline-block;
    width: 0.125rem;
    height: 0.125rem;
    position: absolute;
    top: 0;
    bottom: 0;
    right: 5px;
    margin: auto;
  }
  .fadan-box ul li div span{
    display: inline-block;
    height: 0.428rem;
    line-height: 0.428rem;
    margin-left: 28px;
  }
  .sheji-city,.sheji-tele{
    height: 100%;
    width: 100%;
    border: none;
    background: none;
    font-size: 1em;
    text-align: right;
    white-space: nowrap;
    outline: none;
  }
  .fa-map-marker{
    display: none;
  }
  .sheji-tele{
    display: block;
    height: 0.428rem;
    color: #333;
  }
  #shenming{
    margin:0.1rem 10px;
    text-align: left;
    font-size: 0.1rem;
    height: 20px;
    position: relative;
    overflow: hidden;
    background: none;
  }
  #shenming label{
    display: inline-block;
    width: 11px;
    height: 11px;
    border: 1px solid #666;
    position: relative;
    top: 1.4px;
  }
  #shenming label i{
    color: #468dcc;
    position: absolute;
    font-size: 10px;
  }
  #shenming .a{
    color: #468dcc;
    border-bottom: 1px solid #468dcc;
    display: inline-block;
    margin-left: -2px;
    line-height: 13px;
    height: 14px;
  }
</style>
