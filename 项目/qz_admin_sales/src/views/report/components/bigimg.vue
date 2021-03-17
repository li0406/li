<template>
  <div>
    <div v-if="isshow" class="imgMask" @click="childMethod()">
      <i class="iconfont el-icon-arrow-left prev" @click.stop="leftimg()" />
      <div class="showImg">
        <img class="bigImg" :src="imglist[index].img_full">
      </div>
      <i class="iconfont el-icon-arrow-right next" @click.stop="rightimg()" />
    </div>
  </div>
</template>

<script>
export default {
  props: ['isshow', 'urllist', 'sub'],
  data() {
    return {
      imglist: [],
      index: 0
    }
  },
  watch: {
    sub(val) {
      this.index = val
    },
    urllist(val) {
      this.imglist = this.urllist
    }
  },
  methods: {
    childMethod() {
      this.$parent.changeisshow()
    },
    leftimg() {
      if (this.index > 0) {
        this.index--
      }
    },
    rightimg() {
      if (this.index < this.imglist.length - 1) {
        this.index++
      }
    }
  }
}
</script>

<style scoped>
.imgMask {
  position: fixed;
  height: 100%;
  width: 100%;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  z-index: 99999;
  background: rgba(0, 0, 0, 0.6);
}
.showImg {
  height: 550px;
  width: 800px;
  position: absolute;
  left: 50%;
  top: 50%;
  overflow: hidden;
  transform: translate(-50%, -50%);
}
.bigImg {
  height: 100%;
  display: block;
  margin: 0 auto;
}
.prev {
  position: absolute;
  top: 50%;
  left: 50px;
  font-size: 46px;
  cursor: pointer;
  color: #fff;
  transform: translate(10px, -50%);
}
.next {
  font-size: 46px;
  cursor: pointer;
  transform: translate(10px, -50%);
  position: absolute;
  top: 50%;
  color: #fff;
  right: 50px;
}
</style>
