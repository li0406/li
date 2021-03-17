package model

import (
	"time"

	"github.com/gogf/gf/database/gdb"
	"github.com/gogf/gf/frame/g"
)

// PnpTelCallbackModel 话单回调记录
type PnpTelCallbackModel struct {
	ID            uint32    `json:"id"`
	RequestID     string    `json:"request_id"`      // RequestID 消息请求标识
	TelA          string    `json:"tel_a"`           // TelA 真实号码
	TelX          string    `json:"tel_x"`           // TelX 小号号码
	TelB          string    `json:"tel_b"`           // TelB 对端号码
	Subid         string    `json:"subid"`           // Subid 绑定 id
	Calltype      string    `json:"calltype"`        // Calltype 呼叫类型
	Calltime      time.Time `json:"calltime"`        // Calltime 通话开始时间
	Ringingtime   time.Time `json:"ringingtime"`     // Ringingtime 振铃开始时间
	Starttime     time.Time `json:"starttime"`       // Starttime 通话开始时间
	Releasetime   time.Time `json:"releasetime"`     // Releasetime 通话结束时间
	Callid        string    `json:"callid"`          // Callid 通话标识 业务参考号
	Releasedir    string    `json:"releasedir"`      // Releasedir 释放方向 1 表示主叫， 2 表示被叫， 0 表示平台释放
	Releasecause  string    `json:"releasecause"`    // Releasecause 释放原因
	Callrecording string    `json:"callrecording"`   // Callrecording 录音控制 同绑定接口定义
	RecordURL     string    `json:"record_url"`      // RecordURL 录音地址
	RecordMode    string    `json:"record_mode"`     // RecordMode 录音模式 1：主叫在左声道 2：主叫在右声道
	Calldisplay   string    `json:"calldisplay"`     // Calldisplay 被叫上显示的来显号码
	TelZ          string    `json:"tel_z"`           // TelZ Z 号码 在绑定请求中设置了来显为 Z 号 码时，该值为显示的 Z 号码。
	Remark        string    `json:"remark"`          // Remark 接入商自有字段，如果请求中携 带，则响应中返回
	Userkey       string    `json:"userkey"`         // Userkey 分机号
	RecordURLSave string    `json:"record_url_save"` // RecordURLSave 自扩展字段 录音地址 录音长期保存
	UpdatedAt     uint32    `json:"updated_at"`      // UpdatedAt 更新时间
	CreatedAt     uint32    `json:"created_at"`      // CreatedAt 创建时间
}

func NewPnpTelCallbackModel() *PnpTelCallbackModel {
	return &PnpTelCallbackModel{}
}

func (c *PnpTelCallbackModel) getTable() *gdb.Model {
	return g.DB().Table("qz_pnp_tel_callback a").Safe()
}

func (c *PnpTelCallbackModel) GetRecordURLByCreatedAT(t uint32) (result gdb.Result, err error) {
	r, e := c.getTable().Where("a.created_at >= ? AND a.record_url_save = \"\"", t).
		Fields("id,record_url,record_url_save").All()
	return r, e
}

func (c *PnpTelCallbackModel) GetRecordSaveURLByCreatedAT(t uint32) (result gdb.Result, err error) {
	r, e := c.getTable().Where("a.created_at >= ? AND a.record_url <> \"\" AND a.record_url_save <> \"\"", t).
		Fields("a.remark,a.record_url_save,a.tel_b,a.calltime").All()
	return r, e
}

// GetRecordListByCreatedAT 电话拨打记录
func (c *PnpTelCallbackModel) GetRecordListByCreatedAT(t uint32) (result gdb.Result, err error) {
	r, e := c.getTable().Where("a.created_at >= ? AND a.remark <> \"\"", t).
		Fields("a.remark,a.record_url_save,a.tel_b,a.calltime," +
			"TIMESTAMPDIFF(SECOND,a.starttime,a.releasetime) AS duration,a.callid").All()
	return r, e
}

func (c *PnpTelCallbackModel) UpdateRecordURLSave(d g.List) (bool, error) {
	for _, o := range d {
		result, err := c.getTable().Unscoped().Where("id", o["id"]).Data(o).Update()
		if err != nil {
			return false, err
		}
		ret, err := result.RowsAffected()
		if !(ret > 0) {
			return !!(ret > 0), err
		}
	}

	return true, nil
}
