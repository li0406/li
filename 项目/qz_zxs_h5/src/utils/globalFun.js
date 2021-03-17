const ORDER_SOURCES = {
  'zxs': {
    'sheji': 19011620,
    'baojia': 19011658,
    'luckday': 18121129
  },
  'qz': {
    'sheji': 19070153,
    'baojia': 19070117,
    'luckday': 19070140,
    'fuwu': 20071025,
    'gift': 20080318
  }
}
module.exports = {
  getSrc: function (obj, callback) {
    obj.$bridge.callNative('getChannelSrc', function (data) {
      callback(data)
    })
  },
  mobileSystem: function () {
    const u = navigator.userAgent
    const isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1 // android终端
    const isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/) // ios终端
    if (isAndroid) {
      return 'Android'
    } else if (isiOS) {
      return 'iOS'
    } else {
      return ''
    }
  },
  // 禁用浏览器默认功能
  disableDefault: function () {
    // QQ浏览器图片点击吊起问题
    document.addEventListener('click', (e) => {
      if (e.target.nodeName === 'IMG') {
        let domselect = e.target
        let arrayDom = []
        while (domselect.nodeName !== 'BODY') {
          arrayDom.push(domselect.nodeName)
          domselect = domselect.parentNode
        }
        if (arrayDom.indexOf('A') === -1) {
          e.preventDefault()
        }
      }
    })
    // 长按默认效果阻止
    document.oncontextmenu = function (e) {
      e.preventDefault()
    }
  },
  getIosVer () {
    const str = navigator.userAgent.toLowerCase()
    const ver = str.match(/cpu iphone os (.*?) like mac os/)
    if (!ver) {
      return ''
    } else {
      return ver[1].replace(/_/g, '.')
    }
  },
  setSource (parms, pageName) {
    let testMatch = '0'
    for (let i in ORDER_SOURCES) {
      testMatch = parms === i ? 1 : 0
    }
    if (testMatch === 1) {
      return ORDER_SOURCES[parms][pageName]
    }
    return ORDER_SOURCES['zxs'][pageName]
  }
}
