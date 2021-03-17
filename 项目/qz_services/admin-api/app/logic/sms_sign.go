package logic

import (
    "admin-api/app/model"
    "github.com/gogf/gf/g/database/gdb"
)

type SmsSignLogic struct {
}

func (that SmsSignLogic)GetSignList()  gdb.List{
    m := new(model.SmsSignModel)
    res,_ := m.GetSignList()
    return res
}