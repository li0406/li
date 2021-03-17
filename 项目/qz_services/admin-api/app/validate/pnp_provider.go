package validate

import (
    "github.com/gogf/gf/g/util/gvalid"
)

type PnpProviderValidate struct {
}


// 验证规则
var pnpProviderValidateRules = []string {
    "slot@required",
    "name@required",
    "config@required",
    "is_enabled@required",
}

// 自定义错误提示（单独定义方便维护）
var pnpProviderValidateMsgs = map[string]interface{} {
    "slot" : "请先填写唯一标识",
    "name" : "请先填写供应商名称",
    "config" : "请填写配置",
    "is_enabled" : "请选择是否启用",
}

// 验证规则
var pnpProviderStatusValidateRules = []string {
    "id@required",
    "enabled@required",
}

// 自定义错误提示（单独定义方便维护）
var pnpProviderStatusValidateMsgs = map[string]interface{} {
    "id" : "缺少供应商ID",
    "enabled" : "缺少供应商开启状态",
}


func NewPnpProviderValidate() *PnpProviderValidate {
    return &PnpProviderValidate{}
}

func (c *PnpProviderValidate)Check(data interface{}) *gvalid.Error {
    err := gvalid.CheckMap(data,pnpProviderValidateRules,pnpProviderValidateMsgs)
    return err
}

func (c *PnpProviderValidate)StatusCheck(data interface{}) *gvalid.Error {
    err := gvalid.CheckMap(data,pnpProviderStatusValidateRules,pnpProviderStatusValidateMsgs)
    return err
}