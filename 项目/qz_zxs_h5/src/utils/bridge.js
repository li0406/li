import globalFun from './globalFun.js'
const callHandlers = [] // 调用app方法，端那边给
const registerHandlers = [] // 注册的回调
const bridge = {
  callNative (...args) {
    callHandlers.push(args)
  },
  registWebNew (...args) {
    registerHandlers.push(args)
  }
}
function setupIphoneWebViewJavascriptBridge (callback) {
  if (window.WebViewJavascriptBridge) {
    return callback(window.WebViewJavascriptBridge)
  }
  if (window.WVJBCallbacks) {
    return window.WVJBCallbacks.push(callback)
  }
  window.WVJBCallbacks = [callback]
  const WVJBIframe = document.createElement('iframe')
  WVJBIframe.style.display = 'none'
  WVJBIframe.src = 'wvjbscheme://__BRIDGE_LOADED__'
  document.documentElement.appendChild(WVJBIframe)
  setTimeout(() => { document.documentElement.removeChild(WVJBIframe) }, 0)
  return false
}

function connectAndroidWebViewJavascriptBridge (callback) {
  if (window.WebViewJavascriptBridge) {
    callback(window.WebViewJavascriptBridge)
  } else {
    document.addEventListener('WebViewJavascriptBridgeReady', () => {
      callback(window.WebViewJavascriptBridge)
    }, false)
  }
}

function exportBridge (bridgeObj) {
  bridgeObj.init && bridgeObj.init()
  if (bridgeObj) {
    bridge.callNative = bridgeObj.callHandler
    bridge.registWebNew = bridgeObj.registerHandler

    if (callHandlers.length) {
      callHandlers.forEach(call => {
        bridge.call(...call)
      })
    }
    if (registerHandlers.length) {
      registerHandlers.forEach(register => {
        bridge.register(...register)
      })
    }
  }
}
if (globalFun.mobileSystem() === 'iOS') {
  setupIphoneWebViewJavascriptBridge(exportBridge)
} else if (globalFun.mobileSystem() === 'Android') {
  connectAndroidWebViewJavascriptBridge(exportBridge)
}
export default bridge
