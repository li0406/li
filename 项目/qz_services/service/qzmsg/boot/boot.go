package boot

import (
	"github.com/gogf/gf/g"
	"github.com/gogf/gf/g/os/glog"
	"github.com/gogf/gf/g/net/ghttp"
)

// 用于应用初始化。
func init() {
	v := g.View()
	c := g.Config()
	s := g.Server()

	// 配置对象及视图对象配置
	_ = c.AddPath("config")
	//v.AddPath("template")
	v.SetDelimiters("${", "}")

	// 获取配置文件
	logPath := c.GetString("setting.logpath")
	stdout := c.GetBool("setting.stdout")
	addr := c.GetString("setting.addr")
	port := c.GetInt("setting.port")

	// glog配置
	glog.SetPath(logPath)
	glog.SetStdoutPrint(stdout)

	// Web Server配置
	// s.SetServerRoot("public")
	s.SetLogPath(logPath)
	s.SetNameToUriType(ghttp.NAME_TO_URI_TYPE_ALLLOWER)
	s.SetErrorLogEnabled(true)
	s.SetAccessLogEnabled(true)
	s.SetAddr(addr)
	s.SetPort(port)
}