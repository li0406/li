<template>
  <section style="margin-top:0.25rem">
    <div class="compute-form" v-show="showForm">
      <div class="form-item" @click="openPicker">
        <img src="../../../assets/img/qz/zxkb/icon-addr.png" alt="">
        <span class="input-span">{{cityInfo.text}}</span>
        <i class="fa fa-chevron-down pfm"></i>
      </div>
      <div class="form-item">
        <img src="../../../assets/img/qz/zxkb/icon-mianji.png" alt="">
        <input type="number" placeholder="请输入您的面积" v-model="mianji" @click="cityShow" oninput="if(value.length>4)value=value.slice(0,4)">
        <span class="pfm">㎡</span>
      </div>
      <div class="form-item" @click="openHx">
        <img src="../../../assets/img/qz/zxkb/icon-ys.png" alt="">
        <span class="input-span">{{hxSelectInfo.name}}</span>
        <i class="fa fa-chevron-down pfm"></i>
      </div>
      <div class="form-item">
        <img src="../../../assets/img/qz/zxkb/icon-phone.png" alt="">
        <input type="tel" placeholder="请输入手机号" @click="cityShow" v-model="orderParms.tel" maxlength="11">
      </div>
      <div id="shenming"  @click="hidePicker">
        <label @click="isDisclaimer">
          <i class="fa fa-check" v-if="disclaimer"></i>
        </label>
        <span>我已阅读并同意齐装网的</span>
        <span class="a" @click="toMianze"><span>《免责声明》</span></span>
      </div>
    </div>
    <div class="compute-btn" v-bind:class="{ 'clickable': !clickAble}" @click="toCumpute" v-show="!showResult">
      计算我的装修成本
    </div>
    <section id="hz-picker" v-show="!isHide">
      <div style="width:100%; height:100%;"  @click.stop='cancel'></div>
      <div :class="isHide?'m-picker-box':'m-picker-box open-picker'">
        <div class="m-picker-tool">
          <span class="m-picker-cancel" @click="cancel">取消</span>
          <span class="m-picker-ok" @click="okBtn">确定</span>
        </div>
        <mt-picker :slots="hxSelect" @change="onValuesChange" value-key="name"></mt-picker>
      </div>
    </section>
    <m-tips ref="tips"/>
    <order-result v-if="showResult" :baseInfo="resultData"></order-result>
    <div class="bottom-button">
      <a href="#showForm"  @click="toShowComputed" >
        <img src="../../../assets/img/qz/zxkb/small-packet.png" alt="">
      </a>
    </div>
    <city-select ref='city' v-on:getCityVlaue="getCityVlaue" :csInfo="city"></city-select>
  </section>
