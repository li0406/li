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
        appid: 'wxb0beda7462298924', //① 修改appid
        xcxtitle: '吊顶装修效果图',
        jubuxgtType: 't',
        jubusyxgtpx: 'i,a,b,h,c,t,e',
        sourceMark: 'xcx-all'
    }
};
//console.log(config);

module.exports = config;