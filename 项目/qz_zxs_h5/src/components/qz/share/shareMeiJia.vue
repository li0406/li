<template>
  <section class="share-meijia">
      <open-app></open-app>
      <div class="banner-box swiper-container">
        <swiper :options="swiperOption">
          <swiper-slide v-for="(val,index) in imgLIst" :key="index">
            <img :src="val">
          </swiper-slide>
          <div class="swiper-pagination" slot="pagination"></div>
        </swiper>
      </div>
      <div class="bg">
        <div class="experience-content">
          <div class="experience-tit">
            <img :src="logo"/>
            <span>{{nick_name}}</span>
          </div>
          <p class="meijia-tit">{{detailTitle}}</p>
          <div>
            <ul class="link-label">
              <li v-for="(val,index) in tagList" :key="index">{{val}}</li>
            </ul>
          </div>
          <div class="body-content" v-html="content"></div>
        </div>
      </div>
      <div class="space"></div>
      <top-comment :commentNum="commentNum">
        <template v-if="!noData">
          <div class="comment-list" v-for="(item,index) in comments" :key="index">
            <span class="touxiang">
              <img :src="item.from_head == ''?headImg:item.from_head">
            </span>
            <span>{{item.from_nickname}}</span>
            <p class="comment-content">{{item.content}}</p>
            <p class="comment-content" v-if="item.commentImgs.length>0">
              <ul><li v-for="(val,index) in item.commentImgs" :key="index"><img :src="val" /></li></ul>
            </p>
            <p class="comment-time">{{ item.p_time }}</p>
            <div class="comment-reply" v-if="item.reply_list">
              <div v-for="(val,index) in item.reply_list" :key="index">
                <p><span class="comment-name">{{val.to_nickname}}</span>回复{{item.from_nickname}}：<span>{{val.content}}</span></p>
                <p class="total-comment">共<span>{{item.reply_list.length}}</span>条回复</p>
              </div>
            </div>
          </div>
          <div class="more-comment" @click="qzOpenApp">更多精彩评论去APP内查看>></div>
        </template>
        <template v-if="noData">
          <div class="no-data">
            <img src="@/assets/img/bkzn/nodata.png">
            <p @click="qzOpenApp">当前暂无评论，去APP内抢占沙发>> </p>
          </div>
        </template>
      </top-comment>
      <div class="space"></div>
      <hotAnli>
        <ul class="meijia-anli">
          <li v-for="item in hotList" :key="item.id" @click="qzOpenApp">
            <div class="anli-img"><img :src="item.hotImg"/></div>
            <p class="anli-content">{{item.hotTitle}}</p>
          </li>
        </ul>
      </hotAnli>
      <down-btn></down-btn>
    <wx-tips :isWechat="isWechat"></wx-tips>
  </section>
