const app = getApp(),
        apiUrl = app.getApiUrl(),
        apid = app.getAPPid(),
        mpiUrl = 'https://mpapi.qizuang.com',
        oImgUrl = app.getImgUrl();

let config = require("../../config.js");

function alertViewWithCancel(title, content, confirm) {
    my.alert({
        title: title || "提示",
        content: content || "消息提示",
        success: function () {
            confirm();
        }
    });
}

Page({
  data: {
    isHide: [true, true, true],
    serviceVal: '服务区域',
    styleVal: '擅长风格',
    scaleVal: '公司规模',
    styleId : "",//风格id
    scaleId : "",//规模id
    areaId : "", //区县id
    itemHide: true,
    styleIndex : "",
    styleItemData : [],
    styleListData : [],
    //发单部分
    isHideCity : true,
    province: [],
    city: [],
    area: [],
    json : [],
    provinceIndex: '0',
    cityIndex: '0',
    areaIndex: '0',
    valuecity: [0, 0, 0],
    val:[],
    selectTextDefault : "",
    selectText : "",
    customerName : "",
    customerPhone : "",
    // 初始化参数
    src : "",
    userId : "",
    itemRed:[],
    itemRedOther:[],
    itemRedFG:'',
    itemRedGM:'',
    sjbool1: false,
    sjbool2: true,
    emptyCompany:true,
    companyList: [],// 装修公司的条目数据
    count:9,// 首次加载装修公司的条目,
    loadBool:true,
    oImgUrl : oImgUrl
  },
  openCitySelectBox : function(){
    this.setData({
      isHideCity : !this.data.isHideCity
    });
  },
  closebtn : function(){
    this.setData({
      isHideCity : true
    });
  },
  okbtn : function(){
    let that = this;
    let provinceInfo = that.data.province;
    let cityInfo = that.data.city;
    let areaInfo = that.data.area;

    let provinceId = provinceInfo[that.data.provinceIndex].id;
    let cityId = cityInfo[that.data.cityIndex].id;
    let areaId = areaInfo[that.data.areaIndex].id;

    let provinceText = provinceInfo[that.data.provinceIndex].text;
    let cityText = cityInfo[that.data.cityIndex].text;
    let areaText = areaInfo[that.data.areaIndex].text;

    let provinceCityAreaId = provinceId + ',' + cityId + ',' + areaId;
    let selectText = provinceText + ' ' + cityText + ' ' + areaText;
    this.setData({ 
      isHideCity: true, 
      isColor: true, 
      selectText: selectText, 
      provinceCityAreaId: provinceCityAreaId, 
      areaId: areaId, 
      serverVal: areaText, 
      selectTextDefault:'',
      serviceVal: areaText, 
      isHide: [true, true, true]
    });

    my.httpRequest({
        //url: apiUrl + '/appletcarousel/company?userid=' + that.data.userId,
        url: apiUrl + '/appletcarousel/company',
        data: {
            userid : that.data.userId,
            qx: areaId, 
            fg: that.data.styleId, 
            gm: that.data.scaleId,
            count:9
        },
        header: {
            'content-type': 'application/json'
        },
        success: function (res) {
            let arr = [],
                style = res.data.attribute.fengge,
                scale = res.data.attribute.guimo;
            arr[0] = [];
            arr[1] = style;
            arr[2] = scale;
            if (res.data.list.length <= 0) {
                that.setData({ emptyCompany: false });
            }else{
                that.setData({ emptyCompany: true });
            }
            let listArr = res.data.list
            for (let i = 0; i < listArr.length; i++) {
                if (listArr[i].logo) {
                    listArr[i].logo = that.getImgUrlPath(listArr[i].logo)
                }
            }
            that.setData({ 
                styleListData: arr, 
                companyList: listArr,
                loadBool:true ,
                count:9
            });
        }
    });
  },
  // onload 数据初始化
  onLoad : function(options){
    let that = this, 
    json = [],
    provinceJson = [],
    cityJson = [],
    areaJson = [],
    cityUrl, 
    userId, 
    itemRed = [],
    itemRedOther=[];
	// 删除授权
    // app.getUserInfo(function (res) {
    //     userId = res.userId;
    //     that.setData({
    //         userId : userId
    //     });
    // });
	 let userid = ''
		if(app.globalData.userInfo && app.globalData.userInfo.userId) {
			userid = app.globalData.userInfo.userId
			this.setData({
				userId: userid
			})
		}
    my.httpRequest({
        url: apiUrl + '/appletcarousel/company',
        data: {
            userid : that.data.userId,
            count: 10
        },
        header: {
            'content-type': 'application/json'
        },
        success: function (res) {
            my.showLoading({
                content: '页面加载中',
            })
            setTimeout(function () {
                my.hideLoading()
            }, 1200);
            let arr = [],
                style = res.data.attribute.fengge,
                scale = res.data.attribute.guimo;
            arr[0] = [];
            arr[1] = style;
            arr[2] = scale;
            for (let i = 0; i < res.data.attribute.fengge.length;i++ ){
                itemRed[i] = true
            }
            for (let i = 0; i < res.data.attribute.guimo.length; i++) {
                itemRedOther[i] = true
            }
            let listArr = res.data.list
            for (let i = 0; i < listArr.length;i++){
                if (listArr[i].logo) {
                    listArr[i].logo = that.getImgUrlPath(listArr[i].logo)
                }
            }
            that.setData({ 
                styleListData: arr,
                companyList: listArr, 
                userId: userId, 
                itemRed: itemRed, 
                itemRedOther: itemRedOther
            });
        }
    });
    /**
     * 获取城市缓存数据
     */
    my.getStorage({
        key: 'cityJson',
        success: function(res) {
            let cityJsonNew = res.data;
            if (cityJsonNew) {
                that.setData({
                    province: cityJsonNew.province,
                    city: cityJsonNew.city,
                    area: cityJsonNew.area
                });
            } else {
                that.getCityData();
            }
        },
        fail: function() {
        }
    });
  },
  // ajax获取城市数据
    getCityData() {
        let that = this, provinceJson = [], cityJson = [], areaJson = [], allCityJson = [];
        // ajax请求数据并且数据本地缓存存储
        my.httpRequest({
            url: mpiUrl+'/v1/city/allcity',
            headers: {
                'content-type': 'application/json'
            },
            method: "get",
            dataType: "json",
            success: function(res) {
                provinceJson = res.data.prev
                cityJson = res.data.city
                areaJson = res.data.area
                allCityJson = res.data.json
                that.setData({
                    province: provinceJson,
                    city: cityJson,
                    area: areaJson,
                    json: allCityJson
                });
                // 设置缓存
                my.setStorage({
                    key: 'cityJson',
                    data: {
                        province: provinceJson,
                        city: cityJson,
                        area: areaJson,
                        json: allCityJson
                    },
                    success: function() {
                        // my.alert({
                        //     content: "缓存设置成功"
                        // });
						console.log("缓存设置成功")
                    },
                    fail: function() {
                        // my.alert({
                        //     content: "缓存设置失败"
                        // });
						console.log("缓存设置失败")
                    }
                });
            },
            fail: function(res) {
                my.alert({
                    content: "无法请求," + res.errorMessage + "," + res.error
                });
            }
        })
    },
    
    /**
     * 城市选择滑动
     */
    citySelect: function (e) {
        let that = this;
        let cityJson = [];
        let areaJson = [];
        let oldVal = that.data.val;
        let val = e.detail.value;
        that.setData({ val:val})
        let province = val[0];
        let city = val[1];
        let area = val[2];
        my.getStorage({
            key: 'cityJson',
            success: function (res) {
                let json = res.data ? res.data.json : that.data.json;
                // 滑动省份获取城市
                if (oldVal[0] != province && oldVal[1] == city && oldVal[2] == area) {
                    city = 0; area = 0;
                    that.setData({ valuecity: [province,0,0]})
                } else if (oldVal[0] == province && oldVal[1] != city && oldVal[2] == area) {
                    area = 0;
                    that.setData({ valuecity: [province, city, 0] })
                } else if (oldVal[0] == province && oldVal[1] == city && oldVal[2] != area) {

                }
                for (let j = 0; j < json[province].children.length; j++) {
                    cityJson.push({ id: json[province].children[j].id, text: json[province].children[j].text });
                }
                // 滑动城市获取区域
                for (let k = 0; k < json[province].children[city].children.length; k++) {
                    areaJson.push({ id: json[province].children[city].children[k].id, text: json[province].children[city].children[k].text });
                }
                that.setData({ city: cityJson, area: areaJson, provinceIndex: province, cityIndex: city, areaIndex: area });
            }
        });
    },
    // 选择服务区域
    selectHandle() {
        this.setData({ 
            isHideCity: false, 
            itemHide: true,
            isHide: [false,true,true]
        });
    },
    //选择风格和规模
    styleHandle(e) {
        let isHideArr = [true, true, true],
            index = e.currentTarget.dataset.index,
            itemRed1 = [], itemRed2=[];
        if (e.currentTarget.dataset.index == 1){
            for (let i = 0; i < this.data.styleListData[1].length; i++) {
                itemRed1[i] = true
            }
            if (parseInt(this.data.itemRedFG)>=0){
                itemRed1[this.data.itemRedFG] = false;
            }
            this.setData({ itemRed: itemRed1,sjbool: false });
        } else if (e.currentTarget.dataset.index == 2){
            for (let i = 0; i < this.data.itemRedOther.length; i++) {
                itemRed2[i] = true
            }
            if (parseInt(this.data.itemRedGM) >= 0) {
                itemRed2[this.data.itemRedGM] = false;
            }
            this.setData({ itemRed: itemRed2, sjbool: true });
        }
        isHideArr[index] = !this.data.isHide[index];
        this.setData({ 
          styleIndex: index, 
          itemHide: isHideArr[index], 
          isHideCity: true, 
          isHide: isHideArr, 
          styleItemData: this.data.styleListData[index] 
        });
    },
    /**
     * 装修公司条件筛选
     */
    selectItemVal(e){
        let itemRed1 = this.data.itemRed,
            itemRed2 = this.data.itemRedOther;

        let navIndexVal = e.currentTarget.dataset.val,
            id = e.currentTarget.dataset.id,
            that = this,
            isHideArr = [true, true, true],
            eventIndex = e.currentTarget.dataset.itemindex;
        if (e.currentTarget.dataset.index == 0){

        } else if (e.currentTarget.dataset.index == 1){
            for (let i = 0; i < itemRed1.length; i++) {
                itemRed1[i] = true
            }
            itemRed1[eventIndex] = false;
            that.setData({ 
                itemHide: true, 
                styleVal: navIndexVal, 
                styleId: id, 
                isHide: isHideArr, 
                sjbool: false, 
                itemRed: itemRed1, 
                itemRedFG: e.currentTarget.dataset.itemindex
            });

        } else if (e.currentTarget.dataset.index == 2){
            for (let i = 0; i < itemRed2.length;i++){
                itemRed2[i] = true
            }
            itemRed2[eventIndex] = false;
            that.setData({ 
                itemHide: true, 
                scaleVal: navIndexVal, 
                scaleId: id, 
                isHide: isHideArr, 
                sjbool: true, 
                itemRed: itemRed2, 
                itemRedGM: e.currentTarget.dataset.itemindex 
            });

        }

        my.httpRequest({
            //url: apiUrl + '/appletcarousel/company?userid=' + that.data.userId,
            url: apiUrl + '/appletcarousel/company',
            data: {
                userid : that.data.userId,
                qx: that.data.areaId, 
                fg: that.data.styleId, 
                gm: that.data.scaleId,
                count:9
            },
            header: {
                'content-type': 'application/json'
            },
            success: function (res) {
                let arr = [],
                    style = res.data.attribute.fengge,
                    scale = res.data.attribute.guimo;
                arr[0] = [];
                arr[1] = style;
                arr[2] = scale;
                if (res.data.list.length <= 0) {
                    that.setData({ emptyCompany: false });
                } else {
                    that.setData({ emptyCompany: true });
                }
                let listArr = res.data.list
                for (let i = 0; i < listArr.length; i++) {
                    if (listArr[i].logo) {
                        listArr[i].logo = that.getImgUrlPath(listArr[i].logo)
                    }
                }
                console.log(listArr[0].logo);
                that.setData({ 
                    styleListData: arr, 
                    companyList: listArr, 
                    loadBool: true, 
                    count: 9
                })
            }
        });
    },
    /**
     * 上拉加载更多数据
     */
    downLoad(){
        let count = this.data.count,
            that = this;
        if (that.data.loadBool){
            my.showToast({
                content: '数据加载中...',
                type: 'loading'
            });
            let len = that.data.companyList.length;
            count += 10;
            my.httpRequest({
                url: apiUrl + '/appletcarousel/company',
                data: {
                    userid : that.data.userId, 
                    qx: that.data.areaId, 
                    fg: that.data.styleId, 
                    gm: that.data.scaleId, 
                    count: count 
                },
                header: {
                    'content-type': 'application/json'
                },
                success: function (res) {
                    if (len == res.data.list.length){
                        that.setData({ loadBool:false });
                    }else{
                        let listArr = res.data.list
                        for (let i = 0; i < listArr.length; i++) {
                            if (listArr[i].logo) {
                                listArr[i].logo = that.getImgUrlPath(listArr[i].logo)
                            }
                        }
                        that.setData({ 
                            companyList: listArr, 
                            count: count, 
                            loadBool: true 
                        });
                    }
                }
            });
        }else{
            my.showToast({
                content: '没有更多了',
                type: 'success'
            });
        }
    },
    setCustomerName:function(e){
        this.setData({ 
          customerName: e.detail.value 
        });
    },
    setCustomerPhone:function(e) {
        this.setData({ 
          customerPhone: e.detail.value 
        });
    },
    submitForm:function() {
        let regu = "^[a-zA-Z\u4e00-\u9fa5]+$";
        let re = new RegExp(regu);

        let that = this;
        if (that.data.selectText.length < 1) {
            //that.setData({ selectTextDefault: '请选择城市：'})
            alertViewWithCancel("提示", "请选择您的所在地区", function () { });
            return;
        } else {
            that.setData({ selectTextDefault: ''})
        }
        if (that.data.customerName.length < 1) {
            alertViewWithCancel("提示", "请输入您的称呼", function () {
                //that.setData({ boolName: true });
            });
            return;
        } else if (that.data.customerName.search(re) == -1) {
            alertViewWithCancel("提示", "请输入正确的名称，只支持中文和英文", function () {
                //that.setData({ boolName: true });
            });
            return;
        }
        if (that.data.customerPhone.length < 1) {
            alertViewWithCancel("提示", "请输入手机号", function () { });
            return;
        } else {
            var reg = new RegExp("^(13|14|15|17|18)[0-9]{9}$");
            if (!reg.test(that.data.customerPhone)) {
                alertViewWithCancel("提示", "请输入正确的手机号", function () { });
                return false;
            }
        }

        // if (that.data.src) {
            my.httpRequest({
                url: apiUrl + '/zxjt/submit_order/?src=' + app.globalData.sourceMark,
                data: {
                    name: that.data.customerName,
                    tel: that.data.customerPhone,
                    cs: app.transformCity(that.data.selectText)
                },
                header: {
                    'content-type': 'application/x-www-form-urlencoded'
                },
                method: "POST",
                success: function (res) {
                    alertViewWithCancel("提示", "提交成功，请注意接听电话", function () { });
                },
                fail: function(res) {
                    alertViewWithCancel("提示", "提交失败", function() { });
                }
            });
        // } else {
        //     my.httpRequest({
        //         url: apiUrl + '/zxjt/submit_order/?src='+config.service.sourceMark,
        //         data: {
        //             name: that.data.customerName,
        //             tel: that.data.customerPhone,
        //             cs: app.transformCity(that.data.selectText)
        //         },
        //         header: {
        //             'content-type': 'application/x-www-form-urlencoded'
        //         },
        //         method: "POST",
        //         success: function (res) {
        //             alertViewWithCancel("提示", "提交成功，请注意接听电话", function () { });
        //         }
        //     });
        // }

    },
    toCompanyDetail : function(e){
        let that = this,
            id = e.currentTarget.dataset.id,
            star = e.currentTarget.dataset.star,
			bm = e.currentTarget.dataset.bm,
            anlicount = e.currentTarget.dataset.anlicount;
        my.navigateTo({
            url : "../zxCompany-detail/index?id=" + id + "&star=" + star + "&anlicount=" + anlicount + "&bm=" + bm,
        });
    },
    collectAction:function(e){
        let companyId = e.currentTarget.dataset.id,
            that = this,
            index = e.currentTarget.dataset.index;
			console.log(that.data.userId)
        if (that.data.userId){
            my.httpRequest({
                url: apiUrl + '/appletcarousel/editcollect',
                data: {
                    userid: that.data.userId, // 用户ID
                    classtype: '9', // 装修公司
                    classid: companyId
                },
                header: {
                    'content-type': 'application/x-www-form-urlencoded'
                },
                method: "POST",
                success: function (res) {
                    if(res.data.state == 1){
                        my.showToast({
                            content: '收藏成功',
                            icon: 'success',
                            duration: 1200
                        });
                        let arr = that.data.companyList;
                        arr[index].is_collect = 1;
                        that.setData({ companyList:arr})
                    }
                }
            });
        }else{
			console.log('请求userid')
            app.getLoginAgain(function(res){
                that.setData({
                    userId : res.userId
                });
				console.log(res)
                //提交收藏
                if(that.data.userId){
                    my.httpRequest({
                        url: apiUrl + '/appletcarousel/editcollect',
                        data: {
                            userid: that.data.userId,
                            classtype: '9', // 装修公司
                            classid: companyId
                        },
                        header: {
                            'content-type': 'application/x-www-form-urlencoded'
                        },
                        method: "POST",
                        success: function (res) {
                            if (res.data.state == 1) {
                                my.showToast({
                                    content: '收藏成功',
                                    icon: 'success',
                                    duration: 1200
                                });
                                let arr = that.data.companyList;
                                arr[index].is_collect = 1;
                                that.setData({ companyList: arr })
                            }
                        },
                    });
                }
            })
                
            
        }
    },
    cancelCollectAction:function(e){
        let companyId = e.currentTarget.dataset.id,
            that = this;
        if (that.data.userId) {
            my.httpRequest({
                url: apiUrl + '/appletcarousel/editcollect',
                data: {
                    userid: that.data.userId, // 用户ID
                    classtype: '9', // 装修公司
                    classid: companyId
                },
                header: {
                    'content-type': 'application/json'
                },
                success: function (res) {
                    if(res.data.state ==1){
                        my.showToast({
                            content: '你已取消收藏',
                            icon: 'success',
                            duration: 1200
                        });
                        my.httpRequest({
                            //url: apiUrl + '/appletcarousel/company?userid=' + that.data.userId,
                            url: apiUrl + '/appletcarousel/company',
                            data: { 
                                userid : that.data.userId,
                                qx: that.data.areaId, 
                                fg: that.data.styleId, 
                                gm: that.data.scaleId,
                                count: 9
                            },
                            header: {
                                'content-type': 'application/json'
                            },
                            success: function (res) {
                                let listArr = [];
                                my.showLoading({
                                    title: '页面加载中',
                                })

                                setTimeout(function () {
                                    my.hideLoading()
                                }, 1200);
                                let arr = [],
                                    style = res.data.attribute.fengge,
                                    scale = res.data.attribute.guimo;
                                arr[0] = [];
                                arr[1] = style;
                                arr[2] = scale;
                                listArr = res.data.list;
                                for (let i = 0; i < listArr.length; i++) {
                                    if (listArr[i].logo) {
                                        listArr[i].logo = that.getImgUrlPath(listArr[i].logo)
                                    }
                                }
                                that.setData({ navListData: arr, companyList: listArr });
                            }
                        });
                    }
                }
            });
        }
    },
    getImgUrlPath:function(imgUrl){
        let imgUrlPath = ""
        let imgPath1 = imgUrl.split('https://staticqn.qizuang.com/')[1]
        let imgPath2 = imgUrl.split('http://staticqn.qizuang.com/')[1]
        if (imgPath1) {
            imgUrlPath = imgPath1
        } else if (imgPath2) {
            imgUrlPath = imgPath2
        } else {
            return imgUrl
        }
        return imgUrlPath
    }

});
