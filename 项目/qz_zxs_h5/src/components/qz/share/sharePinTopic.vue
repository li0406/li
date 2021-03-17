<template>
  <div id="topicshare">
      <!-- <open-app></open-app> -->
      <share-pin-comp></share-pin-comp>
      <div class="topic_tu">
        <img :src="headTopicData.img_url" alt="">
        <div class="heading">
          <p>{{ headTopicData.title }}</p>
          <span>{{ headTopicData.views_count }}正在围观</span>
        </div>
        <div class="opci"></div>
      </div>
      <!-- 热门 最新 -->
      <div class="contents">
        <!-- 标题 -->
        <div class="hotnews">
          <div @click="toogle(index)" v-for="(item,index) of dataIndex" :key="index" >
            <p :style="{color:activeIndex == index?'#333':'#999'}">{{ item.name }}</p>
            <p :class="activeIndex==index?'hot':''"></p>
          </div>
        </div>
        <!-- 热门话题评论 -->
        <div>
          <div v-for="(item,index) in topList" :key="index" class="myOrderListWrapper">
            <div class="title">
              <p class="tu" v-if="item.avatar"><img :src="item.avatar" alt=""></p>
              <p class="tu" v-else><img src="../../../assets/img/qz/share/default-logo.png" alt=""></p>
              <p class="text">{{ item.nickname }}</p>
            </div>
            <div class="appcount"><app-content @clickfulltextFn="getfullFn" :content="item.content"></app-content></div>
            <!-- 图片 -->
            <div class="photo" v-if="item.type == 1">
              <div class="picture">
                <p v-for="(items,i) in item.media_info" :key="i" ><img :src="items.url" alt=""></p>
              </div>
            </div>
            <!-- 视频 -->
            <div v-if="item.type == 2" class="video">
               <video-player
                class="vjs-custom-skin"
                ref="videoPlayer"
                :options="item.playerOptions"
                :playsinline="true"
              >
              </video-player>
            </div>
            <div class="bask_sun" @click="qzOpenApp" v-if="item.content && item.topic_name">
              <p class="bask_sun_p"><span class="jing"><img src="../../../assets/img/qz/share/jingicon.png" alt=""></span>{{ item.topic_name }}</p>
            </div>
            <div class="zansou">
              <p @click="qzOpenApp">
                <span class="zanPic"><img src="../../../assets/img/qz/share/zans.png" alt=""></span>
                <span v-if="item.thumbsup_count_total>0">{{item.thumbsup_count_total}}</span>
                <span v-else>点赞</span>
              </p>
              <p @click="qzOpenApp">
                <span class="zanPic"><img src="../../../assets/img/qz/share/changs.png" alt=""></span>
                <span v-if="item.collect_count_total">{{ item.collect_count_total }}</span>
                <span v-else>收藏</span>
              </p>
            </div>
            <div class="lun">
              <p v-for="(itemis,index) in item.comments.slice(0,3)" :key="index" @click="qzOpenApp">
                <span class="names">{{ itemis.nickname }}：</span>
                <span class="lun_cont">{{ itemis.content }}</span>
              </p>
              <div class="look" v-if="item.comments.length>3"  @click="fulltext">查看全部<span>{{ item.comments_count_total }}</span>条评论></div>
            </div>
          </div>
        </div>
        <div v-if="isDI" class="dicls">- 我也是有底线的 -</div>
        <div class="nodata_box" v-if="isnodata">
          <div class="nodata"><img src="../../../assets/img/qz/share/nodata.png" alt=""></div>
          <p>你还没有关注任何内容哦～</p>
        </div>
      </div>
      <!-- 底部悬浮按钮 -->
      <div class="goappsmall" @click="qzOpenApp">去APP内查看更多</div>
      <wx-tips :isWechat="isWechat"></wx-tips>
  </div>
