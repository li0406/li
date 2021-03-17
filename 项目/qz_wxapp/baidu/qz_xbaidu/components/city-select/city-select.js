import { fadanHandle, merchantHandle, getOrderDetail, getCityData } from "../../utils/api.js"
const app = getApp()
Component({
    properties: {
        isHide: {
            type: Boolean,
            value: true
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
    },
    attached: function () {
       this.getCitys()
    },
    detached: function () {},
    methods: {
        setProvince: function (e) {
            let that = this
            let proId = e.currentTarget.dataset.id
            for(let i in that.data.json){
                if(that.data.json[i].id === proId){
                    that.data.json[i].children[0].active = true
                    that.setData({
                        city:that.data.json[i].children,
                        ['province['+i+'].active']:true,
                        activeProv:proId,
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
                        activeCity:cityId,
                        activeCityName:that.data.city[i].text,
                        activeBm: that.data.city[i].bm
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
            app.getStorage({
                key:"cityData",
                success:function(res){
                    if(res.code===0){
                        that.requestCity()
                    }else{
                        that.setData({
                            json:res.data.data
                        })
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
                that.setData({
                    json:data.json
                })
                app.getMyPosition(function(city){
                    that.getMyCity(data.json,city)
                })
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
            this.triggerEvent('closeSelect',[this.data.activeCity,this.data.activeProName+' '+this.data.activeCityName,this.data.activeCityName,1,this.data.activeBm])
        },
        getMyCity:function(allCity,city){
            for(let i in allCity){
                for(let j in allCity[i].children){
                    if(city.indexOf(allCity[i].children[j].text)!==-1){
                        allCity[i].active = true
                        allCity[i].children[j].active = true
                        this.setData({
                            city:allCity[i].children,
                            province:allCity,
                            activeProv:allCity[i].id,
                            activeProName:allCity[i].text,
                            activeCity:allCity[i].children[j].id,
                            activeCityName:allCity[i].children[j].text,
                            activeBm:allCity[i].children[j].bm
                        })
                        this.triggerEvent('closeSelect',[allCity[i].children[j].id ,allCity[i].text+' '+allCity[i].children[j].text,allCity[i].children[j].text,2,allCity[i].children[j].bm])
                    }
                }
            }
        },
        closeSelect: function () {
            this.setData({
                isHide: true
            })
            this.triggerEvent('closeSelect')
        }
    }
});