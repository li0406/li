package util

// StrToBool 字符串转bool
func StrToBool(str string) bool {
	if "true" == str {
		return true
	}
	return false
}