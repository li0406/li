//index.js
//获取应用实例
const app = getApp()
let apiUrl = app.getApiUrl(),
    oImgUrl = app.getImgUrl();
function getLocalTime(nS) {
    return new Date(parseInt(nS) * 1000).toLocaleString().replace(/年|月/g, "-").replace(/日/g, " ").replace('/', '-').replace('/', '-').split(' ')[0];
}
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
Page({
    data: {
        imgUrl: oImgUrl,
        bannerList: [],
        indicatorDots: false,
        count :5,
        loadBool: true,
        isHideCity: true,
        selectText: '',
        selectTextDefault:'选择城市',
        prev: [],
        city: [],
        area: [],
        prevIndex: '0',
        cityIndex: '0',
        areaIndex: '0',
        popHide:true,
        personName:'',
        telNumber:'',
        src:'',
        isColor:false

    },
    onLoad(options){
        
        let that = this,
            json = [],
            prevJson = [],
            cityJson = [],
            areaJson = [],
            cityUrl,
            bannerList;
        // 获取页面来源src
        if (options.src) {
            that.setData({ src: options.src });
            app.setNewStorage('src', options.src, 86400);
        } else {
            if (app.getNewStorage('src')) {
                that.setData({ src: app.getNewStorage('src') });
            } else {
                that.setData({ src: app.globalData.sourceMark });
            }
        }
        
        let popState = app.getNewStorage('popState')||'';
        if (popState==="true") {
            this.setData({ popHide: false });
        }else{
            this.setData({ popHide: true });
        }
        
        app.getUserInfo((res)=>{
            wx.setStorage({
                key: 'userId',
                data: res.userId,
            });
        });
        /**
         * 获取首页banner数据
         */
        bannerList = app.getNewStorage('bannerList') || '';
        if (bannerList){
            that.setData({ bannerList: bannerList });
        }else{
            wx.request({
                url: apiUrl+'/appletcarousel/banner',
                header: {
                    'content-type': 'application/json'
                },
                success (res) {
                    that.setData({bannerList:res.data.bannerList});
                    app.setNewStorage('bannerList', res.data.bannerList, 60)
                }
            });
        }
        /**
         * 获取城市缓存数据
         */
        wx.getStorage({
            key: 'cityJson',
            success (res) {
                let cityJsonNew = res.data;
                that.setData({ prev: cityJsonNew.prev, city: cityJsonNew.city, area: cityJsonNew.area });
            },
            // 获取缓存失败
            fail () {
                // ajax请求数据并且数据本地缓存存储
                wx.request({
                    url: apiUrl + '/zxjt/getcityurl',
                    header: {
                        'content-type': 'application/json'
                    },
                    success (res) {
                        cityUrl = res.data.data.replace("/common/js", "");
                        let cityUrlArr = cityUrl.split(':');
                        let host = cityUrlArr[1].split('.');
                        host[0] = host[0] + 's';
                        cityUrlArr[1] = host.join('.');
                        let cityUrlStr = cityUrlArr[0] + 's:' + cityUrlArr[1] // s:
                        wx.request({
                            url: cityUrlStr, // + 'common/js/rlpca20170721143503.js',
                            header: {
                                'content-type': 'application/json'
                            },
                            success (res) {
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
                                that.setData({ prev: prevJson, city: cityJson, area: areaJson });
                                // 设置缓存
                                wx.setStorage({
                                    key: 'cityJson',
                                    data: { prev: prevJson, city: cityJson, area: areaJson, json: json },
                                });
                            }
                        })
                    }
                })
            }
        });
    },
    /**
     * 用户点击右上角分享
     */
    onShareAppMessage (res) {
        
    },
    selectHandle() {
        this.setData({ isHideCity: false });
    },
    /** 
     * 城市选择滑动
     */
    bindChange (e) {
        let that = this;
        let cityJson = [];
        let areaJson = [];
        let val = e.detail.value;
        let prev = val[0];
        let city = val[1];
        let area = val[2];
        wx.getStorage({
            key: 'cityJson',
            success (res) {
                let json = res.data.json
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
    closebtn() {
        this.setData({ isHideCity: true });
    },
    /**
     * 城市选择
     */
    okbtn() {
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
        this.setData({ isHideCity: true, isColor: true, selectText: selectText, prevCityAreaId: prevCityAreaId, areaId: areaId, serverVal: areaText, selectTextDefault:''});
    },
    changeIndicatorDots (e) {
        this.setData({
            indicatorDots: !this.data.indicatorDots
        })
    },
    changeAutoplay (e) {
        this.setData({
            autoplay: !this.data.autoplay
        })
    },
    intervalChange (e) {
        this.setData({
            interval: e.detail.value
        })
    },
    durationChange (e) {
        this.setData({
            duration: e.detail.value
        })
    },
    /**
     * 跳转到装修设计页面
     */
    toSheji(){
        wx.navigateTo({
          url: '../zhuangxiusj/zhuangxiusj'
        })
    },
    /**
     * 跳转到装修报价页面
     */
    toBaojia() {
        wx.navigateTo({
            url: '../zhuangxiubj/zhuangxiubj'
        })
    },
    /**
    * 跳转到装修攻略
    */
    toGonglue() {
      wx.navigateTo({
        url: '../zxgonglue/zxgonglue'
      })
    },
    /**
     * 跳转到装修公司列表页面
     */
    toCompany(){
        wx.navigateTo({
            url: '../des_company/des_company'
        })
    },
    toPage(e){
        let pageUrl = e.currentTarget.dataset.urlname;
        wx.reLaunch({
            url: '../' + pageUrl +'/'+pageUrl,
        });
    },
    /**
    * 弹窗消失
    */
    popHide(){
        this.setData({popHide:true});
    },
    /**
    * 立即计算跳转到报价页
    */
    toBaojiaPop(){
        this.setData({ popHide: true });
        wx.navigateTo({
            url: '../zhuangxiubj/zhuangxiubj'
        })
    },
    getName(e){
        this.setData({ personName: e.detail.value });
    },
    getPhone(e) {
        this.setData({ telNumber: e.detail.value });
    },
    getSheJi() {
        let regu = "^[a-zA-Z\u4e00-\u9fa5]+$";
        let re = new RegExp(regu);

        let that = this;
        if (that.data.selectText.length < 1) {
            that.setData({ selectTextDefault: '选择城市：'})
            alertViewWithCancel("提示", "请选择您的所在地区", function () { });
            return;
        } else {
            that.setData({ selectTextDefault: ''})
        }
        if (that.data.personName.length < 1) {
            alertViewWithCancel("提示", "请输入您的称呼", function () {
                that.setData({ boolName: true });
            });
            return;
        } else if (that.data.personName.search(re) == -1) {
            alertViewWithCancel("提示", "用户名不能为数字", function () {
                that.setData({ boolName: true });
            });
            return;
        }
        if (that.data.telNumber.length < 1) {
            alertViewWithCancel("提示", "请输入手机号", function () { });
            return;
        } else {
            var reg = new RegExp("^(13|14|15|17|18)[0-9]{9}$");
            if (!reg.test(that.data.telNumber)) {
                alertViewWithCancel("提示", "请填写正确的手机号码", function () { });
                return false;
            }
        }

        if (that.data.src) {
            wx.request({

                url: apiUrl + '/zxjt/submit_order/?src=' + that.data.src,
                data: {
                    name: that.data.personName,
                    tel: that.data.telNumber,
                    cs: that.data.selectText
                },
                header: {
                    'content-type': 'application/x-www-form-urlencoded'
                },
                method: "POST",
                success: function (res) {
                    alertViewWithCancel("提示", "提交成功，请注意接听电话", function () { });
                }
            });
        } else {
            wx.request({
                url: apiUrl + '/zxjt/submit_order/?src=xcx-0',
                data: {
                    name: that.data.personName,
                    tel: that.data.telNumber,
                    cs: that.data.prevCityAreaId
                },
                header: {
                    'content-type': 'application/x-www-form-urlencoded'
                },
                method: "POST",
                success: function (res) {
                    alertViewWithCancel("提示", "提交成功，请注意接听电话", function () { });
                }
            });
        }

    },
    toArticleList:function(e){
        let urlStr = e.currentTarget.dataset.urlstr,
            urlStrName = e.currentTarget.dataset.urlstrname;
        wx.navigateTo({
            url: '../zxgonglue_list/zxgonglue_list?urlstr=' + urlStr + '&urlstrname=' + urlStrName
        })
    },
    toGonglue(){
        wx.navigateTo({
            url: '../zxgonglue/zxgonglue'
        })
    }
})
