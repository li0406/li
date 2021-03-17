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
        appid: 'wx6c8e4dd7c6d8f0da', //① 修改appid
        xcxtitle: '客厅装修效果图欣赏',
        jubuxgtType: 'a',
        jubusyxgtpx: 'i,a,b,h,c,t,e',
        sourceMark: 'xcx-all'
    }
};
//console.log(config);

module.exports = config;