</template>
<script>
import qzOpenApp from "@/mixins/qzOpenApp";
// import openApp from '@/components/common/openApp'
import sharePinComp from '@/components/common/sharePinComp'
import appContent from '@/components/circle/components/appContent'
import { MessageBox } from 'mint-ui';
import api from '@/api/casedetails'
export default {
  name: 'topicshare',
  mixins: [qzOpenApp],
  components: {
    // openApp: () => import('@/components/common/openApp'),
    sharePinComp: () => import('@/components/common/sharePinComp'),
    wxTips: () => import('@/components/common/wxTips'),
    appContent,
  },
  data() {
    return {
      isWechat: false,
      activeIndex : 0,
      dataIndex: [
        {
          id: 1,
          name: "热门"
        },
        {
          id: 2,
          name: "最新"
        }
      ],
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
      topicId: '',  //话题id
      headTopicData: {},  //话题头部数据
      topList: [],
      page: 1,
      limit: 10,
      isDI: false,
      isnodata: false
    }
  },
  mounted() {
  },
  created() {
    this.topicId = this.$route.query.id
    this.getTopicInfo()
    this.getTopicWle()
    window.onscroll= () => {
      let scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
      let clientHeight = document.documentElement.clientHeight || document.body.clientHeight;
      let scrollHeight = document.documentElement.scrollHeight || document.body.scrollHeight;
      if(scrollTop + clientHeight >= scrollHeight-50) {
        this.page +=1
        this.getTopicWle()
      }
    }
  },
  methods: {
    toogle(index) {
      this.activeIndex = index
      this.page = 1
      this.topList = []
      this.getTopicWle()
    },
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
    // 头部
    getTopicInfo () {
      let params = {
        id: this.topicId
      }
      api.topicFn(params).then((res) => {
        if (parseInt(res.data.error_code) === 0) {
          this.headTopicData= res.data.data
        } else {
          this.$message.error(res.data.error_msg)
        }
      })
    },
    // 最新  最热
    getTopicWle () {
      let params = {
        topic_id: this.topicId,
        order: this.activeIndex == 0?'hot':'new',
        page: this.page,
        limit: this.limit
      }
      api.topicWelFn(params).then((res) => {
        if (parseInt(res.data.error_code) === 0) {
          let arr = res.data.data.list
          arr.map(item => {
            if(item.type === 2){
              item.playerOptions = {
                sources:[
                  {
                    type: "video/mp4",
                    src: item.media_info[0].url
                  }
                ],
                poster: item.cover_info.url
              }
            }
            return item
          })
          this.topList.push(...arr)
          if(this.topList.length<=0) {
            this.isnodata= true
          } else {
            this.isnodata= false
          }
          if(arr=='' && this.topList.length>0) {
            this.isDI = true;
            return false;
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
.open-app {
  margin-bottom: 0 !important;
}
img {
  width: 100%;
  height: 100%;
}
.topic_tu {
  width: 100%;
  height: 1.638rem;
  color: #fff;
  position: relative;
}
.opci {
  width: 100%;
  height: 1.638rem;
  background: linear-gradient(0deg, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0));
  opacity: 0.6;
  position: absolute;
  top:0;
  left: 0;
  right: 0;
  bottom:0
}
.myOrderListWrapper {
  border-bottom: 0.004rem solid #F4F4F4;
  padding-bottom: 0.013rem;
}
.look {
  color:#5B7A9F;
}
.heading {
  position: absolute;
  left: 0.128rem;
  bottom: 0.247rem;
  font-size: 0.119rem;
  font-weight: 500;
  z-index: 88;
}
.heading p {
  margin-bottom: 0.171rem;
  font-size: 0.154rem;
  font-weight: bold;
}
.contents {
  padding: 0.171rem 0;
  box-sizing: border-box;
}
.hotnews {
  height: 0.256rem;
  display: flex;
  font-size: 0.154rem;
  font-weight: bold;
  color: #999999;
  padding-left: 0.128rem;
  box-sizing: border-box;
}
.hotnews div {
  margin-right: 0.256rem;
}
.hot {
   width: 0.171rem;
   height: 0.026rem;
   background-color: #FF5353;
   border-radius: 0.013rem;
   margin-top: 0.081rem;
   margin-left: 0.064rem;
}
.title {
  width: 100%;
  /* height: 0.640rem; */
  display: flex;
  align-items: center;
  margin-top: 0.043rem;
  padding-left: 0.128rem;
  box-sizing: border-box;
  margin-top: 0.171rem;
  margin-bottom: 0.128rem;
}
.tu {
  width: 0.341rem;
  height: 0.341rem;
  border-radius: 50%;
  margin-right: 0.090rem;
}
.tu>img {
  border-radius: 50%;

}
.text {
  width: 1.539rem;
  font-size: 0.137rem;
  font-weight: bold;
  color: #333;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
}
.comment {
    max-height: 0.685rem;
    line-height: 0.239rem;
    font-size: 0.137rem;
    font-weight: 500;
    color: #333;

}
.comment2{
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
}
.zankai {
  font-size: 0.137rem;
  font-weight: 500;
  color: #5B7A9F;
}
.appcount {
  padding-left: 0.128rem;
  padding-right: 0.128rem;
  box-sizing: border-box;
}
.photo {
  width: 100%;
  padding-left: 0.064rem;
  box-sizing: border-box;
}
.picture {
  width: 100%;
  display: flex;
  flex-wrap: wrap;
}
.picture > p {
  width: 0.939rem;
  height: 0.939rem;
  margin-top: 0.064rem;
  border-radius: 0.021rem;
  margin-left: 0.064rem;
  border-radius: 0.021rem;
}
.picture > p >img{
  border-radius: 0.021rem;
}
.bask_sun {
  height: 0.201rem;
  font-size: 0.102rem;
  margin-top: 0.102rem;
  margin-left: 0.128rem;
  margin-right: 0.128rem;
  box-sizing: border-box;
  display: flex;
  align-items: center;
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
 .zansou {
    display: flex;
    margin-top: 0.171rem;
    margin-bottom: 0.158rem;
    font-size: 0.102rem;
    font-weight: 500;
    color: #333;
    padding-left: 0.128rem;
    box-sizing: border-box;
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
  .lun {
    font-size: 0.118rem;
    font-weight: 500;
    color: #333;
    padding-left: 0.128rem;
    padding-right: 0.128rem;
    box-sizing: border-box;
    margin-bottom: 0.158rem;
  }
  .hidove {
    overflow: hidden;
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
  .video{
    width: 2.193rem;
    height: 2.927rem;
    position: relative;
    padding-left: 0.128rem;
    box-sizing: border-box;
    margin-top: 0.064rem;
  }
  /deep/ .video-js {
    width: 2.193rem;
    height: 2.927rem;
  }
  /deep/ .video-js .vjs-big-play-button {
    border-radius: 50%;
    width: 0.512rem;
    height: 0.512rem;
    position: absolute;
    top: 50%;
    left: 50%;
    margin-top: -0.256rem;
    margin-left: -0.256rem;
    line-height: 1.7;
    font-size: 0.28rem;
  }
  .dicls {
    width: 100%;
    height: 1rem;
    text-align: center;
    font-size: 0.102rem;
    font-weight: 400;
    padding-top: 0.149rem;
    box-sizing: border-box;
    color: #999999
  }
  .nodata_box {
    margin: 0.564rem auto;
    text-align: center;
  }
  .nodata_box p {
    font-size: 0.119rem;
    color: #999;
  }
  .nodata {
    width: 1.216rem;
    height: 0.900rem;
    margin: 0.213rem auto;
  }
</style>

