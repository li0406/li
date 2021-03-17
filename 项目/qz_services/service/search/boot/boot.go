package boot

import (
	"github.com/gogf/gf/g"
	"github.com/gogf/gf/g/net/ghttp"
)

// 用于应用初始化。
func init() {
	c := g.Config()
	s := g.Server()

	// 配置对象及视图对象配置
	c.AddPath("config")

	//addr := c.GetString("setting.addr")
	port := c.GetInt("setting.port")

	// Web Server配置
	s.SetNameToUriType(ghttp.NAME_TO_URI_TYPE_ALLLOWER)
	s.SetErrorLogEnabled(true)
	s.SetAccessLogEnabled(true)
	//s.SetAddr(addr)
	s.SetPort(port)
}