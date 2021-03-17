# 代理部署

代理部署即前置一层第三方的`WebServer`服务器处理所有的请求，将部分请求(往往是动态处理请求)有选择性地转交给后端的`Golang`应用程序执行，后端部署的`Golang`应用程序可以配置有多个。这种模式常用在复杂的`WebServer`配置中，常见的场景例如：需要静态文件分离、需要配置多个域名及证书、需要自建负载均衡层，等等。

> 虽然`Golang`实现的`WebServer`也能够处理静态文件，但是相比较于专业性的`WebServer`如`nginx`/`apache`来说比较简单，性能也较弱。因此不推荐使用`Golang WebServer`作为前端服务直接处理静态文件请求。

## `Nginx`

我们推荐使用`Nginx`作为反向代理的前端接入层，有两种配置方式实现动静态请求的拆分。

### 静态文件后缀
这种方式通过文件名后缀区分，将指定的静态文件转交给`nginx`处理，其他的请求转交给`golang`应用。
配置示例如下：
```nginx
server {
    listen       80;
    server_name  goframe.org;

    access_log   /var/log/gf-app-access.log;
    error_log    /var/log/gf-app-error.log;

    location ~ .*\.(gif|jpg|jpeg|png|js|css|eot|ttf|woff|svg|otf)$ {
        access_log off;
        expires    1d;
        root       /var/www/gf-app/public;
        try_files  $uri @backend;
    }

    location / {
        try_files $uri @backend;
    }

    location @backend {
        proxy_pass                 http://127.0.0.1:8199;
        proxy_redirect             off;
        proxy_set_header           Host             $host;
        proxy_set_header           X-Real-IP        $remote_addr;
        proxy_set_header           X-Forwarded-For  $proxy_add_x_forwarded_for;
    }
}
```
其中，`8199`为本地的`golang`应用`WebServer`监听端口。

例如，在该配置下，我们可以通过`http://goframe.org/my.png`访问到指定的静态文件。

### 静态文件目录
这种方式通过文件目录区分，将指定目录的访问请求转交给`nginx`处理，其他的请求转交给`golang`应用。
配置示例如下：
```nginx
server {
    listen       80;
    server_name  goframe.org;

    access_log   /var/log/gf-app-access.log;
    error_log    /var/log/gf-app-error.log;

    location ^~ /public {
        access_log off;
        expires    1d;
        root       /var/www/gf-app;
        try_files  $uri @backend;
    }

    location / {
        try_files $uri @backend;
    }

    location @backend {
        proxy_pass                 http://127.0.0.1:8199;
        proxy_redirect             off;
        proxy_set_header           Host             $host;
        proxy_set_header           X-Real-IP        $remote_addr;
        proxy_set_header           X-Forwarded-For  $proxy_add_x_forwarded_for;
    }
}
```
其中，`8199`为本地的`golang`应用`WebServer`监听端口。

例如，在该配置下，我们可以通过`http://goframe.org/public/my.png`访问静态文件。