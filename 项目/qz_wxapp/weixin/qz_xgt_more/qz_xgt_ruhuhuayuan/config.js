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
        appid: 'wx2ad96420fc3befa0', //① 修改appid
        xcxtitle: '入户花园装修效果图',
        jubuxgtType: 'o',
        jubusyxgtpx: 'i,a,b,h,c,t,e',
        sourceMark: 'xcx-all'
    }
};
//console.log(config);

module.exports = config;