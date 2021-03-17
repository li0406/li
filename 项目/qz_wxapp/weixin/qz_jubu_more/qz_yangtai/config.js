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
        appid: 'wx90476f3de76daddb', //① 修改appid
        xcxtitle: '阳台',
        jubuGonglueType: 'yangtai',
        jubuxgtType: '9',
        jubusyxgtpx: 'c,i,a,b,h,t,e',
        sourceMark: 'xcx-all'
    }
};
//console.log(config);

module.exports = config;