<template>
  <div class="team-manager">
    <h2>条件查询</h2>
    <div class="search">
      <div class="fl mr20">
        团队名称：<br>
        <el-input
          class="mt4"
          v-model="teamName"
          placeholder="请输入"
          clearable>
        </el-input>
      </div>
      <div class="fl mr20">
        负责人：<br>
        <el-select
          class="mt4"
          v-model="teamLeader"
          filterable
          remote
          :remote-method="remoteMethodLeader"
          :loading="sloading"
          placeholder="请输入"
          clearable
          >
          <el-option
            v-for="item in leaders"
            :key="item.id"
            :label="item.full_name"
            :value="item.id">
          </el-option>
        </el-select>
      </div>
      <div class="fl mr20">
        所属团队：<br>
        <el-select v-model="teamDataVal" clearable placeholder="请选择">
          <el-option
            v-for="item in teamData"
            :key="item.id"
            :label="item.team_name"
            :value="item.id"
          />
        </el-select>
      </div>
      <div class="fl mr20">
        启用状态：<br>
        <el-select v-model="statusTypeVal" placeholder="请选择">
          <el-option
            v-for="item in statusType"
            :key="item.value"
            :label="item.label"
            :value="item.value"
          />
        </el-select>
      </div>
      <div>
        <br>
        <el-button type="primary" icon="el-icon-search" @click="handleSearch" class="mt4">查询</el-button>
        <el-button type="success" icon="el-icon-plus" @click="handleAddTeam" class="mt4">新建团队</el-button>
      </div>
    </div>
    <div class="qian-main">
      <el-table
        v-loading="loading"
        :data="tableData"
        border
      >
        <el-table-column
          align="center"
          label="团队名称"
          prop="team_name"
        />
        <el-table-column
          align="center"
          label="层级"
        >
          <template slot-scope="scope">
            <span>{{ scope.row.top_name ? scope.row.top_name : '销售中心' }}-{{ scope.row.team_name }}</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="team_leader_name"
          label="负责人"
          :render-header="renderMarks"
        />
        <el-table-column
          align="center"
          label="成员人数"
          :render-header="renderMarks"
        >
          <template slot-scope="scope">
            <span class="link-blue" @click="toMemberList(scope.row.id)" style="cursor: pointer;">{{scope.row.team_count}}</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="状态"
        >
          <template slot-scope="scope">
            <span :class="scope.row.status === 1 ? 'circle circle_green' : 'circle circle_red'"></span>
            {{ scope.row.status === 1 ? '启用' : '禁用' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="描述"
          width="178"
        >
          <template slot-scope="scope">
            <span class="team-des">{{scope.row.description ? scope.row.description : '暂无描述'}}</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="操作"
        >
         <template slot-scope="scope">
            <span class="link-blue pointer" @click="handleExamine(scope.row)">编辑</span>
            <span class="link-blue pointer" @click="handleAddNextTeam(scope.row)">新建下级团队</span>
            <span class="link-blue pointer" @click="handleChangeStatus(scope.row)">{{scope.row.status == 1 ? '禁用' : '启用'}}</span>
          </template>
        </el-table-column>
      </el-table>
      <el-pagination
        :current-page="currentPage"
        :page-size="20"
        layout="total, prev, pager, next, jumper"
        :total="totals"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </div>
    <el-dialog
      :title="diaTitle"
      :visible.sync="dialogVisible"
      @close="closeDia('ruleForm')"
      center
      width="25%">
      <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="100px" class="demo-ruleForm">
        <el-form-item label="上级团队：">
          <el-select v-model="ruleForm.top_team" filterable placeholder="请选择" style="width:100%;margin-top:0;">
            <el-option
              v-for="item in topTeam"
              :key="item.id"
              :label="item.team_name"
              :value="item.id">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="团队名称：" prop="name">
          <el-input v-model="ruleForm.name" maxlength="15" placeholder="请输入团队名称"></el-input>
        </el-form-item>
        <el-form-item label="主负责人：" prop="leader">
          <el-select v-model="ruleForm.leader" filterable remote :remote-method="remoteMethod" :loading="sloading" clearable placeholder="请选择" style="width:100%;margin-top:0;">
            <el-option
              v-for="item in options"
              :key="item.id"
              :label="item.full_name"
              :value="item.id">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="副负责人：" prop="assistant">
          <el-select v-model="ruleForm.assistant" filterable remote :remote-method="xremoteMethod" @change="copyAll(ruleForm.assistant)" :loading="xsloading" clearable placeholder="请选择" style="width:100%;margin-top:0;">
            <el-option
              v-for="item in xoptions"
              :key="item.id"
              :label="item.full_name"
              :value="item"
              :disabled="item.disabled">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="">
          <el-tag
              v-for="tag in manages"
              :key="tag.id"
              class="tags"
              @close="handleTag(tag.id)"
              closable>
            {{tag.name}}
          </el-tag>
        </el-form-item>
        <el-form-item label="显示排序：">
          <el-input v-model="ruleForm.px" type="number" @mousewheel.native.prevent></el-input>
        </el-form-item>
        <el-form-item label="团队描述：">
          <el-input type="textarea" v-model="ruleForm.description" maxlength="200" placeholder="备注：请输入团队描述"  :autosize="{ minRows: 2, maxRows: 4}"></el-input>
        </el-form-item>
        <el-form-item style="text-align: center;margin-left:-100px!important;">
          <el-button @click="resetForm('ruleForm')">取消</el-button>
          <el-button type="primary" @click="submitForm('ruleForm')">保存</el-button>
        </el-form-item>
      </el-form>
    </el-dialog>
  </div>
</template>

<script>
import { fetchTeamPersonList, fetchTopTeamList, fetchTeamList, fetchTeamForbidden, fetchTeamEdit, fetchTeamSave } from '@/api/teamManager'
export default {
  name: 'TeamList',
  components: {

  },
  data() {
    return {
      ruleForm: {
        id: '',
        name: '',
        leader: '',
        assistant: '',
        top_team: '',
        px: 0,
        description: '',
        manage: []
      },
      manages: [],
      rules: {
        name: [
          { required: true, message: '请输入团队名称', trigger: 'blur' }
        ],
        leader: [
          { required: true, message: '请选择主负责人', trigger: 'change' }
        ]
      },
      diaTitle: '新建团队',
      teamName: '',
      teamLeader: '',
      statusType: [
        {
          value: '',
          label: '请选择'
        },
        {
          value: 1,
          label: '启用'
        }, {
          value: 2,
          label: '禁用'
        }
      ],
      teamData: [],
      statusTypeVal: '',
      teamDataVal: '',
      tableData: [],
      dialogVisible: false,
      loading: true,
      sloading: false,
      active: 1,
      top_id: '',
      team_leader_id: '',
      currentPage: 1,
      currentPagexin: '',
      totals: 0,
      pageSize: 20,
      topTeam: [],
      options: [],
      leaders: [],
      diaTeamName: '',
      xsloading: false,
      xoptions: []
    }
  },
  watch: {

  },
  mounted() {

  },
  beforeUpdate() {
    this.currentPage = Number(this.currentPagexin)
  },
  created() {
    this.fetchTopTeamList()
    this.fetchTeamList()
  },
  methods: {
    remoteMethod(query) {
      if (query !== '') {
        this.sloading = true
        fetchTeamPersonList({
          cid: '',
          uid: query,
          psize: ''
        }).then(res => {
          if (res.status === 200 && res.data.error_code === 0) {
            const _this = this
            setTimeout(() => {
              _this.sloading = false
              _this.options = res.data.data[0]
            }, 200)
          }
        })
      } else {
        this.options = []
      }
    },
    xremoteMethod(query) {
      if (query !== '') {
        this.xsloading = true
        fetchTeamPersonList({
          cid: '',
          uid: query,
          psize: ''
        }).then(res => {
          if (res.status === 200 && res.data.error_code === 0) {
            const _this = this
            setTimeout(() => {
              _this.xsloading = false
              _this.xoptions = res.data.data[0]
              _this.xoptions.forEach((item) => {
                _this.manages.forEach((i) => {
                  if (i.id == item.id) {
                    item.disabled = true
                  }
                })
              })
            }, 200)
          }
        })
      } else {
        this.xoptions = []
      }
    },
    remoteMethodLeader(query) {
      if (query !== '') {
        this.sloading = true
        fetchTeamPersonList({
          cid: '',
          uid: query,
          psize: ''
        }).then(res => {
          if (res.status === 200 && res.data.error_code === 0) {
            const _this = this
            setTimeout(() => {
              _this.sloading = false
              _this.leaders = res.data.data[0]
            }, 200)
          }
        })
      } else {
        this.leaders = []
      }
    },
    handleExamine(obj) {
      this.dialogVisible = true
      this.diaTitle = '编辑团队'
      this.ruleForm.id = obj.id
      this.xoptions = []
      this.active = 2
      fetchTeamEdit({
        team_id: obj.id
      }).then(res => {
        if (res.status === 200 && res.data.error_code === 0) {
          this.ruleForm.name = res.data.data.team_name
          this.ruleForm.top_team = res.data.data.top_id === 0 ? '' : res.data.data.top_id
          this.ruleForm.px = res.data.data.px
          this.top_id = res.data.data.top_id === 0 ? '' : res.data.data.top_id
          this.team_leader_id = res.data.data.team_leader
          this.manages = res.data.data.manager
          this.ruleForm.manage = res.data.data.manager.map((item) => {
            return item.id
          })
          fetchTeamPersonList({
            cid: '',
            uid: res.data.data.team_leader,
            psize: ''
          }).then(res => {
            if (res.status === 200 && res.data.error_code === 0) {
              const _this = this
              setTimeout(() => {
                _this.sloading = false
                _this.options = res.data.data[0]
                _this.ruleForm.leader = res.data.data[0][0].id
              }, 200)
            }
          })
          this.ruleForm.description = res.data.data.description
        }
      })
    },
    // 获取团队list
    fetchTopTeamList() {
      fetchTopTeamList().then(res => {
        if (res.status === 200 && res.data.error_code === 0) {
          this.teamData = res.data.data
          this.topTeam = res.data.data
        } else {
          this.$message.error(res.data.error_msg)
        }
      })
    },
    fetchTeamList() {
      fetchTeamList({
        name: this.teamName,
        leader: this.teamLeader,
        team: this.teamDataVal,
        status: this.statusTypeVal,
        page: this.currentPage
      }).then(res => {
        if (res.status === 200 && res.data.error_code === 0) {
          this.loading = false
          this.tableData = res.data.data.list
          this.currentPagexin = res.data.data.page.page_current
          this.totals = res.data.data.page.total_number
        } else {
          this.loading = false
          this.$message.error(res.data.error_msg)
        }
      })
    },
    // 新建团队
    handleAddTeam() {
      this.ruleForm.id = ''
      this.ruleForm.top_team = ''
      this.ruleForm.name = ''
      this.ruleForm.leader = ''
      this.ruleForm.px = 0
      this.ruleForm.description = ''
      this.manages = []
      this.ruleForm.manage = []
      this.dialogVisible = false
      this.dialogVisible = true
      this.diaTitle = '新建团队'
      this.active = 1
      this.options = []
      this.xoptions = []
    },
    // 新建下级团队
    handleAddNextTeam(obj) {
      this.active = 1
      this.options = []
      this.xoptions = []
      this.manages = []
      this.dialogVisible = true
      this.diaTitle = '新建下级团队'
      this.ruleForm.manage = []
      this.ruleForm.top_team = obj.id
    },
    toMemberList(id) {
      this.$router.push({
        path: '/teamManager/memberList?id=' + id
      })
    },
    // 表头添加提示
    renderMarks(h, { column }) {
      if (column.label === '负责人') {
        return [
          column.label,
          h(
            'el-tooltip',
            {
              props: {
                content: (function() {
                  return '同一负责人可管辖多团队，负责人不算在团队成员里'
                })(),
                placement: 'top'
              }
            },
            [
              h('span', {
                class: {
                  'el-icon-question': true
                }
              })
            ]
          )
        ]
      } else {
        return [
          column.label,
          h(
            'el-tooltip',
            {
              props: {
                content: (function() {
                  return '成员人数包括子团队的所有人员总计，成员与团队为1对1关系\n'
                })(),
                placement: 'top'
              }
            },
            [
              h('span', {
                class: {
                  'el-icon-question': true
                }
              })
            ]
          )
        ]
      }
    },
    // 添加/编辑保存
    fetchTeamSave() {
      if (this.active === 2) {
        if (this.ruleForm.top_team !== this.top_id && this.ruleForm.leader !== this.team_leader_id) {
          this.$confirm('确认要更改上级团队和主负责人吗？', '更改上级团队和主负责人提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning',
            center: true
          }).then(() => {
            this.handleTeamSave()
          }).catch(() => {
            this.$message({
              type: 'info',
              message: '已取消'
            })
          })
        } else if (this.ruleForm.top_team !== this.top_id && this.ruleForm.leader === this.team_leader_id) {
          this.$confirm('确认要更改上级团队吗？', '更改上级团队提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning',
            center: true
          }).then(() => {
            this.handleTeamSave()
          }).catch(() => {
            this.$message({
              type: 'info',
              message: '已取消'
            })
          })
        } else if (this.ruleForm.top_team === this.top_id && this.ruleForm.leader !== this.team_leader_id) {
          this.$confirm('确认要更改主负责人吗？', '更改主负责人提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning',
            center: true
          }).then(() => {
            this.handleTeamSave()
          }).catch(() => {
            this.$message({
              type: 'info',
              message: '已取消'
            })
          })
        } else {
          this.handleTeamSave()
        }
      } else {
        this.handleTeamSave()
      }
    },
    handleTeamSave() {
      this.ruleForm.manage = this.manages.map((item) => {
        return item.id
      })
      const query = this.ruleForm
      fetchTeamSave(query).then(res => {
        if (res.status === 200 && res.data.error_code === 0) {
          this.dialogVisible = false
          this.$message({
            type: 'success',
            message: '保存成功!'
          })
          this.fetchTeamList()
          this.fetchTopTeamList()
        } else {
          this.$message.error(res.data.error_msg)
        }
      })
    },
    submitForm(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.fetchTeamSave()
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    resetForm(formName) {
      this.$refs[formName].resetFields()
      this.ruleForm.top_team = ''
      this.ruleForm.name = ''
      this.ruleForm.leader = ''
      this.ruleForm.px = 0
      this.ruleForm.description = ''
      this.dialogVisible = false
    },
    closeDia(formName) {
      this.$refs[formName].resetFields()
      this.ruleForm.top_team = ''
      this.ruleForm.name = ''
      this.ruleForm.leader = ''
      this.ruleForm.px = 0
      this.ruleForm.description = ''
      this.dialogVisible = false
    },
    handleChangeStatus(obj) {
      let statusVal = '启用'
      if (obj.status === 1) {
        statusVal = '禁用'
      }
      this.$confirm('确认要' + statusVal + '吗？', statusVal + '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
        center: true
      }).then(() => {
        fetchTeamForbidden({
          id: obj.id
        }).then(res => {
          if (res.status === 200 && res.data.error_code === 0) {
            this.$message({
              type: 'success',
              message: statusVal + '成功!'
            })
            this.fetchTeamList()
          } else {
            this.$message.error(res.data.error_msg)
          }
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消'
        })
      })
    },
    handleSearch() {
      this.currentPage = 1
      this.fetchTeamList()
    },
    handleSizeChange() {
      this.currentPage = 1
      this.fetchTeamList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.fetchTeamList()
    },
    copyAll(e) {
      this.ruleForm.assistant = ''
      if (e) {
        this.xoptions.forEach((item) => {
          if (e.id == item.id) {
            item.disabled = true
          }
        })
        this.manages.push(e)
        const hash = {}
        this.manages = this.manages.reduce(function(item, next) {
          hash[next.id] ? '' : hash[next.id] = true && item.push(next)
          return item
        }, [])
      }
    },
    handleTag(e) {
      this.xoptions.forEach((item) => {
        if (e == item.id) {
          item.disabled = false
        }
      })
      this.manages = this.manages.filter((item) => {
        return item.id !== e
      })
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
  .mt4{
    margin-top:4px;
  }
  .team-manager{
    width: 100%;
    padding: 20px;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
  }
  .team-manager h2{
    font-size: 16px;
    font-weight: normal;
    color: #666;
    margin-top:0;
  }
  .team-manager .qian-main {
    margin-top: 20px;
    padding: 20px;
    border-top: 3px solid #999;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
    background-color: #fff;
  }
  .team-manager .search {
    background: #fff;
    padding: 20px;
    border-top: 3px solid #999;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
  }
  .team-manager .mr20 {
    margin-right: 20px;
  }
  .team-manager .fl {
    float: left;
  }
  .group .sp1 {
    display: inline-block;
    width: 100px;
    text-align: right;
    margin-bottom: 10px;
  }
  .el-table .cell {
    padding: 0;
  }
  .el-pagination{
    text-align: center;
  }
  .circle{
    display: inline-block;
    width: 5px;
    height: 5px;
    position: relative;
    top: -2px;
    border-radius: 50%;
  }
  .circle_red{
    background: red;
  }
  .circle_green{
    background:green;
  }
  .team-des{
    word-break:break-all;
    word-wrap:break-word;
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
  }
  .tags{
    margin-right: 8px;
  }
</style>
