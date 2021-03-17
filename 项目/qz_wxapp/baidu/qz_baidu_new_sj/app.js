import CONFIG_INFO from './utils/config.js'
import validate from './utils/validate.js'
App({
    onLaunch(options) {
        if(options.query.src){
            this.globalData.sourceMark = options.query.src;
        } else {
            this.globalData.sourceMark = CONFIG_INFO.CHANNEL_FLAG
        }
    },
    onShow(options) {
    },
    onHide() {
    },
    config:CONFIG_INFO,
    checkFun: validate,
    globalData: {
        sourceMark:''
    },
    getMyPosition(fn) {
        let that = this
        swan.getLocation({
            success:function(res){
               fn(res.city)
            },
            fail:function(res){
               fn('苏州')
            }
        })
    },
    // 设置缓存
    setStorage:function(obj){
        swan.setStorage({
            key: obj.key,
            data: {
                data:obj.data,
                startTime:new Date().getTime()
            }
        });
    },
    // 取出缓存
    getStorage:function(obj){
        swan.getStorage({
            key:obj.key,
            success:function(res){
                let startTime = res.data.startTime
                let nowTime = new Date().getTime()
                let time = CONFIG_INFO.CACH_TIME
                if(time>nowTime-startTime){
                    obj.success({
                        code:1,
                        data:res.data
                    })
                }else{
                    swan.removeStorage({
                        key: 'key',
                        success: function (res) {
                            obj.success({
                                code:0
                            })
                        }
                    })
                }
            },
            fail:function(){
                obj.success({
                    code:0
                })
            }
        })
    }
});
