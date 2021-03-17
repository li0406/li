package logic

import (
    "admin-api/app/model"
    "github.com/gogf/gf/g"
    "strconv"
    "strings"
    "time"
)

// 三方网关设置 逻辑
type GatewayTypeMap struct {
}

//  实例化三方网关model
var gatewayMapModel = model.NewGatewayTypeMapModel()

//  网关logic  实例化方式
func NewGatewayTypeMapService() *GatewayTypeMap {
    return &GatewayTypeMap{}
}


//  保存数据
func (gtm *GatewayTypeMap) SaveMulti(gatewayId int,typeS string) (ret bool, err error) {
    var data g.List
    for _, value := range strings.Split(typeS, ",") {
        typeValue, err := strconv.Atoi(value)
        if err == nil {
            mapData := g.Map{
                "type":       typeValue,
                "gateway_id": gatewayId,
                "creator":    model.AdminUser.UserId,
                "operator":   model.AdminUser.UserId,
                "created_at": time.Now().Unix(),
                "updated_at": time.Now().Unix(),
            }
            data = append(data,mapData)
        }
    }
    ret, err = gatewayMapModel.SaveMulti(data)
    return
}

// 删除数据
func (gtm GatewayTypeMap) Delete(gatewayId int) (ret bool, err error) {
    condition := g.Map{"gateway_id": gatewayId}
    ret, err = gatewayMapModel.Delete(condition)
    return
}
