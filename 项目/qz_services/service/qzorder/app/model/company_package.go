package model

import "github.com/gogf/gf/database/gdb"

type CompanyPackageModel struct {

}

type CompanyPackageStruct struct {
    Id int `json:"id"`
    InfoId int `json:"info_id"`
    UseStatus int `json:"use_status"`
    PackageType int `json:"package_type"`
    TotalNumber int `json:"total_number"`
    SendNumber int `json:"send_number"`
    InfoUseStatus int `json:"info_use_status"`
}

func NewCompanyPackageModel() *CompanyPackageModel {
    return &CompanyPackageModel{}
}

// 表名
func (that *CompanyPackageModel) table() string {
    return "qz_company_package"
}

// 配置数据表（带别名）
func (that *CompanyPackageModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

// 获取装企订单包（使用中-未使用-使用完毕）
func (that *CompanyPackageModel) GetCompanyPackage(companyId int, packageType int) (info CompanyPackageStruct, err error) {
    dbQuery := that.connect()
    dbQuery.Where("i.`package_type`=?", packageType)
    dbQuery.Where("t.`company_id`=?", companyId)
    dbQuery.Where("t.`refund_status`=?", 1)

    dbQuery.InnerJoin("qz_company_package_info as i", "i.package_id = t.id")
    dbQuery.Fields("t.id,t.use_status,i.id as info_id,i.use_status as info_use_status,i.package_type,i.total_number,i.send_number,if(i.use_status = 2, 0, i.use_status) as paixu")
    dbQuery.Order("paixu asc,t.id desc")
    err = dbQuery.Struct(&info)
    return info, err
}

// 保存数据
func (that *CompanyPackageModel) Update(id int, data gdb.Map) error{
    _,err := db.Table(that.table()).Where("id=?", id).Data(data).Update()
    return err
}