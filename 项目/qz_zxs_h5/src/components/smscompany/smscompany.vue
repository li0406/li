<template>
  <div>
    <div class="companybanner">
      <a>
        <img class="anniutc" src="https://staticqn.qizuang.com/custom/20200901/FhMXHD3kRJaMofzz6qypwFxT5eq7" />
      </a>
    </div>
    <!--列表页-->
    <div class="liebiaodkz">
      <div class="liebiaozj">
      <div class="flex jus-cen list-title-div">
        <div>
          <img class="ele-img" src="https://staticqn.qizuang.com/custom/20200901/FrVdjWxKsArOU7QMZGFV6tvZQ_UH" alt="">
        </div>
        <div class="font-32 b">为您精心分配的装修公司</div>
        <div>
          <img class="ele-img" src="https://staticqn.qizuang.com/custom/20200901/FvrmLG60oHyWNJ6xvvcfy9-Q4IFk" alt="">
        </div>
      </div>
        <ul class="liebiaoul">
            <li v-for="company of companylist" :key="company.id" @click="tomdetail(company)">
                <div class="gsheader">
                  <img v-if="company.logo!=''" :src="company.logo" alt="">
                  <img v-else src="http://staticqn.qizuang.com/custom/20200903/FvdI34ArY_wVYRnqVcPCffPRS1s6" alt="">
                  <div class="h-con">
                    <div class="title">
                      <span v-if="company.qc.length<7">{{company.qc}}</span>
                      <span v-else>{{company.qc.substring(0,7)}}...</span>
                      <img v-if="company.deposit_money==1" src="https://staticqn.qizuang.com/custom/20200902/FvPfrsPGuvK92WwezEWMOuMoSYLI" alt="">
                    </div>
                    <div class="stars">
                      <div class="s-num" v-if="company.evaluation<20">
                        <img v-for="a of 1" :key="a" src="https://staticqn.qizuang.com/custom/20200902/FnpiBzuZH11bA7wVUiXdS3Qu_xLV" alt="">
                      </div>
                      <div class="s-num" v-if="(company.evaluation>20)&&(company.evaluation<40)">
                        <img v-for="b of 2" :key="b" src="https://staticqn.qizuang.com/custom/20200902/FnpiBzuZH11bA7wVUiXdS3Qu_xLV" alt="">
                      </div>
                      <div class="s-num" v-if="(company.evaluation>40)&&(company.evaluation<60)">
                        <img v-for="c of 3" :key="c" src="https://staticqn.qizuang.com/custom/20200902/FnpiBzuZH11bA7wVUiXdS3Qu_xLV" alt="">
                      </div>
                      <div class="s-num" v-if="(company.evaluation>60)&&(company.evaluation<80)">
                        <img v-for="d of 4" :key="d" src="https://staticqn.qizuang.com/custom/20200902/FnpiBzuZH11bA7wVUiXdS3Qu_xLV" alt="">
                      </div>
                      <div class="s-num" v-if="company.evaluation>80">
                        <img v-for="e of 5" :key="e" src="https://staticqn.qizuang.com/custom/20200902/FnpiBzuZH11bA7wVUiXdS3Qu_xLV" alt="">
                      </div>

                        <div v-if="company.evaluation!=0">{{company.evaluation}}% 好评率</div>
                    </div>
                    <div class="nums">
                        <div class="item anl" v-if="company.case_count!=0">
                          案例数 <span>{{company.case_count}}</span>
                        </div>
                        <div class="item sj" v-if="company.des_count!=0">
                          设计师 <span>{{company.des_count}}</span>
                        </div>
                        <div class="item gd" v-if="company.gd_count!=0">
                          在建工地 <span>{{company.gd_count}}</span>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="gscenter" v-if="company.banners!=''">
                    <div class="flex">
                      <div class="imgbox" v-for="(imgurl,i) of company.banners" :key="i">
                          <img v-if="i<4" :src="imgurl" alt="">
                      </div>
                    </div>
                    <div class="tips">
                        <div class="item" v-for="(companyname,j) of company.tags" :key="j">{{companyname}}</div>
                    </div>
                </div>
                  <div class="gsbottom">
                        <div class="item juan" v-for="(cardname) of company.card" :key="cardname">
                          <span class="son">劵</span>
                          <span class="con">{{cardname}}</span>
                        </div>
                        <div class="item hui" v-for="(activityname) of company.activity" :key="activityname">
                          <span class="son">惠</span>
                          <span class="con">{{activityname}}</span>
                        </div>
                  </div>
                <div class="join-btn" v-if="company.tel!=''"><a @click="phone(company.tel)">一键拨打</a></div>
            </li>
        </ul>
      </div>
      <div class="foot-div">
        <div class="flex colfff font-30 foot-title mt">
          <div>装修咨询热线</div>
          <img class="tel-img" src="http://staticqn.qizuang.com/custom/20200901/FnH2vciIMSR4zityz4TzPW3yp6n9" alt="">
          <div><a :href="'tel:'+tel">400-6969-336</a></div>
        </div>
        <div class="col999 font-24">
          <div class="mt">有房要装，就上齐装！</div>
          <div class="mt">手机齐装网：m.qizuang.com  苏IC12045334号</div>
          <div class="mt">苏州云网通信息科技有限公司</div>
          <div class="font-18">本站内容齐装网保留所有权利·不承担法律责任</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {ordercompany} from '@/api/api'
  export default {
    data() {
      return {
        tel:'400-6969-336',
        order_id:'',
        companylist:[],
        starnum:1
      }
    },
    created() {
      this.getcompanylist()
    },
    methods: {
      phone(tel){
        event.stopPropagation()
        window.location.href=`tel:${tel}`
      },
      tomdetail(company){
        window.location.href=`https://m.qizuang.com/${company.bm}/company_home/${company.id}/`
      },
      getcompanylist(){
        ordercompany(this.$route.query.order_id).then((res) => {
          if (parseInt(res.data.error_code) === 0) {
            this.companylist=res.data.data
          } else {
            this.$message.error(res.data.error_msg)
          }
        })
      }
    },
  }
