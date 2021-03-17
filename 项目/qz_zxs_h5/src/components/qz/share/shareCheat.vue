<template>
  <div class="share-cheat">
    <open-app></open-app>
    <div class="line"></div>
    <div class="article">
      <img src="@/assets/img/bkzn/bkzn.png" style="width: 100%">
      <span class="cheat-text">{{ cheateInfo.title }}</span>
      <div class="cheats" v-for="(item, index) in cheateInfo.list" :key="index">
        <div class="title">
          <span>{{ item.title }}</span>
          <i class="fa fa-angle-up" @click="toggle(index)"></i>
        </div>
        <transition name="fade">
          <div class="text" v-if="cheatList['block' + index]" v-html="item.content"></div>
        </transition>
      </div>
    </div>
    <div class="line"></div>
    <recommend>
      <div class="content">
        <a href="javascript:;" class="item" v-for="item in cheateRmd" :key="item.id" @click="qzOpenApp">{{ item.title }}<i class="fa fa-angle-right"></i></a>
      </div>
    </recommend>
    <wx-tips :isWechat="isWechat"></wx-tips>
  </div>
</template>

<script>
import openApp from '@/components/common/openApp'
import recommend from '@/components/common/recommend'
import wxTips from '@/components/common/wxTips'
import qzOpenApp from '@/mixins/qzOpenApp'
import { fetchCheate, fetchCheateRecmd } from '@/api/share'
export default {
  name: 'shareCheat',
  components: {
    openApp,
    recommend,
    wxTips
  },
  mixins: [qzOpenApp],
  data () {
    return {
      cheateInfo: {},
      cheateRmd: [],
      id: '',
      source: '',
      cheatList: {},
      isWechat: false
    }
  },
  created () {
    this.id = this.$route.query.id
    this.source = this.$route.query.form
    this.getCheateInfo()
    this.getCheateRecommend()
  },
  methods: {
    toggle (val) {
      if (!this.cheatList['block' + val]) {
        this.cheatList['block' + val] = true
      } else {
        this.cheatList['block' + val] = false
      }
    },
    getCheateInfo () {
      fetchCheate({
        id: this.id
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.cheateInfo = res.data.data
          this.cheateInfo.list.forEach((item, index) => {
            this.$set(this.cheatList, 'block' + index, true)
          })
          document.title = this.cheateInfo.title
        }
      })
    },
    getCheateRecommend () {
      fetchCheateRecmd().then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.cheateRmd = res.data.data
        }
      })
    }
  }
}
</script>

<style scoped>
  .share-cheat .cheats .title{
    background-color: #CCC;
    line-height: 3;
    padding-left: 10px;
    font-size: 0.119rem;
    color: #333;
    font-weight: bold;
    position: relative;
  }
  .share-cheat .cheats .title .fa{
    position: absolute;
    right: 10px;
    top: 40%;
  }
  .share-cheat .cheats .text{
    padding: 10px;
    word-break: break-all;
  }
  .share-cheat .article{
    position: relative;
  }
  .share-cheat .cheat-text{
    position: absolute;
    top: 0.2rem;
    left: 0.9rem;
    color: white;
    width: 1.8rem;
    max-height: 0.35rem;
    overflow: hidden;
  }
  .share-cheat .cheats .text p{
    line-height: 2;
    padding-left: 10px;
    color: #999;
    font-size: 0.11rem;
  }
  .share-cheat .line{
    background-color: #F5F5F5;
    height: 15px;
  }
  .share-cheat .content{
    padding: 20px;
  }
  .share-cheat .content .item{
    display: block;
    line-height: 3;
    font-size: 0.128rem;
    color: #333;
    border-bottom: 1px solid #F5F5F5;
    position: relative;
  }
  .share-cheat .content .item .fa{
    position: absolute;
    right: 0;
    top: 40%;
  }
  .fade-enter-active, .fade-leave-active {
    transition: opacity .3s;
  }
  .fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
    opacity: 0;
  }
</style>
