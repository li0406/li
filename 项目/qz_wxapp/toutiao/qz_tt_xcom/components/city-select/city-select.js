import { fadanHandle, merchantHandle, getOrderDetail, getCityData } from "../../utils/mapi.js"
const app = getApp()
Component({
    properties: {
        isHide: { // 属性名
            type: Boolean, // 类型（必填），目前接受的类型包括：String, Number, Boolean, Object, Array, null（表示任意类型）
            value: true // 属性初始值（必填）
        },
        handCity:{
            type:String,
            value:'',
            
        }
    },
    data: {
        province: [
            {
                "id": "340000",
                "text": "A 安徽省"
            },
            {
                "id": "110000",
                "text": "B 北京市"
            },
            {
                "id": "500000",
                "text": "C 重庆市"
            },
            {
                "id": "350000",
                "text": "F 福建省"
            },
            {
                "id": "440000",
                "text": "G 广东省"
            },
            {
                "id": "450000",
                "text": "G 广西壮族自治区"
            },
            {
                "id": "620000",
                "text": "G 甘肃省"
            },
            {
                "id": "520000",
                "text": "G 贵州省"
            },
            {
                "id": "130000",
                "text": "H 河北省"
            },
            {
                "id": "410000",
                "text": "H 河南省"
            },
            {
                "id": "460000",
                "text": "H 海南省"
            },
            {
                "id": "420000",
                "text": "H 湖北省"
            },
            {
                "id": "430000",
                "text": "H 湖南省"
            },
            {
                "id": "230000",
                "text": "H 黑龙江省"
            },
            {
                "id": "220000",
                "text": "J 吉林省"
            },
            {
                "id": "320000",
                "text": "J 江苏省"
            },
            {
                "id": "360000",
                "text": "J 江西省"
            },
            {
                "id": "210000",
                "text": "L 辽宁省"
            },
            {
                "id": "150000",
                "text": "N 内蒙古自治区"
            },
            {
                "id": "640000",
                "text": "N 宁夏回族自治区"
            },
            {
                "id": "630000",
                "text": "Q 青海省"
            },
            {
                "id": "310000",
                "text": "S 上海市"
            },
            {
                "id": "510000",
                "text": "S 四川省"
            },
            {
                "id": "370000",
                "text": "S 山东省"
            },
            {
                "id": "140000",
                "text": "S 山西省"
            },
            {
                "id": "610000",
                "text": "S 陕西省"
            },
            {
                "id": "120000",
                "text": "T 天津市"
            },
            {
                "id": "650000",
                "text": "X 新疆维吾尔自治区"
            },
            {
                "id": "540000",
                "text": "X 西藏自治区"
            },
            {
                "id": "530000",
                "text": "Y 云南省"
            },
            {
                "id": "330000",
                "text": "Z 浙江省"
            }
        ],
        city: [],
        json: [],
        activeProv: '',
        activeCity: '',
        activeProName: '',
        activeCityName: ''
    }, // 私有数据，可用于模版渲染

    // 生命周期函数，可以为函数，或一个在methods段中定义的方法名
    attached: function () {
        // 获取城市数据
        this.getCitys()
    },
    detached: function () { },
    methods: {
        // 确定省份
        setProvince: function (e) {
            let that = this
            let proId = e.currentTarget.dataset.id
            for (let i in that.data.json) {
                if (that.data.json[i].id === proId) {
                    // 设置第一个市为默认值
                    that.data.json[i].children[0].active = true
                    that.setData({
                        city: that.data.json[i].children,
                        ['province[' + i + '].active']: true,
                        activeProv: proId, //选中的省份Id
                        activeProName: that.data.json[i].text
                    })
                } else {
                    that.setData({
                        ['province[' + i + '].active']: false
                    })
                }
            }
        },
        setCity: function (e) {
            let that = this
            let cityId = e.currentTarget.dataset.id
            for (let i in that.data.city) {
                if (that.data.city[i].id === cityId) {
                    that.setData({
                        ['city[' + i + '].active']: true,
                        activeCity: cityId, //选中的省份Id
                        activeCityName: that.data.city[i].text
                    })
                } else {
                    that.setData({
                        ['city[' + i + '].active']: false
                    })
                }
            }
            this.closeCitySelect('')
        },
        closeCitySelectMask:function(){
            this.setData({
                isHide: true
            })
            this.triggerEvent('closeSelect')
        },
        getCitys: function () {
            let that = this
            // 获取缓存
            app.getStorage({
                key: "cityData",
                success: function (res) {
                    if (res.code === 0) {
                        that.requestCity()
                    } else {
                        that.setData({
                            json: res.data.data
                        })
                        if(app.globalData.cityInfo.cid){
                            that.getMyCity(res.data.data, app.globalData.cityInfo.cname)
                        }else{
                            // 自动定位
                            app.getMyPosition(function (city) {
                                that.getMyCity(res.data.data, city)
                            })
                        }
                    }
                }
            })
        },
        requestCity: function () {
            let that = this
            getCityData('/v1/city/allcity', {
                level: 2
            }).then(data => {
                // 赋值省市
                that.setData({
                    json: data.json
                })
                // 定位
                app.getMyPosition(function (city) {
                    that.getMyCity(data.json, city)
                })
                // 添加缓存
                app.setStorage({
                    key: 'cityData',
                    data: data.json
                })
            }).catch(error => {
                console.log(error)
            })
        },
        closeCitySelect: function () {
            this.setData({
                isHide: true
            })
            this.triggerEvent('closeSelect', [this.data.activeCity, this.data.activeProName + ' ' + this.data.activeCityName])
        },
        getMyCity: function (allCity, city) {
            for (let i in allCity) {
                for (let j in allCity[i].children) {
                    if (city.indexOf(allCity[i].children[j].text) !== -1) {
                        // 高亮选中值
                        allCity[i].children[j].active = true
                        this.setData({
                            city: allCity[i].children,
                            ['province['+i+'].active']: true,
                            activeProv: allCity[i].id, //选中的省份Id
                            activeProName: allCity[i].text,
                            activeCity: allCity[i].children[j].id,
                            activeCityName: allCity[i].children[j].text
                        })
                        // 向父组件传递选中项
                        this.triggerEvent('closeSelect', [allCity[i].children[j].id, allCity[i].text + ' ' + allCity[i].children[j].text])
                    }
                }
            }
        }
    }
});
