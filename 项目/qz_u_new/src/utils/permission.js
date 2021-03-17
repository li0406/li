import Vue from 'vue';
// eslint-disable-next-line import/no-unresolved
import router from '../router/index';

// eslint-disable-next-line no-unused-vars
function onInput(el, ele, binding, vnode) {
  function handle() {
    // eslint-disable-next-line no-param-reassign
    ele.value = ele.value.replace(/[^\d]/g, '');
  }

  return handle;
}
const numberInput = {
  bind(el, binding, vnode) {
    const ele = el.tagName === 'INPUT' ? el : el.querySelector('input');
    ele.addEventListener('input', onInput(el, ele, binding, vnode), false);
  },
};
Vue.directive('permission', {
  inserted(el, binding) {
    const action = binding.value.action;
    const currentRight = router.currentRoute.meta.rights;
    if (currentRight) {
      if (currentRight.indexOf(action) === -1) {
        // 不具备权限
        const type = binding.value.effect;
        if (type === 'disabled') {
          // eslint-disable-next-line no-param-reassign
          el.disabled = true;
          el.classList.add('is-disabled');
        } else {
          el.parentNode.removeChild(el);
        }
      }
    }
  },
});
Vue.directive('number-input', numberInput);
