package logic

import (
    "admin-api/app/model"
    "admin-api/library/util"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
    "math"
    "strconv"
    "time"
)

// 号码保护 逻辑
type PnpLogic struct {
}

func NewPnpLogic() *PnpLogic {
    return &PnpLogic{}
}

func (c *PnpLogic)SaveProvider(provider g.Map)(int64)  {
    return model.NewPnpProviderModel().Save(provider)
}

func (c *PnpLogic)GetProviderInfo(id string) g.Map {
    res,err :=  model.NewPnpProviderModel().GetProviderInfo(id)
    if err != nil {
        return nil
    }
    return res
}

func (c *PnpLogic)GetProviderList()(gdb.List)  {
    res,err :=   model.NewPnpProviderModel().GetProviderList()
    if err != nil {
        return  nil
    }
    for _,v := range res{
        i:= int64(v["created_at"].(int))
        t := time.Unix(i,0)
        v["created_at"] = t.Format("2006-01-02 15:04:05")
    }
    return res
}

func (that *PnpLogic)GetAllCity() []model.PnpCitys {
    res,err := model.NewQuyuModel().GetPnpCity()
    if err != nil {
        return nil
    }

    if len(res) > 0 {
        var m  []model.PnpCitys
        for _,v := range res{
            sub := model.PnpCitys{
                Cname:  v["cname"].(string),
                Cid: v["cid"].(string),
            }
            m = append(m,sub)
        }
        return m
    }
    return nil
}

func (c *PnpLogic)GetConfig() map[string] string{
    res,err := model.NewPnpConfigModel().GetAllConfig()
    if err != nil {
        return nil
    }
    var m = make(map[string] string)
    if len(res) > 0 {
        for _,v := range res {
            m[v["pnp_option"].(string)] = v["pnp_option_value"].(string)
        }
    }
    return m
}

func (c *PnpLogic)GetOpenCity()  []model.PnpCitys {
    res,err := model.NewPnpCityWhiteMapModel().GetOpenCity()
    if err != nil {
        return nil
    }

    if len(res) > 0 {
        var m  []model.PnpCitys
        for _,v := range res{

            sub := model.PnpCitys{
                Cname:  v["cname"].(string),
                Cid: v["cid"].(string),
            }
            m = append(m,sub)
        }
        return m
    }
    return nil
}

func  (c *PnpLogic)GetProviderDropDownlist()gdb.List  {
    res,err :=   model.NewPnpProviderModel().GetProviderDropDownlist()
    if err != nil {
        return  nil
    }
    return res
}
func  (c *PnpLogic)SetConfig(option_name string,option_value string) bool {
    m :=gdb.Map{
        "pnp_option_value":option_value,
    }
    res,err := model.NewPnpConfigModel().UpdaeByName(option_name,m);
    if err != nil {
        return false
    }
    return res
}

func (c *PnpLogic)SetOpenCity(citys []string) error  {
    list := gdb.List{}
    for _,v :=range citys {
        m := g.Map{
            "city_id":v,
        }
        list = append(list,m)
    }
    return model.NewPnpCityWhiteMapModel().SaveAll(list)
}

func (c *PnpLogic)DelOpenCity()bool {
    res,err := model.NewPnpCityWhiteMapModel().DelOpenCity()
    if err != nil {
        return false
    }
    return res
}

func (c *PnpLogic)UpdateProvider(m g.Map) (bool) {
    id,_ := strconv.Atoi(m["id"].(string))
    res,err := model.NewPnpProviderModel().Update(id,m)
    if err != nil {
        return false
    }
    return res
}

func (c *PnpLogic)GetPnpTelList(tel string,city string,status int,pageIndex int) map[string] interface{} {
    count,err := model.NewPnpTelephoneModel().GetPnpTelListCount(tel,city,status)
    if err != nil {
        return nil
    }
    var mapList  = make(map[string] interface{})
    pageCount := 20
    if count > 0 {
        p := util.GetPage(pageIndex,pageCount,count)
        list,err := model.NewPnpTelephoneModel().GetPnpTelList(tel,city,status,(pageIndex-1)*pageCount,pageCount)
        if err == nil {
            now := time.Now()
            local := time.Local
            for _,v := range list{
                v["bind_time"] = "-";
                if v["is_bind"].(int) == 1 {
                    un :=time.Unix(int64(v["updated_at"].(int)),0).Format("2006-01-02 15:04:05")
                    current,_ := time.ParseInLocation("2006-01-02 15:04:05",un ,local)
                    v["bind_time"] =  strconv.FormatFloat( math.Round(now.Sub(current).Hours()),'f',0,64 ) + "h"

                    if now.Sub(current).Hours() < 1 {
                        v["bind_time"] =  strconv.FormatFloat( math.Round(now.Sub(current).Minutes()),'f',0,64 ) + "m"
                    }

                    str := []rune(v["tel_a"].(string))
                    v["tel_a"] = string(str[0:3]) + "*****" + string(str[len(str)-3:])
                }

            }

            mapList["list"] = list
            mapList["page"] = p
        }
    }
    return mapList
}

func (c *PnpLogic)GetPnpDropDownCity() g.List  {
    res,err := model.NewPnpTelephoneModel().GetPnpDropDownCity()

    if err != nil {
        return nil
    }

    return res
}

func (c *PnpLogic)GetTelHistory(tel string,order_id string,pageIndex int) map[string] interface{}  {
    var mapList  = make(map[string] interface{})
    pageCount := 20
    count,err := model.NewPnpOrderMapModel().GetTelOrderListCount(tel,order_id)
    if err != nil {
        return nil
    }

    if count > 0 {
        res,err := model.NewPnpOrderMapModel().GetTelOrderList(tel,order_id,(pageIndex-1)*pageCount,pageCount)
        if err != nil {
            return nil
        }
        p := util.GetPage(pageIndex,pageCount,count)
        for _,v :=range res{
            str := []rune(v["tel_a"].(string))
            v["tel_a"] = string(str[0:3]) + "*****" + string(str[len(str)-3:])
        }
        mapList["list"] = res
        mapList["page"] = p
    }
    return mapList
}

func (c *PnpLogic)GetVoiceList(order_id string)(gdb.List)  {
    res,err := model.NewPnpOrderMapModel().GetVoiceList(order_id)
    if err != nil {
        return nil
    }

    if len(res) == 0  {
        return nil
    }
    for _,v := range res{
        str := []rune(v["tel_a"].(string))
        v["tel_a"] = string(str[0:3]) + "*****" + string(str[len(str)-3:])

        str = []rune(v["tel_b"].(string))
        v["tel_b"] = string(str[0:3]) + "*****" + string(str[len(str)-3:])

        v["created_at"] = time.Unix(int64(v["created_at"].(int)),0).Format("2006-01-02 15:04:05")
        switch  v["releasedir"].(string) {
            case "0":v["hangup"] = "平台挂断"
                break
            case "1":
                v["hangup"] = "主叫挂断"
                break
            case "2":
                v["hangup"] = "被叫挂断"
                break
        }
        delete(v,"releasedir")
    }
    return res
}