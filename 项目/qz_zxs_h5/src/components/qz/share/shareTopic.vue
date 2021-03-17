<template>
  <section>
    <open-app></open-app>
    <div class="share-topic">
      <div class="topic-banner">
        <div><img :src="imgUrl" /></div>
        <div class="process">
          <p class="banner-p1">{{title}}</p>
          <p class="banner-p2">
            <span>{{views}}人正在围观</span> |
            <span>{{comment_count}}评论</span>
          </p>
        </div>
      </div>
      <top-comment :commentNum="commentNum">
        <template v-if="!noData">
          <div class="comment-list">
            <span class="touxiang">
              <img :src="commentList.from_head == ''?headImg:commentList.from_head">
            </span>
            <span>{{commentList.from_nickname}}</span>
            <p class="comment-content">{{commentList.content}}</p>
            <p class="comment-content" v-if="commentList.commentImgs.length>0">
            <ul><li v-for="(val,index) in commentList.commentImgs" :key="index"><img :src="val" /></li></ul>
            </p>
            <p class="comment-time">{{ commentList.p_time }}</p>
            <div class="comment-reply" v-if="commentList.reply_list">
              <div v-for="(val,index) in commentList.reply_list" :key="index">
                <p><span class="comment-name">{{val.to_nickname}}</span>回复{{commentList.from_nickname}}：<span>{{val.content}}</span></p>
                <p class="total-comment">共<span>{{commentList.reply_number}}</span>条回复</p>
              </div>
            </div>
          </div>
        </template>
        <template v-if="noData">
          <div class="no-data">
            <img src="@/assets/img/bkzn/nodata.png">
            <p @click="qzOpenApp">话题还没有人参与讨论，去APP抢沙发>></p>
          </div>
        </template>
      </top-comment>
      <topic>
        <div class="topic-content">
          <ul>
            <li v-for="item in topicList" :key="item.id" @click="qzOpenApp">
              <p>{{item.title}}</p>
              <p class="banner-p2 topic-p2">
                <span>围观{{item.watch_num}}</span> |
                <span>评论{{item.comment_num}}</span>
              </p>
            </li>
          </ul>
        </div>
      </topic>
      <div class="comment-list" v-if="commentNum>1">
            <span class="touxiang">
              <img :src="commentSecond.from_head == ''?headImg:commentSecond.from_head">
            </span>
        <span>{{commentSecond.from_nickname}}</span>
        <p class="comment-content">{{commentSecond.content}}</p>
        <p class="comment-content" v-if="commentSecond.commentImgs.length>0"><ul><li v-for="(val,index) in commentSecond.commentImgs" :key="index"><img :src="val" /></li></ul></p>
        <p class="comment-time">{{ commentSecond.p_time }}</p>
        <div class="comment-reply" v-if="commentSecond.reply_list">
          <div v-for="(val,index) in commentSecond.reply_list" :key="index">
            <p><span class="comment-name">{{val.to_nickname}}</span>回复{{commentSecond.from_nickname}}：<span>{{val.content}}</span></p>
            <p class="total-comment">共<span>{{commentSecond.reply_number}}</span>条回复</p>
          </div>
        </div>
        <div class="more-comment" @click="qzOpenApp">更多精彩评论去APP内查看>></div>
      </div>
    </div>
    <down-btn></down-btn>
    <wx-tips :isWechat="isWechat"></wx-tips>
  </section>
