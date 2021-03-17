<template>
  <div class="share-pic">
    <open-app></open-app>
    <div class="line"></div>
    <div class="as-swiper" @click="qzOpenApp">
      <div class="rel">
        <img :src="picInfo.img" style="width: 100%;">
        <span class="picnums" v-if="picInfo.imgs && picInfo.imgs.length > 1">1/{{ picInfo.imgs.length }}</span>
      </div>
      <p class="desc">{{ picInfo.title }}</p>
    </div>
    <div class="line"></div>
    <recommend>
      <div class="content">
        <div class="item" v-for="item in picRecommend" :key="item.id" @click="qzOpenApp">
          <div class="item-content">
            <div class="rel">
              <img :src="item.imgs[0].img_url" alt="">
              <span class="picnums" v-if="item.imgs && item.imgs.length > 1">{{ item.imgs.length }} 图</span>
            </div>
            <p class="title">{{ item.title }}</p>
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
import { fetchPicInfo, fetchPicRecommend } from '@/api/share'
export default {
  name: 'sharePic',
  components: {
    openApp,
    recommend,
    wxTips
  },
  mixins: [qzOpenApp],
  data () {
    return {
      picInfo: {},
      picRecommend: [],
      id: '',
      source: '',
      isWechat: false
    }
  },
  created () {
    this.id = this.$route.query.id
    this.source = this.$route.query.form
    this.getPicInfo()
    this.getPicRecommend()
  },
  methods: {
    getPicInfo () {
      fetchPicInfo({
        picture_id: this.id
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.picInfo = res.data.data
          if (this.picInfo.imgs.length > 0) {
            this.picInfo.img = this.picInfo.imgs[0].img_url
          } else {
            this.picInfo.img = ''
          }
          document.title = this.picInfo.title
        } else {
          alert(res.data.error_msg)
        }
      })
    },
    getPicRecommend () {
      fetchPicRecommend({
        pic_id: this.id
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.picRecommend = res.data.data.list
        }
      })
    }
  }
}
</script>

<style scoped>
.share-pic .desc{
  font-size: 0.1365rem;
  color: #333;
  font-weight: bold;
  line-height: 2.5;
  padding-left: 20px;
}
.share-pic .line{
  background-color: #F5F5F5;
  height: 15px;
}
.share-pic .as-swiper .rel{
  position: relative;
}
.share-pic .as-swiper .picnums{
  position: absolute;
  bottom: 0.1rem;
  background-color: transparent;
  color: white;
  border: 1px solid white;
}
.share-pic .content{
  padding: 20px;
  position: relative;
  columns: 2;
  -webkit-columns:100px 2; /* Safari and Chrome */
  -moz-columns:100px 2; /* Firefox */
}
.share-pic .content .item{
  width: 1.426rem;
  float: left;
  border:1px solid #F5F5F5;
  border-radius: 5px;
  overflow: hidden;
  margin-bottom: 15px;
  box-sizing: border-box;
  break-inside: avoid;
}
.share-pic .content .item .item-content{
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: left;
  height: auto;
  color: #686868;
  box-sizing: border-box;
}
.share-pic .content img{
  max-width: 100%;
  display: block;
}
.share-pic .content .title{
  font-size: 0.11rem;
  font-weight: bold;
  color: #333;
  max-height: 0.35rem;
  line-height: 0.17rem;
  margin: 5px;
  overflow: hidden;
  text-overflow: ellipsis;
}
.share-pic .rel{
  position: relative;
}
.share-pic .picnums{
  background-color: rgba(0, 0, 0, 0.5);
  border-radius: 10px;
  overflow: hidden;
  color: white;
  position: absolute;
  right: 5px;
  bottom: 5px;
  padding: 0 10px;
}
</style>
