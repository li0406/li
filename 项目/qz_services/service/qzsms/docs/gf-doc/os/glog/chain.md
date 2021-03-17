# 链式操作

`glog`模块支持非常简便的`链式操作`方式，相关链式操作方法如下：
```go
// 重定向日志输出接口
func To(writer io.Writer) *Logger
// 日志内容输出到目录
func Path(path string) *Logger
// 设置日志文件分类
func Cat(category string) *Logger
// 设置日志文件格式
func File(file string) *Logger
// 设置日志打印级别
func Level(level int) *Logger
// 是否开启trace打印
func Backtrace(enabled bool) *Logger
// 是否开启终端输出
func Stdout(enabled...bool) *Logger
// 是否输出日志头信息
func Header(enabled...bool) *Logger
// 输出日志调用行号信息
func Line(long...bool) *Logger
// 设置文件回溯值
func Skip(skip int) *Logger
// 异步输出日志
func Async(enabled...bool) *Logger
```

## 示例1, 基本使用
```go
package main

import (
    "github.com/gogf/gf/g/os/glog"
    "github.com/gogf/gf/g/os/gfile"
    "github.com/gogf/gf/g"
)

func main() {
    path := "/tmp/glog-cat"
    glog.SetPath(path)
    glog.Stdout(false).Cat("cat1").Cat("cat2").Println("test")
    list, err := gfile.ScanDir(path, "*", true)
    g.Dump(err)
    g.Dump(list)
}
```
执行后，输出结果为：
```html
null
[
	"/tmp/glog-cat/cat1",
	"/tmp/glog-cat/cat1/cat2",
	"/tmp/glog-cat/cat1/cat2/2018-10-10.log",
]
```

## 示例2, 打印调用行号

```go
package main

import (
	"github.com/gogf/gf/g/os/glog"
)

func main() {
	glog.Line().Println("this is the short file name with its line number")
	glog.Line(true).Println("lone file name with line number")
}
```
执行后，终端输出结果为：
```html
2019-05-23 09:22:58.141 glog_line.go:8: this is the short file name with its line number
2019-05-23 09:22:58.142 /Users/john/Workspace/Go/GOPATH/src/github.com/gogf/gf/geg/os/glog/glog_line.go:9: lone file name with line number
```

## 示例3, 文件回溯`Skip`

有时我们通过一些模块封装了`glog`模块来打印日志，例如封装了一个`common`包通过`common.PrintLog`来打印日志，这个时候打印出来的调用文件行号总是同一个位置，因为对于`glog`来讲，它的调用方即总是`common.PrintLog`方法。这个时候，我们可以通过设置回溯值来跳过回溯的文件数，使用`SetBacktraceSkip`或者链式方法`Skip`实现。

> 文件回溯值的设置同样也会影响`Backtrace`调用回溯打印结果。

```go
package main

import (
	"github.com/gogf/gf/g/os/glog"
)

func PrintLog(content string) {
	glog.Skip(1).Line().Println("line number with skip:", content)
	glog.Line().Println("line number without skip:", content)
}

func main() {
	PrintLog("just test")
}
```
执行后，终端输出结果为：
```html
2019-05-23 19:30:10.984 glog_line2.go:13: line number with skip: just test
2019-05-23 19:30:10.984 glog_line2.go:9: line number without skip: just test
```

