<template>
  <section>
    <div class="zxfw-box">
      <div class="zxfw-banner">
        <img src="//staticqn.qizuang.com/custom/20200630/Fs0enI5gzCp0IZlg5n80pFWHh47A" />
        <div>
          <p class="zxfw-tit">品牌影响</p>
          <p class="zxfw-cctv">
            <img src="../../../assets/img/qzw/rectangle.png" />
            <span>强势登陆CCTV央视频道</span>
            <img src="../../../assets/img/qzw/rectangle.png" />
          </p>
        </div>
      </div>
      <div class="zxfw-vedio">
        <video-player  class="vjs-custom-skin"
         ref="videoPlayer"
         :options="playerOptions"
         :playsinline="true"
         @play="onPlayerPlay($event)"
        >
        </video-player>
      </div>
      <div class="zxfw-pingtai">
        <p class="pingtai-tit">齐装网</p>
        <p class="pingtai-des">深耕互联网装修8载 中国知名大型装修平台</p>
        <p class="line"></p>
        <div class="pingtai-icon">
          <ul>
            <li>
              <div>
                <p><span class="heavy">1500</span>万</p>
                <p>服务业主</p>
              </div>
            </li>
            <li>
              <div>
                <p><span class="heavy">6</span>万</p>
                <p>合作设计师</p>
              </div>
            </li>
            <li>
              <div>
                <p><span class="heavy">86</span>万</p>
                <p>业主点评</p>
              </div>
            </li>
            <li>
              <div>
                <p><span class="heavy">900</span>万</p>
                <p>城市分站</p>
              </div>
            </li>
          </ul>
        </div>
      </div>
      <div class="zxfw-baozhang">
        <img class="baozhang-bg1" src="../../../assets/img/qzw/baozhang-bg1.png" />
        <img class="baozhang-bg2" src="../../../assets/img/qzw/baozhang-bg2.png" />
        <p class="zxfw-tit">装修保障</p>
        <div class="baozhang-list" @click="toqzb">
          <div class="bz-img">
            <img src="../../../assets/img/qzw/peifu.png" />
          </div>
          <div class="bz-right">
            <p class="bz-tit">你装修 我保障</p>
            <p class="bz-des">单笔最高赔付 100万</p>
            <div class="bz-list">
              <ul>
                <li>
                  <img src="../../../assets/img/qzw/rectangle.png" />
                  <span>齐装保 保装修</span>
                </li>
                <li>
                  <img src="../../../assets/img/qzw/rectangle.png" />
                  <span>订单透明</span>
                </li>
                <li>
                  <img src="../../../assets/img/qzw/rectangle.png" />
                  <span>省时省心</span>
                </li>
                <li>
                  <img src="../../../assets/img/qzw/rectangle.png" />
                  <span>全程把控</span>
                </li>
                <li>
                  <img src="../../../assets/img/qzw/rectangle.png" />
                  <span>无忧售后</span>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <div class="baozhang-list">
          <div class="bz-img">
            <img src="../../../assets/img/qzw/quanyi.png" />
          </div>
          <div class="bz-right">
            <p class="bz-tit">装修保障金</p>
            <p class="bz-des">监督装修公司 保障业主权益</p>
            <div class="bz-list">
              <ul>
                <li>
                  <img src="../../../assets/img/qzw/rectangle.png" />
                  <span>施工增项</span>
                </li>
                <li>
                  <img src="../../../assets/img/qzw/rectangle.png" />
                  <span>装修猫腻</span>
                </li>
                <li>
                  <img src="../../../assets/img/qzw/rectangle.png" />
                  <span>卷款跑路</span>
                </li>
                <li>
                  <img src="../../../assets/img/qzw/rectangle.png" />
                  <span>...</span>
                </li>
                <li>
                  <img src="../../../assets/img/qzw/rectangle.png" />
                  <span>怎么办？</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
        <div class="baozhang-list">
          <div class="bz-img">
            <img src="../../../assets/img/qzw/koubei.png" />
          </div>
          <div class="bz-right">
            <p class="bz-tit">深度合作企业征信机构</p>
            <p class="bz-des">查装修公司信用口碑</p>
            <div class="bz-list">
              <ul class="koubei">
                <li>
                  <img src="../../../assets/img/qzw/rectangle.png" />
                  <span>装企查，查装企</span>
                </li>
                <li>
                  <img src="../../../assets/img/qzw/rectangle.png" />
                  <span>装修公司靠谱吗？</span>
                </li>
                <li>
                  <img src="../../../assets/img/qzw/rectangle.png" />
                  <span>企业信用查一下</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div class="zxfw-fuwu">
        <p class="zxfw-tit position">服务特色</p>
        <img class="fuwu-img" src="../../../assets/img/qzw/fuwu-img.png" />
        <span @click="toSheji" class="more"></span>
        <span class="worksite toapp" @click="toWorksite"></span>
      </div>
      <div class="zxfw-fuli" @click="toYouku">
        <p class="zxfw-tit position" id="zxfuli">福利专区</p>
        <img class="fuli-img" src="//staticqn.qizuang.com/custom/20200714/Fu80EzSWIIrPnll7JTewa9sFwdJy" />
        <div class="open-app toapp">
          <div>点击抽取优酷会员</div>
        </div>
      </div>
      <div class="sheji-free" @click="sheji">
        <img src="../../../assets/img/qzw/sheji-free.png" />
      </div>
    </div>
    <design-order v-if="isShow"></design-order>
    <div class="fadan-success" v-if="isSuccess">{{successHtml}}</div>
  </section>
