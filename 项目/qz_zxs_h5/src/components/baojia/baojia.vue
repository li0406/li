<template>
  <section>
    <div class="bj-banner">
      <img src="../../assets/img/baojia/banner.jpg">
    </div>
    <div class="out-box">
      <div class="bj-box">
        <div class="money-box">
          <div class="money-main">
            <div class="money-img">
              <div class="fr">
                <div class="num num-gray"></div>
                <div class="num num-gray"></div>
                <div class="num num-gray"></div>
                <div class="num num-gray"></div>
                <div class="num num-gray"></div>
                <template v-for="(item, index) in radomNum">
                  <template v-if="item==='0'&&index===0">
                    <div class="num num-gray" :key="index+item"></div>
                  </template>
                  <template v-else>
                    <div :class="'num num-'+item" :key="index+item"></div>
                  </template>
                </template>
                <span> 元</span>
              </div>
            </div>
            <div class="home-style">
              <span>客厅：?元</span>
              <span>厨房：?元</span>
              <span>卧室：?元</span>
              <span>卫生间：?元</span>
              <span>水电：?元</span>
              <span>其他：?元</span>
            </div>
          </div>
        </div>
        <input-list :formInfo="formInfo" v-if="!nextStep"></input-list>
        <img src="../../assets/img/baojia/baojia-item4.jpg" v-if="nextStep" class="next-img">
        <input-list :formInfo="formInfo2" v-if="nextStep"></input-list>
        <div class="msg-box" style="background:red;">
          <template v-if="className">
            <div class="msg fadeInUp">
              <img :src="currentInfo.icon"><span class="fl">{{currentInfo.msg}}</span><span class="fl" style='margin-left:20px;'>{{currentInfo.time}}</span>
            </div>
          </template>
          <template v-else>
            <div class="msg">
              <img :src="currentInfo.icon"><span class="fl">{{currentInfo.msg}}</span><span class="fl" style='margin-left:20px;'>{{currentInfo.time}}</span>
            </div>
          </template>
        </div>
      </div>
      <div class="img-list">
        <img src="../../assets/img/baojia/baojia-item1.jpg"/>
        <img src="../../assets/img/baojia/baojia-item2.jpg"/>
        <img src="../../assets/img/baojia/baojia-item3.jpg"/>
      </div>
    </div>
    <m-tips ref="tips"/>
  </section>
