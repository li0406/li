<template>
  <section id="out-box">
    <div class="box" v-if="showbox">
        <div class="box-bg">
            <div class="box-head">
                <div class="prize-time">中奖日期</div>
                <div class="prize-con">中奖内容</div>
            </div>
            <div class="box-main">
                <ul>
                    <li v-for="item in list" :key="item.id">
                        <span class="left">{{item.time}}</span>
                        <span class="fr">{{item.prize.prize_name}}</span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="box-bottom"></div>
    </div>
    <div class="fbox" v-if="showfbox">
        <div class="tip">您还没有中奖哦，赶快去<span class="sp1" @click="toIndex">抽奖</span>吧~</div>
        <div class="up-secret">
            <img src="../../../assets/img/activity/luckdraw/up-secret.png" alt="">
        </div>
        <div class="c-tip">
            <p>使用<span class="sp1">“齐装APP”</span>进行抽奖，赢大奖概率提升<span class="sp2">3</span>倍</p>
        </div>
        <div class="down-btn" v-if="!pcShow" id="qzbottomshare">
            <img src="../../../assets/img/activity/luckdraw/down-btn.png" alt="">
        </div>
        <div class="erweima" v-if="pcShow">
            <img src="../../../assets/img/activity/jjys/erweima.png" alt="齐装app二维码">
        </div>
        <div class="box-bottom"></div>
    </div>
  </section>
</template>
<script>
import { getMyPrizeList } from '@/api/api'
export default {
  name: 'prize',
  components: {
  },
  data () {
    return {
      showbox: false,
      showfbox: false,
      list: [],
      from: '',
      pcShow: false
    }
  },
  created () {
    this.from = this.$route.query.from
    if (this.from === 'pc') {
      this.pcShow = true
    } else {
      this.pcShow = false
    }
    this.getPrizeList()
  },
  methods: {
    getPrizeList: function () {
      getMyPrizeList({
      }).then(res => {
        if (parseInt(res.data.error_code) === 0) {
          if (res.data.data.list.length === 0) {
            this.showbox = false
            this.showfbox = true
          } else {
            this.showbox = true
            this.showfbox = false
            this.list = res.data.data.list
          }
        } else {
          console.log(res.error_msg)
        }
      }).catch(err => {
        console.log(err)
      })
    },
    toIndex: function () {
      location.href = '/activity/jjys?from=' + this.$route.query.from + '&src=' + this.$route.query.src
    }
  }
}
</script>
<style scoped>
    #out-box{
        max-width: 640px;
        height: 100%;
        min-height: 667px;
        margin:0px auto;
        background: #5E0DC2;
        position: relative;
    }
    .box {
        padding-top: 4%;
        width: 90%;
        height: 95%;
        margin-left: 5%;
        color: #fff;
    }
    .box .box-bg {
        background: url(../../../assets/img/activity/luckdraw/border.png) no-repeat;
        background-size: 100% 100%;
        background-position: center;
        width: 100%;
        height: 98%;
        position: relative;
        z-index: 2;
    }
    .box .box-bg .box-head {
        height: 11%;
        padding: 0 3%;
        align-items: center;
        display: flex;
        display: -webkit-flex;
    }
    .box .box-bg .prize-time {
        float: left;
        width: 50%;
        text-align: center;
        font-size: 20px;

    }
    .box .box-bg .prize-con {
        float: right;
        width: 50%;
        text-align: center;
        font-size: 20px;

    }
    .box .box-bg .box-main {
        padding-top: 5px;
        padding-left: 15px;
        padding-right: 15px;
        box-sizing: border-box;
    }
    .box .box-bg .box-main ul li {
        padding-top: 10px;
        padding-bottom: 10px;
        border-bottom: 1px dashed rgba(255,255,255,.3);
    }
    .box .box-bg .box-main ul li .left {
        font-size: 13px;
    }
    .box .box-bg .box-main ul li .fr {
        float: right;
        color: #F5E93D;
        font-size: 15px;
        width: 50%;
        text-align: center;
    }
    .box-bottom {
        background: url(../../../assets/img/activity/luckdraw/bottom.png) no-repeat;
        background-size: 100%;
        width: 90%;
        position: absolute;
        bottom: 0;
        height: 76px;
        left: 8%;
    }
    .fbox {
        padding-top: 4%;
        width: 94%;
        height: 90%;
        margin-left: 3%;
        color: #fff;
    }
    .tip {
        margin: 0 auto;
        font-size: 18px;
        margin-top: 25px;
        margin-bottom: 50px;
        text-align: center;
    }
    .up-secret {
        height: 110px;
        margin-bottom: 20px;
    }
    .up-secret img {
        display: block;
        width: 100%;
        height: 100%;
    }
    .c-tip {
        text-align: center;
        font-size: 15px;
        margin-bottom: 45px;
    }
    .tip .sp1 {
        color: #F5E93D;
        border-bottom: 1px solid #F5E93D;
        margin: 0 2px;
    }
    .c-tip .sp1 {
        color:#F5E93D;
    }
    .c-tip .sp2 {
        display: inline-block;
        width: 20px;
        height: 20px;
        color:#5E0DC2;
        background: #F5E93D;
        border-radius: 50%;
        margin: 0 3px;
        font-weight: bold;
    }
    .down-btn {
        height: 54px;
    }
    .down-btn img {
        display: block;
        width: 100%;
        height: 100%;
    }
    .erweima img {
        display: block;
        margin: 0 auto;
    }
</style>
