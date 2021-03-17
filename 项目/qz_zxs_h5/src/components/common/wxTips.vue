<template>
    <div class="wx_tips">
      <div class="wechat-mask" v-if="isWechat">
        <img src="@/assets/img/share/wxTips.png" style="width:80%;position:fixed;right:-20px;top:-0.198rem">
      </div>
    </div>
</template>

<script>
export default {
  name: 'wxTips',
  props: {
    isWechat: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    hideMask () {
      this.$parent.isWechat = false
    }
  }
}
</script>

<style scoped>
  .wx_tips .wechat-mask{
    position: fixed;
    left:0px;
    top:0px;
    width:100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
  }
</style>
