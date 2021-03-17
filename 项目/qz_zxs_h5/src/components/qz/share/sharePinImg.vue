<template>
  <div class="imgvideo">
    <!-- <open-app></open-app> -->
    <share-pin-comp></share-pin-comp>
    <div id="imgSharing" v-if="typeNum == 1">
      <div class="title">
        <p class="tu"><img :src="imgTextList.avatar" alt=""></p>
        <p class="text">{{ imgTextList.nickname }}</p>
      </div>
      <div class="swiper-banner">
        <swiper :options="swiperOption">
          <swiper-slide class="swiper-item" v-for="(item,index) in tagsimg.imgs" :key="index">
            <img :src="item.url" alt="">
          </swiper-slide>
          <div class="swiper-pagination" slot="pagination"></div>
        </swiper>
      </div>
      <div class="text_cont">
        <div class="tudowm_text">
          {{ tagsimg.content }}
        </div>
        <div class="bask_sun" @click="qzOpenApp" v-if="tagsimg.content && imgTextList.topic_name">
            <p class="bask_sun_p"> <span class="jing"><img src="../../../assets/img/qz/share/jingicon.png" alt=""></span> {{ imgTextList.topic_name }}</p>
        </div>
        <div class="zansou" @click="qzOpenApp">
          <p>
            <span class="zanPic"><img src="../../../assets/img/qz/share/zans.png" alt=""></span>
            <span v-if="imgTextList.thumbsup_count_total>0">{{imgTextList.thumbsup_count_total}}</span>
            <span v-else>点赞</span>
          </p>
          <p>
            <span class="zanPic"><img src="../../../assets/img/qz/share/changs.png" alt=""></span>
            <span v-if="imgTextList.collect_count_total>0">{{imgTextList.collect_count_total}}</span>
            <span v-else>收藏</span>
          </p>
        </div>
        <div class="lun">
          <p v-for="(item,index) in imgTextList.comments.slice(0,3)" :key="index" @click="qzOpenApp">
            <span class="names">{{ item.nickname }}：</span>
            <span class="lun_cont">{{ item.content }}</span>
          </p>
          <div style="color:#5B7A9F;" @click="fulltext" v-if="imgTextList.comments.length>3">查看全部<span>{{ imgTextList.comments_count_total }}</span>条评论></div>
        </div>
        <div class="app_kong"></div>
      </div>
      <div class="goappsmall" @click="qzOpenApp">去APP内查看更多</div>
    </div>

    <div class="videoshare2" id="videoshare" v-if="typeNum == 2">
      <!-- 视频详情 -->
      <div class="videoactic">
        <video-player
          class="vjs-custom-skin"
          ref="videoPlayer"
          :options="playerOptions"
          :playsinline="true"
        >
        </video-player>
      </div>
      <!-- 视频上的信息 -->
      <div class="information">
        <div class="nainfor" @click="qzOpenApp">@{{ imgTextList.nickname }}</div>
        <app-content @clickfulltextFn="getfullFn" :content="tagsvideo.content"></app-content>
      </div>
      <div class="opci_topic"></div>
    </div>

    <wx-tips :isWechat="isWechat"></wx-tips>
  </div>