</template>
<script>
import designOrder from "@/components/qzw/baozhang/designOrder";
export default {
  name: 'zxfw',
  components: {
    designOrder
  },
  data () {
    return {
      isShow: false,
      isSuccess: false,
      successHtml: '获取成功！',
      playerOptions: {
        height: '200',
        autoplay: false,
        controls: true,
        muted: false, // 控制声音
        language: 'en',
        playbackRates: [0.7, 1.0, 1.5, 2.0],
        sources: [{
          type: 'video/mp4',
          src: 'http://staticqn.qizuang.com/video/qizuang/cctv_video.mp4'
        }],
        poster: require('../../../assets/img/qzw/video.png')
      }
    }
  },
  created () {
    // this.formInfo.source = this.$globalFun.setSource(this.$route.query.source, 'baojia')
  },
  methods: {
    // 免费设计
    sheji () {
      this.isShow = true
    },
    toqzb () {
      this.jumpMethod('base_schemeUrl', 'qizuang-qz://qz/go?action=QZ_DECORATION_GUARANTEE')
    },
    toSheji () {
      this.$router.push({
        path: '/qzw/xhxsj?from=app&source=qz'
      })
    },
    toWorksite () {
      this.jumpMethod('Native_decoration_site', '')
    },
    toYouku () {
      this.jumpMethod('base_schemeUrl','qizuang-qz://qz/go?action=QZ_ACTIVITY_YOUKU')
    },
    jumpMethod (method, url) {
      this.$bridge.callNative(method, url)
    }
  }
}
</script>
<style scoped>
  img{
    width:100%;
    vertical-align: middle;
  }
  .zxfw-banner{
    position: relative;
  }
  .zxfw-banner>div{
    width: 100%;
    position: absolute;
    bottom: -4px;
  }
  .zxfw-box .zxfw-tit{
    width: 48%;
    height: 0.205rem;
    line-height: 0.205rem;
    background: url("../../../assets/img/qzw/tit_bg.png") no-repeat center center;
    background-size: 100%;
    margin: 0 auto;
    text-align: center;
    font-size: 0.205rem;
    color: #A37932;
    font-weight: bold;
    font-style:italic;
    margin-bottom: 0.05rem;
  }
  .zxfw-box #zxfuli{
    color: #EAB56C;
    background: url("../../../assets/img/qzw/fuli_bg.png") no-repeat center center;
    background-size: 100%;
  }
  .zxfw-banner .zxfw-cctv{
    text-align: center;
    position: relative;
  }
  .zxfw-banner .zxfw-cctv img{
    width: 0.077rem;
    height: 0.077rem;
    position: absolute;
    top: 0;
    bottom: 0;
    margin: auto;
  }
  .zxfw-banner .zxfw-cctv span{
    font-size: 0.137rem;
    color: #A37932;
    margin: 0 0.081rem 0 0.15rem;
  }
  .zxfw-vedio{
    width: 92%;
    overflow: hidden;
    margin: 0.171rem auto;
  }
  .zxfw-pingtai{
    height: 1.485rem;
    position: relative;
    background: url("../../../assets/img/qzw/pingtai-bg.png");
    background-size: 100% 100%;
  }
  .zxfw-pingtai p{
    text-align: center;
    color: #fff;
  }
  .zxfw-pingtai .pingtai-tit{
    font-size: 0.171rem;
    padding-top: 0.25rem;
    margin-bottom: 0.064rem;
  }
  .zxfw-pingtai .pingtai-des{
    font-size: 0.119rem;
  }
  .zxfw-pingtai .line{
    width: 0.529rem;
    height: 0.026rem;
    background: #CFA670;
    margin: 0.12rem auto 0;
  }
  .zxfw-pingtai .pingtai-icon{
    width: 92%;
    height: 0.922rem;
    background: url("../../../assets/img/qzw/icons-bg.png");
    background-size: 100% 100%;
    position: absolute;
    left: 0;
    right: 0;
    top: 1.033rem;
    margin: auto;
  }
  .pingtai-icon ul{
    height: 100%;
  }
  .pingtai-icon ul li{
    display: inline-block;
    width: 25%;
    float: left;
  }
  .pingtai-icon ul li div{
    width: 0.589rem;
    height: 0.589rem;
    background: url("../../../assets/img/qzw/icon-bg.png");
    background-size: 100% 100%;
    margin-top: 0.171rem;
    margin-left: 0.07rem;
  }
  .pingtai-icon ul li p{
    font-size: 0.081rem;
    color:rgba(255,255,255,1);
    background:linear-gradient(0deg,rgba(107,76,44,1) 0%, rgba(78,44,16,1) 100%);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
  }
  .pingtai-icon ul li p:nth-child(1){
    padding-top: 0.11rem;
  }
  .pingtai-icon ul li p .heavy{
    font-size: 0.183rem;
    font-weight: bold;
  }
  .zxfw-baozhang{
    margin-top: 0.469rem;
    padding-top: 0.299rem;
    position: relative;
  }
  .baozhang-list{
    width: 92%;
    height: 1.212rem;
    margin: 0 auto;
    margin-top: 0.171rem;
    position: relative;
    background: url("../../../assets/img/qzw/bzbg2.png");
    background-size: 100% 100%;
    box-shadow: rgba(0,0,0,0.1)  0px 0px 10px;
  }
  .baozhang-list .bz-img{
    width: 0.93rem;
    height: 0.93rem;
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0.141rem;
    margin: auto;
  }
  .baozhang-list .bz-right{
    padding-left: 1.156rem;
    padding-top: 0.15rem;
  }
  .bz-right .bz-tit{
    font-size: 0.154rem;
    color: #4F2D11;
    font-weight: bold;
    text-align: left;
  }
  .bz-right .bz-des{
    font-size: 0.102rem;
    color: #A57F58;
    margin-bottom: 0.04rem;
  }
  .bz-list ul li{
    display: inline-block;
    width: 40%;
    padding-left: 0.094rem;
    position: relative;
  }
  .bz-list ul li img{
    width: 0.068rem;
    height: 0.068rem;
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    margin: auto;
  }
  .bz-list ul li span{
    font-size: 0.102rem;
    color: #666;
  }
  .bz-list ul.koubei li{
    width: 55%;
  }
  .zxfw-baozhang .baozhang-bg1{
    width: 1.293rem;
    height: 1.31rem;
    position: absolute;
    left: 0;
    top: -5px;
  }
  .zxfw-baozhang .baozhang-bg2{
    width: 1.225rem;
    height: 1.169rem;
    position: absolute;
    right: 0;
    top: 2.321rem;
  }
  .zxfw-fuwu,.zxfw-fuli{
    position: relative;
  }
  .zxfw-fuwu .position{
    position: absolute;
    top: 0.853rem;
    left: 0;
    right: 0;
  }
  .zxfw-fuwu a,.zxfw-fuwu span{
    width: 0.8rem;
    height: 0.3rem;
    position: absolute;
  }
  .zxfw-fuwu span.more{
    top: 2.405rem;
    left: 0.4rem;
  }
  .zxfw-fuwu span.worksite{
    bottom: 0.185rem;
    right: 0.533rem;
    z-index: 999;
    cursor: pointer;
  }
  .zxfw-fuli .position{
    position: absolute;
    top: 0.414rem;
    left: 0;
    right: 0;
  }
  .zxfw-fuli .open-app{
    width: 0.909rem;
    height: 0.213rem;
    position: absolute;
    bottom: 0.299rem;
    right: 0.179rem;
    z-index: 9;
    cursor: pointer;
    background: url("../../../assets/img/qzw/btn.png") no-repeat;
    background-size: 100% 100%;
    font-size: 0.094rem;
    color: #fff;
    text-align: center;
    line-height: 0.213rem;
  }
  .sheji-free{
    width: 0.589rem;
    height: 0.657rem;
    position: fixed;
    z-index: 90;
    bottom: 0.6rem;
    right: 0;
    animation: rotate 1s linear infinite;
  }
  @keyframes rotate{
    0% {
      transform: rotate(0deg)
    }
    25% {
      transform: rotate(10deg)
    }
    50% {
      transform: rotate(0deg)
    }
    75% {
      transform: rotate(-10deg)
    }
    100% {
      transform: rotate(0deg)
    }
  }
  .fadan-success{
    width: 0.922rem;
    height: 0.341rem;
    text-align: center;
    line-height: 0.341rem;
    border-radius: 0.034rem;
    color: #fff;
    background: rgba(0,0,0,0.6);
    position: fixed;
    top:0;
    right: 0;
    left: 0;
    bottom: 0;
    margin: auto;
  }
  /deep/ .video-js{
    width: 100%;
  }
  /deep/ .video-js button{
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    margin: auto;
  }
  /deep/ .vjs-poster{
    background-size: 100% 100%;
  }
</style>
