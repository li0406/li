package enum

import "sort"

type SmsTypeEnum struct {
    Id int `json:"id"`
    Name string `json:"name"`
}

// 设置短信类型枚举
func (that *SmsTypeEnum) getType() map[int]string{
    var typeMap = make(map[int]string)
    typeMap[1] = "验证码通知(行业)"
    typeMap[2] = "营销"
    typeMap[3] = "国际验证码"
    typeMap[4] = "语音验证码"

    return typeMap
}

// 获取列表
func (that *SmsTypeEnum) GetList() []SmsTypeEnum{
    typeMap := that.getType()

    var keys [] int
    for key := range typeMap {
        keys = append(keys, key)
    }

    sort.Ints(keys) // 排序

    // 顺序输出
    var list [] SmsTypeEnum
    for _,key := range keys {
        var smsType SmsTypeEnum
        smsType.Id = key
        smsType.Name = typeMap[key]
        list = append(list, smsType)
    }

    return list
}

// 获取名称
func (that *SmsTypeEnum) GetName(key int) string {
    typeMap := that.getType()
    name,_ := typeMap[key]
    return name
}
