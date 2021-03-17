package util

import (
    "io/ioutil"
    "os"
)


// 写入文件（文件已存在时末尾追加）
func WriteWithIoutil(logName, content string) error {
    data := []byte(content)

    var err error
    if f, err := os.OpenFile(logName, os.O_WRONLY, 0644); err == nil {
        n,_ := f.Seek(0, os.SEEK_END)
        _,err = f.WriteAt(data, n)
        _ = f.Close()
    } else {
        err = ioutil.WriteFile(logName, data,0644)
    }

    return err
}

// 检查文件夹是否存在不存在则创建
func CheckAndMakeDir(logPathDir string) error {
    var err error
    if _,err = os.Stat(logPathDir); err != nil {
        if os.IsNotExist(err) {
            err = os.Mkdir(logPathDir, os.ModePerm)
        }
    }

    return err
}