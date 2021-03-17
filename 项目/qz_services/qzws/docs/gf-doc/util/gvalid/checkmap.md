[TOC]

多数据校验即支持同时对多条数据进行校验，需要给定校验规则，并且可以自定义出错时的错误信息。
其中比较重要且复杂的是校验规则参数的定义。

校验方法：
https://godoc.org/github.com/gogf/gf/g/util/gvalid
```go
// 自定义错误信息: map[键名] => 字符串|map[规则]错误信息
type CustomMsg = map[string]interface{}

func CheckMap(params interface{}, rules interface{}, msgs ...CustomMsg) *Error
```
1. 其中`params`参数支持任意 `map` 数据类型；
1. `rules`参数支持 `[]string` / `map[string]string` 数据类型，前面一种类型支持返回校验结果顺序，后一种不支持(因为`map`是无序的)；
1. `rules`参数中的 `map[string]string` 是一个二维的关联数组/哈希表，第一维**键名**为参数键名，第二维为带有错误的校验*规则名称**，键值为错误信息；
1. `msgs`参数为自定义的错误信息，为非必需参数，类型为`CustomMsg`（`map[string]interface{}`）具体使用请参考后续示例；

# 多数据校验 - CheckMap


## 示例1，多数据多规则校验，使用默认错误提示
```go
params := map[string]interface{} {
    "passport"  : "",
    "password"  : "123456",
    "password2" : "1234567",
}
rules := map[string]string {
    "passport"  : "required|length:6,16",
    "password"  : "required|length:6,16|same:password2",
    "password2" : "required|length:6,16",
}
if e := gvalid.CheckMap(params, rules); e != nil {
    fmt.Println(e.Maps())
}

// 输出： map[passport:map[required:字段不能为空 length:字段长度为6到16个字符] password:map[same:字段值不合法]]
```

## 示例2，多数据多规则校验，使用自定义错误提示
```go
params := map[string]interface{} {
    "passport"  : "",
    "password"  : "123456",
    "password2" : "1234567",
}
rules := map[string]string {
    "passport"  : "required|length:6,16",
    "password"  : "required|length:6,16|same:password2",
    "password2" : "required|length:6,16",
}
msgs  := map[string]interface{} {
    "passport" : "账号不能为空|账号长度应当在:min到:max之间",
    "password" : map[string]string {
        "required" : "密码不能为空",
        "same"     : "两次密码输入不相等",
    },
}
if e := gvalid.CheckMap(params, rules, msgs); e != nil {
    fmt.Println(e.String())
}

// 输出：账号不能为空; 账号长度应当在6到16之间; 两次密码输入不相等
```

该示例同时也展示了`msgs`自定义错误信息传递的两种数据类型，```string```或者```map[string]string```。其中```map[string]string```类型参数需要指定对应字段、对应规则的错误提示信息，是一个二维的“关联数组”。

# 多数据校验 - 校验结果顺序性

如果将上面的**例2**程序多执行几次之后会发现，返回的结果是没有排序的，而且字段及规则输出的先后顺序完全是随机的。即使我们使用`FirstItem`,  `FirstString()`等其他方法获取校验结果也是一样，返回的校验结果不固定。

那是因为校验的规则我们传递的是`map`类型，而golang的map类型并不具有有序性，因此校验的结果和规则一样是随机的，同一个校验结果的同一个校验方法多次获取结果值返回的可能也不一样了。

## 使用`gvalid tag`进行顺序性改进

校验结果中如果不满足`required`那么返回对应的错误信息，否则才是后续的校验错误信息；也就是说，返回的错误信息应当和我设定规则时的顺序一致。改进如下：

```go
package main

import (
    "fmt"
    "github.com/gogf/gf/g/util/gvalid"
)

func main() {
    params := map[string]interface{} {
        "passport"  : "",
        "password"  : "123456",
        "password2" : "1234567",
    }
    rules := []string {
        "passport@required|length:6,16#账号不能为空|账号长度应当在:min到:max之间",
        "password@required|length:6,16|same:password2#密码不能为空|密码长度应当在:min到:max之间|两次密码输入不相等",
        "password2@required|length:6,16#",
    }
    if e := gvalid.CheckMap(params, rules); e != nil {
        fmt.Println(e.Map())
        fmt.Println(e.FirstItem())
        fmt.Println(e.FirstString())
    }
    // 输出:
    // map[required:账号不能为空 length:账号长度应当在6到16之间]
    // passport map[required:账号不能为空 length:账号长度应当在6到16之间]
    // 账号不能为空
}
```
可以看到，我们想要校验结果满足顺序性，只需要将`rules`参数的类型修改为`[]string`，按照一定的规则设定即可，并且`msg`参数既可以定义到`rules`参数中，也可以分开传入(第三个参数)。

`rules`的这种满足顺序性校验结果返回的规则，我们称之为`gvalid tag`，在后续的结构体对象校验中，我们也可以遇得到这种`gvalid tag`，语法是一样的。`gvalid tag`的书写规则如下：
```
[键名名称@]校验规则[#错误提示]
```
其中：
- `属性别名` 和 `错误提示` 为**非必需字段**，`校验规则` 是**必需字段**；
- `键名名称` 非必需字段，指需要校验的map中得键名名称，需要和`params`参数中得键名一致；
- `错误提示` 非必需字段，示自定义的错误提示信息，当规则校验时对默认的错误提示信息进行覆盖；该字段也可以通过`msg`参数传入覆盖；