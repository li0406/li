<template>
  <section id="order-result">
    <div class="result-view">
      <div style="font-size:0.14rem; text-align:center;padding-top:0.15rem">装修成本估价</div>
      <table class="view-table">
        <td>
          <div class="small-text">预估总价</div>
          <div>
            <span style="color:#ff5353; font-size:0.15rem;font-weight:bold;">{{tempDetails.all_price}}</span><span style="color:#999;font-size:0.1rem"> 元</span>
          </div>
        </td>
        <td>
          <div class="changeButton" @click="changeType" v-bind:class="{ 'clickable': !clickAble}">
            <span >{{resultView.version_name}}</span>
            <span class="circle-xunhuan"><img src="../../../assets/img/qz/zxkb/xunhuan.png"></span>
          </div>
        </td>
        <td>
          <div class="small-text">预估半包单价</div>
          <div>
            <span style="font-size:0.15rem;color:#333;font-weight:bold">{{tempDetails.ban_price}}</span>
            <span style="color:#999; font-size:0.1rem">元/平方</span>
          </div>
        </td>
      </table>
      <div class="see-more-detail">
        <span @click="seeMoreDetail">查看更多详细报价信息&nbsp;
          <span class="arrow-down" v-show="!isShowDetail"></span>
          <span class="arrow-up" v-show="isShowDetail"></span>
        </span>
      </div>
    </div>
    <div class="detail-box" v-show="isShowDetail">
      <br>
      <div style="text-align:center; margin-bottom:0.14rem;font-size:0.14rem;">
        {{baseInfo.area}}㎡ &nbsp;&nbsp;&nbsp;&nbsp;{{baseInfo.hx}}
      </div>
      <div class="detail-header">
        <div :style="{width: sliderWidth+'vw'}">
          <div v-for="(item, index) in tempDetails.detail" :key="item.cate_name" @click="changeDetailTab(index)" class="detal-catogery">
            {{item.cate_name}}
            <span class="detail-active" v-if="item.active"></span>
            <span class="detail-active1" v-if="!item.active"></span>
          </div>
        </div>
      </div>
      <div class="tab-content">
        <template v-for="(item, levelIndex) in tempDetails.detail">
          <template v-if="item.is_company === 1">
            <div class="tab-content-item" :key="item.cate_name" v-show="item.active">
              <table style="height:160px">
                <tr>
                  <td style="font-size:0.13rem; color:#ff5353;">费用：{{item.total}}<span class="danwei"> 元</span></td>
                  <td></td>
                  <td style="height: 60px"><span class="go-order-btn" @click="toFindCompany">找装修</span></td>
                </tr>
                <tr v-for="(item1, index) in item.children" :key="item1.cate_name" v-if="index === 0">
                  <td>{{item1.cate_name}}：</td>
                  <td>0<span style="font-size:0.08rem; color:#999"> 元</span>&nbsp;&nbsp;<span style="font-size:0.11rem" class="delText">{{item1.total}} <span style="color:#999; font-size:0.1rem;">元</span></span></td>
                  <td><span class="go-order-btn" @click="toMianfeiSheji">免费设计</span></td>
                </tr>
                <tr v-for="(item1, index) in item.children" :key="item1.cate_name" v-if="index !== 0">
                  <td>{{item1.cate_name}}：</td>
                  <td>{{item1.total}} <span class="danwei">元</span></td>
                  <td></td>
                </tr>
              </table>
            </div>
          </template>
          <template v-else>
            <div class="tab-content-item" :key="item.cate_name" v-show="item.active">
              <div class="cost-level1">
                <div style="font-size:0.13rem; color:#ff5353;">费用：{{item.total}}<span class="danwei"> 元</span></div>
                <div v-for="(item2, levle2Index) in item.children" :key="item2.cate_name" @click="openChildrenList(levelIndex, levle2Index)">
                  <div class="cost-level-title">
                    {{item2.cate_name}}
                    <span class="pull-right">
                      <span>{{item2.total}}<span class="danwei"> 元</span></span>
                      <span>
                        <template v-if="item2.children.length>0">
                          <span class="arrow-down" v-show="!item2.active"></span>
                          <span class="arrow-up" v-show="item2.active"></span>
                        </template>
                      </span>
                    </span>
                  </div>
                   <ul class="const-child-box" v-show="item2.active">
                      <li v-for="item3 in item2.children" :key="item3.cate_name">
                        <span class="pull-left">{{item3.cate_name}}</span>
                        <span class="pull-right">{{item3.total}}<span class="danwei">元</span></span>
                      </li>
                    </ul>
                </div>
              </div>
            </div>
          </template>
        </template>
      </div>
      <p style="color:#999; margin:0px 0.1rem; font-size:0.1rem">*详细报价信息包含装修全部费用，仅供参考</p>
    </div>
    <m-tips ref="tips"/>
  </section>
