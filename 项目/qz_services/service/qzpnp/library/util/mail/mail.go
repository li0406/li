package mail

import (
	"net/smtp"
	"strings"

	"github.com/jordan-wright/email"
)

type Mail struct {
	From        string   // 发件人
	To          []string // 收件人
	Bcc         []string
	Cc          []string
	Subject     string // 标题
	Text        []byte
	HTML        []byte   // 邮件正文
	Attachments []string // 附件
}

type Server struct {
	SmtpAuth *smtp.Auth
	Cfg      *Cfg
}

type Cfg struct {
	MailSMTPServer   string // "smtpdm.aliyun.com:80",
	MailSMTPUser     string // "report@domain.com",
	MailSMTPPassword string // "password"
	MailTo           []string
}

func NewServer(cfg *Cfg) *Server {
	s := &Server{}
	s.Cfg = cfg
	s.SmtpAuth = NewAuth(s.Cfg)
	return s
}

// SendMail 发送邮件
func (s *Server) SendMail(m *Mail) error {

	em := NewEmail(m)
	//glog.Info(em)

	auth := *s.SmtpAuth
	err := em.Send(s.Cfg.MailSMTPServer, auth)
	if err != nil {
		//glog.Error(err)
		return err
	}
	return nil
}

// NewEmail 构建一封邮件
func NewEmail(m *Mail) *email.Email {

	em := email.Email{
		From:    m.From,
		To:      m.To,
		Subject: m.Subject,
		HTML:    []byte(m.HTML),
	}

	em.Bcc = m.Bcc
	em.Cc = m.Cc

	// 增加附件
	attachsArr := m.Attachments
	if len(attachsArr) > 0 {
		for _, v := range attachsArr {
			_, _ = em.AttachFile(v)
		}
	}
	//glog.Info(em)
	return &em
}

// NewAuth 邮件认证
func NewAuth(cfg *Cfg) *smtp.Auth {
	identity := ""
	username := cfg.MailSMTPUser
	password := cfg.MailSMTPPassword
	hostArr := strings.Split(cfg.MailSMTPServer, ":")
	host := hostArr[0]
	auth := smtp.PlainAuth(identity, username, password, host)
	return &auth
}
