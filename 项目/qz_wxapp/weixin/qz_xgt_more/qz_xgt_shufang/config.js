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
        appid: 'wx5da28deabb70bcbb', //① 修改appid
        xcxtitle: '书房装修效果图',
        jubuxgtType: 'j',
        jubusyxgtpx: 'i,a,b,h,c,t,e',
        sourceMark: 'xcx-all'
    }
};
//console.log(config);

module.exports = config;