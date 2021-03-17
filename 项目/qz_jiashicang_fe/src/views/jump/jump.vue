<template>
  <div />
</template>

<script>
export default {
  created() {
    // this.$router.push({ path: '/pubdata' })
    const menulist = JSON.parse(sessionStorage.getItem('menulist'))
    const name = menulist[0].path
    this.$router.push({ path: `/${name}` })
  }
}
</script>

<style>

</style>
