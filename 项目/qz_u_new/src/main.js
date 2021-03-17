import Vue from 'vue';
import * as Sentry from '@sentry/browser';
import { Vue as VueIntegration } from '@sentry/integrations';
import 'normalize.css/normalize.css';
import ElementUI from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
import '@/styles/index.less';
import VueCropper from 'vue-cropper';
import BaiduMap from 'vue-baidu-map';
import MarqueeText from 'vue-marquee-text-component';
import QZ_CONFIG from '@/utils/config';
import '@/utils/permission';
import VueLocalStorage from 'vue-localstorage';
import App from './app.vue';
import router from './router';
// 富文本
import '../public/UE/ueditor.config'
import '../public/UE/ueditor.all'
import '../public/UE/lang/zh-cn/zh-cn'
import '../public/UE/themes/default/css/ueditor.css'

Vue.component('marquee-text', MarqueeText);

Vue.use(VueCropper);
Vue.use(ElementUI);
Vue.use(VueLocalStorage);
Vue.prototype.$locSet = (key, val) => Vue.localStorage.set(key, JSON.stringify(val));
Vue.prototype.$locGet = (key) => JSON.parse(Vue.localStorage.get(key));
Vue.prototype.$locRemove = (key) => Vue.localStorage.remove(key);
Vue.use(BaiduMap, {
  ak: QZ_CONFIG.BAIDU_MAP_AK,
});
Vue.config.productionTip = false;

// Sentry只在生产环境中使用
// production development
// eslint-disable-next-line no-unused-expressions
process.env.NODE_ENV === 'production' &&
  Sentry.init({
    dsn: 'https://2fcf2b3fa24b444097358009a1d3b647@sentry.qizuang.com/8',
    integrations: [new VueIntegration({ Vue, attachProps: true })],
    release: process.env.RELEASE || '1250',
    logErrors: true,
  });

new Vue({
  router,
  render: (h) => h(App),
}).$mount('#app');
