<template>
  <section>
    <div class="activity-box" id="activity-box">
      <article class="activity-content">
        <div class="activity-banner">
          <img src="//staticqn.qizuang.com/custom/20200803/FsyTbCNENc6WhlYhil7uFmIbiRyd"/>
        </div>
        <activity-order ref="getData"></activity-order>
        <div class="line1"></div>
        <div class="line2"></div>
        <div class="xydzp">
          <div class="dzp-tit">
            <img src="../../../assets/img/activity/activity818/dzp-tit.png"/>
          </div>
          <div class="dzp">
            <img class="dzp-wai" src="../../../assets/img/activity/activity818/dzp-wai.png"/>
            <img id="dzp-box" :style="{'transform':'rotate(' + rotate_angle+ 'deg)'}" src="../../../assets/img/activity/activity818/dzp-nei.png"/>
            <button class="dzp-btn" @click="choujiang" :disabled="disabled">
              点击<br/>
              抽奖
            </button>
            <a href="javascript:void(0)" @click="toRule" class="to-rule">抽奖<br/>规则<img
              src="../../../assets/img/activity/activity818/to-rule.png"/></a>
          </div>
          <p class="zdp-notice">※产品以实物为准，图片仅供参考</p>
        </div>
        <div class="line1"></div>
        <div class="line2"></div>
        <div class="jxzq">
          <div class="jxzq-tit">
            <img src="../../../assets/img/activity/activity818/jxzq-tit.png"/>
          </div>
          <div class="activity-city">
            <p @click="changeCity" :class="isCenter?'center':''">{{cityValue}}<img src="../../../assets/img/activity/activity818/more-city.png"/></p>
            <ul class="show" v-if="cityShow">
              <li v-for="(index,item) in cityArray" :key="item"  @click="chooseCity(item)">{{index.city_name}}</li>
            </ul>
          </div>
          <div class="jxzq-box">
            <div class="box-height">
              <div class="jxzq-list" v-for="(item,index) in companyArray" :key="item.id" @click="companyDetail(item.id)">
                <div>
                  <img :src="item.img_url+'?imageView2/1/w/200/h/160'"/>
                </div>
                <p class="company-name">{{ item.company_jc }}</p>
                <button @click.stop @click="serverDialog" type="button" :class="cityShow ? 'yuyue' : 'yuyue z-index'">预约到店</button>
                <p class="jzxq-details">
                  <span class="cu">促</span>
                  <span>{{ item.activity_content }}</span>
                </p>
                <p class="jzxq-details">
                  <span class="li">礼</span>
                  <span>满4万减1000，满10万减4999装修津贴</span>
                </p>
              </div>
            </div>

            <button class="more-company" @click="toMoreCompany">查看更多装修公司<img src="../../../assets/img/activity/activity818/more-company.png"/>
            </button>
          </div>
        </div>
        <div class="line1"></div>
        <div class="line2"></div>
        <div class="choose-qzw">
          <div class="choose-tit">
            <img src="../../../assets/img/activity/activity818/choose-tit.png"/>
          </div>
          <div class="choose-content">
            <img src="//staticqn.qizuang.com/custom/20200803/FkssDlwe5XY7H0q-i7aI-Fn-Qe2c"/>
            <button class="to-bao" @click="toBao">点击查看详情<img src="../../../assets/img/activity/activity818/more-company.png"/>
            </button>
          </div>
        </div>
      </article>
      <div class="dzp-dialog success-dialog" v-if="chouJiangSuccess">
        <div class="dzp-box">
          <img class="close-btn" src="../../../assets/img/activity/activity818/close-btn.png" @click="closeDialog"/>
          <div class="dzp-content">
            <div class="success-top">{{ cjresult }}</div>
            <div class="dzp-success" v-if="successDialog">
              <div class="dzp-success-img">
                <img :src="jiangImage" alt="">
              </div>
              <p class="dzp-tips">{{ jiangHtml }}</p>
              <div class="open-qzapp fadan-btn" @click="checkQuan">查看卡券</div>
            </div>
            <div class="dzp-unsuccess" v-if="!successDialog">
              <div class="dzp-unsuccess-img">
                <img src="../../../assets/img/activity/activity818/unsuccess.png" alt="">
              </div>
              <p class="dzp-tips">抱歉，还差一点点就中奖了…</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