</template>
<script>
import qzOpenApp from "@/mixins/qzOpenApp";
// import openApp from '@/components/common/openApp'
import sharePinComp from '@/components/common/sharePinComp'
import 'swiper/dist/css/swiper.css'
import { swiper, swiperSlide } from 'vue-awesome-swiper'
import { MessageBox } from 'mint-ui'
import wxTips from '@/components/common/wxTips'
import api from '@/api/casedetails'
import appContent from '@/components/circle/components/appContent'
export default {
  name: 'imgvideo',
  mixins: [qzOpenApp],
  data() {
    return {
      isWechat: false,
      activeId: '',
      imgTextList: {},
      tagsimg:[],
      tagsvideo: [],
      typeNum: '',
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
      swiperOption: {
          pagination: {
            el: '.swiper-pagination',
            type: 'fraction',
          },
      }


    }
  },
   components: {
    // openApp: () => import('@/components/common/openApp'),
    sharePinComp: () => import('@/components/common/sharePinComp'),
    wxTips: () => import('@/components/common/wxTips'),
    swiper,
    swiperSlide,
    appContent
  },
  created() {
    this.activeId =this.$route.query.id
    this.converFnget()
  },
  mounted() {
  },
  methods: {
    fulltext() {
      MessageBox({
        title: '',
        message: '是否打开APP查看全文？',
        showCancelButton: true,
        confirmButtonClass: 'determine-btn'
      }).then(res => {
        if(res === "confirm") {
          this.qzOpenApp()
        } else {

        }
      })
    },
    // 在父组件中触发子组件的点击事件
    getfullFn() {
      MessageBox({
        title: '',
        message: '是否打开APP查看全文？',
        showCancelButton: true,
        confirmButtonClass: 'determine-btn'
      }).then(res => {
        if(res === "confirm") {
          this.qzOpenApp()
        } else {

        }
      })
    },
    converFnget () {
      let params = {
        id: this.activeId,
        from: 'share_h5'
      }
      api.converFn(params).then((res) => {
        if (parseInt(res.data.error_code) === 0) {
          this.imgTextList = res.data.data
          this.tagsimg= res.data.data.imgtext
          this.typeNum= this.imgTextList.type
          this.tagsvideo= res.data.data.video
          if(this.typeNum == 2) {
            this.playerOptions.sources = [{
              type: 'video/mp4',
              src: this.tagsvideo.video_url
            }]
            this.playerOptions.poster = this.tagsvideo.cover_url
          }
        } else {
          this.$message.error(res.data.error_msg)
        }
      })
    },


  }

}
</script>

