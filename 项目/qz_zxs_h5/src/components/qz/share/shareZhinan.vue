<template>
    <div class="share-zhinan">
        <div class="article">
            <h1 class="caption">{{ strategyInfo.title }}</h1>
            <div class="article_text" v-html="strategyInfo.content"></div>
        </div>
        <app-guide></app-guide>
        <div class="guess">
            <div class="title">猜你喜欢</div>
            <div class="con">
                <div class="item" v-for="item in likeList" @click="goLike(item.id)">
                    <div class="left">
                        <div class="top">
                            <div class="tit">{{item.title}}</div>
                            <div class="ti"><span  v-for="it in item.tags">{{'#'+it+'#'}}</span></div>
                        </div>
                        <div class="num">{{item.views}}浏览 &nbsp; &nbsp; {{item.collects}}收藏</div>
                    </div>
                    <img class="right" :src="item.image_path" alt="">
                </div>
            </div>
        </div>
        <wx-tips :isWechat="isWechat"></wx-tips>
    </div>
</template>

<script>

  import wxTips from '@/components/common/wxTips'
  import {fetchStrategy, youLike} from '@/api/share'
  import { million } from '@/utils/validate'
  import appGuide from './appGuide.vue'


  export default {
      name: 'shareGongLue',
      components: {
          wxTips,
          appGuide
      },
      data() {
          return {
              strategyInfo: {},
              likeList: [],
              id: '',
              source: '',
              isWechat: false
          }
      },
      created() {
          this.id = this.$route.query.id
          this.source = this.$route.query.form
          this.getStrategyInfo()
          this.youLike()
      },
      methods: {
          getStrategyInfo() {
              fetchStrategy({
                  id: this.id
              }).then(res => {
                  if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                    console.log(res)
                      this.strategyInfo = res.data.data
                      document.title = this.strategyInfo.title
                      this.$nextTick(() => {
                          const imgs = document.getElementsByClassName('article_text')[0].getElementsByTagName('img')
                          Array.prototype.forEach.call(imgs, (item) => {
                              item.setAttribute('width', '100%')
                          })
                      })
                  }
              })
          },
          youLike() {
              youLike({
                  id: this.id
              }).then(res => {
                  if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                      const like =res.data.data
                      like.forEach((item)=>{
                          item.views = million(item.views)
                          item.tags = item.tags.slice(0,4)
                      })
                      this.likeList = like
                  }
              })
          },
          goLike(id){
              this.$bridge.callNative('Native_strategy_detail', id, function (res) {})
          }
      }
  }
</script>

<style scoped>
  .share-zhinan .caption {
      font-size: 0.18rem;
      font-weight: bold;
      color: #333;
      line-height: 0.24rem;
      padding: 0.20rem 0.12rem;
  }

  .article_text {
      font-size: 0.137rem;
      line-height: 0.20rem;
      font-weight: 500;
      color: rgba(51, 51, 51, 1);
      padding: 0 0.12rem;
      padding-bottom: 0.20rem;
  }

  .guess .title {
      font-size: 0.18rem;
      font-weight: bold;
      color: #333;
      line-height: 0.24rem;
      padding: 0.10rem 0.12rem;
  }

  .guess .con {
      padding: 0 0.12rem;
  }

  .guess .item {
      display: flex;
      align-items: center;
      height: 1.20rem;
      border-bottom: 1px solid rgba(229, 229, 229, 0.6);
  }

  .guess .item:last-child {
      border-bottom: 0;
  }

  .item .left {
      display: flex;
      justify-content: space-between;
      flex-direction: column;
      width: 70%;
      height: 100%;
      padding: 0.20rem 0;
      padding-right: 0.12rem;
      box-sizing: border-box;
  }

  .item .right {
      width: 0.93rem;
      height: 0.93rem;
      border-radius: 2px;
  }

  .left .tit {
      font-size: 0.13rem;
      font-weight: bold;
      color: rgba(51, 51, 51, 1);
      line-height: 0.19rem;
      word-break: break-all;
      text-overflow: ellipsis;
      overflow: hidden;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
  }

  .left .ti {
      font-size: 0.10rem;
      font-weight: 500;
      color: rgba(153, 153, 153, 1);
      line-height: 0.16rem;
      display: flex;
      align-items: center;
  }
  .left .ti span{
      margin-right: 0.10rem;
  }

  .left .num {
      font-size: 0.10rem;
      font-weight: 500;
      color: rgba(153, 153, 153, 1);
      line-height: 0.16rem;
  }

  .guess {
      margin-bottom: 0.42rem;
  }
</style>
