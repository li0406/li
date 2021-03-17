// 三方通道网关设置

package v1

import (
    "admin-api/app/enum"
    "admin-api/app/logic"
    "admin-api/app/model"
    "admin-api/library/util"
    "github.com/gogf/gf/g/frame/gmvc"
    "github.com/gogf/gf/g/util/gvalid"
)

type GatewayController struct {
    gmvc.Controller
}

var logicGateway = logic.NewGatewayService()

//  列表
func (gate *GatewayController) List() {
    list, err := logicGateway.List()
    var ret []byte
    if err != nil {
        ret, _ = util.EncodeResponseJson(5000002, enum.CODE_5000002, nil)
    } else {
        data := make(map[string]interface{})
        data["list"] = list
        ret, _ = util.EncodeResponseJson(0, enum.CODE_0, data)
    }
    _ = gate.Response.WriteJson(ret)
    gate.Exit()
}

//  详情
func (gate *GatewayController) Detail() {
    id := gate.Request.GetInt("id")
    //验证id合法性
    rule := "required|integer|min:1"
    if e := gvalid.Check(id, rule, nil); e != nil {
        json, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
        _ = gate.Response.WriteJson(json)
        gate.Exit()
    }

    //是否存在
    isExist, err := logicGateway.HasById(id)
    if !isExist || err != nil {
        json, _ := util.EncodeResponseJson(4000004, enum.CODE_4000004, nil)
        _ = gate.Response.WriteJson(json)
        gate.Exit()
    }

    //获取数据
    list, err := logicGateway.Detail(id)
    var ret []byte
    if err != nil {
        ret, _ = util.EncodeResponseJson(5000002, enum.CODE_5000002, nil)
    } else {
        ret, _ = util.EncodeResponseJson(0, enum.CODE_0, list)
    }
    _ = gate.Response.WriteJson(ret)
    gate.Exit()
}

//  是否启用网关
func (gate *GatewayController) Enable() {
    //实例化
    gateway := model.NewGatewayModel()
    var err error
    var success bool
    //获取请求数据
    err = gate.Request.GetPostToStruct(gateway)
    if err != nil {
        json, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
        _ = gate.Response.WriteJson(json)
        gate.Exit()
    }
    //验证id合法性
    rule := "required|integer|min:1"
    if e := gvalid.Check(gateway.ID, rule, nil); e != nil {
        json, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
        _ = gate.Response.WriteJson(json)
        gate.Exit()
    }
    //是否存在
    isExist, err := logicGateway.HasById(gateway.ID)
    if !isExist || err != nil {
        json, _ := util.EncodeResponseJson(4000004, enum.CODE_4000004, nil)
        _ = gate.Response.WriteJson(json)
        gate.Exit()
    }

    // 假如 is_enable=0 判断是否有模板使用
    if gateway.IsEnable == 0 {
        isUsed, err := logicGateway.HasTemplateByGatewayId(gateway.ID)
        if err != nil {
            json, _ := util.EncodeResponseJson(5000003, enum.CODE_5000003, nil)
            _ = gate.Response.WriteJson(json)
            gate.Exit()
        }
        if isUsed {
            json, _ := util.EncodeResponseJson(5000003, "该通道网关有关联的短信模板，请先取消关联后，再关闭该通道网关", nil)
            _ = gate.Response.WriteJson(json)
            gate.Exit()
        }
    }

    //更新
    success, err = logicGateway.Enable(gateway)

    //判断并响应
    if success == false {
        json, _ := util.EncodeResponseJson(5000003, enum.CODE_5000003, nil)
        _ = gate.Response.WriteJson(json)
        gate.Exit()
    }

    // 添加操作日志
    logingLogic := logic.NewLogingLogic()
    logingLogic.AddLog(gate.Request, "sms_gateway", "修改通道网关是否启用", gateway.ID)

    ret, _ := util.EncodeResponseJson(0, enum.CODE_0, nil)
    _ = gate.Response.WriteJson(ret)
    gate.Exit()
}