</template>
<script>
import citySelect from '../../common/citySelect.vue'
import mTips from '../../common/mTips.vue' // 引入tips 提示框
import orderResult from './orderResult.vue'
import router from '../../../router/index.js'
import {getVersion, orderForm} from '../../../api/qzZxkb.js'
import {getInfo} from '@/api/activityApi.js'
export default {
  name: 'orderForm',
  props: ['city'],
  components: {
    citySelect, mTips, orderResult
  },
  data () {
    return {
      showForm: false,
      showResult: false,
      resultData: '',
      cityInfo: {
        cs: '',
        cname: '',
        qx: '',
        text: '请选择您所在的城市'
      },
      mianji: '',
      disclaimer: true,
      isHide: true,
      tempHxid: '',
      tempHxSelect: '',
      hxSelect: [
        {
          className: 'slot1',
          values: [
            {
              id: 1,
              name: '1室'
            },
            {
              id: 2,
              name: '2室'
            },
            {
              id: 3,
              name: '3室'
            },
            {
              id: 4,
              name: '4室'
            }
          ]
        },
        {
          divider: true,
          content: '-',
          className: 'slot2'
        },
        {
          className: 'slot3',
          values: [
            {
              id: 1,
              name: '1厅'
            },
            {
              id: 2,
              name: '2厅'
            },
            {
              id: 3,
              name: '3厅'
            },
            {
              id: 4,
              name: '4厅'
            }
          ]
        },
        {
          divider: true,
          content: '-',
          className: 'slot4'
        },
        {
          className: 'slot5',
          values: [
            {
              id: 1,
              name: '1厨'
            },
            {
              id: 2,
              name: '2厨'
            },
            {
              id: 3,
              name: '3厨'
            },
            {
              id: 4,
              name: '4厨'
            }
          ]
        },
        {
          divider: true,
          content: '-',
          className: 'slot6'
        },
        {
          className: 'slot7',
          values: [
            {
              id: 1,
              name: '1卫'
            },
            {
              id: 2,
              name: '2卫'
            },
            {
              id: 3,
              name: '3卫'
            },
            {
              id: 4,
              name: '4卫'
            }
          ]
        }
      ],
      hxSelectInfo: {
        id: '1, 1, 1, 1',
        name: '1室1厅1厨1卫'
      },
      orderParms: {
        tel: '',
        mianji: '',
        hx: '0',
        qx: '',
        cs: '',
        source: '19090647'
      },
      versionList: '',
      clickAble: true,
      endCall: false,
      interVal: null
    }
  },
  created () {
    this.getVersionList()
    this.interVal = setInterval(() => {
      if (!this.endCall) {
        this.getPhone()
      } else {
        clearInterval(this.interVal)
      }
    }, 200)
  },
  watch: {
    'mianji': function (newVal) {
      this.orderParms.mianji = newVal
      if (newVal < 60) {
        this.hxSelectInfo.name = '1室 1厅 1厨 1卫'
        this.hxSelectInfo.id = [1, 1, 1, 1]
      }
      if (newVal <= 89 && newVal >= 60) {
        this.hxSelectInfo.name = '2室 1厅 1厨 1卫'
        this.hxSelectInfo.id = [2, 1, 1, 1]
      }
      if (newVal <= 149 && newVal >= 90) {
        this.hxSelectInfo.name = '3室 2厅 1厨 2卫'
        this.hxSelectInfo.id = [3, 2, 1, 2]
      }
      if (newVal >= 150) {
        this.hxSelectInfo.name = '4室 2厅 2厨 2卫'
        this.hxSelectInfo.id = [4, 2, 2, 2]
      }
    }
  },
  methods: {
    getVersionList () {
      getVersion().then(res => {
        if (res.data.error_code === 0) {
          this.versionList = res.data.data
        }
      })
    },
    toCumpute () {
      if (!this.showForm) {
        this.showForm = true
        return
      }
      if (!this.clickAble) {
        return
      }
      //  收集参数
      this.orderParms.cs = this.cityInfo.cs
      this.orderParms.qx = this.cityInfo.qx
      // 验证
      if (this.orderParms.cs === '') {
        this.$refs.tips.tipsFadeIn({
          text: '请选择您所在的城市'
        })
        return false
      }
      if (this.orderParms.mianji === '') {
        this.$refs.tips.tipsFadeIn({
          text: '请输入您的面积'
        })
        return false
      }
      let mianjiReg = /^[1-9]*[1-9][0-9]*$/
      if (!mianjiReg.test(this.orderParms.mianji)) {
        this.$refs.tips.tipsFadeIn({
          text: '面积为1~1000的正整数'
        })
        return false
      }
      if (parseInt(this.orderParms.mianji) > 1000) {
        this.$refs.tips.tipsFadeIn({
          text: '面积为1~1000的正整数'
        })
        return false
      }
      if (this.orderParms.tel === '') {
        this.$refs.tips.tipsFadeIn({
          text: '请输入您的手机号码'
        })
        return false
      }
      this.orderParms.hx = this.hxSelectInfo.id.join(',')
      let reg = new RegExp('^(13|14|15|16|17|18|19)[0-9]{9}$')
      if (!reg.test(this.orderParms.tel)) {
        this.$refs.tips.tipsFadeIn({
          text: '请输入正确11位手机号'
        })
        return false
      }
      if (!this.disclaimer) {
        this.$refs.tips.tipsFadeIn({
          text: '请勾选我已阅读并同意齐装网的《免责声明》'
        })
        return false
      }
      let from = this.$route.query.from
      this.clickAble = false
      if (from === 'app') {
        this.$bridge.callNative('getChannelSrc', {}, src => {
          if (src) {
            this.toSendOrder(src)
          }
        })
      } else {
        this.toSendOrder('zxs-h5')
      }
    },
    toSendOrder (src) {
      orderForm(this.orderParms, src).then(res => {
        if (res.data.error_code === 0) {
          this.showResult = true
          this.showForm = false
          this.setToChild()
        } else {
          this.$refs.tips.tipsFadeIn({
            text: res.data.error_msg
          })
        }
        this.clickAble = true
      }).catch(res => {
        this.$refs.tips.tipsFadeIn({
          text: res.data.error_msg
        })
        this.clickAble = true
      })
    },
    getCityVlaue (city) {
      this.cityInfo.cs = city[1].id
      this.cityInfo.qx = city[2].id
      this.cityInfo.text = city[0].name + ' ' + city[1].name + ' ' + city[2].name
    },
    setToChild () {
      let myStorage = {
        version_id: this.versionList[0].id,
        area: this.orderParms.mianji,
        hx: this.hxSelectInfo.name,
        hxId: this.hxSelectInfo.id.join(','),
        cs: this.orderParms.cs,
        versionList: this.versionList
      }
      this.resultData = myStorage
    },
    isDisclaimer () {
      this.disclaimer = !this.disclaimer
    },
    toShowComputed () {
      this.showForm = true
      this.showResult = false
    },
    toMianze () {
      router.push('/disclaimer/#main')
    },
    cancel () {
      this.isHide = true
      return false
    },
    okBtn () {
      this.isHide = true
      this.hxSelectInfo.id = this.tempHxid
      this.hxSelectInfo.name = this.tempHxSelect
    },
    onValuesChange (picker, value) {
      this.tempHxid = [value[0].id, value[1].id, value[2].id, value[3].id]
      this.tempHxSelect = value[0].name + ' ' + value[1].name + ' ' + value[2].name + ' ' + value[3].name
    },
    openPicker () {
      this.$refs.city.openPicker()
    },
    openHx () {
      this.isHide = false
    },
    hidePicker () {
      this.$refs.city.cancel()
    },
    // 获取APP信息
    getPhone () {
      let that = this
      this.$bridge.callNative('base_userData', {}, function (res) {
        if (res) {
          that.endCall = true
          that.$cookies.set('token', res)
          getInfo().then(res => {
            if (res.data.error_code === 0) {
              that.orderParms.tel = res.data.data.info.phone
            }
          })
        }
      })
    },
    cityShow() {
      this.$refs.city.cityif()
    }
  }
}
</script>
<style scoped>
.bottom-button{
  position: fixed;
  display: block;
  right: 0.1rem;
  bottom: 0.4rem;
  animation: flutter 1.2s infinite linear;
-moz-animation: flutter 1.2s infinite linear;
-webkit-animation: flutter 1.2s infinite linear;
-o-animation: flutter 1.2s infinite linear;
}
@keyframes flutter
{
0%, 70%  {
  transform: rotate(0deg);
-ms-transform: rotate(0deg);
-webkit-transform: rotate(0deg);
-o-transform: rotate(0deg);
-moz-transform: rotate(0deg);
}
80%  {
  transform: rotate(-15deg);
-ms-transform: rotate(-15deg);
-webkit-transform: rotate(-15deg);
-o-transform: rotate(-15deg);
-moz-transform: rotate(-15deg);
}
85%  {
  transform: rotate(15deg);
-ms-transform: rotate(15deg);
-webkit-transform: rotate(15deg);
-o-transform: rotate(15deg);
-moz-transform: rotate(15deg);
}
90% {
  transform: rotate(-15deg);
-ms-transform: rotate(-15deg);
-webkit-transform: rotate(-15deg);
-o-transform: rotate(-15deg);
-moz-transform: rotate(-15deg);
}
95%  {
  transform: rotate(15deg);
-ms-transform: rotate(15deg);
-webkit-transform: rotate(15deg);
-o-transform: rotate(15deg);
-moz-transform: rotate(15deg);
}
100% {
  transform: rotate(0deg);
-ms-transform: rotate(0deg);
-webkit-transform: rotate(0deg);
-o-transform: rotate(0deg);
-moz-transform: rotate(0deg);
}
}
.bottom-button img {
  width: 0.6rem;
}
.compute-btn{
  background: #FFA406;
  color:#fff;
  text-align: center;
  padding:10px;
  margin:0px 0.1rem;
  border-radius: 0.2rem;
}
.compute-form .form-item{
  background: #EDEDED;
  margin:0.1rem;
  border-radius: 8px;
  height: 43px;
  position: relative;
  padding-left:10px;
  overflow: hidden;
}
.compute-form .form-item img{
  width:18px;
  position: absolute;
  left:10px;
  margin:auto;
  top:0px;
  bottom:0px;
}
.pfm{
  right: 10px;
  top:10px;
  position: absolute;
}
input{
  background: none;
  width: 90%;
  font-size: 0.125rem;
  position: absolute;
  top:0px;
  bottom:0px;
  left:35px;
  height: 0.16rem;
  line-height: 0.16rem;
  margin: auto;
}
.input-span{
  margin:auto;
  top:0px;
  bottom:0px;
  position: absolute;
  display: block;
  height:100%;
  line-height: 43px;
  left:35px;
}
.fa-chevron-down{
  font-size: 12px;
  top:15px;
}
#hz-picker{
  width: 100%;
  height: 100%;
  position: fixed;
  bottom:0px;
  z-index: 999;
  left:0px;
  background:rgba(0, 0, 0, 0.7)
}
#hz-picker .m-picker-tool{
  overflow: hidden;
}
#hz-picker .m-picker-box{
    position: fixed;
    left:0px;
    bottom:-250px;
    width:100%;
    background:#FFF;
    transition:all 0.5s;
    -webkit-transition:all 0.5s; /* Safari */
    z-index: 9999;
  }
#hz-picker  .m-picker-tool{
    padding:10px 18px;
    border-bottom:1px solid #dedede;
    overflow:hidden;
    color:#FF5353;
  }
#hz-picker  .m-picker-cancel{
    float:left;
  }
#hz-picker  .m-picker-ok{
    float:right;
  }
#hz-picker  .open-picker{
  bottom:0px;
}
#shenming{
  text-align: left;
  border: none;
  font-size: 0.1rem;
  height: 20px;
  margin:0px 0.14rem;
  margin-bottom:0.1rem;
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
.clickable{
  background: #dedede
}
</style>
