# 容器部署

容器部署即使用`docker`化部署`golang`应用程序，这是在云服务时代最流行的部署方式，也是最推荐的部署方式。

> 在以下我们的示例中，统一使用 `main` 作为项目名称。

## 1. 编译程序
跨平台交叉编译是`golang`的特点之一，可以非常方便地编译出我们需要的目标服务器平台的版本，而且是静态编译，非常容易地解决了运行依赖问题。

使用以下指令可以静态编译`Linux`平台`amd64`架构的可执行文件：
```
CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -o main main.go
```
生成的`main`便是我们静态编译的，可部署于`Linux amd64`上的可执行文件。


## 2. 编译镜像
我们需要将该可执行文件`main`编译生成`docker`镜像，以便于分发及部署。`Golang`的运行环境推荐使用`alpine`基础系统镜像，编译出的容器镜像约为`20MB`左右。

一个参考的`Dockerfile`文件如下：
```dockerfile
FROM loads/alpine:3.8

LABEL maintainer="john@johng.cn"

###############################################################################
#                                INSTALLATION
###############################################################################

# 设置固定的项目路径
ENV WORKDIR /var/www/main

# 添加应用可执行文件，并设置执行权限
ADD ./main   $WORKDIR/main
RUN chmod +x $WORKDIR/main

# 添加静态文件、配置文件、模板文件
ADD public   $WORKDIR/public
ADD config   $WORKDIR/config
ADD template $WORKDIR/template

###############################################################################
#                                   START
###############################################################################
WORKDIR $WORKDIR
CMD ./main
```
其中，我们的基础镜像使用了`loads/alpine:3.8`，中国国内的用户推荐使用该基础镜像，基础镜像的`Dockerfile`地址：https://github.com/johngcn/dockerfiles ，仓库地址：https://hub.docker.com/u/loads

随后使用 "`docker build -t main .`" 指令编译生成名为`main`的`docker`镜像。

### 注意事项
需要注意的是，在某些项目的架构设计中，**静态文件**和**配置文件**可能不会随着镜像进行编译发布，而是分开进行管理和发布。

例如，使用`MVVM`模式的项目中(例如使用`vue`框架)，往往是前后端非常独立的，因此在镜像中往往并不会包含`public`目录。而使用了`配置管理中心`(例如使用`consul`/`etcd`/`zookeeper`)的项目中，也往往并不需要`config`目录。

因此对于以上示例的`Dockerfile`的使用，仅作参考，根据实际情况请进行必要的调整。

## 3. 运行镜像

使用以下指令可直接运行刚才编译成的镜像：
```
docker run main
```

## 4. 镜像分发

容器的分发可以使用`docker`官方的平台：https://hub.docker.com/ ，国内也可以考虑使用阿里云：https://www.aliyun.com/product/acr 。

## 5. 容器编排

在企业级生产环境中，`docker`容器往往需要结合`kubernetes`或者`docker swarm`容器编排工具一起使用。
容器编排涉及到的内容比较多，感兴趣的同学可以参考以下资料：
* https://kubernetes.io/
* https://docs.docker.com/swarm/



