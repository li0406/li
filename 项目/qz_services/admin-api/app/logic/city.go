package logic

import (
    "admin-api/app/model"
    "github.com/gogf/gf/g/database/gdb"
    "github.com/gogf/gf/g/os/gcache"
    "github.com/gogf/gf/g/util/gconv"
)

type CityLogic struct {
}

func NewCityLogic() *CityLogic {
    return &CityLogic{}
}

func (that *CityLogic)GetCityList() (provinceList gdb.List) {
    cacheList := gcache.Get("Service:Common:GetAllCity:CityList")
    if cacheList == nil {
        cityDb := model.NewQuyuModel()
        //获取所有省份
        provinceList,_ = cityDb.GetProvince()
        if len(provinceList) > 0 {
            //获取所有城市
            cityList,_ := cityDb.GetQuyu()
            for k,province := range provinceList{
                var sub gdb.List
                for _,city:= range cityList {
                    if gconv.Int(city["uid"]) == gconv.Int(province["qz_provinceid"]){
                        sub = append(sub, city)
                    }
                }
                provinceList[k]["sub"] = sub
            }
        }
        gcache.SetIfNotExist("Service:Common:GetAllCity:CityList", provinceList, 900000)
    } else {
        provinceList = cacheList.(gdb.List)
    }
    return provinceList
}
