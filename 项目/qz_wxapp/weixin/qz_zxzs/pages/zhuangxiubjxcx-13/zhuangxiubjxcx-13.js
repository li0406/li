// pages/zuangxbj/zuangxbj.js
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

// 获取随机数的方法
function GetRandomNum(Min, Max) {
    var Range = Max - Min;
    var Rand = Math.random();
    return (Min + Math.round(Rand * Range));
}

Page({
    /**
     * 页面的初始数据
     */
    data: {
        mji: "",
        xqu: "",
        tel: "",
        countys: [],
        county: '',
        condition: false,
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
        orderid: '',
        num:'52800',
        lingNum:'00000000000'

    },
    zxbjxq: function (e) {
        wx.navigateTo({
            url: '../zuangxbjxq/zuangxbjxq?orderid=' + e,
        });
    },

    bindRegionChange: function (e) {
        // console.log('picker发送选择改变，携带值为', e.detail.value)
        this.setData({
            region: e.detail.value
        });

    },
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
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
                                });
                            }
                        });
                    }
                });
            }
        });
        // 随机数
        let timer = setInterval(function () {
            let getNum = GetRandomNum(30000, 120000);
            if (getNum>99999){
                that.setData({ lingNum: '0000000000', num: getNum });
            }else{
                that.setData({ lingNum: '00000000000', num: getNum });
            }
        }, 400);
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

    },

    /**
     * 生命周期函数--监听页面隐藏
     */
    onHide: function () {
        this.setData({ isHideCity: true });
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
    boda: function () {
        wx.makePhoneCall({
            phoneNumber: '4008659600',
            success: function () {
                // console.log('成功拨打电话')

            }
        })
    },
    ljjsbjff: function (e) {
        let that = this;
        var bjmj = e.currentTarget.dataset.mianji
        var bjxq = e.currentTarget.dataset.xiaoqu
        var tel = e.currentTarget.dataset.tel
        var city = that.data.selectText;
        if (city.length < 1) {
            alertViewWithCancel("提示", "请选择您的地区", function () {
                console.log("点击确定按钮");
            });
            return;
        }
        if (bjmj.length < 1) {
            alertViewWithCancel("提示", "请输入面积", function () {
                console.log("点击确定按钮");
            });
            return;
        } else {
            if (isNaN(bjmj)) {
                alertViewWithCancel("提示", "请输入正确的面积", function () {
                    console.log("点击确定按钮");
                });
                return;
            }
        }
        if (bjxq.length < 1) {
            alertViewWithCancel("提示", "请输入小区", function () {
                console.log("点击确定按钮");
            });
            return;
        }
        if (tel.length < 1) {
            alertViewWithCancel("提示", "请输入您的手机号码", function () {
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
        }
        wx.request({
            url: apiUrl + '/zxjt/submit_order/?src=xcx-13',
            data: {
                cs: that.data.prevCityAreaId,
                mianji: bjmj,
                xiaoqu: bjxq,
                tel: tel
            },
            header: {
                'content-type': 'application/x-www-form-urlencoded'
            },
            method: "POST",
            success: function (res) {
                that.zxbjxq(res.data.data.orderid);
            }
        });
    },
    Mianji: function (e) {
        // console.log(e)
        this.setData({
            mji: e.detail.value
        });
    },
    Xiaoqu: function (e) {
        // console.log(e)
        this.setData({
            xqu: e.detail.value
        });
    },
    Phone: function (e) {
        this.setData({
            tel: e.detail.value
        });
    },
    // 城市选择滑动
    bindChange: function (e) {
        let that = this,
            cityJson = [],
            areaJson = [],

            val = e.detail.value,

            prev = val[0],
            city = val[1],
            area = val[2];
        wx.getStorage({
            key: 'cityJson',
            success: function (res) {
                let json = res.data.json;
                // 滑动省份获取城市
                for (let j = 0; j < json[prev].children.length; j++) {
                    cityJson.push({ id: json[prev].children[j].id, text: json[prev].children[j].text });
                }
                // 滑动城市获取区域
                for (let k = 0; k < json[prev].children[city].children.length; k++) {
                    areaJson.push({ id: json[prev].children[city].children[k].id, text: json[prev].children[city].children[k].text });
                }
                that.setData({ city: cityJson, area: areaJson, prevIndex: prev, cityIndex: city, areaIndex: area });
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

        let prevId = prevInfo[that.data.prevIndex].id;
        let cityId = cityInfo[that.data.cityIndex].id;
        let areaId = areaInfo[that.data.areaIndex].id;
        
        let prevText = prevInfo[that.data.prevIndex].text;
        let cityText = cityInfo[that.data.cityIndex].text;
        let areaText = areaInfo[that.data.areaIndex].text;

        let prevCityAreaId = prevId + ',' + cityId + ',' + areaId;
        let selectText = prevText + ' ' + cityText + ' ' + areaText;
        this.setData({ isHideCity: true, selectText: selectText, prevCityAreaId: prevCityAreaId });

    },
    selectHandle: function () {
        this.setData({ isHideCity: false });
    }
})