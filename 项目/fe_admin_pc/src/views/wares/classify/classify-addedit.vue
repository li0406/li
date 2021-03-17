<template>
  <div class="business-addedit warp">
    <editPage :title="title">
      <template slot="btn">
        <el-button type="primary" @click="save('ruleForm')">保存</el-button>
        <el-button @click="closeBtn">关闭</el-button>
      </template>
      <el-form ref="ruleForm" :model="ruleForm" :rules="rules" label-width="100px">
        <el-row>
          <el-col :span="4">
            <div class="grid-content bg-purple">
              <el-form-item label="分类名称" prop="typeName">
                <el-input v-model.trim="ruleForm.typeName" placeholder="请输入分类名称" />
              </el-form-item>
            </div>
          </el-col>
          <el-col :span="4">
            <div class="grid-content bg-purple">
              <el-form-item label="渠道代号" prop="channelNo">
                <el-input v-model.trim="ruleForm.channelNo" :disabled="channelNodisable" placeholder="请输入渠道代号" />
              </el-form-item>
            </div>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="8">
            <div class="grid-content bg-purple">
              <el-row>
                <el-col :span="12">
                  <el-form-item label="一级分类" prop="parentId">
                    <div class="grid-content bg-purple">
                      <el-select v-model="ruleForm.parentId" @change="changeParentId">
                        <el-option v-for="item of parentId_list" :key="item.id" :label="item.typeName" :value="item.id" />
                      </el-select>
                    </div>
                  </el-form-item>
                </el-col>
                <el-col :span="12">
                  <el-form-item label="二级分类" prop="id">
                    <div class="grid-content bg-purple-light">
                      <el-select v-model="ruleForm.id">
                        <el-option v-for="item of id_list" :key="item.id" :label="item.typeName" :value="item.id" />
                      </el-select>
                    </div>
                  </el-form-item>
                </el-col>
              </el-row>
            </div>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="24">
            <el-form-item>
              <div class="classify-tips">不选择分类保存后为1级分类，选了1级分类联动出2级分类，只选1级，保存后为2级分类</div>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="4">
            <div class="grid-content bg-purple">
              <el-form-item label="排序值" prop="sort">
                <el-input v-model="ruleForm.sort" oninput="value=value.replace(/[^\d]/g,'')" placeholder="请输入排序值" />
              </el-form-item>
            </div>
          </el-col>
          <el-col :span="4">
            <el-form-item>
              <div class="sort-tips">（排序值越大优先级越高）</div>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="24">
            <el-form-item label="特殊资源" prop="enableFlag">
              <el-radio-group v-model="ruleForm.enableFlag">
                <el-radio label="1">启用(默认)</el-radio>
                <el-radio label="2">停用</el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
    </editPage>
  </div>
</template>

<script>
import { getChild, getDetail, save } from '@/api/goods'
export default {
  categoryname: 'BusinessAddedit',
  data() {
    return {
      id: '',
      title: '',
      channelNodisable: false,
      parentId_list: [],
      id_list: [],
      ruleForm: {
        typeName: '', // 分类名称
        channelNo: '', //  渠道代号
        parentId: 0, // 一级分类
        id: 0, //  二级分类id
        sort: 0, // 排序值
        enableFlag: '1'//  是否启用
      },
      rules: {
        typeName: { required: true, message: '请输入分类名称', trigger: 'change' },
        channelNo: { required: true, message: '请输入渠道代号', trigger: 'change' }
      }
    }
  },
  watch: {
    'ruleForm.parentId'(val) {
      if (val !== 0) {
        this.getChildB(val)
      }
    },
    'ruleForm.id'(val) {
      if (val !== 0) {
        this.$set(this.ruleForm, 'channelNo', '')
        this.channelNodisable = true
        this.rules.channelNo.required = false
      } else {
        this.channelNodisable = false
        this.rules.channelNo.required = true
      }
    }
  },
  created() {
    if (this.$route.query.id) {
      this.id = Number(this.$route.query.id)
      this.getDetail()
      this.$route.meta.title = '商品分类编辑'
      this.title = '商品分类编辑'
    } else {
      this.$route.meta.title = '商品分类添加'
      this.title = '商品分类添加'
    }
    this.getChildA()
  },
  methods: {
    changeParentId() {
      this.ruleForm.id = 0
    },
    getDetail() {
      const params = {
        id: this.id
      }
      getDetail(params).then(res => {
        if (res.code === 0) {
          this.ruleForm = res.data
          if (res.data.typeLevel === 1) {
            this.ruleForm.parentId = 0
            this.ruleForm.id = 0
            this.channelNodisable = false
            this.rules.channelNo.required = true
          }
          if (res.data.typeLevel === 2) {
            this.ruleForm.parentId = res.data.parentId
            this.ruleForm.id = 0
            this.channelNodisable = false
            this.rules.channelNo.required = true
          }
          if (res.data.typeLevel === 3) {
            this.$set(this.ruleForm, 'channelNo', '')
            this.ruleForm.id = res.data.parentId
            this.ruleForm.parentId = res.data.firstParentId
            this.channelNodisable = true
            this.rules.channelNo.required = false
          }
        } else {
          console.log(res.message)
        }
      })
    },
    save(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          const params = {
            typeName: this.ruleForm.typeName,
            channelNo: this.ruleForm.channelNo,
            id: this.id,
            sort: this.ruleForm.sort,
            enableFlag: this.ruleForm.enableFlag
          }
          if (this.ruleForm.parentId !== 0 && this.ruleForm.id !== 0) { // 选择二级分类的情况
            params.parentId = this.ruleForm.id
            params.typeLevel = 3
          }
          if (this.ruleForm.parentId !== 0 && this.ruleForm.id === 0) { // 选择一级分类的情况
            params.parentId = this.ruleForm.parentId
            params.typeLevel = 2
          }
          if (this.ruleForm.parentId === 0 && this.ruleForm.id === 0) { // 没选分类的情况
            params.typeLevel = 1
          }
          save(params).then(res => {
            if (res.code === 0) {
              this.$message({
                message: '保存成功',
                type: 'success'
              })
              this.$router.push({
                path: '/wares/classify-manage'
              })
            } else {
              this.$message.error(res.message)
            }
          })
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    closeBtn() {
      this.$router.push({
        path: '/wares/classify-manage'
      })
    },
    getChildA() {
      const params = {
        parentId: ''
      }
      getChild(params).then(res => {
        if (res.code === 0) {
          this.id_list = [{ typeName: '请选择', id: 0 }]
          this.parentId_list = [{ typeName: '请选择', id: 0 }, ...res.data]
        } else {
          console.log(res.message)
        }
      })
    },
    getChildB(val) {
      const params = {
        parentId: val
      }
      getChild(params).then(res => {
        if (res.code === 0) {
          this.id_list = [{ typeName: '请选择', id: 0 }, ...res.data]
        } else {
          console.log(res.message)
        }
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.classify-tips{
  margin-top: -20px;
  font-size: 13px;
  color: #999;
}
.sort-tips{
  margin-left: -100px;
  font-size: 13px;
  color: #999;
}
</style>
