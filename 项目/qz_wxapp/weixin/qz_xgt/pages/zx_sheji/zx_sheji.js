// pages/zuangxsj/zuangxsj.js
var data = {

    "videoList": [
        {
            "image": "http://m.qizuang.com/assets/mobile/zb/images/sheji1.png",
            "tupian": "http://m.qizuang.com/assets/mobile/zb/images/case1.png",
        },
        {
            "image": "http://m.qizuang.com/assets/mobile/zb/images/sheji2.png",
            "tupian": "http://m.qizuang.com/assets/mobile/zb/images/case2.png",
        },
        {
            "image": "http://m.qizuang.com/assets/mobile/zb/images/sheji3.png",
            "tupian": "http://m.qizuang.com/assets/mobile/zb/images/case3.png",
        },
        {
            "image": "http://m.qizuang.com/assets/mobile/zb/images/sheji4.png",
            "tupian": "http://m.qizuang.com/assets/mobile/zb/images/case4.png",
        },
        {
            "image": "http://m.qizuang.com/assets/mobile/zb/images/sheji5.png",
            "tupian": "http://m.qizuang.com/assets/mobile/zb/images/case5.png",
        }
    ],

}
//显示带取消按钮的消息提示框
function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
    wx.showModal({
        title: title,
        content: content,
        showCancel: false,
        success: function (res) {
            if (res.confirm) {
                confirm();
            }
        }
    });
}
let app = getApp();
let apiUrl = app.getApiUrl();
// let imgUrl = app.getImgUrl();
Page({
    /**
     * 页面的初始数据
     */
    data: {
        indicatorDots: false,
        autoplay: false,
        interval: 5000,
        duration: 1000,
        infoList: data.videoList,
        topImg: data.videoList[0].img,
        topGanwu: data.videoList[0].ganwu,
        topName: data.videoList[0].name,
        topGingyan: data.videoList[0].jingyan,
        topTupian: data.videoList[0].tupian,
        topImage: data.videoList[0].image,
        circular: true,
        inputphone: '',
        inputfangan: '',
        mji: "",
        prev: [],
        city: [],
        area: [],
        prevIndex: '0',
        cityIndex: '0',
        areaIndex: '0',
        valuecity: null,
        json: [],
        isHideCity: true,
        selectText: '',
        xzcity: '请选择城市',
        prevCityAreaId: '',
        src:'',
        valuecity:[],
        val:[]

    },

    EventHandle: function (event) {
        var count = event.detail.current;
        this.data.topImg = data.videoList[count].img
        this.data.topName = data.videoList[count].name
        this.data.topGanwu = data.videoList[count].ganwu
        this.data.topGingyan = data.videoList[count].jingyan
        this.data.topImage = data.videoList[count].image
        this.data.topTupian = data.videoList[count].tupian
        this.setData({ topName: this.data.topName })
        this.setData({ topImg: this.data.topImg })
        this.setData({ topGanwu: this.data.topGanwu })
        this.setData({ topGingyan: this.data.topGingyan })
        this.setData({ topImage: this.data.topImage })
        this.setData({ topTupian: this.data.topTupian })
    },
    tobaojia: function () {
        wx.navigateTo({
            url: '../zx_baojia/zx_baojia'
        })
    },
    // 装修设计表单提交1
    formSubmit: function (e) {
        let that = this;
        var city = this.data.prevCityAreaId;
        var bjmj = e.currentTarget.dataset.mianji;
        var tel = e.currentTarget.dataset.phone;
        var xiaoqu = e.currentTarget.dataset.fangan;
        if (city.length < 1) {
            that.setData({
              xzcity: '请选择城市',
            })
            alertViewWithCancel("提示", "请选择您的所在地区", function () {
                // console.log("点击确定按钮");
            });
            return;
        } else {
            that.setData({
                xzcity: '',
            })
        }
        if (bjmj.length < 1) {
            alertViewWithCancel("提示", "请输入房屋面积", function () {
                // console.log("点击确定按钮");
            });
            return;
        } else if (isNaN(bjmj)) {
            alertViewWithCancel("提示", "请输入正确的房屋面积，仅限数字", function () {
                
            });
            return;
        }
        if (xiaoqu.length < 1) {
            alertViewWithCancel("提示", "请输入您的小区名称", function () {
                // console.log("点击确定按钮");
            });
            return;
        }

        if (tel.length < 1) {
            alertViewWithCancel("提示", "请输入手机号码", function () {
                // console.log("点击确定按钮");
            });
            return;
        } else {
            var reg = new RegExp("^(13|14|15|17|18)[0-9]{9}$");
            if (!reg.test(tel)) {
                alertViewWithCancel("提示", "请输入正确的手机号码", function () {
                });
                // console.log(" ^_^!");
                return false;
            }
        }
        // c

        if(that.data.src){
            wx.request({
                url: apiUrl + '/zxjt/submit_order_v2/?src=' + that.data.src,
                data: {
                    mianji: bjmj,
                    tel: tel,
                    cs: city,
                    xiaoqu: xiaoqu,
                },
                header: {
                    'content-type': 'application/x-www-form-urlencoded'
                },
                method: "POST",
                success: function (res) {
                    alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () {

                    });
                }
            });
        }else{

            wx.request({
                url: apiUrl + '/zxjt/submit_order_v2/?src=' + app.globalData.sourceMark,
                data: {
                    mianji: bjmj,
                    tel: tel,
                    cs: city,
                    xiaoqu: xiaoqu,
                },
                header: {
                    'content-type': 'application/x-www-form-urlencoded'
                },
                method: "POST",
                success: function (res) {
                    alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () {

                    });
                }
            });
        }
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        var that = this;
        // 获取页面来源src
        if (options.src) {
            that.setData({ src: options.src });
            app.setNewStorage('src', options.src, 86400);
        } else {
            if (app.getNewStorage('src')) {
                that.setData({ src: app.getNewStorage('src') });
            } else {
                that.setData({ src: 'xcx-all' });
            }
        }

    },
    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function () {

    },
    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function () {
        let that = this;
        let json = [];
        let prevJson = [];
        let cityJson = [];
        let areaJson = [];
        let cityUrl;

        // 获取缓存城市数据
        wx.getStorage({
            key: 'cityJson',
            success: function (res) {
                let cityJsonNew = res.data;
                that.setData({ prev: cityJsonNew.prev, city: cityJsonNew.city, area: cityJsonNew.area });
                //   that.setData({ json: cityJsonNew.json })
            },
            // 获取缓存失败
            fail: function () {
                // ajax请求数据并且数据本地缓存存储
                wx.request({
                    url: apiUrl + '/zxjt/getcityurl',
                    header: {
                        'content-type': 'application/json'
                    },
                    success: function (res) {
                        cityUrl = res.data.data.replace("/common/js", "");
                        let cityUrlArr = cityUrl.split(':');
                        let host = cityUrlArr[1].split('.');
                        host[0] = host[0] + 's';
                        cityUrlArr[1] = host.join('.');
                        let cityUrlStr = cityUrlArr[0] + ':' + cityUrlArr[1]
                        wx.request({
                            url: cityUrlStr, // + 'common/js/rlpca20170721143503.js',
                            header: {
                                'content-type': 'application/json'
                            },
                            success: function (res) {
                                let str = res.data.replace("var rlpca = ", "");
                                json = JSON.parse(str), prevJson = [], cityJson = [], areaJson = [];
                                json.shift();
                                // 删除空省份/城市/区域
                                for (let i = 0; i < json.length; i++) {
                                    json[i].children.shift()
                                    for (var j = 0; j < json[i].children.length; j++) {
                                        json[i].children[j].children.shift()
                                    }
                                };
                                // 筛选省份名称+id
                                for (let i = 0; i < json.length; i++) {
                                    prevJson.push({ id: json[i].id, text: json[i].text });
                                }
                                // 筛选第一省的第一个城市名称+id
                                for (let j = 0; j < json[0].children.length; j++) {
                                    cityJson.push({ id: json[0].children[j].id, text: json[0].children[j].text })
                                }
                                // 筛选第一省/第一个城市/第一个区域名称+id
                                for (let k = 0; k < json[0].children[0].children.length; k++) {
                                    areaJson.push({ id: json[0].children[0].children[k].id, text: json[0].children[0].children[k].text })
                                }
                                that.setData({ prev: prevJson, city: cityJson, area: areaJson, json: json });
                                wx.setStorage({
                                    key: 'cityJson',
                                    data: { prev: prevJson, city: cityJson, area: areaJson, json: json },
                                })
                            }
                        })
                    }
                })
            }
        });
    },

    /**
     * 生命周期函数--监听页面隐藏
     */
    onHide: function () {
        this.setData({ isHideCity: true })
    },

    /**
     * 生命周期函数--监听页面卸载
     */
    onUnload: function () {

    },

    /**
     * 页面相关事件处理函数--监听用户下拉动作
     */
    onPullDownRefresh: function () {
        wx.stopPullDownRefresh()
    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {

    },

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function () {

    },
    Mianji: function (e) {
        this.setData({ mji: e.detail.value })
        if (parseInt(e.detail.value) > 10000) {
            alertViewWithCancel("提示", "请输入小于10000的数字", function () { });
        }
    },
    inputPhone: function (e) {
        this.setData({ inputphone: e.detail.value })
    },
    inputFangan: function (e) {
        this.setData({ inputfangan: e.detail.value })
    },
    // 城市选择滑动
    bindChange: function (e) {
        let that = this;
        let cityJson = [];
        let areaJson = [];
        const val = e.detail.value;
        let oldVal = that.data.val;
        that.setData({ val: val })
        // let json = that.data.json;
        let prev = val[0];
        let city = val[1];
        let area = val[2];
        wx.getStorage({
            key: 'cityJson',
            success: function (res) {
                let json = res.data.json
                // 滑动省份获取城市
                if (oldVal[0] != prev && oldVal[1] == city && oldVal[2] == area) {
                    city = 0; area = 0;
                    that.setData({ valuecity: [prev, 0, 0] })
                } else if (oldVal[0] == prev && oldVal[1] != city && oldVal[2] == area) {
                    area = 0;
                    that.setData({ valuecity: [prev, city, 0] })
                } else if (oldVal[0] == prev && oldVal[1] == city && oldVal[2] != area) {

                }
                for (let j = 0; j < json[prev].children.length; j++) {
                    cityJson.push({ id: json[prev].children[j].id, text: json[prev].children[j].text });
                }
                // 滑动城市获取区域
                for (let k = 0; k < json[prev].children[city].children.length; k++) {
                    areaJson.push({ id: json[prev].children[city].children[k].id, text: json[prev].children[city].children[k].text });
                }
                that.setData({ city: cityJson, area: areaJson, prevIndex: prev, cityIndex: city, areaIndex: area })
            }
        });
    },
    closebtn: function () {
        this.setData({ isHideCity: true });
    },
    okbtn: function () {
        let that = this;
        let prevInfo = that.data.prev;
        let cityInfo = that.data.city;
        let areaInfo = that.data.area;

        let prevId = prevInfo[that.data.prevIndex].id
        let cityId = cityInfo[that.data.cityIndex].id
        let areaId = areaInfo[that.data.areaIndex].id

        let prevText = prevInfo[that.data.prevIndex].text
        let cityText = cityInfo[that.data.cityIndex].text
        let areaText = areaInfo[that.data.areaIndex].text

        let prevCityAreaId = prevId + ',' + cityId + ',' + areaId
        let selectText = prevText + ' ' + cityText + ' ' + areaText;
        this.setData({ xzcity: '',isHideCity: true, selectText: selectText, prevCityAreaId: prevCityAreaId });
    },
    selectHandle: function () {
        this.setData({ isHideCity: false })
    }
})