</script>

<style scoped>
header {
  position: relative;
  z-index: 99;
}

.m-wrap {
  position: relative !important;
}

.m-header-tit {
  font-size: 14px;
  font-weight: 400;
}

#page-count #current-page {
  color: #ff5353 !important;
}

#jump-num-box ul li a {
  display: block !important;
}

.box {
  position: relative;
  z-index: 9 !important;
  background: #f5f5f5;
}

.companybanner {
  /*background: url(/assets/mobile/company2815/img/compbjban.png) no-repeat center center;*/
  /*background-size: 100% 100%;*/
  position: relative;
  z-index: 9;
}

.companybanner a {
  display: block;
  width: 100%;
  height: 1.11rem;
}

.companybanner .anniutc {
  width: 100%;
  display: inline-block;
  vertical-align: top;
  /*position: absolute;*/
  /*bottom: 0.14rem;*/
  /*left: 0.18rem;*/
}

.daohangquyu {
  display: flex;
  background: #fff;
  position: relative;
  z-index: 10;
}

.daohangquyu li {
  text-align: center;
  flex-grow: 1;
  line-height: 0.4rem;
  color: #333;
  font-size: 0.125rem;
}

.daohangquyu li.noborder {
  border-right: 0 !important;
}

.daohangquyu li .fa-angle-down {
  margin-left: 2px;
  transition: all 0.5s;
  -webkit-transition: all 0.5s;
  -moz-transition: 0.5s;
}
.daohangquyu li .fa-angle-down:before{
  content: '';
  display:block;
  width: 0.080rem;
  height: 0.080rem;
  /*background: url("/assets/mobile/company/img/xxia.png") no-repeat;*/
  background-size: 100% 100%;
  position: relative;
  margin-left: 0.042rem;
}
.daohangquyu li .yansexz:before{
  margin-right: 0.042rem;
}
.beijingtm {
  width: 100%;
  height: 100%;
  position: fixed;
  left: 0;
  right: 0;
  top: 0;
  bottom: 0;
  margin: auto;
  background: rgba(0, 0, 0, .7);
  z-index: 8;
  display: none;
}

