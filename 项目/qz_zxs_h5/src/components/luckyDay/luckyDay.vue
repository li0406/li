<template>
  <div class="m-container">
    <div class="m-body">
      <div class="m-lucky-box">
        <template v-if="!stepRadius[4]">
          <div class="sm-one">
            <div class="sm-title">
                开工<span>吉</span><span>日</span>拿不准？
            </div>
            <div class="sm-by">
                <div class="clound fl"> </div>
                <div class="fl by-nr">10秒测算最佳装修吉日</div>
                <div class="clound fr"></div>
            </div>
          </div>
          <div class="sm-two">
            <template v-for="(item,index) in stepRadius" >
              <div :class="item?'step active':'step'" :key="index" v-if="index!==4">
                <div class="step-radius">{{index+1}}</div>
              </div>
            </template>
          </div>
        </template>
        <div class="sm-three">
            <div :class="stepRadius[0]?'fade-in':'fade-out'">
              <div class="question-title">您家准备什么时候开始装修</div>
              <div class="question-chose one">
                  <template v-for="(item,index) in answer[0].list">
                    <div :class="answer[0].list[index].num==1?'question-item hasChose':'question-item'" @click="choseThis(0,index)" :key="index">
                        <i :class="answer[0].list[index].num==1?'fa fa-check-circle':'fa fa-circle-o'"></i>&nbsp;{{item.title}}
                    </div>
                  </template>
              </div>
            </div>
            <div :class="stepRadius[1]?'fade-in':'fade-out'">
              <div class="question-title">你家房屋朝向</div>
              <div class="question-chose two">
                  <template v-for="(item,index) in answer[1].list">
                    <div :class="answer[1].list[index].num==1?'question-item hasChose':'question-item'" @click="choseThis(1,index)" :key="index">
                        <img :src="answer[1].list[index].num==1?'/static/img/cx_'+(index+1)+'_1.png':'/static/img/cx_'+(index+1)+'.png'" alt="">
                        <i :class="answer[1].list[index].num==1?'fa fa-check-circle':'fa fa-circle-o'"></i>&nbsp;{{item.title}}
                    </div>
                  </template>
              </div>
            </div>
            <div :class="stepRadius[2]?'fade-in':'fade-out'">
              <div class="question-title">您的姓氏和年龄</div>
              <div class="input-box">
                <input type="text" placeholder="请输入您的姓氏" class="input-age" v-model="answer[2].family">
              </div>
              <div class="question-chose three">
                <template v-for="(item,index) in answer[2].list">
                    <div :class="answer[2].list[index].num==1?'question-item hasChose':'question-item'" @click="choseThis(2,index)" :key="index">
                        <img :src="answer[2].list[index].num==1?'/static/img/old_'+(index+1)+'_1.png':'/static/img/old_'+(index+1)+'.png'" alt="">
                        <i :class="answer[2].list[index].num==1?'fa fa-check-circle':'fa fa-circle-o'"></i>&nbsp;{{item.title}}
                    </div>
                </template>
              </div>
            </div>
            <div :class="stepRadius[3]?'fade-in four':'fade-out four'">
              <div class="question-title">选择房屋所在的城市</div>
              <div id="city_2" class="input-box" @click="openPicker">
                  <i class="fa fa-map-marker"></i>{{addText}}
              </div>
              <div class="input-box">
                  <input class="input-age" type="telphone" maxlength="11" placeholder="输入手机号码，获取测算吉日" name="telphone_number" v-model="answer[3].tel">
              </div>
              <div class="input-box" id="shenming" style="height: 0.34rem;border:none">
                <label @click="isDisclaimer">
                  <i class="fa fa-check" v-if="disclaimer"></i>
                </label>
                <span>我已阅读并同意齐装网的</span>
                <span href="/disclaimer" class="a" @click="toMianze"><span>《免责声明》</span></span>
              </div>
            </div>
            <div :class="stepRadius[4]?'fade-in five':'fade-out five'" v-if="stepRadius[4]">
              <div class="result-title">您的装修吉日</div>
              <table class="top-box">
                <tr>
                  <td class="top-left">
                    <p class="today-gl">{{result.current.y}}年{{result.current.m}}月{{result.current.d}}日</p>
                    <div class="today-nl">
                      <p class="big-num red-color">{{result.current.d}}</p>
                      <p class="small-hz red-color">{{result.current.n_month}}{{result.current.n_day}}</p>
                      <div class="owf shuom">
                        <span class="fl" style="margin-right:10px;">宜 :</span>
                        <div class="fl" style="width:80%;">
                          {{result.current.yi}}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="top-right">
                    <p style="font-size:0.13rem">吉日评分</p>
                    <p class="red-color" style="font-size:0.18rem">{{result.current.fens}}分</p>
                  </td>
                </tr>
              </table>
              <p style="text-align:left;padding:20px 0px 10px 15px">其他装修吉日</p>
              <div class="owf" style="margin:0px 15px;">
                <div class="fl other-part">
                  <div>
                    <p>{{result.other[0].n_month}}{{result.other[0].n_day}}</p>
                    <p>{{result.other[0].y}}年{{result.other[0].m}}月{{result.other[0].d}}日</p>
                    <p>吉日评分: <span class="red-color">{{result.other[0].fens}}分</span></p>
                  </div>
                </div>
                <div class="fl other-part owf">
                  <div class="fr">
                    <p>{{result.other[1].n_month}}{{result.other[1].n_day}}</p>
                    <p>{{result.other[1].y}}年{{result.other[1].m}}月{{result.other[1].d}}日</p>
                    <p>吉日评分: <span class="red-color">{{result.other[1].fens}}分</span></p>
                  </div>
                </div>
              </div>
            </div>
        </div>
      </div>
      <div class="hl-bottom">
        <template v-if="!stepRadius[4]">
          <div :class="stepSet.isNext?'page-btn':'page-btn current-page'" v-if="!stepRadius[0]&&!stepRadius[3]" @click="setPrev">&lt;上一页</div>
          <div :class="stepSet.isNext?'page-btn current-page':'page-btn'" @click="setNext" v-if="!stepRadius[3]">下一页&gt;</div>
          <div class="page-btn current-page" v-if="stepRadius[3]" @click="toCompute">立即测算</div>
        </template>
        <template v-if="stepRadius[4]">
          <div class="page-btn current-page" @click="restCompute">重新测算</div>
        </template>
      </div>
    </div>
    <m-tips ref="tips"/>
    <city-select ref='city' v-on:getCityVlaue="getCityVlaue"></city-select>
  </div>
