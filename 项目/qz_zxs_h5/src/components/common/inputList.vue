<template>
  <section>
    <ul class="form-box">
      <li class="input-box" v-if="formInfo.isCity">
        <div class="address-box" @click="openPicker">
          <i class="fa fa-map-marker"></i>{{addrText}}
        </div>
      </li>
      <li v-for="(item,index) in formInfo.inputArray" :key="index" class="input-box" @click="hidePicker">
        <template v-if="item.maxlength"><input :type="item.type" :placeholder="item.placeholder" :name="item.name" :maxlength="item.maxlength" v-model="item.value" @click="cityShow"></template>
        <template v-if="!item.maxlength"><input :type="item.type" :placeholder="item.placeholder" :name="item.name" v-model="item.value" @click="cityShow"></template>
        <template v-if="item.danwei"><span class="danwei">{{item.danwei}}</span></template>
      </li>
      <li class="input-box" id="shenming" style="border:none" @click="hidePicker">
        <label @click="isDisclaimer">
          <i class="fa fa-check" v-if="disclaimer"></i>
        </label>
        <span>我已阅读并同意齐装网的</span>
        <!-- <a href="/disclaimer"><span>《免责声明》</span></a> -->
        <span href="javascript:void(0)" @click='toMianze' class="a"><span>《免责声明》</span></span>
      </li>
      <li class="input-box" style="border:none" @click="hidePicker">
        <button class="m-b-btn" type="button" @click="submit">{{formInfo.buttonText}}</button>
      </li>
    </ul>
    <city-select ref='city' v-on:getCityVlaue="getCityVlaue"></city-select>
    <m-tips ref="tips"/>
  </section>
</template>
<script>
import mTips from '../common/mTips.vue' // 引入tips 提示框
import citySelect from '../common/citySelect.vue'
import router from '../../router/index.js'
export default {
  name: 'inputList',
  components: {
    citySelect, mTips
  },
  props: ['formInfo'],
  data () {
    return {
      addrText: '请选择您所在的城市',
      cs: '',
      qx: '',
      disclaimer: true,
      formData: null
    }
  },
  methods: {
    isDisclaimer () {
      this.disclaimer = !this.disclaimer
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
        parms['fb_type'] = this.formInfo.fdType
        parms['source'] = this.formInfo.source
        if (from === 'app') {
          that.$bridge.callNative('getChannelSrc', {}, function (src) {
            that.formInfo.callBack(parms, that.$parent, src)
          })
        } else {
          that.formInfo.callBack(parms, that.$parent, 'zxs-h5')
        }
      }
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
    toMianze () {
      router.push('/disclaimer')
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
        // 验证名称
        if (yzData[i].name === 'name') {
          if (yzData[i].value === '') {
            that.$refs.tips.tipsFadeIn({
              text: yzData[i].tips[0]
            })
            return false
          }
          let reg = new RegExp('^[\u4e00-\u9fa5a-zA-Z]+$')
          if (!reg.test(yzData[i].value)) {
            that.$refs.tips.tipsFadeIn({
              text: yzData[i].tips[1]
            })
            return false
          }
        }
        // 验证小区
        if (yzData[i].name === 'xiaoqu') {
          if (yzData[i].value === '') {
            that.$refs.tips.tipsFadeIn({
              text: yzData[i].tips[0]
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
    cityShow() {
      this.$refs.city.cityif()
    }
  }
}
</script>
<style scoped>
  .input-box {
    border: 1px solid #ddd;
    height: 48px;
    position: relative;
    margin:0.1rem 0px;
    overflow: hidden;
  }
  .input-box input{
    width:100%;
    height: 100%;
    padding-left:8px;
    font-size: 0.125rem
  }
  .input-box .danwei{
    position: absolute;
    right: 10px;
    height: 48px;
    color:#999;
    top:0px;
    line-height: 48px;
  }
  #shenming{
      text-align: left;
      border: none;
      font-size: 0.1rem;
      height: 20px;
  }
  #shenming .a{
    color: #468dcc;
    border-bottom: 1px solid #468dcc;
    display: inline-block;
    margin-left: -2px;
    line-height: 13px;
    height: 14px;
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
}
#shenming a span{
    display: inline-block;
    width: 6em;
    text-indent: -0.5em;
    color: #468dcc;
}
.m-b-btn {
    height: 50px;
    width: 100%;
    background: #ff5659;
    display: block;
    color: #fff;
    line-height: 50px;
    text-align: center;
    font-size: 1.2em;
    outline: none;
    border:none;
    -webkit-appearance: none;
}
.address-box{
  background: #fff;
  height: 100%;
  line-height: 48px;
}
.address-box i{
  margin:0px 8px;
}
</style>
