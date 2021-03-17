<template>
  <section class="bg">
    <open-app></open-app>
    <div class="bg">
        <div class="company-info">
            <div class="top clearfix">
                <div class="logo">
                    <img :src="companyInfo.logo" alt="" v-if="companyInfo.logo != ''">
                    <img v-else src="../../../assets/img/qz/share/company-default-logo.jpg" alt="">
                </div>
                <div class="name">{{companyInfo.jc}}</div>
                <div class="pingjia">
                    <span class="star" v-for="(n,index) in companyInfo.star" :key="index"></span>
                    <span class="red">{{companyInfo.positive_rate}}</span>好评率
                </div>
                <div v-if="companyInfo.licence.length" class="zhizhao" @click="openLicence">营业执照<img src="../../../assets/img/qz/share/black-right.png" alt=""></div>
                <div class="number" v-if="companyInfo.comment_count != 0"><span class="red">{{companyInfo.comment_count}}</span>位业主点评</div>
            </div>
            <div class="cen">
                <p>案例：<span>{{companyInfo.case_count}}</span>&nbsp;|&nbsp;设计师：<span>{{companyInfo.designer_count}}</span>&nbsp;|&nbsp;在建工地：<span>{{companyInfo.build_count}}</span></p>
            </div>
            <div class="line"></div>
            <div class="bottom clearfix">
                <div class="address">
                    <img src="../../../assets/img/qz/share/address.png" alt="">
                    <p>{{companyInfo.address}}</p>
                </div>
                <div class="tel">
                    <a :href="'tel:' + companyInfo.phone">
                        <img src="../../../assets/img/qz/share/phone.png" alt="">
                    </a>
                </div>
            </div>
        </div>
        <div class="quan" v-if="list.card.length != 0">
            <div class="title">
                <span class="sp1">特色礼券</span>
                <span class="sp3"><img src="../../../assets/img/qz/share/right.png" alt=""></span>
                <span class="sp2" @click="qzOpenTips">查看全部</span>
            </div>
            <swiper class="ul" :options="swiperOption" ref="mySwiper" >
                <!-- slides -->
                <swiper-slide class="swipe-item" v-for="(val,index) in list.card" :key="index">
                    <div class="q-left">
                        <div class="money" v-if="val.active_type == 1">￥{{val.money2}}</div>
                        <div class="money" v-else>{{val.gift}}</div>
                        <div class="jine" v-if="val.active_type == 1">满{{val.money1}}元减{{val.money2}}元</div>
                        <div class="jine" v-else>满{{val.money3}}元可领取</div>
                    </div>
                    <div class="q-right">
                        <div class="q-name" v-if="val.type == 1">{{val.name}}</div>
                        <div class="q-name" v-else>{{val.name}}</div>
                        <div class="q-time">{{val.start}}-{{val.end}}</div>
                        <p class="p">
                            <span @click="qzOpenTips">查看详情</span>
                            <span class="zhankai" @click="qzOpenTips">展开<i class="down"></i></span>
                        </p>
                    </div>
                </swiper-slide>
            </swiper>
        </div>
        <div class="tab" :class="{'is_fixed' : isFixed}" ref="tab">
            <ul class="clearfix">
                <li v-if="list.example.length != 0"><span :class="{'cur':indexActive === 0}" @click="toTab($event)" data-index='0'>案例</span></li>
                <li v-if="list.comment.length != 0"><span :class="{'cur':indexActive === 1}"  @click="toTab($event)" data-index='1'>业主评价</span></li>
                <li v-if="list.designer.length != 0"><span :class="{'cur':indexActive === 2}"  @click="toTab($event)" data-index='2'>设计师</span></li>
                <li><span :class="{'cur':indexActive === 3}" @click="toTab($event)" data-index='3'>企业信息</span></li>
            </ul>
        </div>
        <div class="example" ref="example" v-if="list.example.length != 0">
            <div class="title">
                <span class="sp1">最新案例</span>
                <span class="sp3"><img src="../../../assets/img/qz/share/right.png" alt=""></span>
                <span v-if="companyInfo.case_count != 0" class="sp2" @click="qzOpenTips">全部（{{companyInfo.case_count}}）</span>
            </div>
            <ul class="e-list clearfix">
                <li v-for="(val,index) in list.example" :key="index" @click="qzOpenTips">
                    <img :src="val.img_url" alt="">
                    <div class="e-info">
                        <div class="e-name">{{val.title}}</div>
                        <p class="e-style">{{val.case_attr}}</p>
                    </div>
                </li>
            </ul>
        </div>
        <div class="dianping" ref="dianping" v-if="list.comment.length != 0">
            <div class="title">
                <span class="sp1">业主点评</span>
                <span class="sp3"><img src="../../../assets/img/qz/share/right.png" alt=""></span>
                <span v-if="companyInfo.comment_count != 0" class="sp2" @click="qzOpenTips">查看全部{{companyInfo.comment_count}}条点评</span>
            </div>
            <ul class="p-list">
                <li class="p-li" v-for="(val,index) in list.comment" :key="index" @click="qzOpenTips">
                    <div class="designer-pic">
                        <img v-if="val.comment_user_avatar != ''" :src="val.comment_user_avatar" alt="">
                        <img v-else src="../../../assets/img/qz/share/default-logo.png" alt="">
                    </div>
                    <div class="designer-detail">
                        <div class="clearfix">
                            <span class="designer-name">{{val.comment_user_nickname}}</span>
                            <span class="designer-status" v-if="val.step != ''">{{val.step}}</span>
                        </div>
                        <div>
                            <span class="star-text">打分</span>
                            <p class="stars">
                                <span class="star" v-for="(n,x) in val.comment_score" :key="x">
                                    <i v-if="n == 1">
                                        <img src="@/assets/img/share/star.png"/>
                                    </i>
                                    <i v-else-if="n == 0.3">
                                        <img src="@/assets/img/share/downstar.png"/>
                                    </i>
                                    <i v-else-if="n == 0.7">
                                        <img src="@/assets/img/share/upstar.png"/>
                                    </i>
                                    <i v-else-if="n == 0.5">
                                        <img src="@/assets/img/share/halfstar.png"/>
                                    </i>
                                    <i v-else>
                                        <img src="@/assets/img/share/nostar.png"/>
                                    </i>
                                </span>
                            </p>
                            <span class="designer-time">{{val.comment_time}}</span>
                        </div>
                        <div class="desinger-text">
                            <p>{{val.comment_content}}</p>
                        </div>
                        <div class="designer-img-box clearfix" v-if="val.comment_imgs.length != 0">
                            <ul class="designer-img">
                                <li v-for="(v,i) in val.comment_imgs" :key="i">
                                    <img :src="v" alt="">
                                </li>
                                <p class="img-num">共{{val.comment_imgs.length}}张</p>
                            </ul>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div class="team" ref="team" v-if="list.designer.length != 0">
            <div class="title">
                <span class="sp1">设计团队</span>
                <span class="sp3"><img src="../../../assets/img/qz/share/right.png" alt=""></span>
                <span v-if="companyInfo.designer_count != 0" class="sp2" @click="qzOpenTips">全部（{{companyInfo.designer_count}}）</span>
            </div>
            <swiper class="ul" :options="swiperOption1" ref="mySwiper1" >
                <swiper-slide class="swipe-item" v-for="(val,index) in list.designer" :key="index" @click="qzOpenTips">
                    <div class="avator">
                        <img :src="val.logo" alt="">
                    </div>
                    <div class="name">{{val.name}}</div>
                    <div class="job">{{val.position}}</div>
                    <div class="job-time">从业时间: {{val.job_time}}</div>
                    <div class="example-num">案例数: {{val.case_num}}套</div>
                </swiper-slide>
            </swiper>
        </div>
        <div class="company-intro clearfix" ref="companyIntro">
            <div class="title">
                <span class="sp1">企业信息</span>
                <span class="sp3"><img src="../../../assets/img/qz/share/right.png" alt=""></span>
                <span class="sp2" @click="qzOpenTips">更多</span>
            </div>
            <div class="c-intro">
                <div class="info-item">
                    <span>服务类型：</span>
                    <span>{{companyInfo.fw_type}}</span>
                </div>
                <div class="info-item">
                    <span>承接价位：</span>
                    <span v-if="companyInfo.jiawei == ''|| companyInfo.jiawei == '0'">不限</span>
                    <span v-else>{{companyInfo.jiawei}}万元以上</span>
                </div>
                <div class="info-item">
                    <span>服务区域：</span>
                    <span>{{companyInfo.fw_area}}</span>
                </div>
                <div class="fuwu clearfix" v-if="companyInfo.fw_special.length != 0">
                    <span class="fuwu-ts">特色服务：</span>
                    <div class="f-div">
                        <span v-for="(val,index) in companyInfo.fw_special" :key="index"><i class="icon-gou"></i>{{val.name}}</span>
                    </div>
                </div>
                <div class="info-item">
                    <span>企业简介：</span>
                    <span>{{companyInfo.introduction}}</span>
                </div>
            </div>
        </div>
        <div class="footer">
            <div class="advice" @click="qzOpenTips">
                <img src="../../../assets/img/qz/share/advice.png" alt="">
                <p>咨询</p>
            </div>
            <div class="evaluate" @click="qzOpenTips">
                <img src="../../../assets/img/qz/share/pingjia.png" alt="">
                <p>评价</p>
            </div>
            <div class="appointment" id="qzbottomshare" @click="qzOpenTips">预约服务</div>
        </div>
        <div class="licence" v-if="noLicence" @click="closeLicence">
            <div class="img">
                <img :src="companyInfo.licence[0].img_url" alt="">
            </div>
        </div>
    </div>
    <wx-tips :isWechat="isWechat"></wx-tips>
  </section>