</template>
<script>
import { getResultDetail } from '../../../api/qzZxkb.js'
import mTips from '../../common/mTips.vue' // 引入tips 提示框
export default {
  name: 'orderResult',
  props: ['baseInfo'],
  components: {
    mTips
  },
  data () {
    return {
      typeIndex: 0,
      resultView: {},
      tempDetails: {},
      isShowDetail: false,
      sliderWidth: 100,
      clickAble: true
    }
  },
  created () {
    this.getResult(this.baseInfo.versionList[0].id)
    this.resultView = this.baseInfo.versionList[0]
  },
  methods: {
    changeType () {
      if (!this.clickAble) {
        return
      }
      this.typeIndex = this.typeIndex + 1
      let index = this.typeIndex % this.baseInfo.versionList.length
      this.resultView = this.baseInfo.versionList[index]
      this.clickAble = false
      this.getResult(this.baseInfo.versionList[index].id)
    },
    getResult (versionId) {
      getResultDetail({
        cs: this.baseInfo.cs,
        hx: this.baseInfo.hxId,
        mianji: this.baseInfo.area,
        version_id: versionId
      }).then(res => {
        if (res.data.error_code === 0) {
          this.tempDetails = res.data.data
          if (this.tempDetails.detail.length) {
            this.sliderWidth = this.tempDetails.detail.length * 23
            for (let m = 0; m < this.tempDetails.detail.length; m++) {
              this.tempDetails.detail[m].active = m === 0
            }
          } else {
            this.sliderWidth = 100
          }
        } else {
          this.$refs.tips.tipsFadeIn({
            text: res.data.error_msg
          })
        }
        this.clickAble = true
      }).catch(res => {
        this.$refs.tips.tipsFadeIn({
          text: '网络错误，请稍后重试'
        })
      })
    },
    changeDetailTab (index) {
      for (let i in this.tempDetails.detail) {
        let item = this.tempDetails.detail[i]
        item.active = parseInt(i) === index
        this.tempDetails.detail.splice(i, 1, item)
        this.openChildrenList(index, 0, 'fa')
      }
    },
    seeMoreDetail () {
      this.isShowDetail = !this.isShowDetail
    },
    openChildrenList (one, two, source) {
      for (let i in this.tempDetails.detail[one].children) {
        let item = this.tempDetails.detail[one].children[i]
        if (source === 'fa') {
          if (two === parseInt(i)) {
            item.active = true
          } else {
            item.active = false
          }
        } else {
          if (two === parseInt(i)) {
            item.active = !item.active
          } else {
            item.active = false
          }
        }
        this.tempDetails.detail[one].children.splice(i, 1, item)
      }
    },
    toFindCompany () {
      this.$bridge.callNative('Native_decorate_service', '000', function (res) {})
    },
    toMianfeiSheji () {
      this.$bridge.callNative('Native_design_service', '000', function (res) {})
    }
  }
}
</script>
<style scoped>
#order-result{
  margin: 0px -0.1rem;
}
.view-table{
  width:100%;
  height: 0.8rem;
  vertical-align: middle;
  text-align: center;
  table-layout: fixed;
}
.changeButton{
  padding:0.06rem 0px;
  text-align: center;
  background: #FF9853;
  color:#fff;
  font-size: 0.15rem;
  border-radius: 0.18rem;
}
.circle-xunhuan{
  display: inline-block;
  width:0.15rem;
  height:0.15rem;
  border:1px solid #fff;
  border-radius: 50%;
  position: relative;
  top:0.028rem;
}
.circle-xunhuan img{
  width:0.115rem;
  position: absolute;
  margin:auto;
  left:0px;
  right: 0px;
  top:0px;
  bottom:0px;
}
.small-text{
  color: #999;
}
.detail-box{
  border-top: 1px solid #E6E6E6;
}
.see-more-detail{
  text-align: right;
  color:#999;
  padding-right: 0.1rem;
  font-size: 0.11rem;
  padding-bottom: 0.15rem;
}
.detail-header{
  margin:0px 0.1rem;
  overflow: auto;
}
.detail-header .detal-catogery{
  float: left;
  width:23vw;
  text-align: center;
}
.detail-active{
  display: block;
  width:50px;
  height:3px;
  background:#ff5353;
  white-space: nowrap;
  margin:5px auto;
}
.detail-active1{
  display: block;
  width:50px;
  height:3px;
  background:none;
  white-space: nowrap;
  margin:5px auto;
}
.tab-content{
  margin:0.1rem 0.1rem 5px 0.1rem;
}
.cost-level-title{
  border-bottom: 1px dashed #dedddd;
  font-size: 0.128rem;
  padding: 12px 0px;
}
.const-child-box li{
  font-size: 0.11rem;
  overflow: hidden;
  padding:8px 0px;
}
.danwei{
  color:#999 !important;
  font-size: 0.08rem;
}
.go-order-btn{
  text-align: center;
  display: block;
  width:0.5rem;
  font-size: 0.12rem !important;
  background:#ff5353;
  padding:0.03rem 0.1rem;
  border-radius: 0.14rem;
  color:#fff;
  float: right;
}
.tab-content table{
  width:100%;
  height: 1.2rem;
}
.item.children{
  overflow: hidden;
}
.arrow-down{
  display: inline-block;
  width:12px;
  height: 5px;
  background: url('../../../assets/img/qz/zxkb/arrow-down.png') no-repeat;
  background-size: 100% 100%;
  position: relative;
  top: -2px;
}
.arrow-up{
  display: inline-block;
  width:12px;
  height: 5px;
  background: url('../../../assets/img/qz/zxkb/arrow-up.png') no-repeat;
  background-size: 100% 100%;
  position: relative;
  top: -2px
}
.cost{
  color:#ff5353;
  font-size: 0.128rem;
}
.cost span{
  color:#999!important;
  font-size: 0.11rem;
}
.clickable{
  background:#999;
}
.delText{
  text-decoration:line-through;
}
</style>