</template>
<script>
import mTips from '../common/mTips.vue' // 引入tips 提示框
import citySelect from '../common/citySelect.vue'
import { isvalidPhone } from '../../utils/validate'
import { zbfb, hlresult } from '@/api/api'
import router from '../../router/index.js'
export default {
  components: {
    mTips, citySelect
  },
  data () {
    return {
      stepRadius: [true, false, false, false, false],
      stepSet: {
        isNext: true,
        currentNum: 0
      },
      addText: '',
      disclaimer: true,
      result: {},
      answer: [
        {
          value: '',
          list: [
            {title: '半个月内', num: 0},
            {title: '1个月内', num: 0},
            {title: '3个月内', num: 0},
            {title: '3个月以上', num: 0}
          ]
        },
        {
          value: '',
          list: [
            {title: '朝南', num: 0},
            {title: '朝北', num: 0},
            {title: '朝东', num: 0},
            {title: '朝西', num: 0},
            {title: '不太清楚', num: 0}
          ]
        },
        {
          value: '',
          list: [
            {title: '25岁以下', num: 0},
            {title: '25~35岁', num: 0},
            {title: '35~45岁', num: 0},
            {title: '45岁以上', num: 0}
          ],
          family: ''
        },
        {
          address: '',
          tel: ''
        },
        {
        }
      ]
    }
  },
  mounted: function () {
    this.stepRadius = [true, false, false, false, false]
    this.stepSet.currentNum = 0
  },
  methods: {
    setPrev () {
      this.stepSet.currentNum = this.stepSet.currentNum === 0 ? 0 : --this.stepSet.currentNum
      this.stepSet.isNext = this.stepSet.currentNum === 0
      this.setCurrent(this.stepSet.currentNum)
    },
    setNext () {
      // 验证当前题目是否回答
      let num = this.stepSet.currentNum
      let value = this.answer[num].value
      if (num === 2) {
        let reg = new RegExp('^[\u4e00-\u9fa5a-zA-Z]+$')
        if (this.answer[num].family === '') {
          this.$refs.tips.tipsFadeIn({
            text: '请输入您的姓氏'
          })
          return false
        }
        if (!reg.test(this.answer[num].family)) {
          this.$refs.tips.tipsFadeIn({
            text: '请输入正确的名称，只支持中文和英文'
          })
          return false
        }
      }
      if (value === '') {
        this.$refs.tips.tipsFadeIn({
          text: '请选择本题选项'
        })
        return false
      }
      this.stepSet.currentNum = this.stepSet.currentNum === 3 ? 3 : ++this.stepSet.currentNum
      this.stepSet.isNext = this.stepSet.currentNum !== 3
      this.setCurrent(this.stepSet.currentNum)
    },
    setCurrent (index) {
      for (let i = 0; i < this.stepRadius.length; i++) {
        this.stepRadius.splice(i, 1, (index === i))
      }
    },
    choseThis (line, column) {
      for (let i = 0; i < this.answer[line].list.length; i++) {
        this.answer[line].list[i].num = column !== i ? 0 : 1
      }
      this.answer[line].value = column + 1
    },
    toCompute () {
      let that = this
      let num = this.stepSet.currentNum
      if (num === 3) {
        if (this.answer[num].address === '') {
          this.$refs.tips.tipsFadeIn({
            text: '请选择您的所在城市'
          })
          return false
        }
        if (this.answer[num].tel === '') {
          this.$refs.tips.tipsFadeIn({
            text: '请填写正确的手机号码'
          })
          return false
        }
        if (!isvalidPhone(this.answer[num].tel)) {
          this.$refs.tips.tipsFadeIn({
            text: '请填写正确的手机号码'
          })
          return false
        }
        if (!this.disclaimer) {
          this.$refs.tips.tipsFadeIn({
            text: '请勾选我已阅读并同意齐装网的《免责声明》'
          })
          return false
        }
        let from = that.$route.query.from
        let parms = {
          tel: this.answer[3].tel,
          cs: this.answer[3].address[0],
          qx: this.answer[3].address[1],
          name: this.answer[2].family,
          hltime: this.answer[0].value,
          source: this.$globalFun.setSource(that.$route.query.source, 'luckday'),
          src: 'zxs-h5'
        }
        if (from === 'app') {
          that.$bridge.callNative('getChannelSrc', {}, function (src) {
            zbfb(parms, src).then((result) => {
              if (result.data.error_code === 0) {
                hlresult({id: result.data.data}).then((res) => {
                  if (res.data.error_code === 0) {
                    that.setCurrent(4)
                    that.currentNum = 4
                    that.result = res.data.data
                  } else {
                    that.$refs.tips.tipsFadeIn({
                      text: res.data.error_msg
                    })
                  }
                })
              } else {
                that.$refs.tips.tipsFadeIn({
                  text: result.data.error_msg
                })
              }
            })
          })
        } else {
          zbfb(parms, 'zxs-h5').then((result) => {
            if (result.data.error_code === 0) {
              hlresult({id: result.data.data}).then((res) => {
                if (res.data.error_code === 0) {
                  that.setCurrent(4)
                  that.currentNum = 4
                  that.result = res.data.data
                } else {
                  that.$refs.tips.tipsFadeIn({
                    text: res.data.error_msg
                  })
                }
              })
            } else {
              that.$refs.tips.tipsFadeIn({
                text: result.data.error_msg
              })
            }
          })
        }
      }
    },
    openPicker () {
      this.$refs.city.openPicker()
    },
    toMianze () {
      router.push('/disclaimer')
    },
    getCityVlaue (city) {
      this.addText = city[0].name + ' ' + city[1].name + ' ' + city[2].name
      this.answer[3].address = [city[1].id, city[2].id]
    },
    isDisclaimer () {
      this.disclaimer = !this.disclaimer
    },
    restCompute () {
      location.href = '/luckday'
    }
  }
}
</script>
<style scoped>
.m-container {
  height: 100%;
  overflow: auto;
  background: #FFE8CC;
  user-select:none;
}
.m-body{
  background:url("../../assets/img/luckyday/bg_body.jpg") no-repeat;
  background-size:100% auto;
  padding-top:0.38rem;
}
.m-lucky-box{
  background:#fff;
  border-left: 0.1rem solid #FEAE76;
  border-right: 0.1rem solid #FEAE76;
  margin:0 0.2rem;
  height: 3.5rem;
}
.sm-title {
    padding-top: 0.18rem;
    padding-bottom: 3px;
    font-size: 0.18rem;
    letter-spacing: 3px;
    border-bottom: 1px solid #FB891F;
    margin-bottom: 1px;
}
.sm-title span {
    display: inline-block;
    background: #FB891F;
    color: #fff;
    width: 0.28rem;
    height: 0.28rem;
    border-radius: 0.15rem;
    line-height: 0.28rem;
    text-align: center;
}
.sm-title span:nth-child(2) {
    margin-left: -3px;
    text-indent: 4px;
}
.clound{
  width:0.3rem;
  height: 0.2rem;
  background:url("../../assets/img/luckyday/clound.png") no-repeat;
  background-size:100%
}
.by-nr {
    font-size: 0.1rem;
    width: calc(100% - 0.6rem);
    color: #FC9C49;
    line-height: 0.145rem;
    text-align:center;
}
.sm-one{
      text-align: center;
      color: #FB891F;
      width: 1.9rem;
      margin: 0 auto;
}
.sm-by {
    overflow: hidden;
    text-align: center;
    border-top: 1px solid #FB891F;
    padding-top: 5px;
}
.sm-two {
    padding: 0.32rem 15px;
}
.step {
    height: 2px;
    background: #D6D6D6;
    width: 25%;
    float: left;
    position: relative;
}
.step-radius {
    width: 0.2rem;
    height: 0.2rem;
    border-radius: 0.12rem;
    background: #D6D6D6;
    position: absolute;
    left: 50%;
    margin-left: -0.1rem;
    top: 50%;
    margin-top: -0.1rem;
    text-align: center;
    line-height: 0.2rem;
    color: #fff;
}
.active,.active .step-radius{
    background: #FB891F!important;
}
.sm-three {
    margin-top: -0.12rem;
    text-align: center;
}
.question-title {
    font-size: 0.16rem;
    text-align:center;
}
.one .question-item {
    text-align: left;
    height: 0.3rem;
    margin-bottom:0.3rem;
    width: 40%;
}
.question-chose {
    overflow: hidden;
    width: 85%;
    margin: 0 auto;
    padding: 0.1rem;
    font-size: 0px;
}
.question-item {
    display: inline-block;
    font-size: 0.13rem;
}
.one {
    padding-top: 0.35rem;
}
.el-icon-outline:before{
  content:"";
  width:12px;
  height: 12px;
  border-radius:7px;
  border:2px solid #333;
  display: inline-block;
}
.hasChose{
  color:#FEA34F;
}

