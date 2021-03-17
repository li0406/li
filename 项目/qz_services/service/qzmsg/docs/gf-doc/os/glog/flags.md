# Flags额外特性

`flags`用于控制日志组件的额外特性开关，这些属性使用常量进行组合控制，包括：
```go
F_ASYNC      = 1 << iota // 开启日志异步输出
F_FILE_LONG              // 打印调用行号信息，完整绝对路径，例如：/a/b/c/d.go:23
F_FILE_SHORT             // 打印调用行号信息，仅打印文件名，例如：d.go:23，覆盖 F_FILE_LONG.
F_TIME_DATE              // 打印当前日期，如：2009-01-23
F_TIME_TIME              // 打印当前时间，如：01:23:23
F_TIME_MILLI             // 打印当前时间+毫秒，如：01:23:23.675
F_TIME_STD = F_TIME_DATE | F_TIME_MILLI // (默认)打印当前日期+时间+毫秒，如：2009-01-23 01:23:23.675
```
使用示例：
```go
package main

import (
	"github.com/gogf/gf/g/os/glog"
)

func main() {
	l := glog.New()
	l.SetFlags(glog.F_TIME_TIME|glog.F_FILE_SHORT)
	l.Println("time and short line number")
	l.SetFlags(glog.F_TIME_MILLI|glog.F_FILE_LONG)
	l.Println("time with millisecond and long line number")
	l.SetFlags(glog.F_TIME_STD|glog.F_FILE_LONG)
	l.Println("standard time format and long line number")
}
```
执行后，终端输出结果为：
```html
09:25:49 glog_flags.go:10: time and short line number
09:25:49.310 /Users/john/Workspace/Go/GOPATH/src/github.com/gogf/gf/geg/os/glog/glog_flags.go:12: time with millisecond and long line number
2019-05-23 09:25:49.310 /Users/john/Workspace/Go/GOPATH/src/github.com/gogf/gf/geg/os/glog/glog_flags.go:14: standard time format and long line number
```
