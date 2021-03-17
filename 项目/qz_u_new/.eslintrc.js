module.exports = {
  root: true,
  env: {
    node: true,
    browser: true,
  },
  extends: ['plugin:vue/essential', '@vue/airbnb', 'prettier', 'prettier/vue'],
  parserOptions: {
    parser: 'babel-eslint',
  },
  rules: {
    'arrow-parens': ['error', 'always'],
    'max-len': ['warn', { code: 120 }],
    // 关闭强制解构
    'prefer-destructuring': 0,
  },
};
