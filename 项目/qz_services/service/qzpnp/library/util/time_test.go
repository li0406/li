package util

import (
	"testing"
	"time"
)

func TestTimeNextEarlyMorning(t *testing.T) {
	type args struct {
		t   string
		emn int
	}
	tests := []struct {
		name string
		args args
		want string
	}{
		{name: "1", args: args{t: "2020-05-01 06:11:21",emn: 0}, want:  "2020-05-02 00:00:00"},
		{name: "2", args: args{t: "2020-06-02 07:11:21",emn: 1}, want:  "2020-06-03 01:00:00"},
		{name: "3", args: args{t: "2020-05-01 09:11:21",emn: 2}, want:  "2020-05-02 02:00:00"},
		{name: "4", args: args{t: "2020-05-01 12:11:21",emn: 3}, want:  "2020-05-02 03:00:00"},
		{name: "5", args: args{t: "2020-05-01 13:11:21",emn: 4}, want:  "2020-05-02 04:00:00"},
		{name: "6", args: args{t: "2020-05-01 18:11:21",emn: 5}, want:  "2020-05-02 05:00:00"},
		{name: "7", args: args{t: "2020-05-01 23:11:21",emn: 6}, want:  "2020-05-02 06:00:00"},
		{name: "8", args: args{t: "2020-05-01 18:11:21",emn: 7}, want:  "2020-05-01 18:11:21"},
		{name: "9", args: args{t: "2020-05-01 23:11:21",emn: -1}, want:  "2020-05-01 23:11:21"},
	}
	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			inTime,_ := time.Parse("2006-01-02 15:04:05", tt.args.t)
			got :=  TimeNextEarlyMorning(inTime, tt.args.emn)
			gotStr := got.Format("2006-01-02 15:04:05")
			if gotStr != tt.want {
				t.Errorf("TimeNextEarlyMorning() = %v, want %v", gotStr, tt.want)
			}
		})
	}
}