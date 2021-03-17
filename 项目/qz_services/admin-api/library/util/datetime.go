package util

import (
    "time"
)

const TIMELAYOUT = "2006-01-02 15:04:05"

// 日期格式转化为时间戳
func StrToTime(datetime string) (timestamp int64) {
    tLayout := TIMELAYOUT
    if length := len(datetime); length < 19 {
        tLayout = TIMELAYOUT[0:length]
    }

    loc,err := time.LoadLocation("Local")
    if err == nil {
        tmp,err := time.ParseInLocation(tLayout, datetime, loc)
        if err == nil {
            timestamp = tmp.Unix()
        }
    }

    return timestamp
}

// 时间戳转化为日期
func TimeToDate(timestamp int64, length int) string {
    tLayout := TIMELAYOUT
    if length < 19 {
        tLayout = TIMELAYOUT[0:length]
    }

    datetime := time.Unix(timestamp, 0).Format(tLayout)
    return datetime
}

// 获取当前时间戳
func GetTime() int64 {
    return time.Now().Unix()
}