<script>
import activityOrder from "@/components/activity/activity818/activityOrder";
import { companyList, getChouJiang } from '@/api/activityApi.js'
import router from "@/router";
export default {
  name: 'activity818',
  components: {activityOrder},
  data() {
    return {
      cityShow: false,
      cityValue: '切换城市',
      city_id: '',
      isCenter: false,
      companyArray: [],
      cityArray: [
        {
          id: 1,
          city_id: 510100,
          city_name: '成都'
        },
        {
          id: 2,
          city_id: 340200,
          city_name: '芜湖'
        },
        {
          id: 3,
          city_id: 120001,
          city_name: '天津'
        }
      ],
      rotate_angle: 0,
      cjresult: '恭喜中奖!',
      chouJiangSuccess: false,
      successDialog: true,
      jiangImage: '',
      disabled: false,
      jiangHtml: ''
    }
  },
  created() {
    this.companyList(1)
  },
  methods: {
    toRule() {
      router.push('/activityrule')
    },
    changeCity() {
      this.cityShow = !this.cityShow
    },
    chooseCity(index) {
      this.cityValue = this.cityArray[index].city_name
      this.city_id = this.cityArray[index].city_id
      this.cityShow = false
      this.isCenter = true
      this.companyList(2)
    },
    companyList(type) {
      companyList({
        cs: this.city_id,
        type: type
      }).then(res => {
        if(res.status === 200 && res.data.error_code === 0) {
          this.companyArray = res.data.data
        }
      })
    },
    serverDialog() {
      this.$refs.getData.reserveDisabled = true
    },
    // 装修公司详情
    companyDetail(id) {
      this.$bridge.callNative('Native_decorate_detail', id, function (res) {})
    },
    // 查看更多装修公司
    toMoreCompany() {
      if(this.city_id) {
        this.$bridge.callNative('Native_decorate_more', {city_id:this.city_id,city_name:this.cityValue}, function (res) {})
      } else {
        this.$bridge.callNative('Native_decorate_more', null, function (res) {})
      }
    },
    closeDialog() {
      this.chouJiangSuccess = false
    },
    choujiang() {
      var that = this
      that.disabled = true
      if(!that.$refs.getData.isLoad) {
        // 未登陆状态，提示登录
        that.$refs.getData.callAppLogin()
        that.disabled = false
      } else {
        getChouJiang({
          activity_id: 35,
          tel: that.$refs.getData.formInfo.inputArray[0].value
        }).then(res => {
          if(res.data.error_code === 0) {
            var loop = res.data.data.offset
            that.rotate_angle = 337.5 - loop*45 + 1080
            setTimeout(function (){
              that.chouJiangSuccess = true
              that.disabled = false
            },4000)
            if(parseInt(loop) === 2 || parseInt(loop) === 6) {
              that.cjresult = '谢谢参与'
              that.successDialog = false
            } else if(parseInt(loop) === 0) {
              that.cjresult = '恭喜中奖!'
              that.successDialog = true
              this.jiangHtml = '量房成功即可领取奖励～'
              that.jiangImage = require('../../../assets/img/activity/activity818/gift0.png')
            } else if(parseInt(loop) === 3){
              that.cjresult = '恭喜中奖!'
              that.successDialog = true
              this.jiangHtml = '完成装修签约即可兑换奖品～'
              that.jiangImage = require('../../../assets/img/activity/activity818/gift3.png')
            }
          } else {
            that.disabled = false
            alert(res.data.error_msg)
          }
        }).catch(res => {
          that.disabled = false
          alert(res.data.error_msg)
        })
      }
    },
    // 查看卡券
    checkQuan() {
      this.$bridge.callNative('Native_card_center', '', function (res) {})
    },
    // 为什么选择齐装网
    toBao() {
      this.$bridge.callNative('Native_decorate_guard', '', function (res) {})
    }
  }
}
</script>
<style scoped>
* {
  margin: 0;
  padding: 0;
}

img {
  width: 100%;
  vertical-align: middle;
}

.activity-box .activity-content {
  background: #C61F2D;
  padding-bottom: 0.35rem;
}

.activity-box .line1 {
  width: 92%;
  height: 1px;
  background: #921722;
  margin: 0 auto;
}

.activity-box .line2 {
  width: 92%;
  height: 1px;
  background: #DD2131;
  margin: 0 auto 0.171rem;
}

