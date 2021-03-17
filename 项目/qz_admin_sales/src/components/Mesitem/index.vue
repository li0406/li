<template>
  <div>
    <!--<div class="iconfont tongz" @click="click">&#xe60a;</div>-->
    <div class="iconfont tongz" @click="click"></div>
    <div class="message" v-if="count>0">{{count}}</div>
    <!-- <div class="unread" v-if="count>0">你有消息未读</div> -->
    <!-- <div class="meslist" v-if="infoList">
      <div>
        <div @click="tabdir(item)" class="mes-item" v-for="item in list" :key="item.id">
          {{item.notice }} <span>>></span>
        </div>
      </div>
      <div  class="l-bottom" @click="clickAll">查看全部通知</div>
    </div> -->
  </div>
</template>

<script>
import { getRemind, setRead } from '@/api/home'
export default {
  name: 'Mesitem',
  data() {
    return {
      count: 0,
      list: [],
      infoList: false,
      tabInfo: 0,
      path: this.$qzconfig.ws_protocol + this.$qzconfig.ws_host,
      lockReconnect: false,
      timeout: 28 * 1000,
      timeoutObj: null,
      serverTimeoutObj: null,
      timeoutnum: null,
      socket: null,
      nums: 0
    }
  },
  created() {
    this.getremind()
    this.init()
  },
  mounted() {
  },
  methods: {
    click() {
      this.tabInfo = this.tabInfo + 1
        
    //   const len = (this.tabInfo ).length
        this.$router.push({ path: '/mesList/message' })
      if (this.tabInfo % 2 !== 0) {
        this.infoList = true
      } else {
        this.infoList = false
      }
    //   if (len == 0) {
    //     this.infoList = false
    //     this.$router.push({ path: '/mesList/message' })
    //   }
    },
    clickAll() {
      // this.getremind()
      this.count = 0
      this.infoList = false
      this.$router.push({ path: '/mesList/message' })
    },
    getremind() {
      getRemind({
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.count = res.data.data.no_read_count
          this.list = res.data.data.no_read_list
        } else {
          this.$message({
            message: res.data.error_msg,
            type: 'error'
          })
        }
      })
    },
    tabdir(e) {
      const id = e.id
      const link_url = e.link_url
      setRead({ id: id }).then(res => {
        if (parseInt(res.data.error_code) === 0) {
          if (link_url != '') {
            this.infoList = false
            this.$router.push({ path: link_url })
            this.getremind()
          }
        }
      })
    },
    init() {
      if (typeof (WebSocket) === 'undefined') {
        alert('您的浏览器不支持socket')
      } else {
        const token = localStorage.getItem('token')
        this.socket = new WebSocket(this.path, token)
        this.socket.onopen = this.open
        this.socket.onerror = this.error
        this.socket.onmessage = this.getMessage
        this.socket.onclose = this.getClose
      }
    },
    open: function() {
      console.log('socket连接成功')
    },
    error: function() {
      console.log('连接错误')
    },
    getMessage: function(msg) {
      try {
        const info = JSON.parse(msg.data)
        if (parseInt(info.error_code) === 0) {
          if (info['data']['type'] == 'sales_report') {
            this.getremind()
          } else {
            console.log('token验证失败')
          }
        }
      } catch (e) {
        console.log('处理失败')
      }
    },
    getClose: function() {
      this.reconnect()
    },
    reconnect() {
      var that = this
      that.nums = that.nums + 1
      if (that.lockReconnect || that.nums >= 5) {
        return
      }
      that.lockReconnect = true
      that.timeoutnum && clearTimeout(that.timeoutnum)
      that.timeoutnum = setTimeout(function() {
        that.init()
        that.lockReconnect = false
      }, 5000)
    },
    start() {
      var self = this
      self.timeoutObj && clearTimeout(self.timeoutObj)
      self.serverTimeoutObj && clearTimeout(self.serverTimeoutObj)
      self.timeoutObj = setTimeout(function() {
        console.log(self.socket.readyState, '1111')
        if (self.socket.readyState == 1) {
          self.socket.send('heartCheck')
        } else {
          self.reconnect()
        }
        self.serverTimeoutObj = setTimeout(function() {
          self.socket.close()
        }, self.timeout)
      }, self.timeout)
    }
  }
}
</script>

