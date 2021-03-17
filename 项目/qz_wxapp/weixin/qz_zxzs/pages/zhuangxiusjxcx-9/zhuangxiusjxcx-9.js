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
        infoList: data.videoList,
        topImg: data.videoList[0].img,
        topGanwu: data.videoList[0].ganwu,
        topName: data.videoList[0].name,
        topGingyan: data.videoList[0].jingyan,
        topTupian: data.videoList[0].tupian,
        topImage: data.videoList[0].image,
        circular: true,
        inputname: '',
        inputphone: '',
        inputfangan: '',
        inputname2: '',
        inputphone2: '',
        inputfangan2: '',
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
        prevCityAreaId: '',
        tbsznumber: 200,
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
    formSubmit: function (e) {
        console.log("提交表单");
        console.log(e);
        var city = this.data.selectText;
        var name = e.currentTarget.dataset.username;
        var tel = e.currentTarget.dataset.phone;
        var xiaoqu = e.currentTarget.dataset.fangan;

        if (name.length < 1) {
            alertViewWithCancel("提示", "请输入您的称呼", function () {
                console.log("点击确定按钮");
            });
            return;
        }

        if (tel.length < 1) {
            alertViewWithCancel("提示", "请输入手机号", function () {
                console.log("点击确定按钮");
            });
            return;
        } else {
            var reg = new RegExp("^(13|14|15|17|18)[0-9]{9}$");
            if (!reg.test(tel)) {
                alertViewWithCancel("提示", "请填写正确的手机号码", function () {
                });
                console.log(" ^_^!");
                return false;
            }
        } if (city.length < 1) {
            alertViewWithCancel("提示", "请选择您的所在地区", function () {
                console.log("点击确定按钮");
            });
            return;
        }
        // c
        if (xiaoqu.length < 1) {
            alertViewWithCancel("提示", "请输入您的小区", function () {
                console.log("点击确定按钮");
            });
            return;
        }
        wx.request({
            url: apiUrl + '/zxjt/submit_order/?src=xcx-9',
            data: {
                name: name,
                tel: tel,
                cs: city,
                xiaoqu: xiaoqu,
            },
            header: {
                'content-type': 'application/x-www-form-urlencoded'
            },
            method: "POST",
            success: function (res) {
                // console.log(res)
                alertViewWithCancel("提示", "提交成功，请注意接听电话", function () {
                    console.log("点击确定按钮");
                });
            }
        })

    },



    formSubmit2: function (e) {
        console.log("提交表单");
        var city = this.data.selectText;
        var name = e.currentTarget.dataset.username;
        var tel = e.currentTarget.dataset.phone;
        var xiaoqu = e.currentTarget.dataset.fangan;

        if (name.length < 1) {
            alertViewWithCancel("提示", "请输入您的称呼", function () {
                console.log("点击确定按钮");
            });
            return;
        }

        if (tel.length < 1) {
            alertViewWithCancel("提示", "请输入手机号", function () {
                console.log("点击确定按钮");
            });
            return;
        } else {
            var reg = new RegExp("^(13|14|15|17|18)[0-9]{9}$");
            if (!reg.test(tel)) {
                alertViewWithCancel("提示", "请填写正确的手机号码", function () {
                });
                console.log(" ^_^!");
                return false;
            }
        } if (city.length < 1) {
            alertViewWithCancel("提示", "请选择您的所在地区", function () {
                console.log("点击确定按钮");
            });
            return;
        }
        if (xiaoqu.length < 1) {
            alertViewWithCancel("提示", "请输入您的小区", function () {
                console.log("点击确定按钮");
            });
            return;
        }

        wx.request({
            url: apiUrl + '/zxjt/submit_order/?src=xcx-9',
            data: {
                name: name,
                tel: tel,
                area: city,
                xiaoqu: xiaoqu,
            },
            header: {
                'content-type': 'application/x-www-form-urlencoded'
            },
            method: "POST",
            success: function (res) {
                alertViewWithCancel("提示", "提交成功，请注意接听电话", function () {
                    // console.log("点击确定按钮");
                });
            }
        })
    },

    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function () {

    },
    onLoad: function (options) {
        var that = this;
        that.setData({
            tbsznumber: parseInt(Math.random() * that.data.tbsznumber + 355)
        })

        var now = new Date(), hour = now.getHours()
        if (hour < 6) {
            that.setData({
                tbsznumber: 555
            })
        }
        else if (hour < 9) {
            that.setData({
                tbsznumber: 500
            })
        }
        else if (hour < 12) {
            that.setData({
                tbsznumber: 449
            })
        }
        else if (hour < 14) {
            that.setData({
                tbsznumber: 325
            })
        }
        else if (hour < 17) {
            that.setData({
                tbsznumber: 265
            })
        }
        else if (hour < 19) {
            that.setData({
                tbsznumber: 195
            })
        }
        else if (hour < 22) {
            that.setData({
                tbsznumber: 160
            })
        } else if (hour < 24) {
            that.setData({
                tbsznumber: 113
            })
        }
        else {
            return
        }


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
                        let cityUrlStr = cityUrlArr[0] + 's:' + cityUrlArr[1]
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
    inputName: function (e) {
        this.setData({ inputname: e.detail.value })
    },
    inputPhone: function (e) {
        this.setData({ inputphone: e.detail.value })
    },
    inputFangan: function (e) {
        this.setData({ inputfangan: e.detail.value })
    },
    inputName2: function (e) {
        this.setData({ inputname2: e.detail.value })
    },
    inputPhone2: function (e) {
        this.setData({ inputphone2: e.detail.value })
    },
    inputFangan2: function (e) {
        this.setData({ inputfangan2: e.detail.value })
    },
    // 城市选择滑动
    bindChange: function (e) {
        let that = this;
        let cityJson = [];
        let areaJson = [];
        const val = e.detail.value;
        // let json = that.data.json;
        let prev = val[0];
        let city = val[1];
        let area = val[2];
        wx.getStorage({
            key: 'cityJson',
            success: function (res) {
                let json = res.data.json
                // 滑动省份获取城市
                for (let j = 0; j < json[prev].children.length; j++) {
                    cityJson.push({ id: json[prev].children[j].id, text: json[prev].children[j].text })
                }
                // 滑动城市获取区域
                for (let k = 0; k < json[prev].children[city].children.length; k++) {
                    areaJson.push({ id: json[prev].children[city].children[k].id, text: json[prev].children[city].children[k].text })
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
        console.log(prevCityAreaId + selectText);
        this.setData({ isHideCity: true, selectText: selectText, prevCityAreaId: prevCityAreaId })

    },
    selectHandle: function () {
        this.setData({ isHideCity: false })
    }
})