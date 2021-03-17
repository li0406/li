
[TOC]


# 时间对象

创建`gtime.Time`对象可以通过标准库`time.Time`对象、Unix时间戳、时间字符串（如：`2018-07-18 12:01:00`）、自定义时间字符串（需要给定格式，支持自定义格式及标准库格式）。

## 示例1，自定义时间格式化语法
```go
package main

import (
    "fmt"
    "github.com/gogf/gf/g/os/gtime"
)

func main() {
    formats := []string{
        "Y-m-d H:i:s.u",
        "D M d H:i:s T O Y",
        "\\T\\i\\m\\e \\i\\s: h:i:s a",
        "2006-01-02T15:04:05.000000000Z07:00",
    }
    t := gtime.Now()
    for _, f := range formats {
        fmt.Println(t.Format(f))
    }
}
```
在该示例中，我们给定了四种`format`格式，并将当前时间用这四种格式转换后打印出来。执行后，输出结果如下：
```html
2018-07-22 11:17:13.797
Sun Jul 22 11:17:13 CST +0800 2018
Time is: 11:17:13 am
2006-01-02CST15:04:05.000000000Z07:00
```
可以看到，这个示例演示了几个需要注意的地方：
1. 如果使用的字母与格式化字符冲突时，可以使用`\`符号转义该字符，这样时间格式解析器会认为该字符不是格式化字符，而是普通字母。因此这里的第三个字符串示例输出为：`Time is: 11:17:13 am`
2. 使用`Format`方法接收的是自定义的时间格式化语法（如：`Y-m-d H:i:s`），而非标准库的时间格式语法（如：`2006-01-02 15:04:05`），因此在这里的第四个字符串示例中原样输出参数值；

## 示例2，标准库时间格式化语法
```go
package main

import (
    "fmt"
    "github.com/gogf/gf/g/os/gtime"
)

func main() {
    formats := []string{
        "2006-01-02 15:04:05.000",
        "Mon Jan _2 15:04:05 MST 2006",
        "Time is: 03:04:05 PM",
        "2006-01-02T15:04:05.000000000Z07:00 MST",
    }
    t := gtime.Now()
    for _, f := range formats {
        fmt.Println(t.Layout(f))
    }
}
```
在该示例中，我们使用四种标准库的时间格式化语法格式化当前的时间并输出结果到终端。执行后，输出结果为：
```html
2018-07-22 11:28:13.945
Sun Jul 22 11:28:13 CST 2018
Time is: 11:28:13 AM
2018-07-22T11:28:13.945153275+08:00 CST
```
有几个需要说明的地方：
1. 自定义时间格式化语法与标准库时间格式化语法并不冲突，前者使用`Format`方法，后者使用`Layout`方法行格式化，相互独立，互不冲突，无法混用；
2. 标准库的时间格式化语法自有特点，是不是感觉有点复杂；

## 示例3，时间对象链式操作
```go
package main

import (
    "fmt"
    "github.com/gogf/gf/g/os/gtime"
    "time"
)

func main() {
    // 去年今日
    fmt.Println(gtime.Now().AddDate(-1, 0, 0).Format("Y-m-d"))

    // 去年今日，UTC时间
    fmt.Println(gtime.Now().AddDate(-1, 0, 0).Format("Y-m-d H:i:s T"))
    fmt.Println(gtime.Now().AddDate(-1, 0, 0).UTC().Format("Y-m-d H:i:s T"))

    // 下个月1号凌晨0点整
    fmt.Println(gtime.Now().AddDate(0, 1, 0).Format("Y-m-d 00:00:00"))

    // 1个小时前
    fmt.Println(gtime.Now().Add(-time.Hour).Format("Y-m-d H:i:s"))
}
```
执行后，输出结果为：
```html
2017-07-22
2017-07-22 11:42:36 CST
2017-07-22 03:42:36 UTC
2018-08-22 00:00:00
2018-07-22 10:42:36
```
该示例比较简单，便不多赘述。
