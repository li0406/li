package model

import "github.com/gogf/gf/database/gdb"

type CompanyPackageInfoModel struct {

}

func NewCompanyPackageInfoModel() *CompanyPackageInfoModel {
    return &CompanyPackageInfoModel{}
}

// 表名
func (that *CompanyPackageInfoModel) table() string {
    return "qz_company_package_info"
}

// 配置数据表（带别名）
func (that *CompanyPackageInfoModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

// 保存数据
func (that *CompanyPackageInfoModel) Update(id int, data gdb.Map) error{
    _,err := db.Table(that.table()).Where("id=?", id).Data(data).Update()
    return err
}
