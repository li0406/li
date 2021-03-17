<template>
  <section>
      <open-app></open-app>
      <div class="banner-box swiper-container">
        <swiper :options="swiperOption">
          <swiper-slide v-for="(val,index) in imgLIst" :key="index">
            <img :src="val"  @click="qzOpenApp">
          </swiper-slide>
          <div class="swiper-pagination" slot="pagination"></div>
        </swiper>
      </div>
      <div class="experience-content">
        <div class="experience-tit">
          <img :src="logo"/>
          <span>{{nick_name}}</span>
        </div>
        <p class="experience-col"><span class="reading">{{views}}</span>阅读 | <span class="comment">{{reply_num}}</span>评论</p>
        <p>{{title}}</p>
        <div>
          <ul class="link-label">
            <li v-for="(val,index) in tagList" :key="index">{{val}}</li>
          </ul>
        </div>
        <div class="body-content" v-html="content"></div>
      </div>
    <div class="space"></div>
      <recommend>
        <div class="recommend-content">
          <ul>
            <li class="recommend-tips" v-for="item in commentList" :key="item.id" @click="qzOpenApp">
              <div class="recommend-left">
                <p>{{item.commentTitle}}</p>
                <ul class="link-label">
                  <li v-for="(val,index) in item.commentTags" :key="index">{{val}}</li>
                </ul>
              </div>
              <div class="recommend-right">
                <img :src="item.imgPath"/>
              </div>
            </li>
          </ul>
        </div>
      </recommend>
    <wx-tips :isWechat="isWechat"></wx-tips>
  </section>
</template>
<script>
import 'swiper/dist/css/swiper.css'
import { swiper, swiperSlide } from 'vue-awesome-swiper'
import openApp from '@/components/common/openApp'
import recommend from '@/components/common/recommend'
import wxTips from '@/components/common/wxTips'
import qzOpenApp from '@/mixins/qzOpenApp'
import { shareExperence, shareExperenceTui } from '@/api/api'
export default {
  name: 'shareExperience',
  components: {
    swiper,
    swiperSlide,
    openApp,
    recommend,
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
      imgLIst: [],
      tagList: [],
      id: '',
      title: '',
      nick_name: '',
      logo: '',
      views: '',
      reply_num: '',
      content: '',
      commentList: [],
      isShow: false,
      noData: false,
      isWechat: false
    }
  },
  mounted () {
    var that = this
    that.id = that.$route.query.id
    if (that.id) {
      shareExperence(that.id).then((res) => {
        if (parseInt(res.data.error_code) === 0) {
          that.nick_name = res.data.data.nick_name
          that.logo = res.data.data.logo
          that.title = res.data.data.title
          that.views = res.data.data.views
          that.reply_num = res.data.data.reply_num
          that.content = res.data.data.content
          res.data.data.tags.map(val => {
            that.tagList.push(val)
            return that.tagList
          })
          res.data.data.imgs.map(val => {
            that.imgLIst.push(val)
            return that.imgLIst
          })
        } else {
          that.noData = true
        }
        document.title = that.title
      })
    }
    if (that.id) {
      shareExperenceTui(that.id).then((res) => {
        if (parseInt(res.data.error_code) === 0) {
          res.data.data.map(val => {
            that.commentList.push({
              id: val.id,
              commentTitle: val.title,
              commentTags: val.tag_names,
              imgPath: val.img_path
            })
            return that.commentList
          })
        }
      })
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
    margin-bottom:0.08rem;
  }
  .swiper-pagination{
    color:#fff;
  }
  .banner-box{
    position:relative;
    width:100%;
  }
  .experience-content{
    padding:0 0.1rem;
  }
  .experience-tit{
    height: 0.3rem;
    line-height:0.3rem;
    margin:0.1rem 0
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
  .experience-col{
    font-size: 0.11rem;;
    color:#999;
  }
  .experience-tit span{
    display: inline-block;
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
    font-size:0.12rem;
  }
  .space{
    width:100%;
    height: 0.0777rem;
    background:#F2F2F2;
  }
  .body-content{
    word-break:break-all;
    margin-bottom:0.1rem;
    line-height:1.5;
  }
  .recommend-content li.recommend-tips{
    height: 0.6214rem;
    margin:0.1rem 0.1rem;
    overflow: hidden;
    border-bottom: 1px #F7F7F7 solid;
  }
  .recommend-content li .recommend-left{
    float: left;
    width: 78%;
    height: 0.69rem;
  }
  .recommend-content li .recommend-right{
    float: right;
    width: 0.6214rem;
    height: 0.4661rem;
    overflow: hidden;
    border-radius: 0.05rem;
    margin-top: 0.08rem;
  }
  .recommend-content li .recommend-right img{
    width:100%;
  }
</style>
