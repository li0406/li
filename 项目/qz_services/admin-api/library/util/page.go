package util

import "math"

type PageStrut struct{
    TotalNumber int `json:"total_number"`
    PageTotalNumber int  `json:"page_total_number"`
    PageSize int  `json:"page_size"`
    PageCurrent int  `json:"page_current"`
}

// 统一分页信息返回格式
func GetPage(page int, limit int, count int) PageStrut {
    var pageObj PageStrut

    var pages int
    if limit > 0 {
        pages = int(math.Ceil(float64(count) / float64(limit)))
    }

    pageObj.TotalNumber = count
    pageObj.PageSize = limit
    pageObj.PageCurrent = page
    pageObj.PageTotalNumber = pages

    return pageObj
}

// 获取分页offset
func GetPageOffset(page int, limit int) int {
    offset := (page - 1) * limit
    return offset
}

// 分页参数处理
func PageFormat(page int, def int) int {
    if page <= 0 {
        page = def
    }

    return page
}