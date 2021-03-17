
/**
 * 获取url中指定参数
 * @param name
 * @param url
 * @returns {string}
 * @constructor
 */
function GetQueryStringRegExp(name, url) {
  var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)")
  var r = window.location.search.substr(1).match(reg)
  if (r!=null) return unescape(r[2]); return null
}
const data = ShareInstall.parseUrlParams()
new ShareInstall({
  appKey: 'K6BKK6BE66E6HB',
  onready: function () {
    var m = this, button = document.getElementById('downloadButton')
    var btnsStr = GetQueryStringRegExp('sharebtnid', location.href)
    if (button) {
      button.style.visibility = 'visible'
      button.onclick = function () {
        m.wakeupOrInstall()
      }
    }
    if (btnsStr) {
      var btns = btnsStr.split('_')
      for (var i=0; i<btns.length; i++) {
        // 需要判断节点是否已经存在，不然会报错
        if(document.getElementById(btns[i]) != null) {
          document.getElementById(btns[i]).onclick = function () {
            m.wakeupOrInstall()
          }
        }
      }
    }
  }
}, data)
