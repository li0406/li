package boot

import (
	"github.com/gogf/gf/frame/g"
	"github.com/gogf/gf/net/ghttp"
	"log"
	"passport/library/util"
)

// 用于应用初始化。
func init() {
	c := g.Config()
	s := g.Server()

	// 设置配置文件
	err := c.AddPath("config")
	if err != nil {
		log.Fatal(err)
	}
	//s.SetAddr(addr)

	//日志
	//日志level
	configLevel := c.GetString("log.level")

	g.SetDebug(c.GetBool("app.debug"))
	g.SetLogLevel(util.GetLogLevel(configLevel))
	s.SetLogPath(c.GetString("log.path"))
	s.SetErrorLogEnabled(true)
	s.SetAccessLogEnabled(true)

	//addr := c.GetString("setting.addr")
	port := c.GetInt("app.port")
	// Web Server配置
	s.SetNameToUriType(ghttp.URI_TYPE_FULLNAME)
	s.SetPort(port)
}
