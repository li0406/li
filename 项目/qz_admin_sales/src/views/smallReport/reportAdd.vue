<template>
  <div class="member-report-add">
    <div class="qian-main">
      <div class="red">
        <p>填写提示：</p>
        <template v-if="cooperationVal !== 11">
          <p>1、收款金额和凭证需要核对清楚，确认到账后提交报备</p>
          <p>2、大报备需要小报备审核通过后进行，可直接在列表小报备点击操作报备会员</p>
          <p>3、报备的会员多次收款，则只需要创建一次大报备，本次收款的后续小报备可直接关联首次创建的大报备即可</p>
          <p>4、金额以人民币为单位</p>
        </template>
        <template v-else>
          <p>1、退款金额和凭证要核对清楚，会直接影响个人业绩</p>
          <p>2、提交审核后会递交到对应部门的负责人处进行审核，审核通过后才会计入业绩</p>
          <p>3、金额以人民币为单位</p>
        </template>
      </div>
      <div class="main">
        <el-row v-if="!isEdit" class="mb-20">
          <el-col :span="4">
            <div class="lh-40">
              <i class="red">*</i>合作类型：
            </div>
          </el-col>
          <el-col :span="8">
            <el-select v-model="cooperationVal" placeholder="请选择" class="top-select">
              <el-option
                v-for="item in cooperationOptions"
                :key="item.id"
                :label="item.name"
                :value="item.id"
              />
            </el-select>
          </el-col>
          <el-col v-if="tip" :span="7" class="red lh-40">请先选择合作类型</el-col>
        </el-row>
        <add
          v-if="parseInt(cooperationVal) !== 0"
          :member-type-id="mTypeId"
          :is-edit="isEdit"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { fetchSmallReportEdit } from '@/api/smallReport'
import add from './components/add'
export default {
  name: 'SmallReportAdd',
  components: {
    add
  },
  data() {
    return {
      cooperationVal: 0,
      cooperationOptions: [{ id: 0, name: '请选择' }],
      companyName: '',
      mTypeId: '',
      isEdit: false,
      tip: true,
      orderId: ''
    }
  },
  watch: {
    cooperationVal(val) {
      const that = this
      that.mTypeId = val + ''
      if (that.cooperationVal === 0) {
        that.tip = true
      } else {
        that.tip = false
      }
    }
  },
  created() {
    this.getType()
    // 根据路由参数判断是新增还是编辑
    if (this.$route.query && this.$route.query.type) {
      this.cooperationVal = parseInt(this.$route.query.type)
      if (this.$route.query.orderId) {
        this.isEdit = false
      } else {
        this.isEdit = true
      }

      this.mTypeId = this.$route.query.type + ''
    } else {
      this.isEdit = false
    }
  },
  methods: {
    getType() {
      fetchSmallReportEdit().then(res => {
        if (res.status === 200 && res.data.error_code === 0) {
          const list = res.data.data.cooperation_type_list
          const typeList = [{ id: 0, name: '请选择' }, ...list]
          this.cooperationOptions = typeList
        }
      })
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
.member-report-add {
  width: 100%;
  padding:20px;
  box-sizing: border-box;
  .qian-main {
    padding: 0 20px 20px;
    border-top: 3px solid #999;
    box-sizing: border-box;
    background-color: #fff;
    .main {
      width: 800px;
      .top-select{
        width: 250px;
      }
    }
    .lh-40 {
      line-height: 40px;
    }
    .mb-20 {
      margin-bottom: 20px;
    }
  }
}
</style>
