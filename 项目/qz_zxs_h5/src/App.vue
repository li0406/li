<template>
  <div id="app">
    <keep-alive>
      <router-view/>
    </keep-alive>
  </div>
</template>
<script>
export default {
  name: 'App'
}
</script>

<style>
  #app{
    height: 100%;
    width:100%;
  }
</style>