</template>
<script>
import 'swiper/dist/css/swiper.css'
import { swiper, swiperSlide } from 'vue-awesome-swiper'
import openApp from '@/components/common/openApp'
import downBtn from '@/components/common/downBtn'
import hotAnli from '@/components/common/hotAnli'
import topComment from '@/components/common/topComment'
import wxTips from '@/components/common/wxTips'
import qzOpenApp from '@/mixins/qzOpenApp'
import { shareHotAnLi, shareAnLiDetail, shareAnLiComment } from '@/api/api'
export default {
  name: 'shareMeiJia',
  components: {
    swiper,
    swiperSlide,
    openApp,
    downBtn,
    hotAnli,
    topComment,
    wxTips
  },
  mixins: [qzOpenApp],
  data () {
    return {
      swiperOption: {
        notNextTick: true,
        pagination: {
          el: '.swiper-pagination',
          type: 'fraction'
        },
        slidesPerView: 'auto',
        centeredSlides: true,
        paginationClickable: true,
        observeParents: true
      },
      hotList: [],
      imgLIst: [],
      tagList: [],
      title: '',
      detailTitle: '',
      nick_name: '',
      logo: '',
      content: '',
      comments: [],
      commentNum: '',
      commentImgs: [],
      headImg: require('@/assets/img/share/head.png'),
      id: '',
      source: '',
      noData: false,
      isWechat: false
    }
  },
  mounted () {
    var that = this
    that.id = that.$route.query.id
    that.source = that.$route.query.form
    if (that.id) {
      shareHotAnLi(that.id).then((res) => {
        if (parseInt(res.data.error_code) === 0) {
          res.data.data.map(val => {
            that.hotList.push({
              hotId: val.id,
              hotTitle: val.title,
              hotImg: val.img_paths[0]
            })
            return that.hotList
          })
        }
      })
      shareAnLiDetail(that.id).then((res) => {
        if (parseInt(res.data.error_code) === 0) {
          that.detailTitle = res.data.data.title
          that.nick_name = res.data.data.nick_name
          that.logo = res.data.data.logo
          that.content = res.data.data.content
          res.data.data.tags.map(val => {
            that.tagList.push(val)
            return that.tagList
          })
          res.data.data.imgs.map(val => {
            that.imgLIst.push(val)
            return that.imgLIst
          })
        }
        document.title = that.detailTitle
      })
      that.getCommentList()
    }
  },
  methods: {
    getCommentList () {
      shareAnLiComment({
        id: this.id,
        page_current: 1,
        module_type: 1
      }).then(res => {
        if (res.data.data.list.length !== 0 && parseInt(res.data.error_code) === 0) {
          if (res.data.data.list.length >= 2) {
            for (var j = 0; j < 2; j++) {
              this.comments.push(res.data.data.list[j])
            }
          } else {
            this.comments = res.data.data.list
          }
          this.commentNum = res.data.data.page.total_number
          this.comments.map(res => {
            if (res.img_url == null) {
              res.commentImgs = []
            } else {
              if (res.img_url.length > 3) {
                for (var i = 0; i < 3; i++) {
                  res.commentImgs.push(res.img_url[i])
                }
              } else {
                res.commentImgs = res.img_url
              }
            }
            res.p_time = this.transferTime(res.publisher_time)
          })
        } else {
          this.noData = true
          this.commentNum = 0
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
  img{
    max-width:100%;
    vertical-align: middle;
  }
  p{
    margin-bottom:0.1rem;
  }
  .swiper-pagination{
    color:#fff;
  }
  .banner-box{
    position:relative;
    width:100%;
  }
  .swiper-slide{
     height: 1.75rem;
  }
  .swiper-slide img{
    height: 100%;
    width:100%;
  }
  .bg{
    background:#fff;
  }
  .experience-content{
    padding:0 0.1rem;
  }
  .experience-tit{
    height: 0.3rem;
    line-height:0.3rem;
    padding-top:0.1rem;
    margin-bottom:0.1rem;
  }
  .experience-tit img{
    display: inline-block;
    vertical-align: middle;
    width:0.3rem;
    height: 0.3rem;
    border-radius:50%;
    float: left;
    margin-right:0.1rem;
  }
  .experience-tit span{
    display: inline-block;
  }
  .meijia-tit{
    word-break:break-all;
  }
  .link-label li{
    display: inline-block;
    padding: 0.03rem 0.08rem;
    background:#F2F2F2;
    border-radius:0.5rem;
    text-align:center;
    font-weight:500;
    color:#333;
    margin-bottom:0.12rem;
    margin-right: 0.1rem;
    font-size:14px;
  }
  .space{
    width:100%;
    height: 0.0777rem;
    background:#F2F2F2;
  }
  .body-content{
    word-break:break-all;
    padding-bottom: 0.1rem;
    line-height:1.5;
    white-space: pre-line;
  }
  .comment-list{
    padding:0 0.1rem;
    margin-top:0.1rem;
  }
  .touxiang{
    width:0.3rem;
    height:0.3rem;
    border-radius:50%;
    display:inline-block;
    overflow: hidden;
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
    margin-right:0.03rem;
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
  .meijia-anli li{
    width:100%;
    height: 0.69rem;
    border-bottom:1px solid #F7F7F7;
  }
  .meijia-anli li .anli-img{
    float: left;
    padding: 0.09rem 0.13rem;
    height: 0.5rem;
    width:0.69rem;
  }
  .anli-img img{
    width:100%;
    height: 100%;
    border-radius: 0.05rem;
  }
  .more-comment{
    text-align: center;
    margin-top: 0.15rem;
    padding-bottom:0.15rem;
    color: #959595;
  }
  .anli-content{
    height:70px;
    padding-top:10px;
    word-break:break-all;
    padding-right:0.1rem;
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