.hl-bottom {
    text-align: center;
    padding-top: 0.22rem;
}

.hl-bottom {
    height: 1.8rem;
    background: url('../../assets/img/luckyday/bg_bottom.png') no-repeat;
    background-size: 100%;
}
.page-btn{
    font-size: 0.13rem;
    display: inline-block;
    width: 0.7rem;
    line-height: 0.28rem;
    color: #FEA34F;
}
.current-page {
    color: #fff !important;
    text-align: center;
    background: #FEA34F;
    border-radius: 0.18rem;
}

.two .question-item {
    text-align: center;
    width: 33.3333%;
    height: 0.85rem;
}
.two .question-item div{
  display: inline-block;
  margin-right:3px;
}
.two .question-item img{
  width:0.5rem;
  margin:0px auto;
  display: block
}
.three .question-item {
    text-align: center;
    width: 48%;
    height: 0.85rem;
}
.three{
  margin-top:-0.17rem;
}
.three .question-item img{
  width:0.38rem;
  margin:0px auto;
  display: block
}
.three .question-item div{
  margin-right:3px;
}
.fade-in{
  display: block;
}
.fade-out{
  display: none
}
.input-age{
  width: 100%;
  height: 100%;
  background: none;
  font-size: 0.12rem;
  padding-left: 10px;
  color: rgb(102,102,102);
  border: none;
  outline: none;
  -webkit-appearance: textfield;
}
.input-age::-webkit-input-placeholder{
  color:#999
}
.input-age:-moz-placeholder{
  color:#999
}
.input-age::-moz-placeholder{
  color:#999
}
.input-age:-ms-input-placeholder{
  color:#999
}
.sm-three .input-box{
  border: 1px solid #DFDFDF;
  height: 0.3rem;
  width: 68%;
  margin:0.1rem auto;
}
.four .input-box{
  border: 1px solid #DFDFDF;
  height: 0.3rem;
  width: 78%;
  margin:0.22rem auto;
}