<style scoped>
  .screenfull-svg {
    display: inline-block;
    cursor: pointer;
    fill: #5a5e66;;
    width: 20px;
    height: 20px;
    vertical-align: 10px;
  }
  @font-face {
    font-family: 'iconfont';
    src: url('//at.alicdn.com/t/font_844055_pjf0dfa6x1.eot');
    src: url('//at.alicdn.com/t/font_844055_pjf0dfa6x1.eot?#iefix') format('embedded-opentype'),
    url('//at.alicdn.com/t/font_844055_pjf0dfa6x1.woff2') format('woff2'),
    url('//at.alicdn.com/t/font_844055_pjf0dfa6x1.woff') format('woff'),
    url('//at.alicdn.com/t/font_844055_pjf0dfa6x1.ttf') format('truetype'),
    url('//at.alicdn.com/t/font_844055_pjf0dfa6x1.svg#iconfont') format('svg');
  }
  .iconfont{
    font-family:"iconfont" !important;
    font-size:23px;font-style:normal;
    -webkit-font-smoothing: antialiased;
    -webkit-text-stroke-width: 0.2px;
    -moz-osx-font-smoothing: grayscale;
  }
 .message{
    position: absolute;
    height: 16px;
    font-size: 12px;
    line-height: 16px;
    text-align: center;
    padding:0 3px;
    background: red;
    color: #ffffff;
    border-radius: 8px;
    top:4px;
    right:3px;
    max-width: 40px;
    overflow: hidden;
  }
 .unread{
    position: absolute;
    top:62px;
    left: -16px;
     border-radius: 4px;
     padding: 10px;
     font-size: 12px;
     line-height: 1.2;
     width: 92px;
     word-wrap: break-word;
     background: #303133;
     color: #fff;
     z-index:99
  }
 .unread::after {
    content: '';
    width: 0;
    height: 0;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-bottom: 8px solid #303133;
    position: absolute;
    top: -6px;
    left: 28%;
  }
 .meslist{
    position: absolute;
    top: 52px;
    left: -142px;
   z-index: 100;
    width: 334px;
    /*display: none;*/
    padding: 20px 22px;
    padding-bottom: 50px;
    background-color: #fff;
    border-radius: 4px;
    box-shadow: 5px 7px 1px rgba(0,0,0,.1);
  }
 .meslist::after {
    content: '';
    width: 0;
    height: 0;
    border-left: 12px solid transparent;
    border-right: 12px solid transparent;
    border-bottom: 10px solid white;
    position: absolute;
    top: -10px;
    left: 45%;
  }
  .meslist .l-bottom{
    position: absolute;
    bottom: 0;
    left: 0;
    font-size: 14px;
    width: 100%;
    height: 32px;
    line-height: 32px;
    padding-right: 20px;
    color: #0DB2D8;
    text-align: right;
    border-top: 1px solid #e6e6e6;
  }
  .meslist .mes-item{
    cursor: pointer;
    font-size: 12px;
    line-height: 28px;
    border-bottom: 1px solid #E8E8E8;
  }
  .meslist .mes-item span{
    color: #0DB2D8;
  }
  .meslist .mes-item:last-child{
    border-bottom: none;
  }
  .navbar-custom-menu .nav{
    font-size: 14px;
    height: 100%;
    margin: 0 ;
  }
  .tongz{
    display: block;
    width: 24px;
    height: 24px;
    background: url("data:image/png;base64,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") no-repeat center;
    background-size: 100% 100%;
    margin-top:13px;
  }
</style>