.activity-content .xydzp .dzp-tit {
  width: 62%;
  margin: 0 auto 0.141rem;
}

.activity-content .xydzp .dzp {
  width: 2.953rem;
  height: 2.953rem;
  position: relative;
  margin: 0 auto 0.145rem;
}

.activity-content .xydzp .dzp #dzp-box {
  display: block;
  position: absolute;
  top: 0px;
  transition:transform 4s ease;
  -webkit-transition:transform 4s ease;
  -moz-transition:transform 4s ease;
  -ms-transition:transform 4s ease;
  -o-transition:transform 4s ease;
}

.activity-content .xydzp .dzp .dzp-btn {
  display: block;
  background: url("../../../assets/img/activity/activity818/dzp-btn.png") no-repeat;
  background-size: 100% 100%;
  width: 0.738rem;
  height: 0.806rem;
  text-align: center;
  font-size: 0.115rem;
  padding-top: 0.1rem;
  color: #D83E4A;
  font-weight: 800;
  position: absolute;
  top: 0;
  bottom: 0;
  right: 0;
  left: 0;
  margin: auto;
  z-index: 9;
}

.activity-content .xydzp .dzp .to-rule {
  position: absolute;
  right: 0.119rem;
  top: 0;
  color: #FFE3B1;
}

.activity-content .xydzp .dzp .to-rule img {
  width: 0.107rem;
  height: 0.107rem;
  position: absolute;
  right: -0.119rem;
  top: 0;
  bottom: 0;
  margin: auto;
}

.activity-box .xydzp .zdp-notice {
  font-size: 0.102rem;
  color: #CD9037;
  text-align: center;
  margin-bottom: 0.324rem;
}

.activity-content .jxzq {
  position: relative;
}

.activity-content .jxzq .jxzq-tit {
  width: 68%;
  margin: 0 auto 0.363rem;
}

.activity-content .jxzq .activity-city {
  width: 0.64rem;
  height: 1.024rem;
  position: absolute;
  right: 4%;
  top: 0.265rem;
  z-index: 8;
}

.activity-content .jxzq .activity-city p {
  padding: 5px 10px;
  border-radius: 0.098rem;
  background: #FFE3B1;
  font-size: 0.102rem;
  color: #B2162C;
  position: relative;
  padding-left: 0.064rem;
  z-index: 9;
}

.activity-content .jxzq .activity-city p img {
  width: 0.107rem;
  height: 0.107rem;
  position: absolute;
  top: 0;
  bottom: 0;
  right: 0.038rem;
  margin: auto;
}

.activity-content .jxzq .activity-city ul {
  width: 100%;
  height: 0.746rem;
  background: #fff;
  border-radius: 0 0 0.102rem 0.102rem;
  position: relative;
  top: -0.094rem;
  box-shadow: 0px 1px 20px 0px rgba(4, 0, 0, 0.3);
  padding-top: 0.18rem;
  overflow: hidden;
}

.activity-content .jxzq .activity-city ul li {
  display: block;
  height: 0.235rem;
  line-height: 0.235rem;
  font-size: 0.128rem;
  color: #333;
  text-align: center;
}

.activity-content .jxzq .activity-city p.center {
  padding-left: 0.2rem;
}


.activity-content .jxzq .jxzq-box {
  width: 2.739rem;
  margin: 0 auto 0.35rem;
  border-radius: 0.128rem;
  padding: 0.128rem 0.107rem 0.192rem;
  background: #fff;
}
.activity-content .jxzq .jxzq-box .box-height{
  max-height: 4.75rem;
  overflow: hidden;
  overflow-y: auto;
  margin-bottom: 0.128rem;
}

.jxzq .jxzq-box .jxzq-list {
  height: 0.683rem;
  position: relative;
  padding-left: 0.939rem;
  overflow: hidden;
  margin-bottom: 0.282rem;
}
.jxzq .jxzq-box .jxzq-list:last-child{
  margin-bottom: 0.05rem;
}
.jxzq .jxzq-box .jxzq-list div {
  display: block;
  width: 0.853rem;
  height: 0.683rem;
  position: absolute;
  top: 0;
  left: 0;
}

.jxzq .jxzq-box .jxzq-list div img {
  height: 100%;
  border-radius: 0.043rem;
}

.jxzq-box .jxzq-list .company-name {
  font-size: 0.128rem;
  color: #333;
  font-weight: bold;
  margin-bottom: 0.195rem;
}

