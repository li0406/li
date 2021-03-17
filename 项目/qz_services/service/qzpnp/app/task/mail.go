package task

import (
	"qzpnp/app/service/mail"

	"github.com/gogf/gf/frame/g"
)

type MailTask struct {
}

func NewMailTask() *MailTask {
	return &MailTask{}
}

// Run 邮件处理逻辑入口
func (v *MailTask) RunRecordCallListReport() {
	open := g.Config().GetBool("mail.mail_voice_report_open")
	appEnv := g.Config().GetString("setting.app_env")
	if open && appEnv == "prod" {
		// 打开邮件报送 和 是生产环境
		mail.RecordCallListReport()
	}
}
