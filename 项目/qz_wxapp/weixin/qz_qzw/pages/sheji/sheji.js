// pages/sheji/sheji.js
var config = require('../../config');
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
        emptymianji: "",
        emptyxiaoqu: "",
        emptyphone: "",
        prev: [],
        city: [],

        prevIndex: '0',
        cityIndex: '0',

        valuecity: null,
        json: [],
        isHideCity: true,
        selectText: '',
        xzcity: '请选择城市',
        cityId: '',
        src: '',
        valuecity: [],
        val: [],
        checkValue: true,
        clickAble: true,
        autoGetPhone: true,
        imageUrl: ''
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
    kk2: function () {
        wx.navigateTo({
            url: '../zuangxbj/zuangxbj'
        })
    },
    // 装修设计表单提交1
    formSubmit: function (e) {
        let clickAble = this.data.clickAble;
        if (!clickAble) {
            alertViewWithCancel("提示", "请勿频繁操作", function () { });
            return false;
        }
        let that = this;
        var city = this.data.cityId;
        var bjmj = e.currentTarget.dataset.mianji;
        var tel = e.currentTarget.dataset.phone;
        var xiaoqu = e.currentTarget.dataset.fangan;
        var check = that.data.checkValue;
        if (city.length < 1) {
            that.setData({
                xzcity: '请选择城市',
            })
            alertViewWithCancel("提示", "请选择您的所在地区", function () {

            });
            return;
        } else {
            that.setData({
                xzcity: '',
            })
        }
        if (bjmj.length < 1) {
            alertViewWithCancel("提示", "请输入您的房屋面积", function () {
            });
            return;
        } else {
            var reg3 = new RegExp("^[1-9][0-9]{0,3}$");
            if (!reg3.test(bjmj)) {
                alertViewWithCancel("提示", "房屋面积请输入1-10000的数字", function () {
                    that.setData({
                        emptymianji: "",
                    })
                });
                return;
            }
        }
        if (xiaoqu.length < 1) {
            alertViewWithCancel("提示", "请输入您的小区", function () {
            });
            return;
        } else {
            var reg4 = /^\s*$/g;
            if (xiaoqu == "" || reg4.test(xiaoqu)) {
                alertViewWithCancel("提示", "小区不能为空", function () {
                    that.setData({
                        emptyxiaoqu: "",
                    })
                });
                return;
            }
        }

        if (tel.length < 1) {
            alertViewWithCancel("提示", "请输入手机号", function () {
            });
            return;
        } else {
            var reg = new RegExp("^(13|14|15||16|17|18|19)[0-9]{9}$");
            if (!reg.test(tel)) {
                alertViewWithCancel("提示", "请填写正确的手机号码", function () {
                    that.setData({
                        emptyphone: "",
                    })
                });
                return false;
            }
        }
        if (!check) {
            alertViewWithCancel("提示", "请勾选我已阅读并同意齐装网的《免责申明》！", function () {
            });
            return false;
        }
        that.setData({
            clickAble: false
        });
        setTimeout(function () {
            that.setData({
                clickAble: true
            })
        }, 5000)
        if (that.data.src) {
            wx.request({
                url: apiUrl + '/fb_order?src=' + that.data.src + '&gdt_vid=' + app.globalData.gdt_vid + "&aid=" + app.globalData.aid
                    + "&click_id=" + app.globalData.click_id,
                data: {
                    mianji: bjmj,
                    tel: tel,
                    cs: city,
                    xiaoqu: xiaoqu,
                    gdt_vid: app.globalData.gdt_vid,
                    aid: app.globalData.aid,
                    click_id: app.globalData.click_id,
                },
                header: {
                    'content-type': 'application/x-www-form-urlencoded'
                },
                method: "POST",
                success: function (res) {
                    if (res.data.status == 1) {
                        that.setData({
                            emptymianji: "",
                            emptyxiaoqu: "",
                            mji: "",
                            inputfangan: ""
                        });
                        app.globalData.personNum = parseInt(app.globalData.personNum) + 1;
                        alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () { });
                    } else {
                        alertViewWithCancel("提示", res.data.info, function () { });
                    }

                }
            });
        } else {

            wx.request({
                url: apiUrl + '/fb_order?src=' + app.globalData.sourceMark + '&gdt_vid=' + app.globalData.gdt_vid + "&aid=" + app.globalData.aid
                    + "&click_id=" + app.globalData.click_id,
                data: {
                    mianji: bjmj,
                    tel: tel,
                    cs: city,
                    xiaoqu: xiaoqu,
                    gdt_vid: app.globalData.gdt_vid,
                    aid: app.globalData.aid,
                    click_id: app.globalData.click_id,
                },
                header: {
                    'content-type': 'application/x-www-form-urlencoded'
                },
                method: "POST",
                success: function (res) {
                    if (res.data.status == 1) {
                        that.setData({
                            emptymianji: "",
                            emptyxiaoqu: ""
                        });
                        app.globalData.personNum = parseInt(app.globalData.personNum) + 1;
                        alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () { });
                    } else {
                        alertViewWithCancel("提示", res.data.info, function () { });
                    }

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
            that.setData({
                src: options.src
            });
        } else {
            that.setData({
                src: app.globalData.sourceMark
            });
        }
        var timestamp = Date.parse(new Date())
        that.setData({
            imageUrl: 'https://bj8-api.ad.jiguang.cn/v3/push/trk/h5track?jcid=' + app.globalData.gdt_vid + '&event=enter_page&product_id=204&ts=' + timestamp
        })
        // wx.getStorage({
        //   key: 'telInfo',
        //   success: function (res) {
        //     that.setData({
        //       autoGetPhone: true,
        //       tel: res.data.tel,
        //       emptyphone: res.data.tel,
        //       inputphone: res.data.tel
        //     })
        //   }
        // })
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

        let cityUrl;

        // 获取缓存城市数据
        wx.getStorage({
            key: 'cityJson',
            success: function (res) {
                let cityJsonNew = res.data;
                that.setData({ prev: cityJsonNew.prev, city: cityJsonNew.city });
                app.getMyPosition(cityJsonNew, that)
            },
            // 获取缓存失败
            fail: function () {
                wx.request({
                    url: apiUrl + '/city/allcity?level=2',
                    header: {
                        'content-type': 'application/json'
                    },
                    success: function (res) {
                        that.setData({ prev: res.data.prev, city: res.data.city, json: res.data.json });
                        app.getMyPosition(res.data, that)
                        wx.setStorage({
                            key: 'cityJson',
                            data: { prev: res.data.prev, city: res.data.city, json: res.data.json },
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
    onShareAppMessage: function (ops) {
        if (ops.from === 'button') {
            // 来自页面内转发按钮
        }
        return {
            title: '齐装网装修家居',
            path: 'pages/zhuangxiusj/zhuangxiusj',
            success: function (res) { },
            fail: function (res) { }
        }
    },
    Mianji: function (e) {
        this.setData({ mji: e.detail.value })

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

        const val = e.detail.value;
        let oldVal = that.data.val;
        that.setData({ val: val })
        // let json = that.data.json;
        let prev = val[0];
        let city = val[1];

        wx.getStorage({
            key: 'cityJson',
            success: function (res) {
                let json = res.data.json
                // 滑动省份获取城市
                if (oldVal[0] != prev && oldVal[1] == city) {
                    city = 0;
                    that.setData({ valuecity: [prev, 0, 0] })
                } else if (oldVal[0] == prev && oldVal[1] != city) {

                    that.setData({ valuecity: [prev, city, 0] })
                } else if (oldVal[0] == prev && oldVal[1] == city) {

                }
                for (let j = 0; j < json[prev].children.length; j++) {
                    cityJson.push({ id: json[prev].children[j].id, text: json[prev].children[j].text });
                }

                that.setData({ city: cityJson, prevIndex: prev, cityIndex: city })
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

        let prevId = ''
        let cityId = ''


        let prevText = ''
        let cityText = ''

        if (prevInfo && (prevInfo instanceof Array) && prevInfo.length > 0) {
            prevId = prevInfo[that.data.prevIndex].id
            prevText = prevInfo[that.data.prevIndex].text
        }

        if (cityInfo && (cityInfo instanceof Array) && cityInfo.length > 0) {
            cityId = cityInfo[that.data.cityIndex].id
            cityText = cityInfo[that.data.cityIndex].text
        }



        let prevCityAreaId = prevId + ',' + cityId;
        let selectText = prevText + ' ' + cityText;
        this.setData({ xzcity: '', isHideCity: true, selectText: selectText, cityId: cityId });
    },
    selectHandle: function () {
        this.setData({ isHideCity: false })
    },
    // 切换免责
    checkboxChange: function (e) {
        let that = this;
        if (that.data.checkValue == true) {
            that.setData({
                checkValue: false
            })
        } else {
            that.setData({
                checkValue: true
            })
        }
    },
    getPhoneNumber: function (e) {
        app.getPhoneAuto(e, this)
    }
})