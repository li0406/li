package v1

import (
	"qzpnp/app/enum"
	"qzpnp/app/service/vocie"
	"qzpnp/library/util"
	"time"

	"github.com/gogf/gf/util/gconv"
)

func (c *PnpController) VoiceSync() {
	req := c.Request
	stime := req.GetString("stime")

	if stime == "" {
		json, _ := util.EncodeResponseJson(4000002, enum.CODE_4000002, nil)
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}

	timeStime, err := time.ParseInLocation("2006-01-02 15:04:05", stime, time.Local)
	if err != nil {
		json, _ := util.EncodeResponseJson(4000022, enum.CODE_4000022+";"+err.Error(), nil)
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}

	t := gconv.Uint32(timeStime.Unix())
	vocie.Process(t)

	json, _ := util.EncodeResponseJson(0, enum.CODE_0, nil)
	c.Request.Response.WriteJson(json)
}
