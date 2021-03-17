## 环境
dev 开发
testing testing1 testing2 testing3 测试
staging 准生产
prod 生产

```bash
cd kustomize
kubectl kustomize edit set image qizuang/qzpnp:latest
#kustomize build $OVERLAYS/staging |kubectl apply -f -
kubectl apply -k $OVERLAYS/staging
```


## 参考
* examples https://github.com/kubernetes-sigs/kustomize/tree/master/examples
* https://blog.scottlowe.org/2019/09/13/an-introduction-to-kustomize/