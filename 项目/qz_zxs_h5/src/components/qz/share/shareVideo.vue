<template>
  <div class="share-video">
    <open-app></open-app>
    <div class="line"></div>
    <div class="article">
      <video-player  class="vjs-custom-skin"
                     ref="videoPlayer"
                     :options="playerOptions"
                     :playsinline="true"
                     @play="onPlayerPlay($event)"
                     >
      </video-player>
      <div class="avatar-box">
        <img :src="videoInfo.teacher_img" class="avatar">
        <span class="username">{{ videoInfo.teacher_name }}</span>
      </div>
      <h1 class="caption">{{ videoInfo.title }}</h1>
      <div class="article_text">{{ videoInfo.vedio_desc }}</div>
    </div>
    <div class="line"></div>
    <recommend>
      <div class="content">
        <div class="item clearfix" v-for="item in videoRecmd" :key="item.id" @click="qzOpenApp">
          <div class="left">
            <p>{{ item.title }}</p>
            <div class="recommend-avatar-box">
              <div class="avatar"><img :src="item.teacher_img"></div>
              <span class="username">{{ item.teacher_name }}</span>
            </div>
          </div>
          <div class="right">
            <img :src="item.img_url" style="width: 100%">
            <img src="@/assets/img/share/icon-play.png" class="icon-play">
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
import { fetchVideoInfo, fetchVideoRecommend } from '@/api/share'
export default {
  name: 'shareVideo',
  components: {
    openApp,
    recommend,
    wxTips
  },
  mixins: [qzOpenApp],
  data () {
    return {
      videoInfo: {},
      videoRecmd: [],
      id: '',
      source: '',
      playerOptions: {
        height: '200',
        autoplay: false,
        controls: true,
        muted: false, // 控制声音
        language: 'en',
        playbackRates: [0.7, 1.0, 1.5, 2.0],
        sources: [{
          type: 'video/mp4',
          src: 'https://video.qizuang.com/zxs/2019/04/29/FlzzjSyjSED2qakPfUQLrsNXxgII.mp4'
        }],
        poster: 'https://zxsqn.qizuang.com/zxs/20190426/Fg_ZNLZ_oUGnOnHQjFIUOtD_eLow'
      },
      isWechat: false
    }
  },
  created () {
    this.id = this.$route.query.id
    this.source = this.$route.query.form
    this.getVideoInfo()
    this.getVideoRecommend()
  },
  methods: {
    getVideoInfo () {
      fetchVideoInfo({
        vedio_id: this.id
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.videoInfo = res.data.data
          document.title = this.videoInfo.title
          this.videoInfo.v_url = this.$qzConfig.oss_video_domain + this.videoInfo.vedio_url
          this.playerOptions.sources = [{
            type: 'video/mp4',
            src: this.videoInfo.vedio_url
          }]
          this.playerOptions.sources.poster = this.videoInfo.img_url
        }
      })
    },
    getVideoRecommend () {
      fetchVideoRecommend().then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.videoRecmd = res.data.data
        }
      })
    },
    onPlayerPlay (player) {
      console.log(player)
    }
  }
}
</script>

<style scoped>
  .share-video .vjs-custom-skin{
    width: 100%;
  }
  .share-video .caption{
    font-size: 0.1365rem;
    font-weight: bold;
    color: #333;
    padding: 0px 20px;
  }
  .share-video .video{
    width: 100%;
  }
  .share-video .line{
    background-color: #F5F5F5;
    height: 15px;
  }
  .share-video .avatar-box{
    padding: 10px 20px;
  }
  .share-video .username{
    vertical-align: 0.1rem;
  }
  .share-video .avatar{
    width: 0.3rem;
    height: 0.3rem;
    border-radius: 50%;
  }
  .share-video .content{
    padding: 20px;
  }
  .share-video .tag{
    display: inline-block;
    color: #FF5353;
    border:1px solid rgba(255,83,83,1);
    border-radius:5px;
    padding: 0.1em 0.25em;
  }
  .share-video .article_text{
    padding: 20px;
  }
  .share-video .content .item{
    border-bottom: 1px solid #F5F5F5;
    padding-bottom: 0.1rem;
    margin-bottom: 0.1rem;
  }
  .share-video .content .item .left{
    width: 1.8rem;
    height: 0.98rem;
    float: left;
    position: relative;
  }
  .share-video .content .item .left .recommend-avatar-box{
    position: absolute;
    bottom: 0;
  }
  .share-video .content .item .right{
    width: 0.98rem;
    height: 0.98rem;
    overflow: hidden;
    float: right;
    border-radius: 0.5em;
    position: relative;
  }
  .share-video .content .item .right .icon-play{
    position: absolute;
    width: 0.256rem;
    height: 0.256rem;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    margin: auto;
  }
  .share-video .content .item .right img{
    width: 100%;
    height: 100%;
  }
  .share-video .recommend-avatar-box .avatar{
    width: 0.2rem;
    height: 0.2rem;
    overflow: hidden;
    border-radius: 50%;
    display: inline-block;
  }
  .share-video .recommend-avatar-box .avatar img{
    width: 100%;
    height: 100%;
  }
  .share-video .recommend-avatar-box .username{
    vertical-align: 0.05rem;
  }
</style>
