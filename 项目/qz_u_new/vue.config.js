/* eslint-disable no-param-reassign */

const path = require('path');

function resolve(dir) {
  return path.join(__dirname, dir);
}

module.exports = {
  outputDir: 'web',
  devServer: {
    open: true,
    https: true,
  },
  configureWebpack: {
    name: '齐装网商家后台',
    resolve: {
      alias: {
        '@': resolve('src'),
      },
    },
  },
  chainWebpack: (config) => {
    config.plugin('html').tap((args) => {
      args[0].title = '管理系统';
      return args;
    });
  },
};