.jxzq-box .jxzq-list .yuyue {
  width: 0.597rem;
  height: 0.196rem;
  line-height: 0.196rem;
  text-align: center;
  border-radius: 0.098rem;
  color: #B2162C;
  font-size: 0.102rem;
  border: none;
  outline: none;
  background: linear-gradient(0deg, rgba(255, 183, 83, 1), rgba(254, 230, 165, 1));
  position: absolute;
  right: 0;
  top: 0.15rem;
}
.jxzq-box .jxzq-list .z-index{
  z-index: 11;
}

.jxzq-box .jxzq-list .jzxq-details {
  height: 0.119rem;
  line-height: 0.119rem;
  margin-bottom: 0.043rem;
}

.jxzq-list span {
  display: inline-block;
  color: #666;
  font-size: 0.085rem;
}

.jxzq-list span:first-child {
  padding: 0 1px;
  border-radius: 3px;
  position: relative;
  top: -2px;
}

.jxzq-list span.cu {
  color: #644FFF;
  border: 1px solid #644FFF;
}

.jxzq-list span.li {
  color: #FE4D35;
  border: 1px solid #FE4D35;
}

.jxzq-list span:nth-child(2) {
  width: 85%;
  word-break: break-all;
  word-wrap: break-word;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.jxzq-box .more-company,.choose-qzw .to-bao {
  width: 2.641rem;
  height: 0.35rem;
  border: 1px solid #F2B45F;
  color: #F2B45F;
  text-align: center;
  line-height: 0.35rem;
  font-size: 0.154rem;
  display: block;
  margin: 0 auto;
  position: relative;
  border-radius: 0.175rem;
  cursor: pointer;
}

.jxzq-box .more-company img,.choose-qzw .to-bao img {
  width: 0.107rem;
  height: 0.107rem;
  position: absolute;
  top: 0;
  bottom: 0;
  right: 0.546rem;
  margin: auto;
}
.choose-qzw .choose-tit {
  width: 77%;
  margin: 0 auto 0.213rem;
}

.choose-qzw .choose-content {
  width: 2.689rem;
  margin: 0 auto;
  border-radius: 0.128rem;
  padding: 0.299rem 0.132rem;
  background: #fff;
}
.choose-qzw .choose-content>img{
  margin-bottom: 0.3rem;
}
/*转盘抽奖弹窗*/
.success-dialog{
  width: 100%;
  height: 100%;
  position: fixed;
  top:0;
  background: rgba(0,0,0,0.6);
  z-index: 99;
}
.success-dialog .close-btn{
  display: block;
  width: 0.256rem;
  height: 0.256rem;
  position: absolute;
  right: 0;
  top: -0.384rem;
}
.success-dialog .success-top{
  height: 0.461rem;
  background: url("../../../assets/img/activity/activity818/success-top.png") no-repeat;
  background-size: 100% 100%;
  text-align: center;
  line-height: 0.461rem;
  font-size: 0.179rem;
  color: #fff;
  font-weight: bold;
}
.dzp-dialog .dzp-box{
  width: 2.688rem;
  height: 2.56rem;
  background: #fff;
  border-radius: 0.043rem;
  position: absolute;
  top:0;
  right: 0;
  left: 0;
  bottom: 0;
  margin: auto;
}
.dzp-dialog .dzp-box .dzp-content .dzp-tips{
  font-size: 0.102rem;
  color: #999;
  text-align: center;
  margin: 0.1rem 0;
}
.dzp-content .dzp-success,.dzp-content .dzp-unsuccess{
  padding: 0 0.128rem;
}
.dzp-dialog .dzp-box .dzp-success .dzp-success-img{
  height: 1.28rem;
  position: relative;
}
.dzp-dialog .dzp-box .dzp-success .dzp-success-img img{
  width: 45%;
  display: block;
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  margin: auto;
}
.dzp-dialog .dzp-box .dzp-unsuccess .dzp-unsuccess-img{
  width: 0.725rem;
  height: 0.687rem;
  margin: 0.384rem auto 0.256rem;
}
.fadan-btn{
  width: 2.432rem;
  height: 0.341rem;
  margin: 0 auto;
  line-height: 0.341rem;
  text-align: center;
  font-size: 0.128rem;
  color: #fff;
  cursor: pointer;
  border-radius: 3px;
  background: #F2203E;
}
</style>
