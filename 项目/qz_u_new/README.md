# 齐装 B 端新商户后台(前端)

## 命令说明

```bash
// 安装依赖
yarn install

// 启动开发服务
yarn start

// 构建生产版本
yarn build:prod

// 构建准生产版本
yarn build:stage

// 代码格式检查
yarn lint

// 单元测试
yarn test:unit

// 预览构建版本
yarn preview

// 启动文档开发服务
yarn docs

// 构建文档
yarn docs:build
```

## 规范

1. 事件命名 handle + 组件类型 + 组件名称 + 事件名称

   ```js
   // 示例
   handleDropdownCommand;

   handleUploadPreview;

   handleSelectStatusChange;
   ```

## 未完善

1. 使用 vuex
2. async/await try/catch 处理

## 小贴士

1. 文件名使用小写，多单词使用 `-` 连接。(防止 git 文件名带小写敏感导致的问题)

2. 关于迭代器。[Iterators and Generators](https://github.com/airbnb/javascript#iterators-and-generators)

3. git crlf 和 lf 冲突问题

   ```bash
   // git 提交时自动将换行符转换为 lf
   git config --global core.autocrlf input

   // 使用 EditorConfig 控制编辑器的换行符
   // 需在编辑器安装 EditorConfig 插件
   // 在 .editorconfig 文件中配置
   end_of_line = lf
   ```

4. 语法

   ```js
   // `?.` optional chaining 可选链
   // https://www.babeljs.cn/docs/babel-plugin-proposal-optional-chaining
   obj?.test?.a;

   // https://es6.ruanyifeng.com/?search=anync&x=0&y=0#docs/async
   async/await

   // 计算属性
   const obj = {
      a:'hello',
   };

   const name = "a";

   obj[name];

   // 模版字符串
   const str1 = 'world';
   const str = `hello ${world}`;
   ```

5. [语意化版本](https://semver.org/lang/zh-CN/)

## vscode 相关

[vscode 推荐文档](https://geek-docs.com/vscode/vscode-tutorials/what-is-vscode.html)

| 插件名称                  | 插件描述                                      | 推荐等级                           |
| ------------------------- | --------------------------------------------- | ---------------------------------- |
| TabNine                   | 通过机器学习提供代码提示。                    | :star: :star: :star: :star: :star: |
| Quokka.js                 | JS 实时运行平台。                             | :star: :star: :star: :star: :star: |
| Vetur                     | Vue 的开发工具。                              | :star: :star: :star: :star: :star: |
| GitLens--Git supercharged | git 工具。                                    | :star: :star: :star: :star: :star: |
| Live Server               | 启动一个静态服务器。                          | :star: :star: :star: :star: :star: |
| EditorConfig for VS Code  | 设置编辑器的编码格式。                        | :star: :star: :star: :star: :star: |
| Prettier - Code formatter | 代码格式化。                                  | :star: :star: :star: :star: :star: |
| ESLint                    | 编码规范检查。                                | :star: :star: :star: :star: :star: |
| Code Spell Checker        | 单词拼写检查。                                | :star: :star: :star: :star:        |
| Auto Close Tag            | HTML/XML 自动关闭标签。                       | :star: :star: :star: :star:        |
| Auto Rename Tag           | HTML/XML 自动配对重命名标签。                 | :star: :star: :star: :star:        |
| Bracket Pair Colorizer 2  | 括号着色。                                    | :star: :star: :star: :star:        |
| Format in context menus   | 支持多文件格式化。                            | :star: :star: :star: :star:        |
| Highlight Matching tag    | 高亮开闭标签。                                | :star: :star: :star: :star:        |
| npm Dependency Links      | 在 package.json 中可点击依赖跳转到 npm 官网。 | :star: :star: :star: ​             |
| TODO Highlight            | 高亮 TODO、FIXME 等。                         | :star: :star: :star:               |
| Todo Tree                 | 树形列表展示 TODO、FIXME 等。                 | :star: :star: :star:               |
