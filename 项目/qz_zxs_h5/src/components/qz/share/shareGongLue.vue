<template>
  <div class="share-gonglue">
    <open-app></open-app>
    <div class="line"></div>
    <div class="article">
      <img :src="strategyInfo.image_path" style="width: 100%">
      <h1 class="caption">{{ strategyInfo.title }}</h1>
      <div class="tag-box">
        <div class="tag" v-for="item in strategyInfo.tags" :key="item.tag_id">{{ item.tag_name }}</div>
      </div>
      <div class="article_text" v-html="strategyInfo.content"></div>
    </div>
    <div class="line"></div>
    <recommend>
      <div class="content">
        <div class="item clearfix" v-for="item in strategyRmd" :key="item.id" @click="qzOpenApp">
          <div class="left">
            <p>{{ item.title }}</p>
            <div class="tags">
              <span v-for="tag in item.strategytags" :key="tag.tag_id">#{{ tag.tag_name }}#&nbsp;</span>
            </div>
          </div>
          <div class="right">
            <img :src="item.images" style="width: 100%">
          </div>
        </div>
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
import { fetchStrategy, fetchStagRecmd } from '@/api/share'
export default {
  name: 'shareGongLue',
  components: {
    openApp,
    recommend,
    wxTips
  },
  mixins: [qzOpenApp],
  data () {
    return {
      strategyInfo: {},
      strategyRmd: [],
      id: '',
      source: '',
      isWechat: false
    }
  },
  created () {
    this.id = this.$route.query.id
    this.source = this.$route.query.form
    this.getStrategyInfo()
    this.getStrategyRecommend()
  },
  methods: {
    getStrategyInfo () {
      fetchStrategy({
        id: this.id
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
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
    getStrategyRecommend () {
      fetchStagRecmd({
        strategy_id: this.id
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.strategyRmd = res.data.data.list
        }
      })
    }
  }
}
</script>

<style scoped>
.share-gonglue .article img{
  max-width: 100%;
}
.share-gonglue .caption{
  font-size: 0.1365rem;
  font-weight: bold;
  color: #333;
  padding: 20px 20px 0;
}
.share-gonglue .line{
  background-color: #F5F5F5;
  height: 15px;
}
.share-gonglue .tag-box{
  padding: 10px 20px;
}
.share-gonglue .content{
  padding: 20px;
}
.share-gonglue .tag{
  display: inline-block;
  color: #FF5353;
  border:1px solid rgba(255,83,83,1);
  border-radius:5px;
  margin-right: 5px;
  padding: 0.1em 0.25em;
}
.share-gonglue .article_text{
  margin: 20px;
  overflow: hidden;
}
.share-gonglue .article_text img{
  width: 100%;
}
.share-gonglue .content .item{
  border-bottom: 1px solid #F5F5F5;
  padding-bottom: 0.1rem;
  margin-bottom: 0.1rem;
}
.share-gonglue .content .item .left{
  width: 1.8rem;
  float: left;
  height: 0.98rem;
  position: relative;
}
.share-gonglue .content .item .left .tags{
  position: absolute;
  bottom: 0;
}
.share-gonglue .content .item .right{
  width: 0.98rem;
  height: 0.98rem;
  overflow: hidden;
  float: right;
  border-radius: 0.5em;
}
.share-gonglue .content .item .right img{
  width: 100%;
  height: 100%;
}
</style>