<style scoped>
  .close_html {
    width: 100%;
    height: 0.427rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 0.128rem;
    box-sizing: border-box;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 888;
    background-color: #fff;
  }
  .goapp {
    width: 100%;
    height: 0.427rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background-color: #FEE0E0;
    padding: 0 0.128rem;
    box-sizing: border-box;
    font-size: 0.111rem;
    font-weight: 500;
    color: #333;
  }
  .goapp>div {
    display: flex;
    align-items: center;

  }
  .tit_img {
    width: 0.320rem;
    height: 0.320rem;
    margin-right: 0.064rem;
  }
  .aApp {
    width: 0.747rem;
    height: 0.213rem;
    color: #FF5353;
    line-height: 0.213rem;
    text-align: center;
    background-color: #fff;
    border-radius: 0.107rem;
    font-size: 0.102rem;
    font-weight: 500;
  }
  .title {
    width: 100%;
    height: 0.640rem;
    display: flex;
    align-items: center;
  }
  .tu {
    width: 0.341rem;
    height: 0.341rem;
    border-radius: 50%;
    margin-left: 0.124rem;
    margin-right: 0.090rem;
  }
  .tu>img {
    border-radius: 50%;
    background-color: yellowgreen;
    width: 100%;
    height: 100%;
  }
  .text {
    max-width: 1.539rem;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
    font-size: 0.137rem;
    font-weight: bold;
    color: #333;
  }
  .swiper-item {
    width: 100%;
    height: 2.398rem;
    text-align: center;
  }
  .swiper-banner img {
    max-width: 100%;
    max-height: 2.398rem;
  }
  .text_cont {
    padding: 0.171rem  0.128rem 0;
    box-sizing: border-box;
  }
  .tudowm_text {
    font-size: 0.137rem;
    font-weight: 500;
    color: #333;
    line-height: 0.239rem;
  }
  .bask_sun {
    width: 100%;
    box-sizing: border-box;
    height: 0.201rem;
    display: flex;
    align-items: center;
    font-size: 0.102rem;
    border-radius: 0.098rem;
    margin-top: 0.102rem;

  }
  .bask_sun_p {
    max-width: 1.949rem;
    color:#5B7A9F;
    background-color: #F5F7FA;
    padding: 0.021rem 0.085rem;
    box-sizing: border-box;
    border-radius: 0.098rem;
  }
  .jing {
    width: 0.128rem;
    height: 0.128rem;
    display: inline-block;
    margin-right: 0.043rem;
    background-color: #F5F7FA;
    vertical-align: middle;
  }
  .jing>img {
    width: 100%;
    height: 100%;
  }
  .app_kong {
    width: 100%;
    height: 0.853rem;
  }
  .zansou {
    display: flex;
    margin-top: 0.171rem;
    margin-bottom: 0.158rem;
    font-size: 0.102rem;
    font-weight: 500;
    color: #333;
  }
  .zansou>p {
    display: flex;
    align-items: center;
    margin-right: 0.171rem;
  }
  .zanPic {
    width: 0.154rem;
    height: 0.154rem;
    margin-right: 0.051rem;
  }
  .zanPic >img{
    width: 100%;
    height: 100%;
  }
  .lun {
    font-size: 0.118rem;
    font-weight: 500;
    color: #333;
  }
  .lun>p {
    margin-bottom: 0.077rem;
  }
  .lun_cont {
    color: #999;
    vertical-align:middle
  }
  .names {
    max-width: 1.339rem;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
    display: inline-block;
    vertical-align:middle;
  }
  .goappsmall {
    width: 1.195rem;
    height: 0.341rem;
    line-height: 0.341rem;
    background-color: #FF5353;
    color: #fff;
    text-align: center;
    border-radius: 0.171rem;
    font-size: 0.119rem;
    font-weight: 500;
    position: fixed;
    bottom: 0.256rem;
    left: 50%;
    margin-left: -0.597rem;
  }
  .kong {
    width: 100%;
    height: 0.427rem;
  }

  /* 修改指示器的样式 */
  .swiper-pagination-fraction, .swiper-pagination-custom, .swiper-container-horizontal > .swiper-pagination-bullets {
    width: 0.316rem;
    height: 0.154rem;
    line-height: 0.154rem;
    left: 85%;
    background-color: #000;
    background: rgba(0,0,0,0.7);
    border-radius: 0.077rem;
    color: #fff;
    font-size: 0.094rem;
  }

</style>

<style scoped>
.imgvideo {
  width: 100%;
  height: 100%;
}
#videoshare {
  height: 100%;
}
.open-app {
  margin-bottom: 0 !important;
}
.videoactic {
  width: 100%;
  height: 100%;
}
.vjs-custom-skin {
  width: 100%;
  height: 100%;
}
/deep/ .video-js {
  width: 100%;
  height: 100%;
}
.information {
  position: fixed;
  bottom: 0.533rem;
  left: 0.132rem;
  right: 0.132rem;
  color: #fff;
  z-index: 88;
}
.opci_topic {
  width: 100%;
  height: 2.231rem;
  background: linear-gradient(0deg, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0));
  opacity: 0.6;
  position: fixed;
  left: 0;
  right: 0;
  bottom:0
}
.nainfor {
  margin-bottom: 0.077rem;
  max-width: 1.339rem;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
  font-size: 0.137rem;
  font-weight: bold;

}
/deep/ .video-js .vjs-big-play-button {
  border-radius: 50%;
  width: 0.512rem;
  height: 0.512rem;
  position: fixed;
  top: 50%;
  left: 50%;
  margin-top: -0.256rem;
  margin-left: -0.256rem;
  line-height: 1.7;
  font-size: 0.28rem;
}
.videocom {
  line-height: 0.199rem;
}
.imgvideo >>>.txt {
  color: #fff;
}
.imgvideo >>> .zankai {
  color: #fff;
}
</style>
