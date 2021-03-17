<template>
  <div class="jump">跳转</div>
</template>

<script>
export default {
  name: 'Jump',
  mounted() {
    this.$router.push({
      path: 'dashboard'
    })
  }
}
</script>

<style lang="scss" scoped>

</style>