#city_2{
  line-height:35px;text-align:left;
}
#city_2 i{padding:0px 5px}
#shenming{
  text-align: left;
  border: none;
  font-size: 0.1rem;
  height: 20px;
}
#shenming label{
  display: inline-block;
  width:11px;
  height:11px;
  border:1px solid #666;
  position: relative;
  top:1.4px;
}
#shenming label i{
  color:#468dcc;
  position: absolute;
}
#shenming .a {
    color: #468dcc;
    border-bottom: 1px solid #468dcc;
    display: inline-block;
    margin-left: -2px;
    line-height: 13px;
    height: 14px;
}
#shenming a span {
    display: inline-block;
    width: 6em;
    text-indent: -0.5em;
    color: #468dcc;
}
.result-title {
  padding:0.13rem 0px;
  text-align:center;
}
.red-color{
  color: #F72500;
}
.top-box {
  border-spacing: 0px;
  margin: 0px 15px;
}
.top-box .top-left{
  background: #FEF1E0
}
.top-box .top-right{
  background: #FFE6C7;
  width: 30%;
}
.top-box .today-gl {
  font-size: 0.13rem;
  padding-top: 18px;
}
.big-num{
  font-size: 0.3rem;
  padding-top: 0.1rem;
  color: #F72500;
}
.small-hz{
  font-size: 0.145rem;
  margin-bottom: 10px;
  color: #F72500;
}
.shuom{
  font-size: 0.105rem;
  text-align: left;
  height: 0.45rem;
  overflow: hidden;
  color: #746C61;
  padding-top: 6px;
  width: 92%;
  margin:0px auto;
}
.other-part{
  width: 50%
}
.other-part div{
  text-align: center;
  background: #FEF6EB;
  padding: 10px 0px;
  width: 92%;
}
.other-part p{
  padding-bottom: 0.1rem;
  font-size: 0.12rem;
}
.other-part p span{
  font-size: 0.14rem;
  color: #F72500;
}
</style>
