<template>
  <div class="skeleton-wrapper">
    <section class="skeleton-block" style="background: #F2F3F7">
      <div class="head" style=" width: 100%;height: 208px;background: #E6E7EB;"></div>
      <img  style="display:block; width: 92%; margin: 0 auto; margin-top: -20px;" src="data:image/png;base64,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">
      <div class="head" style=" width: 100%;height: 80px;background:  #F2F3F7;"></div>
    </section>
  </div>
</template>

<script>
  export default {
    name: 'skeleton'
  };
</script>

<style scoped>
  .skeleton-block {
    display: flex;
    flex-direction: column;
    padding-top: 8px;
  }
</style>