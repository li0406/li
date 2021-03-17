<template>
  <section>
    <open-app></open-app>
    <div class="select-guide">
      <ul class="guide-tit">
        <li v-for="(item,index) in titList" :key="index" :class="{'active':index==(type_id-1)}" @click="toggle($event)">{{item.name}}</li>
      </ul>
      <template v-if="!isClass">
        <div class="guide-banner">
          <div><img :src="bannerImg" /></div>
        </div>
        <div>
          <ul class="taglist">
            <li v-for="item in tagList" :key="item.id" :class="id==item.id?'activity':''" :data-id="item.id" @click="getClick($event)">{{item.cate_name}}</li>
          </ul>
        </div>
        <div class="paihang">
          <ul>
            <li v-for="(val,index) in contentList" :key="index" @click="qzOpenApp">
              <span v-if="index == 0"><img src="@/assets/img/share/first.png" /></span>
              <span v-else-if="index == 1"><img src="@/assets/img/share/second.png" /></span>
              <span v-else-if="index == 2"><img src="@/assets/img/share/third.png" /></span>
              <span v-else>{{index+1}}</span>
              <span><img :src="val.logo" /></span>
              <span>
              <p class="star-title">{{val.brand_name}}</p>
              <p>
                <label v-for="(n,index) in val.score1" :key="index">
                  <i v-if="n == 1">
                    <img src="@/assets/img/share/star.png"/>
                  </i>
                  <i v-else-if="n == 0.3">
                    <img src="@/assets/img/share/downstar.png"/>
                  </i>
                  <i v-else-if="n == 0.7">
                    <img src="@/assets/img/share/upstar.png"/>
                  </i>
                  <i v-else-if="n == 0.5">
                    <img src="@/assets/img/share/halfstar.png"/>
                  </i>
                  <i v-else>
                    <img src="@/assets/img/share/nostar.png"/>
                  </i>
                </label>
              </p>
            </span>
              <span class="total-score">综合{{val.score}}分</span>
            </li>
          </ul>
        </div>
      </template>
      <template v-if="isClass">
        <div class="guide-banner">
          <div><img :src="bannerImg" /></div>
        </div>
        <div>
          <ul class="taglist">
            <li v-for="item in tagfList" :key="item.id" :class="id==item.id?'activity':''" :data-id="item.id" @click="getClickFen($event)">{{item.cate_name}}</li>
          </ul>
        </div>
        <div class="paihang">
          <ul>
            <li v-for="(val,index) in contentfList" :key="index" @click="qzOpenApp">
              <span v-if="index == 0"><img src="@/assets/img/share/first.png" /></span>
              <span v-else-if="index == 1"><img src="@/assets/img/share/second.png" /></span>
              <span v-else-if="index == 2"><img src="@/assets/img/share/third.png" /></span>
              <span v-else>{{index+1}}</span>
              <span><img :src="val.logo" /></span>
              <span>
              <p class="star-title">{{val.material_name}}</p>
              <p>
                <label v-for="(n,index) in val.scoref" :key="index">
                  <i v-if="n == 1">
                    <img src="@/assets/img/share/star.png"/>
                  </i>
                  <i v-else-if="n == 0.3">
                    <img src="@/assets/img/share/downstar.png"/>
                  </i>
                  <i v-else-if="n == 0.7">
                    <img src="@/assets/img/share/upstar.png"/>
                  </i>
                  <i v-else-if="n == 0.5">
                    <img src="@/assets/img/share/halfstar.png"/>
                  </i>
                  <i v-else>
                    <img src="@/assets/img/share/nostar.png"/>
                  </i>
                </label>
              </p>
            </span>
              <span class="total-score">综合{{val.material_score}}分</span>
            </li>
          </ul>
        </div>
      </template>
    </div>
    <down-btn></down-btn>
    <wx-tips :isWechat="isWechat"></wx-tips>
  </section>
