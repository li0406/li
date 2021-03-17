# 小程序复制工具
可以通过母板小程序复制为扩展的小程序
根据定义的config/mores/*.json 文件复制



## 使用方法
PHP环境要求≥7
如果PHP已经加入环境变量使用
```
php copytool.php copy
```

如果PHP未加入环境变量,可以下载PHP执行引擎,手动制定php引擎地址
```
d:\php7\php.exe copytool.php  copy
```


## 执行成功输出
```
array:7 [
  0 => "D:\projects\qz_wxapp\copytool\../qz_jubu_more/qz_yangtai\"
  1 => "D:\projects\qz_wxapp\copytool\../qz_jubu_more/qz_keting\"
  2 => "D:\projects\qz_wxapp\copytool\../qz_jubu_more/qz_woshi\"
  3 => "D:\projects\qz_wxapp\copytool\../qz_jubu_more/qz_chufang\"
  4 => "D:\projects\qz_wxapp\copytool\../qz_jubu_more/qz_canting\"
  5 => "D:\projects\qz_wxapp\copytool\../qz_jubu_more/qz_shufang\"
  6 => "D:\projects\qz_wxapp\copytool\../qz_jubu_more/qz_ertongfang\"
]
2018-01-23T11:27:20+08:00 复制成功
2018-01-23T11:27:20+08:00 修改配置成功
```