</template>
<script>
import mTips from '../common/mTips.vue' // 引入tips 提示框
import inputList from '../common/inputList.vue'
import {baojia, baojia2} from '@/api/api'
export default {
  name: 'baojia',
  components: {
    inputList,
    mTips
  },
  data () {
    return {
      nextStep: false,
      orderId: '',
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
        buttonText: '立即计算报价',
        isCity: true,
        callBack: function (obj, parent, src) {
          baojia(obj, src).then((res) => {
            if (res.data.error_code === 0) {
              parent.nextStep = true
              parent.orderId = res.data.data
            } else {
              parent.$refs.tips.tipsFadeIn({
                text: res.data.error_msg
              })
            }
          })
        }
      },
      formInfo2: {
        source: 19011620,
        inputArray: [
          {
            name: 'name',
            placeholder: '怎么称呼您',
            type: 'text',
            value: '',
            tips: [
              '您填写的姓名为空 ^_^!',
              '请输入正确的姓名，只支持中文和英文 ^_^!'
            ]
          },
          {
            name: 'xiaoqu',
            placeholder: '填写小区名称以便准确匹配',
            type: 'text',
            value: '',
            tips: [
              '请填写您的小区以便精准匹配^_^!'
            ]
          }
        ],
        buttonText: '立即计算报价',
        isCity: false,
        callBack: function (obj, parent, src) {
          obj.orderid = parent.orderId
          obj.tel = parent.formInfo.inputArray[1].value
          baojia2(obj, src).then((res) => {
            if (res.data.error_code === 0) {
              location.href = '/baojiadone?orderid=' + obj.orderid
            } else {
              parent.$refs.tips.tipsFadeIn({
                text: res.data.error_msg
              })
            }
          })
        }
      },
      radomNum: ['1', '0', '6', '8', '2', '1'],
      radomInfo: [
        {
          'icon': 'http://staticqn.qizuang.com/desLogo/201705/100.jpg',
          'msg': '来自盐城的陈先生发起了申请',
          'time': '1秒前'
        },
        {
          'icon': 'http://staticqn.qizuang.com/desLogo/20161130/FmJ4GGIfw5S6TDVOjoDRkffzjaS9',
          'msg': '来自苏州的马女士发起了申请',
          'time': '6秒前'
        },
        {
          'icon': 'http://staticqn.qizuang.com/desLogo/20161130/Fi1b63xaSd-0pk3TCp9XHHl-DX7M',
          'msg': '来自芜湖的朱先生发起了申请',
          'time': '7秒前'
        },
        {
          'icon': 'http://staticqn.qizuang.com/desLogo/20161130/FrdMGFpLBnsNb8MvcWWHE9_dJHAc',
          'msg': '来自杭州的李先生发起了申请',
          'time': '13秒前'
        },
        {
          'icon': 'http://staticqn.qizuang.com/desLogo/20161130/Fk-7YW9x8qTUAh-IILlUjsR87Mq2',
          'msg': '来自上海的赵先生发起了申请',
          'time': '9秒前'
        },
        {
          'icon': 'http://staticqn.qizuang.com/desLogo/20161130/Fj-C_o_rrHgTdDG3VCsq8857FKHZ',
          'msg': '来自漯河的李小姐发起了申请',
          'time': '11秒前'
        },
        {
          'icon': 'http://staticqn.qizuang.com/desLogo/20161130/FtofOIR1h8G-_4Yxn-OeS1i3BO3E',
          'msg': '来自信阳的曹小姐发起了申请',
          'time': '3秒前'
        },
        {
          'icon': 'http://staticqn.qizuang.com/desLogo/20161130/FtofOIR1h8G-_4Yxn-OeS1i3BO3E',
          'msg': '来自贵阳的吴小姐发起了申请',
          'time': '12秒前'
        },
        {
          'icon': 'http://staticqn.qizuang.com/desLogo/20161130/FrX0mqcteABpqvNuvZiDlzOVJXsb',
          'msg': '来自南宁的钱小姐发起了申请',
          'time': '9秒前'
        },
        {
          'icon': 'http://staticqn.qizuang.com/desLogo/20161130/FljRwrjYW6RqGzoXx94VCwv9QnML',
          'msg': '来自嘉兴的冯小姐发起了申请',
          'time': '12秒前'
        },
        {
          'icon': 'http://staticqn.qizuang.com/desLogo/20161130/FhC7pnEVY5L2m_Zfqpsbxc59yKDR',
          'msg': '来自银川的杨先生发起了申请',
          'time': '16秒前'
        },
        {
          'icon': 'http://staticqn.qizuang.com/desLogo/20161130/FtgfBcOanHSB2Wd7u0hQXXtMvIy5',
          'msg': '来自西安的周女士发起了申请',
          'time': '25秒前'
        }
      ],
      currentInfo: {
        'icon': 'http://staticqn.qizuang.com/desLogo/201705/100.jpg',
        'msg': '来自盐城的陈先生发起了申请1秒前'
      },
      className: false
    }
  },
  created () {
    let that = this
    setInterval(function () {
      let num = that.getRadomNumber(30000, 120000) + ''
      num = num.length < 6 ? '0' + num : num
      that.radomNum = num.split('')
    }, 300)
    that.setMsg(0)
    this.formInfo.source = this.$globalFun.setSource(that.$route.query.source, 'baojia')
    this.formInfo2.source = this.$globalFun.setSource(that.$route.query.source, 'baojia')
  },
  methods: {
    getRadomNumber (Min, Max) {
      let Range = Max - Min
      let Rand = Math.random()
      return (Min + Math.round(Rand * Range))
    },
    setMsg (num) {
      let that = this
      num = num % that.radomInfo.length
      that.currentInfo = that.radomInfo[num]
      setTimeout(function () {
        that.className = true
        that.setMsg(++num)
      }, 9000)
      setTimeout(function () {
        that.className = false
      }, 2000)
    }
  }
}
</script>
<style scoped>
.bj-banner img{
  width:100%;
  vertical-align: top
}
.out-box{
  margin:0px 15px;
}
.bj-box{
  margin:0px 7px;
}
.money-box {
  border: 1px solid #dedee0;
  border-radius: 5px;
  margin-top: 15px;
}
.home-style span {
  display: block;
  float: left;
  width: 33%;
  font-size: 14px;
  line-height: 14px;
  margin: 14px auto;
  overflow: hidden;
}
.home-style span:nth-child(3n) {
    text-align: right;
}
.money-main {
  margin: 0 8px;
  overflow: hidden;
}
.money-img {
  height: 40px;
  margin-top: 8px;
  border: 2px solid #bfbfbf;
  border-radius: 5px;
  background: #555555;
}
.money-img span{
  display: inline-block;
  color:#bfbfbf;
  margin-top:16px;
  font-size:13px;
  margin-left:2px;
  margin-right:6px;
}
.num{
  width: 16px;
  height: 28px;
  background:url('../../assets/img/baojia/num.png') no-repeat;
  float: left;
  margin-left: 4px;
  margin-top:5px;
  background-size:1400%;
}
.num-gray{
  background-position:-2px 0;
}
.home-style span:nth-child(2), .home-style span:nth-child(5) {
    text-align: center;
}
.num-gray{background-position: -2px 0 !important;}
.num-0{background-position: -22px 0 !important;}
.num-1{background-position: -42px 0 !important;}
.num-2{background-position: -63px 0 !important;}
.num-3{background-position: -84px 0 !important;}
.num-4{background-position: -104px 0 !important;}
.num-5{background-position: -124px 0 !important;}
.num-6{background-position: -145px 0 !important;}
.num-7{background-position: -165px 0 !important;}
.num-8{background-position: -186px 0 !important;}
.num-9{background-position: -206px 0 !important;}
.img-list img{
  width:100%;
  margin-bottom:25px;
}
.msg-box{
  position: fixed;
  width: 100%;
  top: 3em;
  z-index: 99;
  left: 0px;
}
.msg-box .msg {
width: 85%;
position: absolute;
top: 5em;
background-color: rgba(0,0,0,.5);
line-height: 2em;
margin-left: 10px;
border-radius: 1.5em;
-webkit-border-radius: 1.5em;
-moz-border-radius: 1.5em;
-ms-border-radius: 1.5em;
color: #fff;
font-size: 14px;
padding:3px;
opacity: 0;
}
.msg img{
width: 2em;
height: 2em;
border-radius: 50%;
-webkit-border-radius: 50%;
-moz-border-radius: 50%;
-ms-border-radius: 50%;
overflow: hidden;
margin-right:4px;
float: left;
}
.fadeInUp{
  animation: fadeInUp 1.5s;
  -moz-animation: fadeInUp 1.5s;
  -webkit-animation: fadeInUp 1.5s;
  -o-animation: fadeInUp 1.5s;
}
@keyframes fadeInUp
{
0% {top:5em; opacity: 1;}
50%{top:3em;opacity: 1}
60%{top:3em;opacity: 1}
100% {top:0em;opacity: 0}
}

@-moz-keyframes fadeInUp
{
0% {top:5em; opacity: 1;}
50%{top:3em;opacity: 1}
60%{top:3em;opacity: 1}
100% {top:0em;opacity: 0}
}

@-webkit-keyframes fadeInUp
{
0% {top:5em; opacity: 1;}
50%{top:3em;opacity: 1}
60%{top:3em;opacity: 1}
100% {top:0em;opacity: 0}
}

@-o-keyframes fadeInUp
{
0% {top:5em; opacity: 1;}
50%{top:3em;opacity: 1}
60%{top:3em;opacity: 1}
100% {top:0em;opacity: 0}
}
.next-img{
  width:100%;
  margin:25px 0px 10px 0px;
}
</style>
