package v1

import (
    "admin-api/app/enum"
    "admin-api/app/logic"
    "admin-api/app/model"
    "admin-api/app/validate"
    "admin-api/library/util"
    "encoding/json"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/frame/gmvc"
    "time"
)

type PnpController struct {
    gmvc.Controller
}

func (c *PnpController)GetProviderInfo()  {
    req := c.Request
    id := req.GetString("id")

    if id == "" {
        ret, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
        c.Request.Response.WriteJson(ret)
        c.Exit()
    }

    res := logic.NewPnpLogic().GetProviderInfo(id)
    json, _ := util.EncodeResponseJson(0, enum.CODE_0, res)
    c.Request.Response.WriteJson(json)
    c.Exit()
}

func (c *PnpController)SetProviderInfo() {
    gjson := c.Request
    provider := g.Map{
        "id": gjson.GetInt("id"),
        "slot": gjson.GetString("slot"),
        "name": gjson.GetString("name"),
        "config": gjson.GetString("config"),
        "remarks": gjson.GetString("remark"),
        "is_enabled": gjson.GetInt("enabled"),
        "update_at": time.Now().Unix(),
        "op_uid" : model.AdminUser.UserId,
        "op_uname": model.AdminUser.UserName,
    }

    if provider["id"] != "" {
        provider["created_at"]  = time.Now().Unix()
    }

    err := validate.NewPnpProviderValidate().Check(provider)
    if err != nil {
        ret, _ := util.EncodeResponseJson(4000002, err.FirstString(), nil)
        c.Request.Response.WriteJson(ret)
        c.Exit()
    }


    res := logic.NewPnpLogic().SaveProvider(provider)
    json, _ := util.EncodeResponseJson(0, enum.CODE_0, nil)
    if res  == 0 {
        json, _ = util.EncodeResponseJson(5000002, enum.CODE_5000002, nil)
    }

    c.Request.Response.WriteJson(json)
    c.Exit()
}

func  (c *PnpController)GetProviderList()  {
    res := logic.NewPnpLogic().GetProviderList();
    data := make(map[string]interface{})
    data["list"] = nil
    if res != nil {
        data["list"] = res
    }
    json, _ := util.EncodeResponseJson(0, enum.CODE_0, data)
    c.Request.Response.WriteJson(json)
    c.Exit()
}

func (c *PnpController)GetConfig()  {
    data := model.NewPnpConfigModel()
    //获取设置项
    config := logic.NewPnpLogic().GetConfig()

    for k,v := range config{
        sub := model.PnpOption{
            Pnp_option: k,
            Pnp_option_value: v,
        }
        data.Option = append(data.Option,sub)
    }
    //获取所有城市
    data.Citys = logic.NewPnpLogic().GetAllCity()

    //获取开放城市
    data.Open_city = logic.NewPnpLogic().GetOpenCity()


    json, _ := util.EncodeResponseJson(0, enum.CODE_0, data)
    c.Request.Response.WriteJson(json)
    c.Exit()
}

func (c *PnpController)GetProviderDropDownlist()  {
    res := logic.NewPnpLogic().GetProviderDropDownlist()
    data := make(map[string]interface{})
    data["list"] = nil
    if len(res) > 0 {
        data["list"] = res
    }
    json, _ := util.EncodeResponseJson(0, enum.CODE_0, data)
    c.Request.Response.WriteJson(json)
    c.Exit()
}

