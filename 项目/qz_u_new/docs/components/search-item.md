# SearchItem 查询项

## 基本用法

```js
<qz-search-item label="名称">
  <el-input v-model="input" placeholder="订单号/小区/电话号"></el-input>
</qz-search-item>
```

## 属性

| 参数  | 说明          | 类型   | 可选值 | 默认值 |
| ----- | ------------- | ------ | ------ | ------ |
| label | 标签文本      | string |        |        |
| id    | for 绑定的 id | string |        |        |

## Slot

| name | 说明     |
| ---- | -------- |
| ——   | 查询组件 |
