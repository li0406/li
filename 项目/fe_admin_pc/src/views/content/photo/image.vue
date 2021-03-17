<template>
  <div class="content-photo">
    <tableSearch title="图片管理">
      <el-form slot="from" ref="ruleForm" :inline="true" :model="formData">
        <h4>查询条件</h4>
        <el-form-item label="标题">
          <el-input v-model="formData.title" clearable placeholder="请输入标题" />
        </el-form-item>
        <el-form-item label="图片来源">
          <el-select v-model="formData.source" clearable placeholder="请选择">
            <el-option label="笔记" value="1" />
            <el-option label="文章" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="标记状态">
          <el-select v-model="formData.remark" clearable placeholder="请选择">
            <el-option label="未标记" value="1" />
            <el-option label="已标记" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="标签">
          <el-select v-model="formData.tag" clearable placeholder="请选择">
            <el-option v-for="item in tagList" :key="item.id" :label="item.tagName" :value="item.id" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="onSearch">查询</el-button>
          <el-button @click="onReset">重置</el-button>
        </el-form-item>
      </el-form>
      <div class="image-box">
        <div class="image-list" v-for="(index, item) in 4">
          <div style="padding: 20px 20px 0 20px;">
            <div class="image">
              <img src="//staticqn.qizuang.com/webstatic/img/indexbanner.png" />
              <p class="tags">
                <el-tag
                  v-for="tag in tags"
                  :key="tag"
                  closable
                  @close="handleClose(tag)">
                  {{tag}}
                </el-tag>
              </p>
            </div>
            <h3 class="tag-title">标题最好不要换行</h3>
            <div class="tag-des">
              <span style="margin-right: 15px;">来自：笔记</span>
              <span>关联商品：<span class="blue">3</span></span>
              <span style="float:right;">发布时间：2020-12-21</span>
            </div>
          </div>
          <div class="tag-edit" v-if="show" @click="edit(index)"><i class="el-icon-edit"></i>编辑</div>
          <el-form :inline="true" style="padding-left: 10px;margin-top: 10px;" v-if="!show">
            <el-form-item>
              <el-select
                v-model="newTag"
                multiple
                filterable
                allow-create
                default-first-option
                placeholder="新建或搜索标签">
                <el-option
                  v-for="item in options"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="save">保存</el-button>
              <el-button @click="cancel">取消</el-button>
            </el-form-item>
          </el-form>
        </div>
      </div>
      <el-pagination
        v-if="total"
        class="mt20 text-center"
        :current-page="currentPage"
        :page-sizes="[10, 20, 50, 100]"
        :page-size="pageSize"
        layout="total, sizes, prev, pager, next, jumper"
        :total="total"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </tableSearch>
  </div>
</template>

<script>
export default {
  name: 'ContentPhoto',
  components: {},
  data() {
    return {
      currentPage: 1,
      pageSize: 20,
      total: 0,
      formData: {
        title: '',
        source: '',
        remark: '',
        tag: ''
      },
      tagList: [
        {
          id: 1,
          tagName: '测试1'
        },
        {
          id: 2,
          tagName: '测试2'
        },
        {
          id: 3,
          tagName: '测试3'
        }
      ],
      tags: ['标签一', '标签二', '标签三'],
      show: true,
      newTag: [],
      options: [{
        value: 'HTML',
        label: 'HTML'
      }, {
        value: 'CSS',
        label: 'CSS'
      }, {
        value: 'JavaScript',
        label: 'JavaScript'
      }]
    }
  },
  computed: {},
  watch: {},
  created() {
  },
  methods: {
    onSearch() {

    },
    onReset() {
      this.formData.title = ''
      this.formData.source = ''
      this.formData.remark = ''
      this.formData.tag = ''
    },
    handleClose(tag) {
      this.tags.splice(this.tags.indexOf(tag), 1)
    },
    edit(index) {
      this.show = false
    },
    save() {
      console.log(111)
    },
    cancel() {
      this.newTag = []
      this.show = true
    },
    handleSizeChange(val) {
      this.currentPage = 1
      this.pageSize = val
    },
    handleCurrentChange(val) {
      this.currentPage = val
    }
  }
}
</script>
<style rel=" stylesheet/scss" lang="scss" scoped>
.content-photo{
  padding: 20px;
  h4{
    font-weight: normal;
    margin-bottom: 20px;
  }
  .image-list{
    width: 24%;
    margin-right: 1%;
    border: 1px #ccc solid;
    float: left;
    img{
      width: 100%;
      height: 230px;
      vertical-align: middle;
    }
    .image{
      position: relative;
    }
    .tags{
      position: absolute;
      bottom: 0;
    }
    .el-tag{
      margin-right: 5px;
      margin-top: 5px;
    }
    .blue{
      color: blue;
    }
    .tag-title{
      line-height: 2;
      word-break:break-all;
      word-wrap:break-word;
      overflow: hidden;
      text-overflow:ellipsis;
      white-space: nowrap;
    }
    .tag-des{
      font-size: 12px;
      color: #666;
      overflow: hidden;
    }
    .tag-edit{
      border-top: 1px #ccc solid;
      margin-top: 10px;
      height: 50px;
      line-height: 50px;
      text-align: center;
      cursor: pointer;
    }
    .el-form-item{
      margin-bottom: 10px;
    }
  }
}
</style>