func (c *PnpController)SetConfigUp()  {
    req := c.Request

    sw := req.GetString("pnp_switch")
    expire := req.GetString("pnp_expire")
    provider := req.GetString("pnp_provider")
    var open_city []string
    json.Unmarshal([]byte(req.Get("open_city")), &open_city)

    config := g.Map{
       "pnp_switch":sw,
       "pnp_expire":expire,
       "pnp_provider":provider,
    }
    validate :=  validate.NewPnpConfigValidate()
    err :=validate.Check(config)

    if err != nil {
        ret, _ := util.EncodeResponseJson(4000002, err.FirstString(), nil)
        c.Request.Response.WriteJson(ret)
        c.Exit()
    }

    if len(open_city) == 0 {
        ret, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
        c.Request.Response.WriteJson(ret)
        c.Exit()
    }
    //保存配置项目
    //开关
    logic.NewPnpLogic().SetConfig("pnp_switch",sw)
    //有效时间
    logic.NewPnpLogic().SetConfig("pnp_expire",expire)
    //供应商
    logic.NewPnpLogic().SetConfig("pnp_provider",provider)

    //开放城市
    //删除现有的开放城市
    logic.NewPnpLogic().DelOpenCity()
    //保存新的城市
    logic.NewPnpLogic().SetOpenCity(open_city)
    json, _ := util.EncodeResponseJson(0, enum.CODE_0, nil)
    c.Request.Response.WriteJson(json)
    c.Exit()
}

func (c *PnpController)SetProviderStatus()  {
    req := c.Request
    enabled := req.GetString("enabled")
    id := req.GetString("id")
    m:= g.Map{
        "id" :id,
        "enabled":enabled,
    }

    validate :=  validate.NewPnpProviderValidate()

    err :=validate.StatusCheck(m)
    if err != nil {
        ret, _ := util.EncodeResponseJson(4000002, err.FirstString(), nil)
        c.Request.Response.WriteJson(ret)
        c.Exit()
    }
    provider := g.Map{
        "id": id,
        "is_enabled":enabled,
        "update_at": time.Now().Unix(),
        "op_uid" : model.AdminUser.UserId,
        "op_uname": model.AdminUser.UserName,
    }
    res :=  logic.NewPnpLogic().UpdateProvider(provider)
    var json []byte
    if res {
        json, _ = util.EncodeResponseJson(0, enum.CODE_0, nil)
    } else {
        json, _ = util.EncodeResponseJson(5000002, enum.CODE_5000002, nil)
    }
    c.Request.Response.WriteJson(json)
    c.Exit()
}

func  (c *PnpController)GetPnpTelList()  {
    req := c.Request
    tel := req.GetString("tel")
    city := req.GetString("city")
    status := req.GetInt("status")
    pageIndex := req.GetInt("page")
    if pageIndex == 0 {
        pageIndex = 1
    }
    data := make(map[string]interface{})
    data = logic.NewPnpLogic().GetPnpTelList(tel,city,status,pageIndex)
    json, _ := util.EncodeResponseJson(0, enum.CODE_0, data)
    c.Request.Response.WriteJson(json)
    c.Exit()
}

func (c *PnpController)GetPnpDropDownCity()  {
    data := make(map[string]interface{})
    data["list"] = logic.NewPnpLogic().GetPnpDropDownCity()
    json, _ := util.EncodeResponseJson(0, enum.CODE_0, data)
    c.Request.Response.WriteJson(json)
    c.Exit()
}

func (c *PnpController)GetTelHistory()  {
    req := c.Request
    tel := req.GetString("tel")
    order_id := req.GetString("order_id")
    pageIndex := req.GetInt("page")
    if pageIndex == 0 {
        pageIndex = 1
    }

    if tel == "" {
        ret, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
        c.Request.Response.WriteJson(ret)
        c.Exit()
    }

    //查询电话订单绑定记录
    res := logic.NewPnpLogic().GetTelHistory(tel,order_id,pageIndex)
    ret, _ := util.EncodeResponseJson(0, enum.CODE_0, res)
    c.Request.Response.WriteJson(ret)
    c.Exit()
}

func (c *PnpController)GetVoiceList()  {
    req := c.Request
    order_id := req.GetString("order_id")
    if order_id == "" {
        ret, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
        c.Request.Response.WriteJson(ret)
        c.Exit()
    }
    data := make(map[string]interface{})
    data["list"] = logic.NewPnpLogic().GetVoiceList(order_id)
    ret, _ := util.EncodeResponseJson(0, enum.CODE_0, data)
    c.Request.Response.WriteJson(ret)
    c.Exit()
}