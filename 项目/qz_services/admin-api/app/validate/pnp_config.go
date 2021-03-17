package validate

import (
    "fmt"
    "github.com/gogf/gf/g/util/gvalid"
)

type PnpConfigValidate struct {
}

// 验证规则
var pnpConfigValidateRules = []string {
    "pnp_switch@required",
    "pnp_provider@required",
    "pnp_expire@required",
}


// 自定义错误提示（单独定义方便维护）
var pnpConfigValidateMsgs = map[string]interface{} {
    "pnp_switch" : "请选择开启状态",
    "pnp_provider" : "请先填写供应商名称",
    "pnp_expire" : "请填写绑定时长",
}

func NewPnpConfigValidate() *PnpConfigValidate {
    return &PnpConfigValidate{}
}

func (c *PnpConfigValidate)Check(data interface{}) *gvalid.Error {
    fmt.Println(data)
    err := gvalid.CheckMap(data,pnpConfigValidateRules,pnpConfigValidateMsgs)
    return err
}


