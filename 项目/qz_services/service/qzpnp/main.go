package main

import (
	"fmt"
	"qzpnp/app/task"
	_ "qzpnp/boot"
	_ "qzpnp/router"

	"github.com/gogf/gf/frame/g"
	"github.com/gogf/gf/os/gcron"
)

var (
	AppName      string // 应用名称
	AppVersion   string // 应用版本
	BuildVersion string // 编译版本
	BuildTime    string // 编译时间
	GitRevision  string // Git版本
	GitBranch    string // Git分支
	GoVersion    string // Golang信息
)

func main() {
	Version()
	Cron() // 内建计划任务
	g.Server().Run()
}

func Cron() {
	gcron.Add("@every 3m", func() { task.NewVoiceTask().Run() })                         // 录音短轮询处理 3分钟执行一次
	gcron.Add("10 10 02 * * *", func() { task.NewPnpTask().Run() })                      // 电话解绑状态同步
	gcron.Add("30 30 03 * * *", func() { task.NewMailTask().RunRecordCallListReport() }) // 邮件报告昨天电话拨打记录
	g.Dump(gcron.Entries())
	gcron.Start("cron")
}

func Version() {
	fmt.Printf("App Name:\t%s\n", AppName)
	fmt.Printf("App Version:\t%s\n", AppVersion)
	fmt.Printf("Build version:\t%s\n", BuildVersion)
	fmt.Printf("Build time:\t%s\n", BuildTime)
	fmt.Printf("Git revision:\t%s\n", GitRevision)
	fmt.Printf("Git branch:\t%s\n", GitBranch)
	fmt.Printf("Golang Version: %s\n", GoVersion)
}