.xialakz {
  width: 100%;
  background: #fff;
  overflow: hidden;
  position: absolute;
  z-index: 9;
}

.xialakz .choseul {
  display: none;
  margin-top: -1000px;
  padding: 10px;
  padding-left: 5px;
  padding-right: 5px;
  box-sizing: border-box;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
}

.xialakz .choseul li {
  display: inline-block;
  float: left;
  width: 24%;
  height: 0.35rem;
  line-height: 0.35rem;
  text-align: center;
  margin-right: 1.3%;
  border: 1px solid #e6e6e6;
  border-radius: 5px;
  box-sizing: border-box;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  margin-bottom: 0.08rem;
}

.xialakz .choseul li a {
  display: inline-block;
  width: 100%;
  height: 100%;
  border-radius: 5px;
  overflow: hidden;
  -ms-text-overflow: ellipsis;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.xialakz .choseul li:nth-child(4n+0) {
  margin-right: 0 !important;
}

.xuanze {
  color: #fff !important;
  background: #ff5353 !important;
}

.yanse {
  color: #ff5353 !important;
}

.baise {
  color: #fff !important;
}

.yansexz {
  color: #ff5353 !important;
  transform: rotate(-180deg);
  -webkit-transform: rotate(-180deg);
  -moz-transform: rotate(-180deg);
  margin-bottom: 0.020rem;
}

.tofixed {
  position: fixed;
  top: 0px;
  left: 0px;
  z-index: 9 !important;
  width: 100%;
}

.input-box input {
  width: 100%;
  border: none;
  height: 100%;
  outline: none;
  padding-left: 8px;
  font-size: 14px !important;
}

.input-box button {
  border: none;
  width: 100%;
  height: 100%;
  background: none;
  text-align: left;
  padding-left: 8px;
  outline: none;
  color: #333 !important;
}

.wujieguo {
  margin: 0 10px;
  overflow: hidden;
  text-align: center;
}

.wujieguo .wenzitis {
  text-align: center;
  font-size: 0.14rem;
  color: #333;
  margin-top: 0.4rem;
}

.wujieguo img {
  width: 0.765rem;
  height: 0.765rem;
  margin-top: 0.05rem;
}



/*优惠券*/
.ticket-box {
  position: fixed;
  top: 135px;
  left: 0;
  margin: 0px 20px;
  border-radius: 10px;
  background: #fff;
  width: 80%;
  /*height: 300px;*/
  z-index: 30;
  padding: 20px;
  display: none;
}

.c-ticket,
.c-ticket-detail,
.c-ticket-detail span,
.c-ticket-detail p {
  display: inline-block;
  line-height: 14px;
}

.c-ticket {
  width: 100%;
  margin-top: 5px;
  border-top: 1px solid #e6e6e6;
}

.c-ticket-detail {
  width: 100%;
  /*font-size: 0;*/
  padding: 3px;
  line-height: 16px;
  /*border: 1px solid orange;*/
  /*border-radius: 20px;*/
  margin-top: 5px;
}

.c-ticket-total {
  color: darkorange;
  padding: 8px 0px 5px 3px;
  line-height: 16px;
}

.c-ticket-total .left-arrow {
  float: right;
  margin-right: 5px;
  font-size: 14px;
}

.ticket-2 {
  /*border: 1px solid orangered;*/
}

.ticket-name {
  vertical-align: middle;
  font-size: 14px;
  color: #fff;
  background: darkorange;
  /*border-radius: 50%;*/
  text-align: center;
  padding: 3px;
  line-height: 15px;
}

.ticket-arrow_1 {
  background: orangered;
}

.ticket-name_1,
.ticket-arrow_1 {
  background: orangered;
}

p.ticket-intro {
  width: 85%;
  /*color: darkorange;*/
  font-size: 14px;
  line-height: 16px;
  vertical-align: middle;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}

.ticket-intro_1 {
  color: #000;
}

.ticket-arrow {
  vertical-align: middle;
  font-size: 14px;
  color: #fff;
  background: darkorange;
  border-radius: 50%;
  text-align: center;
  /* display: inline-block; */
  line-height: 15px;
  margin-right: 3px;
  padding: 1px 2px;
}

.ticket-content {
  position: relative;
}

.ticket-names {
  font-size: 20px;
  text-align: center;
  margin-bottom: 5px;
}

.ticket-detail {
  /*background: #fcbc19 url("/assets/mobile/company2815/img/ticket.png") repeat-x bottom;*/
  padding: 10px;
  background-position-x: 2px;
  border-top-left-radius: 5px;
  border-top-right-radius: 5px;
}

.ticket-style {
  color: #FFF;
  text-align: center;
}

.ticket-style i,
.ticket-style span {
  vertical-align: middle;
  display: inline-block;
}

.ticket-style i {
  font-size: 14px;
  margin-top: 15px;
}

.ticket-style span {
  font-size: 30px;
}

.ticket-style p {
  display: inline-block;
  vertical-align: middle;
  font-size: 14px;
}

.ticket-time {
  color: #fff;
  font-size: 16px;
}

.instructions {
  background: #fff9eb;
  padding: 10px;
}

.instructions h3 {
  font-size: 14px;
  line-height: 16px;
  margin: 5px 0;
}

.instructions p {
  font-size: 14px;
  color: #333;
  line-height: 20px;
}

.order-btn {
  display: block;
  padding: 10px 40px;
  font-size: 16px;
  border-radius: 20px;
  background: pink;
  text-align: center;
  margin: 10px 0;
}

.free-order {
  background: #ff5353;
  color: #fff;
}

.order-done {
  background: #f2f2f2;
  color: #333;
}

.close-icon {
  position: absolute;
  top: -50px;
  right: 0;
  font-size: 30px;
  color: #fff;
  padding: 10px;
  font-weight: 400;
}

.tomap {
  font-size: 0.125rem;
  color: #333;
}
/*1120 PC&M端818活动站内推广新增广告*/
.yinliu-818{
  display: block;
  width:  0.683rem;
  height: 0.683rem;
  position: fixed;
  right: 0.154rem;
  bottom: 1.1rem;
  /*background: url("/assets/mobile/article/images/yinliu.gif") no-repeat;*/
  background-size: 100% 100%;
}
/*首页广告*/
#q-a-z-dv {
  padding: 0.10rem 7px;
  background-color: white;
  border-bottom: 1px solid #e6e6e6;
}
.qz_ad_single {
  margin-bottom: 0 !important;
}
.qz_ad_single_text {
  margin-bottom: 0 !important;
  color: #333 !important;
  font-size: 0.16rem !important;
}
.qz_ad_multi {
  margin-bottom: 0 !important;
}
.qz_ad_multi_text .qz_ad_multi_text-title {
  margin-bottom: 0 !important;
  color: #333 !important;
  font-size: 0.16rem !important;
}
.qz_ad_multi_text {
  margin-bottom: 0 !important;
}
.qz_ad_slide {
  margin-bottom: 0 !important;
}

