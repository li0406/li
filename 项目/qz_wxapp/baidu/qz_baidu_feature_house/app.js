/**
 * @file app.js
 * @author swan
 */
/* globals swan */
import CONFIG_INFO from './utils/config.js'
import { getCityIdByName } from './utils/api.js';
App({
    onLaunch(options) {
        if(options.src){
            this.globalData.src = options.src
        }else{
            this.globalData.src = CONFIG_INFO.CHANNEL_FLAG
        }
    },
    onShow(options) {
        // do something when show
    },
    onHide() {
        // do something when hide
    },
    globalData:{
        cityId:'',
        cityName:'error',
        source: CONFIG_INFO.ORDER_SOURCE,
        src:''
    },
    getCityName(){
        let me = this
        swan.getLocation({
            // 默认为 wgs84 返回 gps 坐标，可选 gcj02 。
            type: 'gcj02',
            // 传入 true 会返回高度信息，获取高度需要较高精度且需要打开 gps ，会很耗时，默认没有用 gps。
            altitude: false,
            // 接口调用成功的回调函数，返回内容详见返回参数说明。
            success: res => {
                me.globalData.cityName = res.city
            },
            // 接口调用失败的回调函数
            fail: res => {
                me.globalData.cityName = 'error'
            },
            // 接口调用结束的回调函数（调用成功、失败都会执行）
            complete: res => {}
        });
    }
    
});
