# 基本类型转换
常用基本类型的转换方法比较简单，我们这里使用一个例子来演示转换方法的使用及效果。
```go
package main

import (
    "fmt"
    "github.com/gogf/gf/g/util/gconv"
)

func main() {
    i := 123.456
    fmt.Printf("%10s %v\n", "Int:",        gconv.Int(i))
    fmt.Printf("%10s %v\n", "Int8:",       gconv.Int8(i))
    fmt.Printf("%10s %v\n", "Int16:",      gconv.Int16(i))
    fmt.Printf("%10s %v\n", "Int32:",      gconv.Int32(i))
    fmt.Printf("%10s %v\n", "Int64:",      gconv.Int64(i))
    fmt.Printf("%10s %v\n", "Uint:",       gconv.Uint(i))
    fmt.Printf("%10s %v\n", "Uint8:",      gconv.Uint8(i))
    fmt.Printf("%10s %v\n", "Uint16:",     gconv.Uint16(i))
    fmt.Printf("%10s %v\n", "Uint32:",     gconv.Uint32(i))
    fmt.Printf("%10s %v\n", "Uint64:",     gconv.Uint64(i))
    fmt.Printf("%10s %v\n", "Float32:",    gconv.Float32(i))
    fmt.Printf("%10s %v\n", "Float64:",    gconv.Float64(i))
    fmt.Printf("%10s %v\n", "Bool:",       gconv.Bool(i))
    fmt.Printf("%10s %v\n", "String:",     gconv.String(i))

    fmt.Printf("%10s %v\n", "Bytes:",      gconv.Bytes(i))
    fmt.Printf("%10s %v\n", "Strings:",    gconv.Strings(i))
    fmt.Printf("%10s %v\n", "Ints:",       gconv.Ints(i))
    fmt.Printf("%10s %v\n", "Floats:",     gconv.Floats(i))
    fmt.Printf("%10s %v\n", "Interfaces:", gconv.Interfaces(i))
}
```
执行后，输出结果为：
```
      Int: 123
     Int8: 123
    Int16: 123
    Int32: 123
    Int64: 123
     Uint: 123
    Uint8: 123
   Uint16: 123
   Uint32: 123
   Uint64: 123
  Float32: 123.456
  Float64: 123.456
     Bool: true
   String: 123.456
    Bytes: [119 190 159 26 47 221 94 64]
  Strings: [123.456]
     Ints: [123]
   Floats: [123.456]
Interfaces: [123.456]
```
