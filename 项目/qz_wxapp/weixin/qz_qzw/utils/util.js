const formatTime = date => {
  const year = date.getFullYear()
  const month = date.getMonth() + 1
  const day = date.getDate()
  const hour = date.getHours()
  const minute = date.getMinutes()
  const second = date.getSeconds()

  return [year, month, day].map(formatNumber).join('/') + ' ' + [hour, minute, second].map(formatNumber).join(':')
}

const formatNumber = n => {
  n = n.toString()
  return n[1] ? n : '0' + n
}

function throttle(fn, gapTime) {
  if (gapTime == null || gapTime == undefined) {
    gapTime = 1500
  }

  let _lastTime = null

  // 返回新的函数
  return function() {
    let _nowTime = +new Date()
    if (_nowTime - _lastTime > gapTime || !_lastTime) {
      fn.apply(this, arguments) //将this和参数传给原函数
      _lastTime = _nowTime
    }
  }
}

function writePhotosAlbum(success) {
  isAuthorize('writePhotosAlbum', success)
}

function isAuthorize(scope, success) {
  wx.getSetting({
    success: res => {
      const status = res.authSetting[`scope.${scope}`];
      if (status != undefined) {
        /// 请求过授权
        status ? success() : wx.openSetting({
          success: res => {
            if (res.authSetting[`scope.${scope}`]) {
              success();
            }
          }
        });
      } else {
        /// 未请求授权
        wx.authorize({
          scope: `scope.${scope}`,
          success: res => {
            success();
          },
          fail: res => {
            wx.showToast({
              title: '请求授权失败',
              icon: 'none',
              mask: true
            })
          }
        })
      }
    }
  })
}

const path = function mathRandom() {
  var path = ''
  var randoms = parseInt(Math.random() * 4)
  switch (randoms) {
    case 0:
      path = 'https://staticqn.qizuang.com/custom/20201204/FvJcD2MWdAVjYAwNPAWjaRnLM5zi'
      break;
    case 1:
      path = 'https://staticqn.qizuang.com/custom/20201204/FvVRpUHpPaOCfx3MU49ge7jyPxme'
      break;
    case 2:
      path = 'https://staticqn.qizuang.com/custom/20201204/Fij1vnOefYhRlOOhcZDiLPImXRrG'
      break;
    case 3:
      path = 'https://staticqn.qizuang.com/custom/20201204/FplriUHf5JQgZp6eipcSaF5gtVhf'
      break;
  }
  return path
}

function matTime(number, format) {
  var formateArr = ['Y', 'M', 'D', 'h', 'm', 's'];
  var returnArr = [];
  var date = new Date(number * 1000);
  returnArr.push(date.getFullYear());
  returnArr.push(formatNumber(date.getMonth() + 1));
  returnArr.push(formatNumber(date.getDate()));
  returnArr.push(formatNumber(date.getHours()));
  returnArr.push(formatNumber(date.getMinutes()));
  returnArr.push(formatNumber(date.getSeconds()));
  for (var i in returnArr) {
    format = format.replace(formateArr[i], returnArr[i]);
  }
  return format;
}

module.exports = {
  formatTime: formatTime,
  throttle: throttle,
  writePhotosAlbum: writePhotosAlbum,
  path: path,
  matTime: matTime
}