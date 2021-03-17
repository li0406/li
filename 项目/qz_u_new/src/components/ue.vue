<template>
  <div>
    <script :id="id" type="text/plain"></script>
  </div>
</template>
<script>
export default {
  name: 'UE',
  props: {
    defaultMsg: {
      type: String,
      default: ''
    },
    config: {
      type: Object,
      default() {
        return {}
      }
    },
    id: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      editor: null
    }
  },
  mounted() {
    const that = this
    // eslint-disable-next-line no-undef
    this.editor = UE.getEditor(this.id, this.config) // 初始化UE
    this.editor.addListener('ready', function() {
      that.editor.setContent(that.defaultMsg) // 确保UE加载完成后，放入内容。
    })
  },
  destroyed() {
    this.editor.destroy()
  },
  methods: {
    getUEContent() { // 获取内容方法
      return this.editor.getContent()
    },
    getUEContentTxt() { // 获取纯文本内容方法
      return this.editor.getContentTxt()
    },
    removeUEContent() { // 数据保存成功后清空编辑器内容
      return this.editor.setContent('')
    }
  }
}
</script>