</template>
<script>
import openApp from '@/components/common/openApp'
import downBtn from '@/components/common/downBtn'
import wxTips from '@/components/common/wxTips'
import qzOpenApp from '@/mixins/qzOpenApp'
import { shareGuideTag, shareGuideList, shareGuideBanner, shareGuidefTag, shareGuidefList } from '@/api/api'
export default {
  name: 'shareSelectGuide',
  components: {
    openApp,
    downBtn,
    wxTips
  },
  mixins: [qzOpenApp],
  data () {
    return {
      isClass: false,
      swiperOption: {
        width: 60,
        freeMode: true
      },
      id: '',
      type_id: '',
      bannerImg: '',
      titList: [{
        name: '品牌榜'
      }, {
        name: '分类榜'
      }],
      pinpaiImg: require('@/assets/img/share/banner.png'),
      cailiaoImg: require('@/assets/img/share/cailiao.png'),
      tagList: [],
      tagfList: [],
      contentList: [],
      contentfList: [],
      isWechat: false
    }
  },
  created () {
    this.type_id = this.$route.query.type_id
    document.title = '史上最强大的装修家居挑选说明书，选择困难症的福音！'
    if (parseInt(this.type_id) === 1) {
      this.isClass = false
      this.getSelectTag()
    } else if (parseInt(this.type_id) === 2) {
      this.isClass = true
      this.getGuidefTag()
    }
    this.getSelectBanner()
  },
  methods: {
    toggle: function (event) {
      if (event.target.innerHTML === '品牌榜' && this.isClass === false) {
        this.type_id = 1
        this.getSelectTag()
      } else if (event.target.innerHTML === '分类榜' && this.isClass === true) {
        this.type_id = 2
        this.getGuidefTag()
      } else if (event.target.innerHTML === '品牌榜' && this.isClass === true) {
        this.isClass = !this.isClass
        this.type_id = 1
        this.getSelectTag()
      } else if (event.target.innerHTML === '分类榜' && this.isClass === false) {
        this.isClass = !this.isClass
        this.type_id = 2
        this.getGuidefTag()
      }
      this.getSelectBanner()
    },
    getSelectTag: function () {
      shareGuideTag({
        type_id: this.type_id
      }).then(res => {
        if (parseInt(res.data.error_code) === 0) {
          this.id = res.data.data[0].id
          this.tagList = res.data.data
        }
        this.getSelectList()
      })
    },
    getSelectList: function () {
      let that = this
      shareGuideList({
        cate_id: that.id,
        page_current: 1
      }).then(res => {
        console.log(res)
        if (res.data.error_code === 0) {
          res.data.data.list.forEach(val => {
            val.score1 = this.setStars(val.score)
          })
          that.contentList = res.data.data.list
        }
      })
    },
    getSelectBanner: function () {
      shareGuideBanner({
        type_id: this.type_id
      }).then(res => {
        if (res.data.error_code === 0 && res.data.data.length !== 0) {
          this.bannerImg = res.data.data[0].img_url
        } else {
          console.log(this.type_id)
          if (parseInt(this.type_id) === 1) {
            this.bannerImg = this.pinpaiImg
          } else {
            this.bannerImg = this.cailiaoImg
          }
        }
      })
    },
    getGuidefTag: function () {
      shareGuidefTag({
        id: this.id
      }).then(res => {
        if (res.data.error_code === 0) {
          this.id = res.data.data[0].id
          this.tagfList = res.data.data
        }
        this.getSelectfList()
      })
    },
    getSelectfList: function () {
      shareGuidefList({
        cate_id: this.id,
        page_current: 1
      }).then(res => {
        if (res.data.error_code === 0) {
          this.contentfList = res.data.data.list
          res.data.data.list.forEach(val => {
            val.scoref = this.setStars(val.material_score)
          })
        }
      })
    },
    setStars: function (num) {
      var zheng = Math.floor(Number(num)) // 整星
      var yu = num - zheng // 零星
      var stars = []

      if (yu < 0.5 && yu > 0) {
        yu = 0.3
      }
      if (yu > 0.5 && yu < 1) {
        yu = 0.7
      }
      for (var i = 0; i < 5; i++) {
        if (i < zheng) {
          stars.push(1)
        } else if (zheng === i) {
          stars.push(yu)
        } else {
          stars.push(0)
        }
      }
      return stars
    },
    getClick: function (event) {
      this.id = event.target.getAttribute('data-id')
      this.getSelectList()
    },
    getClickFen: function (event) {
      this.id = event.target.getAttribute('data-id')
      this.getSelectfList()
    }
  }
}
</script>
<style scoped>
  img{
    max-width:100%;
    vertical-align: middle;
  }
  .select-guide .guide-tit{
    text-align: center;
    margin:0.1rem 0;
  }
  .guide-banner img{
    width:100%;
  }
  .guide-tit li{
    display: inline-block;
    width: 0.855rem;
    height: 0.24rem;
    line-height: 0.24rem;
    font-size:16px;
    color:#fff;
    background:#CCCCCC;
  }
  .guide-tit li:first-child{
    border-radius:4px  0  0  4px;
    margin-right: -6px;
  }
  .guide-tit li:first-child+li{
    border-radius:0 4px 4px 0;
  }
  .guide-tit li.active{
    background:#FF5353;
  }
  .taglist{
    white-space: nowrap;
    overflow: hidden;
    overflow-x: auto;
  }
  .taglist::-webkit-scrollbar {
    display: none;
  }
  .select-guide .taglist li{
    display: inline-block;
    font-size:14px;
    height: 22px;
    margin: 0.1rem 0.1rem 0 0.1rem;
    padding-bottom:0.1rem;
  }
  .select-guide .taglist li.activity{
    border-bottom: 1px solid #FF5353;
    color:#FF5353;
  }
  .paihang ul{
    padding:0 0.1rem;
  }
  .paihang ul li{
    height: 0.66rem;
    display: table;
    width:100%;
  }
  .paihang ul li span{
    display: table-cell;
    vertical-align: middle;
  }
  .paihang ul li span:first-child{
    padding-right: 0.1rem;
    display: inline-block;
    width: 0.25rem;;
    text-align: center;
    margin-top: -0.17rem;
  }
  .paihang ul li span:first-child+span{
    padding-right: 0.17rem;
    height: 0.35rem;
    width: 0.522rem;
    overflow: hidden;
  }
  .paihang ul li span:first-child+span img{
    height: 0.35rem;
    width: 0.522rem;
  }
  .total-score{
    color:#FFB87E;
    border:1px #FFB87E solid;
    float: right;
    border-radius:4px;
    margin-top: 0.24rem;
    margin-left: 0.3rem;
    padding: 0.03rem 0.05rem;
    font-size:10px;
  }
  .star-title{
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
    overflow: hidden;
    width:1rem;
  }
  .swiper-slide{
    overflow: hidden;
  }
</style>
