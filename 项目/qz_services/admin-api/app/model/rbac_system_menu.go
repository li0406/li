package model


func  GetSmsMenu()(r  []map[string]interface {},err error)  {
    result,err := db.Table("qz_rbac_system_menu").Safe().
        Where("version = 8").
        And("enabled = 1").
        And("level <= 2").
        OrderBy("level,px").
        Select()
    return result.ToList(),err
}
