/**
 * 小程序配置文件
 */

// API接口
var host_api = 'https://appapi.qizuang.com';
var host_img = 'https://staticqns.qizuang.com/';

var config = {
  service: {
    host_api: host_api,
    host_img: host_img,
    appid: 'wxb4467e997279691e', //① 修改appid
    xcxtitle: '装修攻略',
    jubuGonglueType: '',
    jubuxgtType: '8',
    jubusyxgtpx: 'i,a,b,h,c,t,e',
    sourceMark: 'xcx-zxgl'
  }
};
//console.log(config);

module.exports = config;