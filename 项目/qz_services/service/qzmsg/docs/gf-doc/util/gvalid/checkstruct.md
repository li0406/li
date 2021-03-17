# 多数据校验 - CheckStruct

`CheckStruct`的使用方式同`CheckMap`，除了第一个参数为`struct类型`的结构体对象（也可以是对象指针）。**但是需要注意的一个细节是，struct的属性会有`默认值`，因此某些情况下会引起`required`规则的失效，因此根据实际情况配合多种规则一起校验会是一个比较严谨的做法。**

接口文档：
https://godoc.org/github.com/gogf/gf/g/util/gvalid
```go
func CheckStruct(object interface{}, rules interface{}, msgs ...CustomMsg) *Error
```

### 示例1，使用`map`指定规则及提示信息
```go
package main

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/util/gvalid"
)

func main() {
    type Object struct {
        Name string
        Age  int
    }
    rules := map[string]string {
        "Name" : "required|length:6,16",
        "Age"  : "between:18,30",
    }
    msgs  := map[string]interface{} {
        "Name" : map[string]string {
            "required" : "名称不能为空",
            "length"   : "名称长度为:min到:max个字符",
        },
        "Age"  : "年龄为18到30周岁",
    }
    obj := Object{Name : "john"}
    // 也可以是指针
    // obj := &Object{Name : "john"}
    if e := gvalid.CheckStruct(obj, rules,msgs); e != nil {
        g.Dump(e.Maps())
    }
}

/*
输出：
{
	"Age": {
		"between": "年龄为18到30周岁"
	},
	"Name": {
		"length": "名称长度为6到16个字符"
	}
}

*/
```

在以上示例中，```Age```属性由于默认值`0`的存在，因此会引起```required```规则的失效，因此这里没有使用```required```规则而是使用```between```规则来进行校验。

### 示例2，使用gvallid tag绑定规则及提示信息


使用`gvalid tag`设置的规则，其校验结果是顺序性的。

```go
package main

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/util/gvalid"
)

type User struct {
    Uid   int    `gvalid:"uid      @integer|min:1"`
    Name  string `gvalid:"name     @required|length:6,30#请输入用户名称|用户名称长度非法"`
    Pass1 string `gvalid:"password1@required|password3"`
    Pass2 string `gvalid:"password2@required|password3|same:password1#||两次密码不一致，请重新输入"`
}

func main() {
    user := &User{
        Name : "john",
        Pass1: "Abc123!@#",
        Pass2: "123",
    }

    // 使用结构体定义的校验规则和错误提示进行校验
    if e := gvalid.CheckStruct(user, nil); e != nil {
        g.Dump(e.Maps())
    }

    // 自定义校验规则和错误提示，对定义的特定校验规则和错误提示进行覆盖
    rules := map[string]string {
        "Uid" : "required",
    }
    msgs  := map[string]interface{} {
        "Pass2" : map[string]string {
            "password3" : "名称不能为空",
        },
    }
    if e := gvalid.CheckStruct(user, rules, msgs); e != nil {
        g.Dump(e.Maps())
    }
}
```
可以看到，我们可以对在`struct`定义时使用了`gvalid`的标签属性(`gvalid tag`)来绑定校验的规则及错误提示信息，规则如下：
```html
[属性别名@]校验规则[#错误提示]
```

可以看到，`CheckStruct`和`CheckMap`的`gvalid tag`规则是一样的，不过在字段的含义上有一点点小区别：
- `属性别名` 和 `错误提示` 为**非必需字段**，`校验规则` 是**必需字段**；
- `属性别名` 非必需字段，指定在校验中使用的对应struct属性的别名，同时校验成功后的map中的也将使用该别名返回，例如在处理请求表单时比较有用，因为表单的字段名称往往和struct的属性名称不一致；
- `错误提示` 非必需字段，表示自定义的错误提示信息，当规则校验时对默认的错误提示信息进行覆盖；

在此示例代码中，`same:password1`规则同使用`same:Pass2`规则是一样的效果。也就是说，在数据校验中，可以同时使用原有的struct属性名称，也可以使用别名。但是，返回的结果中只会使用别名返回，这也是别名最大的用途。

此外，在使用`CheckStruct`方法对struct对象进行校验时，也可以传递校验或者和错误提示参数，这个时候会覆盖struct在定义时绑定的对应参数。

以上示例执行后，输出结果为：
```json
{
	"name": {
		"length": "用户名称长度非法"
	},
	"password2": {
		"password3": "密码格式不合法，密码格式为任意6-18位的可见字符，必须包含大小写字母、数字和特殊字符",
		"same": "两次密码不一致，请重新输入"
	},
	"uid": {
		"min": "字段最小值为1"
	}
}
{
	"name": {
		"length": "用户名称长度非法"
	},
	"password2": {
		"password3": "密码格式不合法，密码格式为任意6-18位的可见字符，必须包含大小写字母、数字和特殊字符",
		"same": "两次密码不一致，请重新输入"
	},
	"uid": {
		"min": "字段最小值为1"
	}
}
```
