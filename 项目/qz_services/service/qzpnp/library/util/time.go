package util

import (
	"fmt"
	"strconv"
	"time"
)

// TimeNextEarlyMorning
// 把给定的时间扩展到第次日凌晨的时间
// @t 时间
// @emn 次日凌晨几点 0-6
func TimeNextEarlyMorning(t time.Time, emn int) time.Time {
	// 扩展的时间，超出0-6直接返回
	if !(emn >= 0 && emn <= 6) {
		return t
	}

	nowDayStr := t.Format("2006-01-02")
	nowDay, _ := time.ParseInLocation("2006-01-02", nowDayStr, time.Local)
	nextEarlyMorningSpan := time.Duration(86400 + (emn * (60 * 60)))
	nextEarlyMorning := nowDay.Add(time.Second * nextEarlyMorningSpan)

	return nextEarlyMorning
}

func Timestamp2String(timestamp int64) string {
	return time.Unix(timestamp, 0).Format("2006-01-02 15:04:05")
}

func ParseTime(format string, val string) time.Time {
	t, _ := time.ParseInLocation(format, val, time.Local)
	return t
}

func Yesterday(today int32) int32 {
	t, _ := time.ParseInLocation("20060102", fmt.Sprintf("%d", today), time.Local)
	yesterday := t.AddDate(0, 0, -1)
	yesterdayStr := yesterday.Format("20060102")
	yesterdayInt, _ := strconv.Atoi(yesterdayStr)
	return int32(yesterdayInt)
}

func Tomorrow(today int32) int32 {
	t, _ := time.ParseInLocation("20060102", fmt.Sprintf("%d", today), time.Local)
	tomorrow := t.AddDate(0, 0, 1)
	tomorrowStr := tomorrow.Format("20060102")
	tomorrowInt, _ := strconv.Atoi(tomorrowStr)
	return int32(tomorrowInt)
}

func Time2Weekday(t time.Time) string {
	switch t.Weekday() {
	case time.Monday:
		return "星期一"
	case time.Tuesday:
		return "星期二"
	case time.Wednesday:
		return "星期三"
	case time.Thursday:
		return "星期四"
	case time.Friday:
		return "星期五"
	case time.Saturday:
		return "星期六"
	case time.Sunday:
		return "星期天"
	}
	return ""
}

// short string format
func Duration2String(d time.Duration) string {

	u := uint64(d)
	if u < uint64(time.Second) {
		switch {
		case u == 0:
			return "0"
		case u < uint64(time.Microsecond):
			return fmt.Sprintf("%.2fns", float64(u))
		case u < uint64(time.Millisecond):
			return fmt.Sprintf("%.2fus", float64(u)/1000)
		default:
			return fmt.Sprintf("%.2fms", float64(u)/1000/1000)
		}
	} else {
		switch {
		case u < uint64(time.Minute):
			return fmt.Sprintf("%.2fs", float64(u)/1000/1000/1000)
		case u < uint64(time.Hour):
			return fmt.Sprintf("%.2fm", float64(u)/1000/1000/1000/60)
		default:
			return fmt.Sprintf("%.2fh", float64(u)/1000/1000/1000/60/60)
		}
	}

}
