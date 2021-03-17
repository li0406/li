/etc/systemd/system/admin-api.service

部署
```
sudo systemctl enable admin-api.service
sudo systemctl start admin-api.service
sudo systemctl status admin-api.service
```

更新
```
sudo systemctl restart admin-api.service
sudo systemctl status admin-api.service
```