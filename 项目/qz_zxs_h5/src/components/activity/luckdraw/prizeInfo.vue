<template>
  <section>
    <!-- 中奖 -->
    <div class="bj-box" v-if="zhong">
      <img src="../../../assets/img/activity/luckdraw/jiang.png" alt="">
      <div class="list" v-for="item in listData" v-bind:key="item.index">
        <p><span>中奖名称：</span><span>{{item.prize.prize_name}}</span></p>
        <p><span>姓&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;名：</span><span>{{item.consignee}}</span></p>
        <p><span>电&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;话：</span><span>{{item.tel}}</span></p>
        <p><span class="wen-a">收货地址：</span><span class="wen-b">{{item.address}}</span></p>
      </div>
    </div>
    <!-- 没中奖 -->
    <div class="bj-box" v-else>
      <div class="head-box">
        <img src="../../../assets/img/activity/luckdraw/nojiang.png">
        <div class="ku">
          <img src="../../../assets/img/activity/luckdraw/face.png">
        </div>
      </div>
      <p class="tip">暂未中奖~</p>
    </div>
  </section>
</template>
<script>
import { getWinList, shareAddTimes } from '@/api/api'
export default {
  name: 'prizeinfo',
  data () {
    return {
      zhong: true,
      listData: [],
      endCall: false
    }
  },
  created () {
    let that = this
    // 修改header
    that.changeHeader()
  },
  mounted () {
    let that = this
    getWinList().then(res => {
      if (res.data.error_code === 0) {
        that.listData = res.data.data.list
        that.zhong = res.data.data.list.length > 0
      } else {
        that.zhong = false
      }
    })
  },
  methods: {
    changeHeader () {
      let that = this
      that.$bridge.callNative('header', {
        navTitle: '中奖记录',
        customMenuList: [
          {
            text: '',
            name: 'share',
            menuStatus: '2',
            textColor: '',
            menuType: '2',
            menuImageUrl: ''
          }
        ]
      }, function (res) {
        if (res) {
          that.endCall = true
        }
      })
      // 右上角分享回调
      that.$bridge.registWebNew('JS_onNavClick', function (res, nativeCallBack) {
        let data = JSON.parse(res)
        if (data.key === 'share') {
          that.setShare()
        }
      })
      that.$bridge.callNative('UI_onBackClick', '000', function (res) {
      })
      that.$bridge.registWebNew('JS_onBackClick', function (data, nativeCallBack) {
        nativeCallBack(-1)
      })
      if (!that.endCall) {
        setTimeout(function () {
          that.changeHeader()
        }, 200)
      }
    },
    // 分享功能调用
    setShare () {
      let that = this
      this.isMask = false
      // 分享成功，实际环境
      that.$bridge.callNative('base_share', {
        mShareUrl: 'https://' + document.domain + '/activity/luckdraw/activityshare',
        mShareTitle: '下载送好礼 惊喜无止境',
        mShareSubTitle: '3000元美的空调等你来领'
      }, function (res) {
        if (res === '0') {
          shareAddTimes(that.activityId).then(res => {
            if (res.data.error_code === 0) {
              location.reload()
            } else {
              that.$refs.tips.tipsFadeIn({
                text: res.data.error_msg
              })
            }
          })
        }
      })
    }
  }
}
</script>
<style scoped>
.bj-box {
  position: relative;
  height: 100%;
}
.head-box {
  position: relative;
}
.bj-box img {
  width: 100%;
  display: block;
  margin: 0px auto;
}
.ku {
  width: 0.64rem;
  height: 0.64rem;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%,-50%);
  -ms-transform: translate(-50%,-50%);
  -moz-transform: translate(-50%,-50%);
  -webkit-transform: translate(-50%,-50%);
  -o-transform: translate(-50%,-50%);
}
.list {
  width: 96%;
  height: 1.536rem;
  box-shadow:0px 1px 10px 0px rgba(0, 0, 0, 0.1);
  border-radius:0.085rem;
  margin: 0.128rem auto;
  padding: 0.153rem 0.268rem 0 0.17rem;
  box-sizing: border-box;
}
.list p {
  color: #333;
  font-size: 0.128rem;
  margin-bottom: 0.12rem;
}
.wen-a {
  display: block;
  float: left;
}
.wen-b {
  width: 75%;
  display: block;
  float: left;
  color: #666;
}
.tip {
  font-size: 0.2rem;
  font-weight:500;
  color:rgba(102,102,102,1);
  text-align: center;
  margin-top: 0.9rem;
}
</style>
