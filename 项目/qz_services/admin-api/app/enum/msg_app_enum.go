package enum

type MsgAppEnum struct {
}


func NewMsgAppEnum() *MsgAppEnum {
    return &MsgAppEnum{}
}

// 设置app枚举
func (that *MsgAppEnum) getType() map[int]string{
    var typeMap = make(map[int]string)
    typeMap[1] = "6d8fb8c330b43b3b67c399948f82c1f6" //齐装App
    typeMap[2] = "c50a5ed98f4b77f07f28d181e15566f7" //装修说App

    return typeMap
}

// 获取app标识
func (that *MsgAppEnum) GetSlot(key int) string {
    typeMap := that.getType()
    name,_ := typeMap[key]
    return name
}
