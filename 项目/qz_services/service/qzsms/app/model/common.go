package model

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
)

var db gdb.DB

func init() {
    db = g.DB()
    // 读取配置文件
    config := g.Config()
    appDebug := config.GetBool("setting.app_debug")

    // debug模式开启DB调试
    if appDebug == true {
        db.SetDebug(true)
    }
}

type DBer interface {
    Add(i interface{}) int64
    Edit(i interface{}) int64
    Delete(i interface{}) int64
}