</template>
<script>
import openApp from '@/components/common/openApp'
import downBtn from '@/components/common/downBtn'
import topComment from '@/components/common/topComment'
import wxTips from '@/components/common/wxTips'
import topic from '@/components/common/topic'
import qzOpenApp from '@/mixins/qzOpenApp'
import { shareTopicDetail, shareTopicComment, shareTopic } from '@/api/api'
export default {
  name: 'shareTopic',
  components: {
    openApp,
    downBtn,
    topComment,
    topic,
    wxTips
  },
  mixins: [qzOpenApp],
  data () {
    return {
      swiperOption: {
        freeMode: true,
        freeModeMomentum: true,
        loop: true,
        initialSlide: 0,
        width: 160
      },
      id: '',
      imgUrl: '',
      headImg: require('@/assets/img/share/head.png'),
      title: '',
      views: '',
      topicList: [],
      commentList: [],
      commentSecond: [],
      comment_count: '',
      commentImgs: [],
      commentNum: '',
      noData: false,
      isWechat: false
    }
  },
  created () {
    this.id = this.$route.query.id
    this.getTopicDetail()
    this.getTopicComment()
    this.getTopic()
  },
  methods: {
    getTopicDetail: function () {
      shareTopicDetail({
        id: this.id
      }).then(res => {
        if (res.data.error_code === 0) {
          this.imgUrl = res.data.data.img_url
          this.title = res.data.data.title
          this.views = res.data.data.views
          this.comment_count = res.data.data.comment_count
        }
        document.title = this.title
      })
    },
    getTopicComment: function () {
      shareTopicComment({
        id: this.id,
        page_current: 1,
        module_type: 4
      }).then(res => {
        console.log(res)
        if (res.data.error_code === 0 && res.data.data.list.length !== 0) {
          this.commentList = res.data.data.list[0]
          this.commentList.p_time = this.transferTime(this.commentList.publisher_time)
          this.commentNum = res.data.data.page.total_number
          if (this.commentNum >= 2) {
            this.commentSecond = res.data.data.list[1]
            this.commentSecond.p_time = this.transferTime(this.commentSecond.publisher_time)
          } else {
            this.commentSecond = []
          }
          this.getImg(this.commentList)
          this.getImg(this.commentSecond)
        } else {
          this.noData = true
          this.commentNum = 0
        }
      })
    },
    getTopic: function () {
      shareTopic({
        topic_id: this.id
      }).then(res => {
        if (parseInt(res.data.error_code) === 0) {
          if (res.data.data.list.length > 3) {
            for (var i = 0; i < 3; i++) {
              this.topicList.push(res.data.data.list[i])
            }
          } else {
            this.topicList = res.data.data.list
          }
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
    },
    getImg: function (contain) {
      if (contain.img_url == null) {
        contain.commentImgs = []
      } else {
        if (contain.img_url.length > 3) {
          for (var i = 0; i < 3; i++) {
            contain.commentImgs.push(contain.img_url[i])
          }
        } else {
          contain.commentImgs = contain.img_url
        }
      }
    }
  }
}
</script>
<style scoped>
  img{
    max-width:100%;
    vertical-align: middle;
  }
  p{
    margin-bottom:0.1rem;
  }
  .topic-banner{
    position: relative;
  }
  .topic-banner p{
    position: absolute;
    z-index:999;
    margin:0 0.2rem;
    color:#fff;
  }
  .process{
    position: absolute;
    width:100%;
    /*height: 0.7rem;*/
    bottom: 0;
    /*background: linear-gradient(#fff, #666);*/
  }
  .topic-banner .banner-p1{
    bottom: 0.45rem;
    font-weight:700;
    font-size:16px;
  }
  .topic-banner .banner-p2{
    bottom: 0.2rem;
    font-size:12px;
  }
  .comment-list{
    padding:0 0.1rem;
    margin-top:0.1rem;
    background:#fff;
  }
  .touxiang{
    width:0.3rem;
    height:0.3rem;
    border-radius:50%;
    display:inline-block;
    overflow: hidden;
    float: left;
  }
  .touxiang img{
    width:100%;
    height: 100%;
  }
  .comment-list span:first-child+span{
    display: inline-block;
    height: 0.3rem;
    line-height: 0.3rem;
    margin-left: 0.1rem;
  }
  .comment-content,.comment-time{
    margin-left: 0.4rem;
  }
  .comment-content ul{
    display: flex;
    justify-content:flex-start;
  }
  .comment-content ul li{
    width:32%;
  }
  .comment-time{
    color:#999999;
    font-size:10px;
    margin-top:0.05rem;
    margin-bottom:0.1rem;
  }
  .comment-reply{
    padding:0.05rem;
    background:#F3F3F3;
    margin-left: 0.4rem;
  }
  .comment-name{
    color:#FF5353;
  }
  .total-comment{
    color:#FF5353;
    font-size:10px;
  }
  .topic-content ul li{
    display: inline-block;
    width:1.1rem;
    margin-right:0.1rem;
    padding:0.05rem;
    box-shadow: #e9e8e8 0px 0px 25px;
  }
  .topic-content ul li p:first-child{
    height: 0.34rem;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
    white-space: normal;
    word-break:break-all;
  }
  .topic-content ul{
    white-space: nowrap;
    overflow: hidden;
    overflow-x: auto;
    padding:0.1rem;
  }
  .topic-content ul::-webkit-scrollbar {
    display: none;
  }
  .topic-content p span{
    font-size:10px;
  }
  .more-comment{
    text-align: center;
    margin: 0.1rem 0;
  }
  .no-data {
    height: 100%;
    padding:0.3rem 0px;
    background: #F7F7F7;
    color:#999999;
    text-align: center;
    font-size: 0.13rem;
  }
  .no-data img{
    width: 61.3333%;
  }
  .no-data p{
    padding:0.1rem;
  }
</style>
