package task

import (
	"testing"
)

func TestMailTask_Run(t *testing.T) {
	v := &MailTask{}
	v.RunRecordCallListReport()
}
