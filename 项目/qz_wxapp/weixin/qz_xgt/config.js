/**
 * 小程序配置文件
 */

// API接口
var host_api = 'https://wxapp.qizuang.com';
var host_img = 'https://staticqns.qizuang.com/';

var config = {
  service: {
    host_api: host_api,
    host_img: host_img,
    appid: 'wxc760fdfce2e0762e', //① 修改appid
    xcxtitle: '客厅装修图',
    jubuGonglueType: 'weiyu',
    jubuxgtType: 'a',
    jubusyxgtpx: 'i,a,b,h,c,t,e',
    sourceMark: 'xcx-all',
    bdMapKey: 'Y0glUX95DIaKDjqIEBGqC8tPb4c63SNs',
    qqMapKey: 'KGWBZ-HKQHP-QVGDL-VETAU-TWGP7-QBFOL',
  }
};
//console.log(config);

module.exports = config;