<template>
<div id="app">
    <keep-alive>
        <router-view v-if="$route.meta.keepAlive"></router-view>
    </keep-alive>
    <router-view v-if="!$route.meta.keepAlive"></router-view>
</div>
</template>

<script>
import {
    initDynamicRoutes
} from '@/router/index';

export default {
    name: 'app',
    created() {
        const href = window.location.href;
        const match = href.indexOf('/home');
        if (match === -1) {
            const childList = JSON.parse(localStorage.getItem('childList'));
            initDynamicRoutes(childList);
        }
    },
};
</script>

<style lang="less">
#app {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    color: #2c3e50;
    font-family: Avenir, Helvetica, Arial, sans-serif;
}
</style>
