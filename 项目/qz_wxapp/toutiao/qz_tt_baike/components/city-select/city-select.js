import { fadanHandle, merchantHandle, getOrderDetail, getCityData } from "../../utils/api.js"
const app = getApp()
Component({
    properties: {
        isHide: { // 属性名
            type: Boolean, // 类型（必填），目前接受的类型包括：String, Number, Boolean, Object, Array, null（表示任意类型）
            value: true // 属性初始值（必填）
        }
    },
    data: {
        province:[],
        city:[],
        json:[],
        activeProv:'',
        activeCity:'',
        activeProName:'',
        activeCityName:''
    }, // 私有数据，可用于模版渲染

    // 生命周期函数，可以为函数，或一个在methods段中定义的方法名
    attached: function () {
        // 获取城市数据
       this.getCitys()
    },
    detached: function () {},
    methods: {
        // 确定省份
        setProvince: function (e) {
            let that = this
            let proId = e.currentTarget.dataset.id
            for(let i in that.data.json){
                if(that.data.json[i].id === proId){
                    // 设置第一个市为默认值
                    that.data.json[i].children[0].active = true
                    that.setData({
                        city:that.data.json[i].children,
                        ['province['+i+'].active']:true,
                        activeProv:proId, //选中的省份Id
                        activeProName:that.data.json[i].text
                    })
                }else{
                    that.setData({
                        ['province['+i+'].active']:false
                    })
                }
            }
        },
        setCity:function (e) {
            let that = this
            let cityId = e.currentTarget.dataset.id
            for (let i in that.data.city) {
                if(that.data.city[i].id === cityId){
                    that.setData({
                        ['city['+i+'].active']:true,
                        activeCity:cityId, //选中的省份Id
                        activeCityName:that.data.city[i].text
                    })
                }else{
                    that.setData({
                        ['city['+i+'].active']:false
                    })
                }
            }
            this.closeCitySelect()
        },
        getCitys: function () {
            let that = this
            // 获取缓存
            app.getStorage({
                key:"cityData",
                success:function(res){
                    if(res.code===0){
                        that.requestCity()
                    }else{
                        that.setData({
                            json:res.data.data
                        })
                        // 定位
                        app.getMyPosition(function(city){
                            that.getMyCity(res.data.data,city)
                        })
                    }
                }
            })
        },
        requestCity:function(){
            let that = this
       
            getCityData('/v1/city/allcity',{
                level:2
            }).then(data=>{
                // 赋值省市
                that.setData({
                    json:data.json
                })
                // 定位
                app.getMyPosition(function(city){
                    that.getMyCity(data.json,city)
                })
                // 添加缓存
                app.setStorage({
                    key:'cityData',
                    data:data.json
                })
            }).catch(error=>{
                console.log(error)
            })
        },
        closeCitySelect:function(){
            this.setData({
                isHide:true
            })
            this.triggerEvent('closeSelect',[this.data.activeCity,this.data.activeProName+' '+this.data.activeCityName])
        },
        getMyCity:function(allCity,city){
            for(let i in allCity){
                for(let j in allCity[i].children){
                    if(city.indexOf(allCity[i].children[j].text)!==-1){
                        // 高亮选中值
                        allCity[i].active = true
                        allCity[i].children[j].active = true
                        this.setData({
                            city:allCity[i].children,
                            province:allCity,
                            activeProv:allCity[i].id, //选中的省份Id
                            activeProName:allCity[i].text,
                            activeCity:allCity[i].children[j].id,
                            activeCityName:allCity[i].children[j].text
                        })
                        // 向父组件传递选中项
                        this.triggerEvent('closeSelect',[allCity[i].children[j].id ,allCity[i].text+' '+allCity[i].children[j].text])
                    }
                }
            }
        }
    }
});