/*公司列表*/
.liebiaodkz {
  width: 100%;
  margin: 0 auto;
  overflow: hidden;
  background: #fff;
}

.liebiaodkz .liebiaozj {
  overflow: hidden;
}

.liebiaodkz .liebiaozj .liebiaoul {
  overflow: hidden;
}
.liebiaodkz .liebiaozj .liebiaoul li {
  position: relative;
  box-sizing: border-box;
  padding: 0.085rem 0.128rem;
  overflow: hidden;
  border-bottom: 0.085rem solid #F7F7F7;
}
/*1. header*/
.gsheader{
  overflow: hidden;
  display: flex;
}
.gsheader>img{
  display: block;
  width:0.683rem;
  height:0.341rem;
  border-radius:0.017rem;
  margin-right: 0.085rem;
  margin-top: 0.052rem;
}
.gsheader .h-con .title{
  /*height:0.213rem;*/
  display: flex;
  align-items: center;
  font-size:0.119rem;
  font-weight:bold;
  color:rgba(51,51,51,1);
}
.gsheader .h-con img{
  display: block;
  width: 0.427rem;
  height: 0.128rem;
  margin-left: 0.043rem;
}
.gsheader .h-con .stars{
  height:0.213rem;
  display: flex;
  align-items: center;
  font-size:0.085rem;
  color: #333333;
  overflow: hidden;
}
.gsheader .h-con .stars .s-num{
  height:100%;
  display: flex;
  box-sizing: border-box;
  align-items: center;
  padding-bottom: 0.020rem;
}
.gsheader .h-con .stars .s-num img{
  display: inline-block;
  width: 0.102rem;
  height:0.094rem;
  margin-left: 0.010rem;
  border-radius: 0.017rem;
}
.gsheader .h-con .nums{
  overflow: hidden;
  display: flex;
  align-items: center;
  font-size:0.085rem;
  font-weight:500;
  color:rgba(102,102,102,1);
}
.gsheader .h-con .nums .item{
  padding: 0 0.055rem;
  border: 1px solid #dddddd;
  height: 0.180rem;
  display: flex;
  justify-content: center;
  align-items: center;
  margin-right:0.043rem;
  border-radius:0.017rem;
  line-height:0.190rem;
}
.gsheader .h-con .nums .item span{
  color: #FF7A2A;
}
/*2. center*/
.gscenter {
  padding-top: 0.085rem;
  overflow: hidden;
}
.gscenter .imgbox{
  display: flex;
  align-items: center;
  overflow: hidden;
}
.gscenter .imgbox img{
  display: block;
  width: 0.704rem;
  height: 0.393rem;
  margin-left: 0.043rem;
  border-radius:0.017rem;
}
.gscenter .imgbox img:first-child{
  margin-left: 0;
}
.imgbox:not(:first-child){
  margin-left: 0.043rem;
}
.gscenter .tips{
  display: flex;
  align-items: center;
  overflow: hidden;
  min-height: 0.324rem;
  flex-wrap: wrap;
}
.gscenter .tips .item{
  display: flex;
  padding: 0.030rem 0.055rem;
  border: 1px solid #DB2C2C;
  margin-right:0.043rem;
  border-radius:0.017rem;
  font-size: 0.085rem;
  color: #DB2C2C;
  box-sizing: border-box;
}
.gscenter  .tips .item:first-child{
  margin-left: 0;
}
/*3.gsbottom */
.gsbottom{
  overflow: hidden;
}
.gsbottom .item{
  width:50%;
  height: 0.265rem;
  padding-right: 0.085rem;
  box-sizing: border-box;
  display: flex;
  align-items: center;
  float: left;
  font-size:0.094rem;
  font-weight:500;
  color:rgba(51,51,51,1);
  overflow: hidden;
  text-overflow:ellipsis;
  white-space: nowrap;
}
.gsbottom .item .son{
  display: flex;
  overflow: hidden;
  padding: 0px 3px;
  justify-content: center;
  font-size: 0.085rem;
  color: #fff;
  margin-right: 0.060rem;
  border-radius: 0.017rem;
  box-sizing: border-box;
}
.gsbottom .hui .son{
  background: #FF7A2A;
}
.gsbottom .juan .son{
  background: #F33535;
}
.gsbottom .item .con{
  width: 1.20rem;
  overflow: hidden;
  text-overflow:ellipsis;
  white-space: nowrap;
}
/*4.预约*/
.join-btn{
  position: absolute;
  top: 0.205rem;
  right:0.142rem;
  width:0.512rem;
  height:0.205rem;
  background:rgba(219,44,44,1);
  border-radius:0.102rem;
  font-size:0.102rem;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  outline: none;
  border: none;
}

