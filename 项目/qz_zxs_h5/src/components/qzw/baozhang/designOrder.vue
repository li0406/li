<template>
  <section>
    <div class="zxfw-dialog">
      <div class="zxfw-content">
        <div class="dia-tit">
          <img src="../../../assets/img/qzw/dia-tit.png" />
        </div>
        <div class="zxfw-fadan">
          <ul class="m-bj-edit">
            <li class="input-box jsq-city" v-if="formInfo.isCity">
              <div class="address-box" @click="openPicker">
                <img src="../../../assets/img/qzw/map.png" class="map">
                {{addrText}}
              </div>
            </li>
            <li>
              <input class="m-row-int1 m-bj-edit-list" type="tel" v-model="formInfo.inputArray[0].value" maxlength="11" name="tel-number" placeholder="请输入手机号">
            </li>
          </ul>
          <div class="input-box" id="shenming" style="border:none" @click="hidePicker">
            <label @click="isDisclaimer">
              <i class="fa fa-check" v-if="disclaimer"></i>
            </label>
            <span>我已阅读并同意齐装网的</span>
            <span @click='toMianze' class="a"><span>《免责声明》</span></span>
          </div>
          <!-- 立即计算报价按钮 -->
          <div style="margin:0 0.128rem;" @click="submit">
            <button type="button" class="sheji-btn">
              0元获取4份设计方案
            </button>
          </div>

        </div>
        <div class="close-btn" @click="closeDia">
          <img src="../../../assets/img/qzw/close.png" />
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
import {zxfw} from '@/api/api'
import router from "@/router";
import {getInfo} from "@/api/activityApi";
export default {
  name: 'designOrder',
  components: {
    citySelect,
    mTips
  },
  data () {
    return {
      addrText: '请选择您所在的城市',
      disclaimer: true,
      endCall: false,
      formInfo: {
        source: '',
        inputArray: [
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
        callBack: function (obj, parent, src) {
          let that = this
          zxfw(obj, src).then((res) => {
            if (res.data.error_code === 0) {
              parent.isShow = false
              parent.isSuccess = true
              parent.successHtml = '获取成功！'
              setTimeout(function () {
                parent.isSuccess = false
              },1000)
            } else {
              parent.isSuccess = true
              parent.successHtml = res.data.error_msg
              setTimeout(function () {
                parent.isSuccess = false
              },1000)
            }
          })
        }
      }
    }
  },
  created () {
    this.formInfo.source = this.$globalFun.setSource(this.$route.query.source, 'fuwu')
    this.getHasPhone()
  },
  methods: {
    getHasPhone () {
      if (!this.endCall) {
        setTimeout(() => {
          this.getHasPhone()
          this.getAppStatus()
        }, 200)
      }
    },
    getAppStatus () {
      this.$bridge.callNative('base_userData', {}, res => {
        if (res) {
          this.$cookies.set('token', res)
          getInfo().then(res => {
            if (res.data.error_code === 0) {
              this.formInfo.inputArray[0].value = res.data.data.info.phone
              this.endCall = true
            }
          })
        }
      })
    },
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
    // 关闭弹窗
    closeDia () {
      this.$parent.isShow = false
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
      if (this.validateForm()) {
        let yzData = this.formInfo.inputArray
        for (let i in yzData) {
          parms[yzData[i].name] = yzData[i].value
        }
        parms['cs'] = this.cs
        parms['qx'] = this.qx
        parms['source'] = this.formInfo.source
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
    width: 100%;
    vertical-align: middle;
  }
  .zxfw-dialog{
    width: 100%;
    height: 100%;
    position: fixed;
    z-index: 99;
    top: 0;
    bottom: 0;
    right: 0;
    left: 0;
    margin: auto;
    background: rgba(0,0,0,0.6);
  }
  .zxfw-dialog .zxfw-content{
    width: 2.688rem;
    height: 2.368rem;
    background: #fff;
    border-radius: 6px;
    position: absolute;
    top:0;
    bottom: 0;
    right: 0;
    left: 0;
    margin: auto;
  }
  .zxfw-content .dia-tit{
    width: 1.933rem;
    height: 0.316rem;
    margin: 0 auto;
    position: relative;
    top: -0.064rem;
  }
  .zxfw-fadan{
    margin-top: 0.107rem;
  }
  .m-bj-edit{
    margin:0 0.128rem;
  }
  .m-bj-edit li{
    margin-bottom: 0.128rem;
    position: relative;
  }
  .m-bj-edit .map{
    display: block;
    width: 0.125rem;
    height: 0.125rem;
    position: absolute;
    top: 0.129rem;
    left: 10px;
    z-index: 99;
  }
  .m-bj-edit button{
    color: #333 !important;
  }
  .m-bj-edit li .address-box,.m-bj-edit li .m-row-int1{
    height: 0.384rem;
    line-height: 0.384rem;
    font-size: 0.119rem;
    border: none;
    outline: none;
    background: #EDF1F3;
    border-radius: 6px;
    padding-left: 10px;
    width: -webkit-fill-available;
  }
  .m-bj-edit li .address-box{
    padding-left: 0.22rem;
  }
  .sheji-btn{
    display: block;
    width: 100%;
    height: 0.384rem;
    border-radius: 6px;
    background: #C5A771;
    border: none;
    outline: none;
    font-size: 0.154rem;
    color: #fff;
  }
  #shenming{
    margin:0.1rem 0.128rem;
    text-align: left;
    font-size: 0.1rem;
    height: 20px;
    position: relative;
    overflow: hidden;
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
  .close-btn{
    width: 0.316rem;
    height: 0.316rem;
    position: absolute;
    left: 0;
    right: 0;
    top: 2.624rem;
    margin: auto;
  }
</style>
