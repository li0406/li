package vocie

import (
	"fmt"
	"log"
	"qzpnp/library/util"
	"testing"
)

func TestFileByteUpload(t *testing.T) {

	urlmp3 := "http://61.182.139.17:9101/spnscp/core/record/download/CALL/QZWCS_F_SCP/CLN200616091811AC150A0804206472.mp3"
	var timeout int64
	timeout = 60
	mp3Byte, err := util.GetNetFileByte(urlmp3, timeout)
	if err != nil {
		log.Fatalln(err)
	}
	putRet, err := FileByteUpload(mp3Byte)
	if err != nil {
		log.Fatalln(err)
	}
	fmt.Println(putRet)

}