</template>
<script>
import globalData from '../../../utils/globalData.js'
import openApp from '@/components/common/openApp'
import wxTips from '@/components/common/wxTips'
// import qzOpenApp from '@/mixins/qzOpenApp'
import qzOpenTips from '@/mixins/qzOpenApp'
import 'swiper/dist/css/swiper.css'
import { swiper, swiperSlide } from 'vue-awesome-swiper'
import { shareCompanyBasicinfo, shareCompanyDetaillist } from '@/api/api'
export default {
  name: 'shareBrand',
  components: {
    openApp,
    wxTips,
    swiper,
    swiperSlide
  },
  mixins: [qzOpenTips],
  data () {
    return {
      isWechat: false,
      tel: '',
      id: '',
      indexActive: 0,
      swiperOption: {
        slidesPerView: 1.3,
        spaceBetween: 0,
        loop: false
      },
      swiperOption1: {
        slidesPerView: 2.5,
        spaceBetween: 10,
        loop: false
      },
      companyInfo: {
        id: '',
        logo: '',
        jc: '',
        licence: [],
        star: '',
        positive_rate: '',
        comment_count: '',
        case_count: '',
        designer_count: '',
        build_count: '',
        address: '',
        phone: '',
        fw_type: '',
        jiawei: '',
        fw_area: '',
        fw_special: [],
        introduction: ''
      },
      list: {
        card: [],
        example: [],
        comment: [],
        designer: []
      },
      noLicence: false,
      isFixed: false
    }
  },
  created () {
    this.tel = globalData.tel400
    this.id = this.$route.query.id
    this.getBaseinfo()
    this.getDetaillist()
  },
  computed: {
    swiper () {
      return this.$refs.mySwiper.swiper
    },
    swiper1 () {
      return this.$refs.mySwiper1.swiper
    }
  },
  mounted () {
    window.addEventListener('scroll', this.handleScroll, true)
  },
  methods: {
    openLicence: function () {
      this.noLicence = true
    },
    closeLicence: function () {
      this.noLicence = false
    },
    toTab: function (event) {
      let index = Number(event.target.getAttribute('data-index'))
      this.indexActive = index
      if (index === 0) {
        this.$nextTick(() => {
          document.documentElement.scrollTo(0, this.$refs.example.offsetTop - 50)
          document.body.scrollTo(0, this.$refs.example.offsetTop - 50)
        })
      } else if (index === 1) {
        this.$nextTick(() => {
          document.documentElement.scrollTo(0, this.$refs.dianping.offsetTop - 50)
          document.body.scrollTo(0, this.$refs.dianping.offsetTop - 50)
        })
      } else if (index === 2) {
        this.$nextTick(() => {
          document.documentElement.scrollTo(0, this.$refs.team.offsetTop - 50)
          document.body.scrollTo(0, this.$refs.team.offsetTop - 50)
        })
      } else if (index === 3) {
        this.$nextTick(() => {
          document.documentElement.scrollTo(0, this.$refs.companyIntro.offsetTop - 50)
          document.body.scrollTo(0, this.$refs.companyIntro.offsetTop - 50)
        })
      }
    },
    getBaseinfo () {
      shareCompanyBasicinfo({
        company_id: this.id
      }).then(res => {
        if (parseInt(res.data.error_code) === 0) {
          this.companyInfo = res.data.data
          document.title = this.companyInfo.jc + '-齐装APP'
          this.companyInfo.introduction = res.data.data.introduction.substr(0, 120) + '...'
          this.companyInfo.introduction = this.companyInfo.introduction.replace(/&quot;/g, '"')
        }
      }).catch(err => {
        console.log(err)
      })
    },
    getDetaillist () {
      shareCompanyDetaillist({
        company_id: this.id
      }).then(res => {
        if (parseInt(res.data.error_code) === 0) {
          this.list.card = res.data.data.card
          this.list.example = res.data.data.example
          this.list.comment = res.data.data.comment
          this.list.designer = res.data.data.designer
          this.list.card.forEach(val => {
            val.start = this.getTime(val.start)
            val.end = this.getTime(val.end)
          })
          this.list.comment.forEach(val => {
            val.comment_score = this.setStars(val.comment_score)
          })
        }
      }).catch(err => {
        console.log(err)
      })
    },
    handleScroll: function () {
      let scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop
      let offsetTop = this.$refs.tab.offsetTop
      this.isFixed = scrollTop > offsetTop
    },
    getTime: function (timestamp) {
      let date = new Date(timestamp * 1000)
      let Y = date.getFullYear() + '.'
      let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '.'
      let D = date.getDate()
      return Y + M + D
    },
    setStars: function (num) {
      if (num < 3) {
        num = 3.5
      }
      var zheng = Math.floor(Number(num)) // 整星
      var yu = num - zheng // 零星
      var stars = []

      if (yu < 0.5 && yu > 0) {
        yu = 0.3
      }
      if (yu > 0.5 && yu < 1) {
        yu = 0.7
      }
      for (var i = 0; i < 5; i++) {
        if (i < zheng) {
          stars.push(1)
        } else if (zheng === i) {
          stars.push(yu)
        } else {
          stars.push(0)
        }
      }
      return stars
    }
  }
}
</script>
<style scoped>
.bg {
    background: #f5f5f5;
    width: 100%;
    box-sizing: border-box;
}
.company-info {
    padding: 10px 15px;
    background: #fff;
    border-bottom: 1px solid #eee;
}
.company-info .top {
    position: relative;
}
.company-info  .red {
    color: #FF5353;
}
.company-info .logo {
    width: 0.675rem;
    height: 0.504rem;
    border: 1px solid rgba(204,204,204,1);
    border-radius: 2.5px;
    float: left;
}
.company-info .logo img {
    display: block;
    width: 100%;
    height: 100%;
}
.company-info .name {
    float: left;
    font-size: 0.162rem;
    font-weight: bold;
    color: #333;
    margin-left: 0.117rem;
    width: 1.3rem;
}
.company-info .pingjia {
    float: left;
    width: 1.3rem;
    margin-left: 0.117rem;
    margin-top: 0.135rem;
    font-size: 13px;
}
.company-info .pingjia .star {
    display: block;
    width: 10px;
    height: 10px;
    background: url(../../../assets/img/qz/share/star.png) no-repeat;
    background-size: 100%;
    float: left;
    position: relative;
    top: 4px;
}
.company-info .pingjia .red {
    margin-left: 2px;
}
.company-info .zhizhao {
    width: 0.675rem;
    height: 0.18rem;
    /* line-height: 0.18rem; */
    padding-top: 0.025rem;
    box-sizing: border-box;
    text-align: center;
    font-size: 9px;
    font-weight: bold;
    color: #333;
    border:1px solid rgba(51,51,51,1);
    border-radius: 10px;
    position: absolute;
    top: 0;
    right: 0;
}
.company-info .zhizhao img {
    display: block;
    width: 5.5px;
    height: 7.5px;
    position: absolute;
    top: 6px;
    right: 5px;
    color: #333;
}
.company-info .number {
    position: absolute;
    top: 0.35rem;
    right: 0;
}
.company-info .cen {
    margin-top: 0.135rem;
}
.company-info .cen p {
    font-size:13px;
    color: #999;
}
.company-info .cen p span {
    color: #333;
}
.company-info .line {
    width: 100%;
    height: 1px;
    background: #f5f5f5;
    position: absolute;
    top: 1.5rem;
    left: 0;
}
.company-info .bottom {
    margin-top: 0.234rem;
    position: relative;
}
.company-info .bottom .address {
    min-height: 20px;
    position: relative;
}
.company-info .bottom .address img {
    width: 11.5px;
    height: 14.5px;
    margin-right: 8px;
    position: relative;
    top: 3px;
    float: left;
}
.company-info .bottom .address p {
    float: left;
    width: 80%;
    overflow: hidden;
    text-overflow: ellipsis;
    display: box;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
.company-info .bottom .tel {
    position: absolute;
    top: 0;
    right: 0;
}
.company-info .bottom .tel a img {
    display: block;
    width: 18px;
    height: 18px;
}
.quan,.team,.company-intro {
    padding: 10px 15px;
    background: #fff;
    margin-bottom: 0.078rem;
    height: 1rem;
}
.quan .title,.example .title, .dianping .title, .team .title, .company-intro .title {
    height: 0.216rem;
    line-height: 0.216rem;
}
.quan .title .sp1,.example .title .sp1, .dianping .title .sp1, .team .title .sp1, .company-intro .title .sp1 {
    color: #333;
    font-weight: bold;
    font-size: 0.114rem;
    float: left;
}
.quan .title .sp2,.example .title .sp2,.dianping .title .sp2,.team .title .sp2, .company-intro .title .sp2 {
    float: right;
    color: #999;
    font-size: 13px;
    margin-right: 7px;
}
.example .title .sp2,.team .title .sp2 {
    margin-right: 0;
}
.quan .title .sp3,.example .title .sp3,.dianping .title .sp3,.team .title .sp3,.company-intro .title .sp3 {
    float: right;
    position: relative;
    top: 0.05rem;
}
.example .title .sp3,.dianping .title .sp3,.team .title .sp3,.company-intro .title .sp3 {
    top: 0.06rem;
}
.quan .title .sp3 img,.example .title .sp3 img,.dianping .title .sp3 img,.team .title .sp3 img,.company-intro .title .sp3 img {
    display: block;
    width: 8px;
    height: 12px;
}
.quan .ul {
    margin-left: -10px;
}
.quan .ul .swipe-item {
    width: 2.2995rem;
    height: 0.9rem;
    background: url(../../../assets/img/qz/share/quan.png) no-repeat;
    background-size: 100%;
    touch-action: none;
}
.quan .ul .swipe-item .q-left {
    float: left;
    color: #fff;
    font-weight: bold;
    text-align: center;
    width: 40%;
    box-sizing: border-box;
}
.quan .ul .swipe-item .q-left .money {
    font-size: 0.2rem;
    margin-top: 0.18rem;
}
.quan .ul .swipe-item .q-left .jine {
    font-size: 9px;
    width: 80%;
    margin: 0 auto;
    word-break: break-all;
}
.quan .ul .swipe-item .q-right {
    float: left;
    color: #C2876B;
    width: 60%;
    height: 100%;
    box-sizing: border-box;
    position: relative;
}
.quan .ul .swipe-item .q-right .q-name {
    font-weight: bold;
    font-size: 0.135rem;
    margin-top: 0.135rem;
    width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.quan .ul .swipe-item .q-right .q-time {
    font-size: 9px;
}
.quan .ul .swipe-item .q-right .get-now {
    position: absolute;
    top: 0.18rem;
    right: 0.126rem;
    width: 0.5rem;
    height: 0.16rem;
    line-height: 0.18rem;
    text-align: center;
    font-size: 12px;
    border:1px solid rgba(194,135,107,1);
    border-radius: 0.09rem;
}
.quan .ul .swipe-item .q-right .p {
    position: absolute;
    bottom: 0.15rem;
    left: 0;
    font-size: 9px;
    width: 100%;
}
.quan .ul .swipe-item .q-right .p .zhankai {
    float: right;
    margin-right: 0.135rem;
}
.quan .ul .swipe-item .q-right .p .zhankai i {
    display: block;
    width: 9px;
    height: 6px;
    background: url(../../../assets/img/qz/share/down.png) no-repeat;
    background-size: 100%;
    float: right;
    position: relative;
    top: 2px;
}
.tab {
    padding: 10px 0;
    background: #fff;
}
.is_fixed {
  position: fixed;
  top: 0px;
  left: 0px;
  z-index: 2;
  width: 100%;
}
.tab ul {
    display: flex;
}
.tab ul li {
    float: left;
    height: 0.2rem;
    flex: 1;
}
.tab ul li:first-child {
    padding-left: 15px;
}
.tab ul li:last-child {
    margin-right: 0;
}
.tab ul li span {
    display: inline-block;
    height: 100%;
}
.tab ul li span.cur {
    border-bottom: 2px solid #FF4D4D;
    font-weight: bold;
}
.example,.dianping {
    padding: 10px 15px;
    padding-top: 0;
    background: #fff;
}
.example .e-list {
    margin-top: 0.09rem;
    padding-bottom: 0.1rem;
    border-bottom: 1px solid #f5f5f5;
}
.example .e-list li {
    float: left;
    width: 49%;
    margin-right: 2%;
}
.example .e-list li:nth-child(2n) {
    margin-right: 0;
}
.example .e-list li img {
    display: block;
    width: 100%;
    height: 1rem;
    background: #ccc;
    border-radius: 5px;
}
.example .e-list li .e-name {
    font-size: 13px;
    color: #333;
    font-weight: bold;
    margin-top: 0.09rem;
    width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.example .e-list li .e-style {
    font-size: 9px;
    color: #666;
    margin: 0.045rem 0 0.1rem;
}
.dianping ul li.p-li {
    display: flex;
    justify-content: space-around;
}
.dianping ul li.p-li {
    padding-top: 0.135rem;
}
.dianping ul li .designer-pic {
    width: 15%;
}
.dianping ul li .designer-pic img {
    width: 100%;
    vertical-align: top;
    border-radius: 50%;
}
.dianping ul li .designer-detail {
    width: 81%;
    border-bottom: 1px dashed #f5f5f5;
    padding-bottom: 0.135rem;
}
.dianping ul li:last-child {
    border-bottom: 1px solid #f5f5f5;
}
.dianping ul li:last-child .designer-detail {
    border-bottom: none;
}
.dianping ul li .designer-name {
    font-size: 0.135rem;
    color: #999;
}
.dianping ul li .designer-status {
    color: #FFAD5E;
    border: 1px solid #FFAD5E;
    border-radius: 9px;
    width: 0.45rem;
    height: 0.162rem;
    line-height: 0.162rem;
    text-align: center;
    font-size: 9px;
    padding: 0 5px;
    position: relative;
    top: -2px;
}
.dianping ul li .star-text {
    font-size: 12px;
    color: #999;
}
.dianping ul li .stars {
    display: inline-block;
}
.dianping ul li .stars span i img {
    display: block;
    width: 10px;
    height: 10px;
    float: left;
    margin-right: 2px;
}
.dianping ul li .designer-time {
    height: 19px;
    line-height: 18px;
    font-size: 12px;
    font-family: PingFang-SC-Medium;
    font-weight: 500;
    color: rgba(153, 153, 153, 1);
    float: right;
}
.dianping ul li .desinger-text p {
    width: 100%;
    font-size: 12px;
    font-family: PingFang-SC-Medium;
    color: #333;
    overflow: hidden;
    -ms-text-overflow: ellipsis;
    text-overflow: ellipsis;
    display: -webkit-box;
    display: box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
.designer-img-box {
    position: relative;
}
.img-num {
    font-size: 9px;
    text-align: center;
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 31px;
    height: 15px;
    background: rgba(0, 0, 0, .5);
    border-radius: 8px;
    color: rgba(255, 255, 255, 1);
    padding: 1px 1px 0 2px;
}
.designer-img {
    margin-top: 5px;
}
.designer-img li {
    width: 32%;
    float: left;
    height: 0.77rem;
    background: #ccc;
    margin-right: 5px;
    margin-bottom: 5px;
}
.designer-img li:nth-child(3n) {
    margin-right: 0;
}
.designer-img  li img {
    display: block;
    width: 100%;
    height: 100%;
    border-radius: 5px;
    vertical-align: top;
}
.team {
    height: 2rem;
    border-bottom: 1px solid #f5f5f5;
    margin-bottom: 0;
}
.team .ul {
    padding: 5px 4px;
}
.team .ul .swipe-item {
    width: 1.17rem;
    height: 1.5rem;
    box-shadow: 0px 1px 10px 0px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
    touch-action: none;
    text-align: center;
}
.team .ul .swipe-item .avator img {
    display: block;
    width: 0.57rem;
    height: 0.57rem;
    background: #ccc;
    border-radius: 50%;
    margin: 0.09rem auto;
}
.team .ul .swipe-item .name {
    font-size: 0.135rem;
    color: #333;
    font-weight: bold;
    width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.team .ul .swipe-item .job {
    color: #fff;
    font-size: 9px;
    width: 0.4rem;
    height: 0.126rem;
    background: url(../../../assets/img/qz/share/card.png) no-repeat;
    background-size: 100%;
    margin: 0 auto 0.09rem;
}
.team .ul .swipe-item .job-time {
    color: #999;
    font-size: 0.09rem;
}
.team .ul .swipe-item .example-num {
    color: #999;
    font-size: 0.09rem;
}
.company-intro {
    padding-bottom: 0.2rem;
}
.company-intro a {
    display: block;
    width: 100%;
    height: 100%;
}
.company-intro .c-intro {
    padding-bottom: 0.54rem;
}
.company-intro .c-intro .info-item {
    display: flex;
    justify-content: flex-start;
    align-items: flex-start;
    padding: 0.09rem 0;
}
.company-intro .info-item > span:first-child {
    display: flex;
    font-size: 12px;
    color: #999;
}
.company-intro .info-item > span:last-child {
    display: flex;
    flex: 2;
    font-size: 12px;
    color: #333;
}
.company-intro .fuwu .fuwu-ts {
    font-size: 12px;
    color: #999;
    float: left;
    width: 60px;
}
.company-intro .fuwu .f-div {
    font-size: 12px;
    color: #333;
    float: left;
    width: 80%;
}
.company-intro .fuwu .f-div span {
    margin-right: 4px;
}
.company-intro .fuwu .f-div span:nth-child(3n) {
    margin-right: 0;
}
.company-intro .fuwu .icon-gou {
    display: inline-block;
    width: 15px;
    height: 15px;
    background: url(../../../assets/img/qz/share/icon-gou.png) no-repeat;
    background-size: 100%;
    margin-right: 2px;
    position: relative;
    top: 3px;
}
.footer {
    position: fixed;
    z-index: 99;
    bottom: 0;
    width: 100%;
    height: 0.54rem;
    background:rgba(255,255,255,1);
    box-shadow: 0px 1px 10px 0px rgba(0, 0, 0, 0.1);
}
.footer .advice {
    float: left;
    margin: 0.09rem 0.5rem 0.09rem 0.36rem;
}
.footer .advice img {
    display: block;
    width: 25px;
    height: 25px;
}
.footer .evaluate {
    float: left;
    margin: 0.09rem 0.5rem 0.09rem 0;
}
.footer .evaluate img {
    display: block;
    width: 25px;
    height: 25px;
}
.footer .appointment {
    float: left;
    width: 1.26rem;
    height: 0.315rem;
    line-height: 0.315rem;
    text-align: center;
    font-size: 0.135rem;
    color: #fff;
    margin-top: 0.1rem;
    background: -webkit-linear-gradient(left, #FF795E , #FF5353);
    background: -o-linear-gradient(right, #FF795E, #FF5353);
    background: -moz-linear-gradient(right, #FF795E, #FF5353);
    background: linear-gradient(to right, #FF795E , #FF5353);
    border-radius: 0.15rem;
}
.footer .appointment a {
    display: block;
    width: 100%;
    height: 100%;
}
.footer p {
    font-size: 12px;
    color: #666;
}
.licence {
    width: 100%;
    height: 100%;
    background: #000;
    position: fixed;
    top: 0;
    z-index: 999;
}
.licence .img {
    position: absolute;
    display: -webkit-box;
    display: -webkit-flex;
    display: flex;
    -webkit-box-align: center;
    -webkit-align-items: center;
    align-items: center;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
    justify-content: center;
    left: 0;
    right: 0;
    bottom: 0;
    top: 0;
    width: 100%;
    height: 100%;
    margin: 0;
}
.licence img {
    display: block;
    transform-style: preserve-3d;
    width: auto;
    height: auto;
    max-width: 100%;
    max-height: 100%;
}
</style>
