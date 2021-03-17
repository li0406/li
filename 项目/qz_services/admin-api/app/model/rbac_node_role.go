package model

import (
    //_"admin-api/app/controller/v1"
    "github.com/gogf/gf/g/database/gdb"
)



func GetMyMenu(role_id int64) (list gdb.List,e error) {
    result,err := db.Table("qz_rbac_node_role a").Safe().Where("role_id = ?",role_id).And("b.version = 8").And("b.enabled = 1").Fields("node_id").LeftJoin("qz_rbac_system_menu b","b.nodeid = a.node_id").Select()
    return result.ToList(),err
}
