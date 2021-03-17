import { mobileSystem } from '../utils/globalFun'

export default {
  data () {
    return {
      isChrome: false,
      openIframe: null
    }
  },
  created () {
    this.isChrome = this.checkChrome()
    this.createIframe()
  },
  methods: {
    qzOpenTips () {
      const sys = mobileSystem().toLowerCase()
      const ua = navigator.userAgent.toLowerCase()
      // 判断是否是ios的qq内置浏览器和qq浏览器的区别
      if (sys === 'ios' && / QQ/i.test(navigator.userAgent)) {
        this.$parent.$parent.isWechat = true
        this.isWechat = true
        this.$parent.isWechat = true
      }
      // 判断是否是android的qq内置浏览器和qq浏览器的区别
      if (sys === 'android' && /MQQBrowser/i.test(navigator.userAgent) && /QQ/i.test((navigator.userAgent).split('MQQBrowser'))) {
        this.$parent.$parent.isWechat = true
        this.isWechat = true
        this.$parent.isWechat = true
      }
      // 判断是否是微信、微博内
      if (ua.indexOf('micromessenger') !== -1 || ua.indexOf('weibo') !== -1) {
        if (this.$parent.$parent.isWechat === undefined) {
          this.$parent.isWechat = true
          this.isWechat = true
        }
      }
    },
    qzOpenApp () {
      const sys = mobileSystem().toLowerCase()
      const ua = navigator.userAgent.toLowerCase()
      if (ua.indexOf('micromessenger') !== -1 || ua.indexOf('weibo') !== -1) {
        if (this.$parent.$parent.isWechat === undefined) {
          this.$parent.isWechat = true
          this.isWechat = true
          return
        } else {
          this.$parent.$parent.isWechat = true
          this.isWechat = true
          this.$parent.isWechat = true
          return
        }
      }
      // this.openAndroidApp()
      if (sys === 'android' || ua.indexOf('miui') > -1) {
        this.openAndroidApp()
      } else if (sys === 'ios') {
        this.openIOSApp()
      }
    },
    openAndroidApp () {
      if (this.isChrome) {
        // chrome浏览器用iframe打不开得直接去打开，算一个坑
        if (this.$route.path.indexOf('qzb') > -1) {
          const urlValue = window.sessionStorage.getItem('urlValue')
          window.location.href = 'qizuang-qz://' + urlValue
        } else {
          window.location.href = 'qizuang-qz://com.qizuang.qz'
        }
      } else {
        // 抛出你的scheme
        if (this.$route.path.indexOf('qzb') > -1) {
          const urlValue = window.sessionStorage.getItem('urlValue')
          window.location.href = 'qizuang-qz://' + urlValue
        } else {
          window.location.href = 'qizuang-qz://com.qizuang.qz'
        }
      }
      setTimeout(function () {
        window.location.href = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.qizuang.qz'
      }, 500)
    },
    openIOSApp () {
      // 打开APP
      // window.location.href = 'qizuang-qz://';
      const tempwindow = window.open('_blank') // 先打开页面
      if (this.$route.path.indexOf('qzb') > -1) {
        const urlValue = window.sessionStorage.getItem('urlValue')
        tempwindow.location = 'fkak9979://' + urlValue
      } else {
        tempwindow.location = 'fkak9979://' // 后更改页面地址
      }

      // 下载APP
      const loadDateTime = Date.now()
      setTimeout(function () {
        const timeOutDateTime = Date.now()
        if (timeOutDateTime - loadDateTime < 1000) {
          window.location.href = 'https://itunes.apple.com/cn/app/id1466041458'
        }
      }, 25)
    },
    checkChrome () {
      return window.navigator.userAgent.indexOf('Chrome') !== -1
    },
    createIframe () {
      if (!this.openIframe) {
        this.openIframe = document.createElement('iframe')
        this.openIframe.style.display = 'none'
        document.body.appendChild(this.openIframe)
      }
    }
  }
}
