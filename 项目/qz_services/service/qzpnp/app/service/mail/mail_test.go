package mail

import (
	"testing"
	"time"

	"github.com/gogf/gf/os/glog"
)

func TestRecordReport(t *testing.T) {

	nowDayStr := time.Now().Format("2006-01-02")
	nowDay, _ := time.ParseInLocation("2006-01-02", nowDayStr, time.Local)
	lastDay := nowDay.Add(-time.Second * time.Duration((24 * (60 * 60))))
	glog.Info(lastDay.Unix())

	if err := RecordCallListReport(); err != nil {
		t.Errorf("RecordCallListReport() error = %v", err)
	}
}
