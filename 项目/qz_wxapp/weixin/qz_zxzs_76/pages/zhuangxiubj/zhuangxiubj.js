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
        xzcity: '请选择城市',
        prevCityAreaId: '',
        orderid: '',
        num:'52800',
        lingNum:'00000000000',
        src:"",
        inputTxt:'',
        val:[],
        valuecity:null
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
        let that = this,
            json = [],
            prevJson = [],
            cityJson = [],
            areaJson = [],
            cityUrl;
        // 获取页面来源src
        if (options.src){
            that.setData({ src: options.src });
            app.setNewStorage('src', options.src, 86400);
        }else{
            if (app.getNewStorage('src')){
                that.setData({ src: app.getNewStorage('src') });
            }else{
                that.setData({ src: app.globalData.sourceMark });
            }
        }
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
     * 生命周期函数--监听页面隐藏
     */
    onHide: function () {
        this.setData({ isHideCity: true });
    },

    /**
     * 页面相关事件处理函数--监听用户下拉动作
     */
    onPullDownRefresh: function () {
        wx.stopPullDownRefresh()
    },
    boda () {
        wx.makePhoneCall({
            phoneNumber: '4008659600',
            success: function () {
                // console.log('成功拨打电话')
            }
        })
    },
    ljjsbjff (e) {
        let that = this;
        var bjmj = e.currentTarget.dataset.mianji || that.data.mji;
        var bjxq = e.currentTarget.dataset.xiaoqu || that.data.xqu;
        var tel = e.currentTarget.dataset.tel || that.data.tel;
        var city = that.data.selectText;
        if (city.length < 1) {
            that.setData({
                xzcity: '请选择城市',
            })
            alertViewWithCancel("提示", "请选择您的地区", function () {
                // console.log("点击确定按钮");
            });
            return;
        } else {
            that.setData({
                xzcity: '',
            })
        }
        if (!bjmj || bjmj===" ") {
            alertViewWithCancel("提示", "请输入面积", function () {
                // console.log("点击确定按钮");
            });
            return;
        } else {
            if (bjmj < 1 || isNaN(bjmj)) {
                alertViewWithCancel("提示", "请输入正确房屋面积，仅限数字", function () {
                    // console.log("点击确定按钮");
                });
                return;
            }
        }
        if (bjxq.length < 1) {
            alertViewWithCancel("提示", "请输入小区", function () {
                // console.log("点击确定按钮");
            });
            return;
        }
        if (tel.length < 1) {
            alertViewWithCancel("提示", "请输入您的手机号码", function () {
                // console.log("点击确定按钮");
            });
            return;
        } else {
            var reg = new RegExp("^(13|14|15|17|18)[0-9]{9}$");
            if (!reg.test(tel)) {
                alertViewWithCancel("提示", "请填写正确的手机号码", function () {
                });
                // console.log(" ^_^!");
                return false;
            }
        }
        wx.request({
            url: apiUrl + '/zxjt/submit_order/?src=' + that.data.src,
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
            success (res) {
                that.zxbjxq(res.data.data.orderid);
            }
        });
    },
    mianji (e) {
        this.setData({
            mji: e.detail.value
        });
        if (parseInt(e.detail.value) > 10000) {
            alertViewWithCancel("提示", "请输入小于10000的数字", function () { });
        }
    },
    Xiaoqu (e) {
        this.setData({
            xqu: e.detail.value
        });
    },
    Phone (e) {
        this.setData({
            tel: e.detail.value
        });
    },
    // 城市选择滑动
    bindChange (e) {
        let that = this;
        let cityJson = [];
        let areaJson = [];
        let oldVal = that.data.val;
        let val = e.detail.value;
        that.setData({ val: val });
        let prev = val[0];
        let city = val[1];
        let area = val[2];
            
        wx.getStorage({
            key: 'cityJson',
            success: function (res) {
                let json = res.data.json;
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
                that.setData({ city: cityJson, area: areaJson, prevIndex: prev, cityIndex: city, areaIndex: area });
            }
        });
    },
    closebtn () {
        this.setData({ isHideCity: true });
    },
    okbtn () {
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
        this.setData({ xzcity: '',isHideCity: true, selectText: selectText, prevCityAreaId: prevCityAreaId });

    },
    selectHandle () {
        this.setData({ isHideCity: false });
    }
})