<template>
  <div class="member-report-add">
    <div class="qian-main">
      <div class="red">
        <p>填写提示：</p>
        <p>1、公司名称：统一填写营业执照全称</p>
        <p>2、合作倍数：一定要选择准确，千万不要选错</p>
        <p>3、日期录入：请记得注意查看年份，千万不要填错</p>
        <p>4、备注：有任何的特殊情况，都必须备注说明清楚，如特别复杂的情况，必须提前QQ事先反馈给到副总裁知悉，再填写报备</p>
        <p>5、金额：所有金额都以元为单位</p>
        <p>6、开站讨论组备注：已开站/未开站城市讨论组是否存在</p>
      </div>
      <div class="main">
        <el-row v-if="operationTag === 'add'" class="mb-20">
          <el-col :span="4">
            <div class="lh-40"><i class="red">*</i>合作类型：</div>
          </el-col>
          <el-col :span="7">
            <el-select v-model="cooperationVal" placeholder="请选择">
              <el-option
                v-for="item in cooperationOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              />
            </el-select>
          </el-col>
          <el-col :span="8" class="red lh-40">请先选择合作类型</el-col>
        </el-row>
        <home-decoration
          v-if="parseInt(cooperationVal) === 1 || parseInt(cooperationVal) === 2 || parseInt(cooperationVal) === 3 || parseInt(cooperationVal) === 7 || parseInt(cooperationVal) === 14"
          :member-type-id="mTypeId"
          :member-type="mType"
          :operation-action-tag="operationTag"
          :cooperation-val="cooperationVal"
          :is-qh="isQh"
          @refreshList="refreshList"
        />
        <san-wei-jia
          v-if="parseInt(cooperationVal) === 4"
          :member-type-id="mTypeId"
          :member-type="mType"
          :operation-action-tag="operationTag"
        />
        <erp
          v-if="parseInt(cooperationVal) === 5"
          :member-type-id="mTypeId"
          :member-type="mType"
          :operation-action-tag="operationTag"
        />
        <commission-m
          v-if="parseInt(cooperationVal) === 6 || parseInt(cooperationVal) === 15"
          :member-type-id="mTypeId"
          :member-type="mType"
          :operation-action-tag="operationTag"
          :cooperation-val="cooperationVal"
          :is-qh="isQh"
          @refreshList="refreshList"
        />
        <delay
          v-if="parseInt(cooperationVal) === 8"
          :member-type-id="mTypeId"
          :member-type="mType"
          :operation-action-tag="operationTag"
        />
      </div>
    </div>
  </div>
</template>

<script>
import homeDecoration from './components/homeDecoration'
import sanWeiJia from './components/sanWeiJia'
import erp from './components/erp'
import delay from './components/delay'
import commissionM from './components/commissionM'
import { fetchOptions } from '@/api/memberReport'

export default {
  name: 'ReportAdd',
  components: {
    homeDecoration,
    sanWeiJia,
    erp,
    delay,
    commissionM
  },
  data() {
    return {
      cooperationVal: '0',
      cooperationOptions: [],
      companyName: '',
      mType: '',
      mTypeId: '',
      operationTag: 'add',
      isQh: true
    }
  },
  watch: {
    'cooperationVal'(val) {
      if (this.isQh) {
        this.cooperationOptions.forEach(item => {
          if (val === item.value) {
            this.mType = item.label
            this.mTypeId = item.value
          }
        })
      }
    }
  },
  created() {
    this.getType()
    // 根据路由参数判断是新增还是编辑
    if (this.$route.query && this.$route.query.type) {
      this.operationTag = 'edit'
      this.mTypeId = this.$route.query.type
    } else {
      this.operationTag = 'add'
    }
  },
  methods: {
    getType() {
      fetchOptions().then(res => {
        if (parseInt(res.data.error_code) === 0) {
          const list = res.data.data.cooperation_type_list
          const arr = list.map((it) => {
            return {
              value: it.id + '',
              label: it.name
            }
          })
          const typeList = [{ value: '0', label: '请选择' }, ...arr]
          this.cooperationOptions = typeList
          const smallReport = JSON.parse(localStorage.getItem('smallReport'))
          if (smallReport) {
            this.cooperationVal = String(smallReport.type)
          }
          if (this.$route.query && this.$route.query.type) {
            this.cooperationVal = String(this.$route.query.type)
          }
        } else {
          this.$message.warning(res.data.error_msg)
        }
      })
    },
    refreshList(e, i, t, c) { // e:新类型 i:老类型 c: 会员名称
      this.cooperationVal = e
      this.mTypeId = i
      this.mType = t
      this.isQh = c
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
  .member-report-add {
    width: 100%;
    padding: 20px;
    box-sizing: border-box;

    .qian-main {
      margin-top: 20px;
      padding: 20px;
      border-top: 3px solid #999;
      box-sizing: border-box;
      background-color: #fff;

      .main {
        width: 800px;
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
