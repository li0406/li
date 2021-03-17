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
        appid: 'wxe4480dd6f2c5f25f', //① 修改appid
        xcxtitle: '儿童房',
        jubuGonglueType: 'ertongfang',
        jubuxgtType: '12',
        jubusyxgtpx: 'd,a,b,i,h,c,t,',
        sourceMark: 'xcx-all'
    }
};
//console.log(config);

module.exports = config;