//  保存
func (gate *GatewayController) Save() {
    //实例化
    gateway := model.NewGatewayModel()
    gatewayTypeMap := logic.GatewayTypeMap{}
    var err error
    var success bool = false
    //获取请求数据
    err = gate.Request.GetPostToStruct(gateway)
    if err != nil {
        json, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
        _ = gate.Response.WriteJson(json)
        gate.Exit()
    }

    //数据验证
    gatewayValidate := logic.GatewayValidate{
        ID:       gateway.ID,
        Slot:     gateway.Slot,
        Type:     gateway.Type,
        Name:     gateway.Name,
        Config:   gateway.Config,
        Level:    gateway.Level,
        IsEnable: gateway.IsEnable,
    }

    if e := gvalid.CheckStruct(gatewayValidate, nil); e != nil {
        json, _ := util.EncodeResponseJson(4000002, e.FirstString(), nil)
        _ = gate.Response.WriteJson(json)
        gate.Exit()
    }

    // 正则验证
    rule := `regex:^[a-zA-Z]+[\w-]{7,}$`
    if e := gvalid.Check(gateway.Slot, rule, "唯一标识只能是以字母开头，且只能包含字母+数字+中划线/下划线"); e != nil {
        json, _ := util.EncodeResponseJson(4000002, e.FirstString(), nil)
        _ = gate.Response.WriteJson(json)
        gate.Exit()
    }
    //是否存在slot
    isExistSlot, err := logicGateway.HasBySlot(gateway.ID, gateway.Slot)
    if err != nil {
        json, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
        _ = gate.Response.WriteJson(json)
        gate.Exit()
    }

    if isExistSlot {
        json, _ := util.EncodeResponseJson(4000003, "该唯一标识已存在", nil)
        _ = gate.Response.WriteJson(json)
        gate.Exit()
    }

    //名称是否存在
    isExistName, err := logicGateway.HasByName(gateway.ID, gateway.Name)
    if err != nil {
        json, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
        _ = gate.Response.WriteJson(json)
        gate.Exit()
    }

    if isExistName {
        json, _ := util.EncodeResponseJson(4000003, "该通道名称已存在", nil)
        _ = gate.Response.WriteJson(json)
        gate.Exit()
    }

    //优先级是否存在
    isExistLevel, err := logicGateway.HasByLevel(gateway.ID, gateway.Level)
    if err != nil {
        json, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
        _ = gate.Response.WriteJson(json)
        gate.Exit()
    }

    if isExistLevel {
        json, _ := util.EncodeResponseJson(4000003, "该优先级已存在", nil)
        _ = gate.Response.WriteJson(json)
        gate.Exit()
    }

    //是否存在id
    isExist, err := logicGateway.HasById(gateway.ID)
    if err != nil {
        json, _ := util.EncodeResponseJson(5000003, enum.CODE_5000003, nil)
        _ = gate.Response.WriteJson(json)
        gate.Exit()
    }

    if !isExist {
        //保存
        insertId, err := logicGateway.Save(gateway)
        if insertId<=0 || err !=nil {
            success = false
        }else {
            success = true
        }
        gateway.ID = insertId
    } else {
        // 更新时，假如 is_enable=0 判断是否有模板使用
        if gateway.IsEnable == 0 {
            isUsed, err := logicGateway.HasTemplateByGatewayId(gateway.ID)
            if err != nil {
                json, _ := util.EncodeResponseJson(5000003, enum.CODE_5000003, nil)
                _ = gate.Response.WriteJson(json)
                gate.Exit()
            }
            if isUsed {
                json, _ := util.EncodeResponseJson(4000101, enum.CODE_4000101, nil)
                _ = gate.Response.WriteJson(json)
                gate.Exit()
            }
        }
        //更新
        success, err = logicGateway.Update(gateway)
    }

    if err != nil {
        json, _ := util.EncodeResponseJson(5000003, enum.CODE_5000003, nil)
        _ = gate.Response.WriteJson(json)
        gate.Exit()
    }

    //判断并响应
    if success == false {
        json, _ := util.EncodeResponseJson(5000003, enum.CODE_5000003, nil)
        _ = gate.Response.WriteJson(json)
        gate.Exit()
    }

    //删除旧的映射
    _, _ = gatewayTypeMap.Delete(gateway.ID)
    //保存映射map
    isSaveMap, err := gatewayTypeMap.SaveMulti(gateway.ID, gateway.Type)
    if err != nil {
        json, _ := util.EncodeResponseJson(5000003, enum.CODE_5000003, nil)
        _ = gate.Response.WriteJson(json)
        gate.Exit()
    }

    //判断并响应
    if isSaveMap == false {
        json, _ := util.EncodeResponseJson(5000003, enum.CODE_5000003, nil)
        _ = gate.Response.WriteJson(json)
        gate.Exit()
    }

    // 添加操作日志
    logingLogic := logic.NewLogingLogic()
    logingLogic.AddLog(gate.Request, "sms_gateway", "编辑通道网关信息", gateway.ID)

    ret, _ := util.EncodeResponseJson(0, enum.CODE_0, nil)
    _ = gate.Response.WriteJson(ret)
    gate.Exit()
}
