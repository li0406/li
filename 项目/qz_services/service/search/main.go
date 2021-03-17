package main

import (
    "fmt"
    "github.com/gogf/gf/g"
    _ "search/boot"
    _ "search/router"
	_ "search/library/redis"
    _"search/library/prometheus/clsfactory"
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

func main()  {

    Version()
    g.Server().Run()
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

