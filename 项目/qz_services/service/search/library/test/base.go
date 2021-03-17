package test

import "fmt"

var (
    list = make(map[string] string)
)

func init()  {
    list["a"] = "a1"
    fmt.Println("list")
}