/*发单*/
.mask{
  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(4, 4, 4, 0.5);
  display: none;
  z-index: 99;
}
.order-box{
  width: 80%;
  max-width: 4.6rem;
  margin:1.5rem auto 0px auto;
  border-radius: 5px;
  background: #fff;
  box-shadow: 0px 0px 10px #dedede;
  padding: 0.1rem 0.1rem;
  padding-top: 0.10rem;
  position: fixed;
  top: 0.02rem;
  left: 0;
  right: 0;
  display: none;
  z-index: 100
}
.order-box .m-bj-edit li{
  margin-bottom:18px;
}
.order-head-title{
  text-align: center;
  height: 18px;
  width:56%;
  margin:0px auto;
  line-height: 35px;
}
.order-head-title span{
  background:#fff;
  padding:10px 15px;
  font-size:0.154rem;
  font-weight:bold;
  color:rgba(51,51,51,1);
}
.border-body{
  margin-top:0.24rem;
}
.m-b-btn{
  background: #DB2B2B;
  height:0.375rem;
  line-height: 0.375rem;
  border-radius: 5px;
  text-align: center;
  margin:0.034rem;
  font-size: 0.137rem;
}

.m-bj-edit li input,.m-bj-edit li button{
  height: 0.375rem;
  line-height:  0.375rem;
  width: 100%;
  font-size: 0.102rem;
  padding: 0;
  padding-left: 0.128rem;
  border: none;
  outline: none;
  color: rgb(51,51,51);
  background:#F7F7F7;
  border-radius: 5px;
}
.order-mz{
  height:0.160rem !important;
  background: #fff !important;
  font-size: 0.102rem !important;
}
.result-box{
  position: fixed;
  margin: auto;
  left:0px;
  right: 0px;
  top:30px;
  bottom: 0px;
  width: 88%;
  height: 1.809rem;
  z-index: 999;
  background:white;
  border-radius: 5px;
  display: none;
  text-align:center;
}
.result-box .close{
  position: absolute;
  right:0.128rem;
  top:0.128rem;
  z-index: 9999;
  width:25px;
  height:25px;
  /*background:url("../img/close.png") center center;*/
  background-size: 100%
}
.order-box .closeOne{
  position: absolute;
  right:0.128rem;
  top:0.128rem;
  z-index: 9999;
  width:25px;
  height:25px;
  /*background:url("../img/close.png") center center;*/
  background-size: 100%
}
.result-box .result-img{
  display:block;
  width: 0.597rem;
  height: 0.597rem;
  margin: 0.120rem auto;
  margin-top:0.280rem;
}
.result-box .result-title{
  font-size:0.137rem;
  font-weight:bold;
  color:rgba(51,51,51,1);
  line-height:0.375rem;
}
.result-box .result-con{
  font-size:0.102rem;
  font-weight:500;
  color:rgba(101,101,101,1);
  line-height:0.200rem;
}
.daohangquyu li .line{
  float: right;
  width:0.009rem;
  height:0.171rem;
  background:#F7F7F7;
  border-radius:1px;
  margin-top: 0.114rem;
}
.get-recommend{
  margin-top: 0;
  padding-top: 0;
}
.input-box{
  border: none;
}
.input-box button{
  background: #f7f7f7;
}
.input-box input{
  background: #f7f7f7;
}
#mianze:checked + label{
  color: #BF2626;
  top: 0px;
}
.fa-check:before{
  left: 0px;
}
.flex{
  display: flex;
}
.jus-cen{
  justify-content: center;
}
.list-title-div{
  color: #FF9D2A;
  margin: 0.1834rem 0 0.1rem 0;
}
.font-32{
  font-size: 0.1365rem;
}
.ele-img{
  width: 0.298rem;
  height: 0.085rem;
}
.b{
  font-weight: bold;
}
.col999{
  color: #999;
}
.colfff{
  color: #fff;
}
.font-30{
  font-size: 0.128rem;
}
.font-24{
  font-size: 0.1024rem;
}
.font-18{
  font-size: 0.0768rem;
}
.foot-div{
  width: 100%;
  background-color: #333;
  text-align: center;
  padding: 0.2rem 0 0.3rem 0;
}
.foot-title{
  justify-content: center;
  align-items: center;
}
.tel-img{
  width: 0.1194rem;
  height: 0.1322rem;
}
.mt{
  margin: 0 0 0.1rem 0;
}
</style>
