[TOC]

接口文档：
https://godoc.org/github.com/gogf/gf/g/database/gdb

# 数据结构

查询结果的数据结构如下：
```go
type Value       []byte                 // 返回数据表记录值
type Result      []Record               // 返回数据表记录列表
type Record      map[string]Value       // 返回数据表记录键值对
```

`Value/Record/Result`用于ORM操作的结果数据类型，其中```Result```表示数据表记录列表，```Record```表示一条数据表记录，```Value```表示记录中的一条键值数据。

# Result结果集处理

`Result/Record`数据类型根据数据结果集操作的需要，往往需要根据记录中**特定的字段**作为键名进行数据检索，因此它包含多个用于转换`Map/List`的方法，同时也包含了常用数据结构`JSON/XML`的转换方法，如下：
```go
// 数据表记录列表
type Result
    func (r Result) ToList() List
	func (r Result) ToJson() string
    func (r Result) ToXml() string
    func (r Result) ToStringMap(key string) map[string]Map
    func (r Result) ToIntMap(key string) map[int]Map
	func (r Result) ToUintMap(key string) map[uint]Map

    func (r Result) ToStringRecord(key string) map[string]Record
    func (r Result) ToIntRecord(key string) map[int]Record
    func (r Result) ToUintRecord(key string) map[uint]Record
    func (r Record) ToStructs(objPointerSlice interface{}) error
```
由于方法比较简单，这里便不再举例说明。需要注意的是两个方法```Record.ToMap```及```Result.ToList```，这两个方法也是使用比较频繁的方法，用以将ORM查询结果信息转换为可做展示的数据类型。例如，由于结果集字段值底层为```[]byte```类型，虽然使用了新的```Value```类型做了封装，并且也提供了数十种常见的类型转换方法(具体请阅读【[通用动态变量](container/gvar/index.md)】章节)，但是大多数时候需要直接将结果```Result```或者```Record```直接作为```json```或者```xml```数据结构返回，就需要做转换才行。

# Record记录集处理

```go
// 数据表记录
type Record
    func (r Record) ToMap() Map
    func (r Record) ToJson() string
    func (r Record) ToXml() string
    func (r Record) ToStruct(objPointer interface{}) error
```

`gdb`为数据表记录操作提供了极高的灵活性和简便性，除了支持以`map`的形式访问/操作数据表记录以外，也支持将数据表记录转换为`struct`进行处理。我们以下使用一个简单的示例来演示该特性。

首先，我们的用户表结构是这样的（简单设计的示例表）：
```sql
CREATE TABLE `user` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '昵称',
  `site` varchar(255) NOT NULL DEFAULT '' COMMENT '主页',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
```
其次，我们的表数据如下：
```html
uid  name   site
1    john   https://goframe.org
```
最后，我们的示例程序如下：

```go
package main

import (
    "fmt"
)

type User struct {
    Uid  int
    Name string
}

func main() {
    if r, err := db.Table("user").Where("uid=?", 1).One(); err == nil {
        u := User{}
        if err := r.ToStruct(&u); err == nil {
            fmt.Println(" uid:", u.Uid)
            fmt.Println("name:", u.Name)
        } else {
            fmt.Println(err)
        }
    } else {
        fmt.Println(err)
    }
}
```
执行后，输出结果为：
```shell
 uid: 1
name: john
```
这里，我们自定义了一个`struct`，里面只包含了```Uid```和```Name```属性，可以看到它的属性并不和数据表的字段一致，这也是ORM灵活的特性之一：支持指定属性获取。

通过```gdb.Model.One```方法获取的返回数据表记录是一个```gdb.Map```数据类型，我们可以直接通过它的```ToStruct(obj interface{}) error```方法转换为指定的struct对象。

**属性字段映射规则：**

需要注意的是，map中的键名为`uid,name,site`，而struct中的属性为`Uid,Name`，那么他们之间是如何执行映射的呢？主要是以下几点简单的规则：
1. struct中需要匹配的属性必须为`公开属性`(首字母大写)；
2. map中键名会自动按照 **`不区分大小写`** 且 **忽略`-/_/空格`符号** 的形式与`struct`属性进行匹配；
3. 如果匹配成功，那么将键值赋值给属性，如果无法匹配，那么忽略；

以下是几个匹配的示例：
```html
map键名    struct属性     是否匹配
name       Name           match
Email      Email          match
nickname   NickName       match
NICKNAME   NickName       match
Nick-Name  NickName       match
nick_name  NickName       match
nick_name  Nick_Name      match
NickName   Nick_Name      match
Nick-Name  Nick_Name      match
```
> 由于数据库结果集转struct的底层是依靠`gconv.Struct`方法实现的，因此如果想要实现**自定义的属性转换**，以及更详细的映射规则说明，请参考【[gconv](util/gconv/struct.md)】章节。

我们可以通过`gconv`标签来实现自定义的属性转换，例如：
```go
type User struct {
    Uid  int    `gconv:"user_id"`
    Name string `gconv:"user_name"`
}
```




