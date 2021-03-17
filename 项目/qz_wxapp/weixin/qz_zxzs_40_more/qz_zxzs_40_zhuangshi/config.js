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
        appid: 'wx6b1f53a1b3696fee', //① 修改appid
        sourceMark: 'xcx-all'
    }
};

export { config as default };