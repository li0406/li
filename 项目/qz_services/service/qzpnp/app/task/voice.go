package task

import (
	"qzpnp/app/service/vocie"
	"time"
)

type VoiceTask struct {
}

func NewVoiceTask() *VoiceTask {
	return &VoiceTask{}
}

// Run 录音处理逻辑入口
func (v *VoiceTask) Run() {
	// 查最近8小时
	t := time.Now().Add(-(time.Hour * 8)).Unix()
	vocie.Process(uint32(t))
}
