// 跳转页

<template>
<!-- FIXME: 储存token 跳转页面 -->
<div>跳转页面</div>
</template>

<script>
export default {
    created() {
        // localStorage.clear();
        // localStorage.setItem('token', this.$route.query.token);
        // this.$router.push('home');
        this.$router.push('login');
    },
};
</script>
