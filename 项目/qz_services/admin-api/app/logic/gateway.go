package logic

import (
    "admin-api/app/enum"
    "admin-api/app/model"
    "github.com/gogf/gf/g"
    "strconv"
    "strings"
    "time"
)

// 三方网关设置 逻辑
type Gateway struct {
}

//  数据验证
type GatewayValidate struct {
    ID       int    `gvalid:"id      @integer#无效的ID"`
    Slot     string `gvalid:"slot      @required|min-length:8#请输入唯一标识|唯一标识长度不小于8个字符"`
    Type     string `gvalid:"type      @required#请选择通道类型"`
    Name     string `gvalid:"name      @required#请输入通道名称"`
    Config   string `gvalid:"config      @required|json#请输入配置文件(JSON)|配置文件只能是JSON格式"`
    Level    int    `gvalid:"level      @required|integer|min:1#请输入优先级,只能输入纯数字|请输入优先级,只能输入纯数字|请输入优先级且必须大于0"`
    IsEnable uint8  `gvalid:"is_enable      @required|in:0,1#请选择是否启用|是否启用选择有误"`
}

//  实例化通道类型枚举
var smsTypeEnum = new(enum.SmsTypeEnum)

//  实例化三方网关model
var gatewayModel = model.NewGatewayModel()

//  网关logic  实例化方式
func NewGatewayService() *Gateway {
    return &Gateway{}
}

// 获取列表
func (gate *Gateway) List() (list []model.Gateway, err error) {
    list, err = gatewayModel.List()
    if err != nil {
        return nil, err
    }
    for key, value := range list {
        list[key] = gate.transform(&value)
    }
    return
}

// 获取详情
func (gate *Gateway) Detail(id int) (data map[string]interface{}, err error) {
    list, err := gatewayModel.Detail(id)
    if err != nil {
        list = model.Gateway{}
        return
    }
    list = gate.transform(&list)
    data = make(map[string]interface{})
    data["list"] = list
    data["sms_type"] = smsTypeEnum.GetList()
    return
}

//  根据id判断是否存在
func (gate *Gateway) HasById(id int) (isExist bool, err error) {
    condition := g.Map{"id": id}
    isExist, err = gatewayModel.Has(condition)
    return
}

//  根据level判断是否存在
func (gate *Gateway) HasByLevel(id int, level int) (isExist bool, err error) {
    var condition g.Map
    if id > 0 {
        condition = g.Map{"id <> ?": id, "level": level}
    } else {
        condition = g.Map{"level": level}
    }
    isExist, err = gatewayModel.Has(condition)
    return
}

//  根据name判断是否存在
func (gate *Gateway) HasByName(id int, name string) (isExist bool, err error) {
    var condition g.Map
    if id > 0 {
        condition = g.Map{"id <> ?": id, "name": name}
    } else {
        condition = g.Map{"name": name}
    }
    isExist, err = gatewayModel.Has(condition)
    return
}

//  根据唯一标识判断是否存在
func (gate *Gateway) HasBySlot(id int, slot string) (isExist bool, err error) {
    var condition g.Map
    if id > 0 {
        condition = g.Map{"id <> ?": id, "slot": slot}
    } else {
        condition = g.Map{"slot": slot}
    }

    isExist, err = gatewayModel.Has(condition)
    return
}

//  保存数据
func (gate *Gateway) Save(gateway *model.Gateway) (ret int, err error) {
    data := g.Map{
        "slot":       gateway.Slot,
        //"type":       gateway.Type,
        "name":       gateway.Name,
        "config":     gateway.Config,
        "level":      gateway.Level,
        "note":       gateway.Note,
        "is_enable":  gateway.IsEnable,
        "creator":    model.AdminUser.UserId,
        "operator":   model.AdminUser.UserId,
        "created_at": time.Now().Unix(),
        "updated_at": time.Now().Unix(),
    }
    ret, err = gatewayModel.Save(data)
    return
}

//  更新数据
func (gate Gateway) Update(gateway *model.Gateway) (ret bool, err error) {
    data := g.Map{
        "slot":       gateway.Slot,
        //"type":       gateway.Type,
        "name":       gateway.Name,
        "config":     gateway.Config,
        "level":      gateway.Level,
        "note":       gateway.Note,
        "is_enable":  gateway.IsEnable,
        "operator":   model.AdminUser.UserId,
        "updated_at": time.Now().Unix(),
    }
    condition := g.Map{"id": gateway.ID}
    ret, err = gatewayModel.Update(condition, data)
    return
}

//  是否启用网关
func (gate *Gateway) Enable(gateway *model.Gateway) (ret bool, err error) {
    data := g.Map{
        "is_enable":  gateway.IsEnable,
        "operator":   model.AdminUser.UserId,
        "updated_at": time.Now().Unix(),
    }
    condition := g.Map{"id": gateway.ID}
    ret, err = gatewayModel.Update(condition, data)
    return
}

//  根据网关ID判断模板是否存在
func (gate Gateway) HasTemplateByGatewayId(id int) (ret bool, err error) {
    smsTempGatewayModel := new(model.SmsTemplateGatewayModel)
    ret, err = smsTempGatewayModel.HasTemplateByGatewayID(id)
    return
}

// 清洗数据
func (gate *Gateway) transform(gateway *model.Gateway) (data model.Gateway) {
    data = *gateway
    var typeName []string
    for _, value := range strings.Split(gateway.Type, ",") {
        typeValue, err := strconv.Atoi(value)
        if err == nil {
            typeName = append(typeName, smsTypeEnum.GetName(typeValue))
        }
    }
    data.TypeName = strings.Join(typeName, ",")
    return
}
