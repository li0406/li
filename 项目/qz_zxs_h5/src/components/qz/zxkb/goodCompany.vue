<template>
  <section>
    <br>
    <div class="good-comp-box">
      本地优秀装企推荐
      <span class="pull-right" @click="toSeeMoreCompany">更多优秀装企 > </span>
    </div>
    <ul class="good-com-list">
      <li class="company-item" v-for="item in dataList" :key="item.id" @click="toDetail(item.id)">
        <div class="company-top-info">
          <div class="company-logo pull-left">
            <img :src="item.logo">
          </div>
          <div class="company-top-right pull-left">
            <div class="company-title">
              <span class="pull-left">{{item.name}}</span>
              <span class="pull-right addr-info">{{item.area_name}}</span>
            </div>
            <div class="star-dp">
              <template v-if="item.comment_count > 3">
                <template v-if="item.score === 5">
                  <span class="red-star star"></span>
                  <span class="red-star star"></span>
                  <span class="red-star star"></span>
                  <span class="red-star star"></span>
                  <span class="red-star star"></span>
                </template>
                <template v-if="item.score < 5 && item.score >= 4.5">
                  <span class="red-star star"></span>
                  <span class="red-star star"></span>
                  <span class="red-star star"></span>
                  <span class="red-star star"></span>
                  <span class="half-star star"></span>
                </template>
                <template  v-if="item.score < 4.5 && item.score >= 4">
                  <span class="red-star star"></span>
                  <span class="red-star star"></span>
                  <span class="red-star star"></span>
                  <span class="red-star star"></span>
                  <span class="gray-star star"></span>
                </template>
                <template v-if="item.score < 4">
                  <span class="red-star star"></span>
                  <span class="red-star star"></span>
                  <span class="red-star star"></span>
                  <span class="half-star star"></span>
                  <span class="gray-star star"></span>
                </template>
                &nbsp;&nbsp;<span style="color:#999; padding-right:5px;">{{item.comment_count}}</span><span>位业主点评</span>
              </template>
            </div>
          </div>
        </div>
        <div class="neck-info">
          <template v-if="item.case_count>0">案例：<span>{{item.case_count}}</span></template>
          <template v-if="item.case_count>0 && item.team_count>0"><span style="position:relative;top:-2px; color:#333; font-size:0.09rem;"> | </span> </template>
          <template v-if="item.team_count>0">设计师：<span>{{item.team_count}}</span></template>
          <template v-if="item.building_count>0 && item.team_count>0"><span style="position:relative;top:-2px; color:#333; font-size:0.09rem;"> | </span>  </template>
          <template v-if="item.building_count>0">在建工地：<span>{{item.building_count}}</span></template>
        </div>
        <div class="tags-list" v-if='item.tags.length > 0'>
          <span v-for="item2 in item.tags" :key="item2.id">{{item2.name}}</span>
        </div>
        <div v-if="item.card.length > 0 || item.activity.length > 0">
          <div class="company-quan">
            <span class="icon-quan" v-if="item.card.length>0"></span>
            <template v-for="item2 in item.card" >
              <span class="quan-info" :key="item2.id">{{item2.name}}</span>
            </template>
          </div>
          <div class="company-quan">
            <span class="icon-cu" v-if="item.activity.length>0"></span>
            <span class="quan-info" v-for="item2 in item.activity" :key="item2.id">{{item2.title}}</span>
          </div>
        </div>
      </li>
    </ul>
  </section>
</template>
<script>
import '../../../assets/css/zxkb.css'
import { getTuijianComp } from '../../../api/qzZxkb.js'
export default {
  name: 'goodCompany',
  props: ['city'],
  data () {
    return {
      dataList: [],
      endCall: false,
      cs: '',
      interVal: ''
    }
  },
  created () {
    this.interVal = setInterval(() => {
      if (!this.endCall) {
        this.getCurrentCity()
      } else {
        clearInterval(this.interVal)
      }
    }, 200)
    if (process.env.NODE_ENV === 'development') {
      getTuijianComp(320500).then(res => {
        if (res.data.error_code === 0) {
          this.dataList = res.data.data.list
        }
      })
    }
  },
  methods: {
    // 查看企业详情
    toDetail (id) {
      this.$bridge.callNative('Native_decorate_detail', id, function (res) {})
    },
    // 查看更多优秀企业
    toSeeMoreCompany () {
      this.$bridge.callNative('Native_decorate_more', '000', function (res) {})
    },
    // 获取城市信息
    getCurrentCity () {
      let that = this
      that.$bridge.callNative('base_userCity', '000', function (res) {
        if (res) {
          that.endCall = true
          let resJson = JSON.parse(res)
          getTuijianComp(resJson.city_id).then(res => {
            if (res.data.error_code === 0) {
              that.dataList = res.data.data.list
            }
          })
        }
      })
    }
  }
}
</script>
<style scoped>
.good-comp-box{
  margin:0.1rem 0px;
  overflow: hidden;
  font-size: 0.14rem;
  background: #f7f7f7;
  margin-left: -0.1rem;
  margin-right: -0.1rem;
  height: 0.3rem;
  line-height: 0.3rem;
  padding-left: 0.1rem;
}
.company-title .pull-left{
  max-width:50%;
  overflow: hidden;
  font-size: 0.135rem;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.good-comp-box .pull-right{
  color:#999;
  font-size: 0.12rem;
  margin-right: 0.1rem;
}
.star {
  display: inline-block;
  width: 15px;
  height: 15px;
  margin-right:-4px;
}
.red-star {
  background: url('../../../assets/img/qz/share/star.png') no-repeat center center;
  background-size: 15px;
}
.gray-star {
  background: url('../../../assets/img/qz/zxkb/gray-star.png') no-repeat center 2px;
  background-size: 13px;
}
.half-star {
  background: url('../../../assets/img/qz/share/half-star.png') no-repeat center center;
  background-size: 14px;
}
</style>
