package boot

/*
 *  初始化配置
 */
import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/net/ghttp"
    "github.com/gogf/gf/g/os/genv"
)

func init() {
    s := ghttp.GetServer()
    // Web Server配置
    //s.SetServerRoot("public")
    s.SetNameToUriType(ghttp.NAME_TO_URI_TYPE_ALLLOWER)
    s.SetLogPath(genv.Get("APP_LOGPATH", g.Config().GetString("setting.logpath")))
    s.SetErrorLogEnabled(true)
    s.SetAccessLogEnabled(true)
    s.SetPort(20000)
}
