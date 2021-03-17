package util

import (
	"github.com/gogf/gf/os/glog"
	"strings"
)

// 获取配置文件中的日志等级
func GetLogLevel(logLevel string) (level int) {
	logLevel = strings.ToUpper(logLevel)
	level = glog.LEVEL_ERRO
	switch logLevel {
	case "ALL":
		level = glog.LEVEL_ALL
	case "DEV":
		level = glog.LEVEL_ALL
	case "PROD":
		level = glog.LEVEL_PROD
	case "DEBUG":
		level = glog.LEVEL_DEBU
	case "INFO":
		level = glog.LEVEL_INFO
	case "NOTIFY":
		level = glog.LEVEL_NOTI
	case "WARNING":
		level = glog.LEVEL_WARN
	case "ERROR":
		level = glog.LEVEL_ERRO
	case "CRIT":
		level = glog.LEVEL_CRIT
	}
	return level
}
