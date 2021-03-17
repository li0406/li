<template>
  <div class="share-contrast">
    <open-app></open-app>
    <div class="line"></div>
    <div class="contrast" @click="qzOpenApp">
      <div class="left">
        <div class="thumb"><img :src="contrastData.first_img_url" alt=""></div>
        <p class="title">{{contrastData.first_product}}</p>
        <p class="support">{{contrastData.first_up}}人支持</p>
        <p class="support-icon"><img src="@/assets/img/share/support-blue.png" alt="">支持它</p>
      </div>
      <div class="right">
        <div class="thumb"><img :src="contrastData.second_img_url" alt=""></div>
        <p class="title">{{contrastData.second_product}}</p>
        <p class="support cf53">{{contrastData.second_up}}人支持</p>
        <p class="support-icon cf53 b-cf53"><img src="@/assets/img/share/support-red.png" alt="">支持它</p>
      </div>
    </div>
    <div class="line"></div>
    <hot-comment :has-comment="true" :num="comments.length" v-if="comments.length > 0">
      <div class="comment-item" v-for="(item, index) in comments" :key="item.id" v-if="index < 2" @click="qzOpenApp">
        <div class="content">
          <p class="avatar"><img :src="item.from_head" alt=""><span class="name">{{ item.from_nickname }}</span></p>
          <div class="text">{{ item.content }}</div>
          <div class="commentimg-box" v-if="item.img_url.length > 0">
            <div class="comment-img" v-for="(img, index) in item.img_url" :key="index">
              <img :src="img" alt="">
            </div>
          </div>
          <p class="time">{{ item.p_time }}</p>
        </div>
      </div>
      <p class="more-comment" @click="qzOpenApp">更多精彩评论去APP内查看>></p>
    </hot-comment>
    <div v-else class="without-comment">
      <img src="@/assets/img/share/nonecomment.png" alt="">
      <p>当前暂无评论，去APP内抢占沙发>></p>
    </div>
    <wx-tips :isWechat="isWechat"></wx-tips>
  </div>
</template>

<script>
import openApp from '@/components/common/openApp'
import hotComment from '@/components/common/hotComment'
import wxTips from '@/components/common/wxTips'
import qzOpenApp from '@/mixins/qzOpenApp'
import { fetchShareContrast, fetchCommentList } from '@/api/share'

export default {
  name: 'shareContrast',
  components: {
    openApp,
    hotComment,
    wxTips
  },
  mixins: [qzOpenApp],
  data () {
    return {
      contrastData: {},
      comments: [],
      id: '',
      source: '',
      isWechat: false
    }
  },
  created () {
    this.id = this.$route.query.id
    this.source = this.$route.query.form
    this.getContrastContent()
    this.getCommentList()
  },
  methods: {
    getContrastContent () {
      fetchShareContrast({
        id: this.id
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.contrastData = res.data.data
        }
      })
    },
    getCommentList () {
      fetchCommentList({
        id: this.id,
        page_current: 1,
        module_type: 5
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.comments = res.data.data.list
          this.comments.forEach(item => {
            item.p_time = this.transferTime(item.publisher_time)
          })
        }
      })
    },
    transferTime (timeStamp) {
      const date = new Date(timeStamp * 1000)
      const month = date.getMonth() + 1
      const day = date.getDate()
      const hours = date.getHours()
      const minutes = date.getMinutes()
      return month + '-' + day + ' ' + hours + ':' + minutes
    }
  }
}
</script>

<style scoped>
  .share-contrast .line{
    background-color: #F5F5F5;
    height: 15px;
  }
  .share-contrast .contrast{
    font-size: 0;
    text-align: center;
  }
  .share-contrast .contrast img{
    max-width: 100%;
  }
  .share-contrast .contrast > div{
    display: inline-block;
    width: 50%;
    font-size: 0.119rem;
    box-sizing: border-box;
    padding: 0.1rem 0;
  }
  .share-contrast .contrast .thumb{
    width: 0.938rem;
    height: 0.938rem;
    box-shadow: #ccc 0 0 10px;
    border-radius: 0.5em;
    overflow: hidden;
    margin: 0 auto;
    padding: 0.05rem;
    box-sizing: border-box;
  }
  .share-contrast .contrast .title{
    width: 0.938rem;
    color: #333;
    line-height: 3;
    white-space: nowrap;
    overflow: hidden;
    margin: 0 auto;
    text-overflow: ellipsis;
  }
  .share-contrast .contrast .support{
    color: #54CFD1;
  }
  .share-contrast .contrast .support-icon{
    width: 0.85rem;
    height: 0.256rem;
    line-height: 0.256rem;
    text-align: center;
    margin: 0.05rem auto 0;
    color: #54CFD1;
    border:1px solid #54CFD1;
    border-radius: 0.4em;
  }
  .share-contrast .cf53{
    color: #FF5353 !important;
  }
  .share-contrast .b-cf53{
    border-color: #FF5353 !important;
  }
  .share-contrast .comment-item{

  }
  .share-contrast .comment-item img{
    width: 100%;
  }
  .share-contrast .comment-item{
    padding: 0.05rem 0;
  }
  .share-contrast .comment-item .avatar img{
    width: 0.196rem;
    height: 0.196rem;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 0.05rem;
  }
  .share-contrast .comment-item .avatar span{
    vertical-align: 0.05rem;
  }
  .share-contrast .comment-item .content{
    padding-left: 0.15rem;
    padding-right: 0.1rem;
  }
  .share-contrast .comment-item .content .name{
    font-size: 0.119rem;
    font-weight: bold;
    line-height: 2.5;
  }
  .share-contrast .comment-item .content .text{
    padding-left: 0.246rem;
  }
  .share-contrast .comment-item .content .name{
    line-height: 2.5;
  }
  .share-contrast .comment-item .content .time{
    color: #999;
    line-height: 3;
    padding-left: 0.246rem;
  }
  .share-contrast .more-comment{
    color: #959595;
    font-size: 0.11rem;
    text-align: center;
    line-height: 3;
  }
  .share-contrast .commentimg-box{
    font-size: 0;
    padding-left: 0.246rem;
    margin-top: 0.1rem;
  }
  .share-contrast .commentimg-box .comment-img{
    display: inline-block;
    width: 0.8rem;
    height: 0.8rem;
    padding-right: 0.1rem;
    overflow: hidden;
  }
  .share-contrast .commentimg-box .comment-img img{
    width: 100%;
  }
  .share-contrast .without-comment{
    text-align: center;
    color: #666;
    padding-top: 0.3rem;
  }
  .share-contrast .without-comment img{
    width: 1.7rem;
    display: block;
    margin: 0 auto 0.1rem;
  }
</style>
