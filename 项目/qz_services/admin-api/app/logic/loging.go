package logic

import (
    "admin-api/app/model"
    "admin-api/library/util"
    "fmt"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/encoding/gjson"
    "github.com/gogf/gf/g/net/ghttp"
)

type LogingLogic struct {
}

//  日志logic  实例化方式
func NewLogingLogic() *LogingLogic {
    return &LogingLogic{}
}

// 添加日志
func (that *LogingLogic) AddLog (request *ghttp.Request, module string, remark string, params interface{}) {
    logData := make(map[string]interface{})
    logData["datetime"] = util.TimeToDate(util.GetTime(), 19)
    logData["userid"] = model.AdminUser.UserId
    logData["nickname"] = model.AdminUser.NickName
    logData["clientIp"] = request.GetClientIp()
    logData["method"] = request.Method
    logData["url"] = request.Router.Uri
    logData["module"] = module
    logData["remark"] = remark
    logData["params"] = params

    res,_ := gjson.Encode(logData)
    content := fmt.Sprintf("%s  %s\n", logData["datetime"], string(res))

    if logPath,err := that.GetLogPath(); err == nil {
        logname := fmt.Sprintf("%s/%s.log", logPath, util.TimeToDate(util.GetTime(), 10))
        _ = util.WriteWithIoutil(logname, content)
    }
}

// 获取日志保存路径
func (that *LogingLogic) GetLogPath () (string, error) {
    config := g.Config()
    logPath := config.GetString("setting.logpath")
    logPathDir := fmt.Sprintf("%s/operate", logPath)

    err := util.CheckAndMakeDir(logPathDir)
    return logPathDir, err
}
