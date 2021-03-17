[TOC]

# gtype

并发安全基本类型。

**使用场景**：

`gtype`使用得非常频繁，任何需要并发安全的场景下都适用。
在普通的并发安全场景中，一个基本类型的变量，特别是一个`struct`的属性，往往使用使用互斥(读写)锁或者多把(读写)锁来进行管理。
但是在这样的使用中，`变量/struct/属性`的操作性能**十分低下**，且由于互斥锁机制的存在往往使得操作变得相当复杂，必须小心翼翼地维护好`变量/属性`的并发安全性(特别是使用到的`(RW)Mutex`)。

`gtype`针对于最常用的基本数据类型，提供了对应的并发安全数据类型，便于在并发安全场景下更好地维护变量/属性，开发者无需在struct中再创建和维护繁琐的`(RW)Mutex`。且`gtype`内部绝大多数基本类型都使用了`atomic`原子操作来维护并发安全性，因此效率往往会比`(RW)Mutex`互斥锁高出数十倍。

**使用方式**：

```go
import "github.com/gogf/gf/g/container/gtype"
```

**接口文档**：

https://godoc.org/github.com/gogf/gf/g/container/gtype


## 性能测试

基准测试结果如下:
```shell
john@john-B85M:~/Workspace/Go/GOPATH/src/github.com/gogf/gf/g/container/gtype$ go test -bench=".*"  -benchmem
goos: linux
goarch: amd64
pkg: github.com/gogf/gf/g/container/gtype
BenchmarkInt_Set-4            300000000           5.87 ns/op        0 B/op        0 allocs/op
BenchmarkInt_Val-4            2000000000          0.46 ns/op        0 B/op        0 allocs/op
BenchmarkInt_Add-4            300000000           5.86 ns/op        0 B/op        0 allocs/op
BenchmarkInt32_Set-4          300000000           5.87 ns/op        0 B/op        0 allocs/op
BenchmarkInt32_Val-4          2000000000          0.47 ns/op        0 B/op        0 allocs/op
BenchmarkInt32_Add-4          300000000           5.85 ns/op        0 B/op        0 allocs/op
BenchmarkInt64_Set-4          300000000           5.88 ns/op        0 B/op        0 allocs/op
BenchmarkInt64_Val-4          2000000000          0.46 ns/op        0 B/op        0 allocs/op
BenchmarkInt64_Add-4          300000000           5.88 ns/op        0 B/op        0 allocs/op
BenchmarkUint_Set-4           300000000           5.88 ns/op        0 B/op        0 allocs/op
BenchmarkUint_Val-4           2000000000          0.46 ns/op        0 B/op        0 allocs/op
BenchmarkUint_Add-4           300000000           5.87 ns/op        0 B/op        0 allocs/op
BenchmarkUint32_Set-4         300000000           5.86 ns/op        0 B/op        0 allocs/op
BenchmarkUint32_Val-4         2000000000          0.50 ns/op        0 B/op        0 allocs/op
BenchmarkUint32_Add-4         200000000           5.86 ns/op        0 B/op        0 allocs/op
BenchmarkUint64_Set-4         300000000           5.86 ns/op        0 B/op        0 allocs/op
BenchmarkUint64_Val-4         2000000000          0.47 ns/op        0 B/op        0 allocs/op
BenchmarkUint64_Add-4         300000000           5.85 ns/op        0 B/op        0 allocs/op
BenchmarkBool_Set-4           300000000           5.85 ns/op        0 B/op        0 allocs/op
BenchmarkBool_Val-4           2000000000          0.46 ns/op        0 B/op        0 allocs/op
BenchmarkString_Set-4         20000000            90.1 ns/op       23 B/op        1 allocs/op
BenchmarkString_Val-4         2000000000          1.58 ns/op        0 B/op        0 allocs/op
BenchmarkBytes_Set-4          20000000            76.2 ns/op       35 B/op        2 allocs/op
BenchmarkBytes_Val-4          2000000000          1.58 ns/op        0 B/op        0 allocs/op
BenchmarkInterface_Set-4      50000000            30.7 ns/op        8 B/op        0 allocs/op
BenchmarkInterface_Val-4      2000000000          0.74 ns/op        0 B/op        0 allocs/op
BenchmarkAtomicValue_Store-4  50000000            27.3 ns/op        8 B/op        0 allocs/op
BenchmarkAtomicValue_Load-4   2000000000          0.73 ns/op        0 B/op        0 allocs/op
PASS
ok   github.com/gogf/gf/g/container/gtype 49.454s
```


## 使用示例

`gtype`并发安全基本类型的使用非常简单，往往就类似以下几个方法(以`gtype.Int`类型举例)：

```go
// 创建并发安全基本类型对象
func NewInt(value...int) *Int
// 克隆一个对象
func (t *Int) Clone() *Int
// 设置变量值，并返回旧的变量值
func (t *Int) Set(value int) (old int)
// 获取当前变量值
func (t *Int) Val() int
// (整型/浮点型有效)数值 增加/删除 delta
func (t *Int) Add(delta int) int {
```

基本使用示例：

```go
package main

import (
    "github.com/gogf/gf/g/container/gtype"
    "fmt"
)

func main() {
    // 创建一个Int型的并发安全基本类型对象
    i := gtype.NewInt()

    // 设置值
    fmt.Println(i.Set(10))

    // 获取值
    fmt.Println(i.Val())

    // 数值-1，并返回修改之后的数值
    fmt.Println(i.Add(-1))
}
```

执行后，输出结果为：
```html
0
10
9
```
