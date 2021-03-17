<template>
  <div class="report-again">
    <!-- 可编辑列表 -->
    <div class="public">
      <!-- 第一 -->
      <el-table
        v-if="HandelBianji"
        ref="tableData"
        :data="tableData"
        border
        :rules="tablerules"
        style="width: 100%"
      >
        <el-table-column
          align="center"
          label="会员ID"
          width="160"
        >
          <template slot-scope="scope">
            <el-autocomplete
              v-if="isEdit"
              class="inline-input"
              v-model="editOldMember"
              :fetch-suggestions="queryOldMemberSearch"
              placeholder="请输入内容"
              :trigger-on-focus="false"
              @select="handleSelect"
              @blur="handleBlur"
            ></el-autocomplete>
            <span v-else>{{ parseInt(scope.row.user_id) === 0 ? '----' : scope.row.user_id }}</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="公司名称"
        >
          <template slot-scope="scope">
            <span>{{ scope.row.company_name }}</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="城市/区县"
          width="120"
        >
          <template slot-scope="scope">
            <span>{{ scope.row.cname }} / {{ scope.row.aname }}</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          width="120"
          label="联系人"
        >
          <template slot-scope="scope">
            <div v-for="(contat, index) in scope.row.contacts" :key="index">
              <div v-if="isEdit"><el-input v-model="contat.name" placeholder="请输入内容"></el-input>
              </div>
              <div v-else>
                <div>{{ contat.name }}</div>
              </div>
            </div>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="手机"
          width="160"
        >
          <template slot-scope="scope">
            <div v-for="(contat, index) in scope.row.contacts" :key="index">
              <div v-if="isEdit"><el-input v-model="contat.tel" placeholder="请输入内容"></el-input></div>
              <div v-else>
                <div>{{ contat.tel ? contat.tel : '——' }}</div>
              </div>
            </div>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="微信"
          width="160"
        >
          <template slot-scope="scope">
            <div v-for="(contat, index) in scope.row.contacts" :key="index">
              <div v-if="isEdit"><el-input v-model="contat.wx" placeholder="请输入内容"></el-input></div>
              <div v-else>
                <div>{{ contat.wx ? contat.wx : '——' }}</div>
              </div>
            </div>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="QQ"
          width="160"
        >
          <template slot-scope="scope">
            <div v-for="(contat, index) in scope.row.contacts" :key="index">
              <div v-if="isEdit"><el-input v-model="contat.qq" placeholder="请输入内容"></el-input></div>
              <div v-else>
                <div>{{ contat.qq ? contat.qq : '——' }}</div>
              </div>
            </div>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="地址"
        >
          <template slot-scope="scope">
            <span v-if="isEdit"><el-input v-model="scope.row.address" placeholder="请输入内容"></el-input></span>
            <span v-else>{{ scope.row.address }}</span>
          </template>
        </el-table-column>

        <el-table-column
          align="center"
          width="100"
          label="意向"
        >
          <template slot-scope="scope">
            <span v-if="isEdit">
              <el-select v-model="scope.row.intention_text" placeholder="请选择">
                <el-option
                  v-for="item in intentionSelect"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
                </el-option>
              </el-select>
            </span>
            <span v-else>{{ scope.row.intention_text }}</span>
          </template>
        </el-table-column>

        <el-table-column
          align="center"
          label="客户类型"
          width="120"
        >
          <template slot-scope="scope">
            <span v-if="isEdit">
              <el-select v-model="scope.row.is_new_text" placeholder="请选择">
                <el-option
                  v-for="item in style"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
                </el-option>
              </el-select>
            </span>
            <span v-else>{{ scope.row.is_new_text }}</span>
          </template>
        </el-table-column>

        <el-table-column
          align="center"
          label="操作"
          width="220"
        >
          <template slot-scope="scope">
            <el-button v-if="isEdit" style="color: #67C23A" type="text" @click="Handlesavelist(scope.row)">保存</el-button>
            <el-button v-if="isEdit" style="color: #E6A23C" type="text" @click="Handelexit(scope.row)">取消</el-button>
            <el-button v-if="!isEdit" type="text" @click="dialogFormVisible = true">新增联系人</el-button>
            <el-button v-if="!isEdit" style="color: #F56C6C" type="text" @click="HandelC(scope.row.id)">编辑</el-button>
          </template>
        </el-table-column>
      </el-table>
      <!-- 第二 -->
      <el-table
        class="mt20"
        :data="contractTime"
        border
      >
        <el-table-column
          align="center"
          label="总合同开始时间"
        >
          <template slot-scope="scope">
            {{ scope.row.allStartTime ? scope.row.allStartTime : "----" }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="总合同结束时间"
        >
          <template slot-scope="scope">
            {{ scope.row.allEndTime ? scope.row.allEndTime : "----" }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="本次合同开始时间"
        >
          <template slot-scope="scope">
            {{ scope.row.theStartTime ? scope.row.theStartTime : "----" }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="本次合同结束时间"
        >
          <template slot-scope="scope">
            {{ scope.row.theEndTime ? scope.row.theEndTime : "----" }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="尾款时间"
        >
          <template slot-scope="scope">
            {{ scope.row.balanceTime ? scope.row.balanceTime : "----" }}
          </template>
        </el-table-column>
      </el-table>
    </div>

    <!-- 新增拜访记录 -->
    <div class="public mt20">
      <el-form ref="visitform" :model="visitform" :rules="visitrules" class="demo-ruleForm">
        <div class="clearfix">
          <el-form-item label="拜访时间" required class="fl mr20">
            <el-form-item prop="visitTime">
              <el-date-picker
                style="width:380px"
                v-model="visitform.visitTime"
                type="datetimerange"
                format="yyyy-MM-dd HH:mm"
                value-format="yyyy-MM-dd HH:mm"
                range-separator="至"
                start-placeholder="开始日期"
                end-placeholder="结束日期">
              </el-date-picker>
            </el-form-item>
          </el-form-item>
          <el-form-item label="谈判方式" prop="visit_style" class="fl mr20">
            <el-select v-model="visitform.visit_style" placeholder="请选择谈判方式" @change="callphone">
              <el-option label="上门" value="1" />
              <el-option label="电话" value="2" />
              <el-option label="QQ" value="3" />
              <el-option label="微信" value="4" />
            </el-select>
          </el-form-item>
          <el-form-item v-show="phone" label="点击打电话" class="fl mr20">
            <i class="fa fa-headphones" style="font-size:30px;color:skyblue;cursor:pointer;" @click="dialogphoneVisible = true" />
          </el-form-item>
          <el-form-item label="下次联系时间" required class="fl mr20">
            <el-form-item prop="visit_next_time">
              <el-date-picker
                v-model="visitform.visit_next_time"
                type="datetime"
                placeholder="选择日期"
                format="yyyy-MM-dd HH:mm"
              />
            </el-form-item>
          </el-form-item>
          <el-form-item label="签约状态" prop="qianyue_status" class="fl mr20">
            <el-select v-model="visitform.qianyue_status" placeholder="请选择签约状态" @change="qianyue">
              <el-option label="未签约" value="1" />
              <el-option label="已签约" value="2" />
            </el-select>
          </el-form-item>
          <el-form-item v-show="qian" label="尾款时间" class="fl mr20">
            <el-form-item prop="retainage_time">
              <el-date-picker
                v-model="visitform.retainage_time"
                type="datetime"
                placeholder="选择日期"
                format="yyyy-MM-dd HH:mm"
              />
            </el-form-item>
          </el-form-item>
          <el-form-item class="fl mt40">
            <el-button type="primary" @click="handleSave('visitform')">保存</el-button>
          </el-form-item>
        </div>
        <div class="clearfix">
          <el-form-item label="谈话内容" prop="desc" class="text">
            <el-input v-model="visitform.desc" style="resize:none;" type="textarea" placeholder="谈话内容（请尽量多描述本次谈话，35字以上）" />
          </el-form-item>
        </div>
      </el-form>
    </div>

    <div class="public mt20">
      <el-table
        :data="pageData"
        border
        style="width: 100%"
      >
        <el-table-column
          align="center"
          label="记录次数"
          width="80"
          prop="index"
        />
        <el-table-column
          align="center"
          label="拜访时间"
        >
          <template slot-scope="scope">
            <div>{{ scope.row.contact_start_time }}</div>
            <div>{{ scope.row.contact_end_time }}</div>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="record_time"
          label="录入时间"
        />
        <el-table-column
          align="center"
          prop="next_contact_time"
          label="下次联系时间"
        />
        <el-table-column
          align="center"
          label="签约状态"
          width="100"
        >
          <template slot-scope="scope">
            <span :class="scope.row.status === 2 ? 'green' : ''">{{ scope.row.qianyue_status }}</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="uname"
          label="报备人"
          width="100"
        />
        <el-table-column
          align="center"
          prop="visit_style"
          label="谈判方式"
          width="100"
        />
        <el-table-column
          align="center"
          prop="desc"
          label="谈话内容"
          width="380"
        />
        <el-table-column
          align="center"
          label="录音"
          width="80"
        >
          <template slot-scope="scope">
            <div v-if="scope.row.log_list.length > 0">
              <el-button type="text" v-for="(rcd, index) in scope.row.log_list" :key="index" @click="handleSoundRecord(rcd)">录音{{ index+1 }}</el-button>
            </div>
            <span v-else>----</span>
          </template>
        </el-table-column>
      </el-table>
      <div class="pag">
        <el-pagination
          :current-page="currentPage"
          :page-size="pageSize"
          layout="total, prev, pager, next, jumper"
          :total="totals"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </div>

    <!-- 新增联系人dialog -->
    <el-dialog title="新增联系人" :visible.sync="dialogFormVisible">
      <el-form ref="form" :model="form" :rules="addrules">
        <el-form-item label="联系人：" :label-width="formLabelWidth" style="width: 400px" prop="user_name">
          <el-input v-model="form.user_name" autocomplete="off" />
        </el-form-item>
        <el-form-item label="手机：" :label-width="formLabelWidth" style="width: 400px" prop="user_tel">
          <el-input v-model="form.user_tel" autocomplete="off" />
        </el-form-item>
        <el-form-item label="微信：" :label-width="formLabelWidth" style="width: 400px" prop="user_wx">
          <el-input v-model="form.user_wx" autocomplete="off" />
        </el-form-item>
        <el-form-item label="QQ:" :label-width="formLabelWidth" style="width: 400px" prop="user_qq">
          <el-input v-model="form.user_qq" autocomplete="off" />
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogFormVisible = false">取 消</el-button>
        <el-button type="primary" @click="dialogFormSure('form')">确 定</el-button>
      </div>
    </el-dialog>

    <el-dialog
      title="录音播放"
      :visible="recordPlay"
      width="30%"
      @close="handleCloseRecord"
      >
        <audio class="player" :src="recordUrl" autoplay preload="auto" controls="" data-on="0" ref="record" v-if="isplaying">
        </audio>
    </el-dialog>
    <qz-dialog :dia-title="'id：' + orderNO + ' 的电话呼叫记录'" :dialog-visible="dialogTableVisible" :dia-width="'950px'" @diaClose="closeDialog">
      <el-table :data="gridData" border>
        <el-table-column align="center" property="time_add" label="产生时间" width="150" />
        <el-table-column align="center" property="action_name" label="呼叫动作" />
        <el-table-column align="center" property="bye" label="挂机类型(代码)" width="120" />
        <el-table-column align="center" label="通话录音" width="100" >
          <template slot-scope="scope">
            <div v-if="scope.row.action =='Hangup' && parseInt(scope.row.duration) > 0">
              <span @click="handleRecordDetail(scope.row)" class="pointer link-blue">
                <i class="fa fa-play-circle-o" aria-hidden="true"></i>
              </span>
              <span>{{ scope.row.duration }}秒</span>
            </div>
            <div v-else>
              <span>-</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column align="center" property="call_sid" label="callSid(callID)" width="190" />
        <el-table-column align="center" property="caller" label="主叫号码" width="120" />
        <el-table-column align="center" property="called" label="被叫号码" width="120" />
      </el-table>
    </qz-dialog>
    <!-- 打电话 -->
    <el-dialog title="选择联系人dd" :visible.sync="dialogphoneVisible">
      <el-table :data="tableDataperson">
        <el-table-column
          align="center"
          width="120"
          label="联系人"
          prop="name"
        >
        </el-table-column>
        <el-table-column
          align="center"
          label="手机"
          prop="tel"
        >
        </el-table-column>
        <el-table-column
          align="center"
          width="120"
          label="通话"
        >
          <template slot-scope="scope">
            <button @click="telphone(scope.row.tel)">通话</button>
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>
  </div>
</template>

<script>
import { fetchListUser, fetchListVisit, fetchMemberOrderID, fetchAddUser, fetchEditUser, fetchAddListVisit, fetchTelPhone, fetchContract, fetchSoundRecordList, fetchSoundRecordDetail } from '@/api/report-again'
import { isvalidPhone } from '@/utils/validate'
import QzDialog from '@/components/QzDialog'
export default {
  name: 'ReportAgain',
  components: {
    QzDialog
  },
  data() {
    const validPhone = (rule, value, callback) => {
      if (this.form.user_tel === '' && this.form.user_wx === '' && this.form.user_qq === '') {
        callback(new Error('请输入联系方式，手机、微信、QQ必须填写一项'))
      } else if (value && !isvalidPhone(value)) {
        callback(new Error('请输入正确的11位手机号码'))
      } else {
        callback()
      }
    }
    return {
      qian: false,
      phone: false,
      HandelBianji: true,
      dialogFormVisible: false,
      dialogTableVisible: false,
      dialogphoneVisible: false,
      isEdit: false,
      formLabelWidth: '120px',
      intention: '',
      // 打电话返回数据
      callId: '',
      createTime: '',
      // 分页器
      pageData: [],
      user_name: [],
      user_tel: [],
      user_wx: [],
      user_qq: [],
      currentPage: 1,
      pageSize: 20,
      totals: 0,
      // 可编辑列表数据
      tableData: [],
      tableDataperson: [],
      // 合同时间
      contractTime: [{
        allStartTime: '',
        allEndTime: '',
        theStartTime: '',
        theEndTime: '',
        balanceTime: ''
      }],
      // 新增联系人
      form: {
        user_name: '',
        user_tel: '',
        user_wx: '',
        user_qq: ''
      },
      visitform: {
        visitTime: '',
        visit_style: '',
        visit_next_time: '',
        qianyue_status: '',
        retainage_time: '',
        desc: ''
      },
      addrules: {
        user_name: [
          { required: true, message: '联系人必填', trigger: 'change' }
        ],
        user_tel: [
          { validator: validPhone, trigger: 'blur' },
          { max: 11, message: '手机号最多11位', trigger: 'change'}
        ],
        user_wx: [],
        user_qq: []
      },
      tablerules: {
        user_name: [
          { required: true, message: '请输入内容', trigger: 'change' }
        ],
        user_dz: [
          { required: true, message: '请输入内容', trigger: 'change' }
        ],
        user_tel: [],
        user_wx: [],
        user_qq: [],
        memberid: [],
        user_intention: [],
        user_style: []
      },
      visitrules: {
        visitTime: [
          { required: true, message: '请选择拜访时间', trigger: 'blur' }
        ],
        visit_style: [
          { required: true, message: '请选择谈判方式', trigger: 'blur' }
        ],
        visit_next_time: [
          { type: 'date', required: true, message: '请选择下次联系时间', trigger: 'blur' }
        ],
        qianyue_status: [
          { required: true, message: '请选择签约状态', trigger: 'blur' }
        ],
        desc: [
          { required: true, message: '请输入谈话内容', trigger: 'blur' },
          { min: 35, max: 999, message: '内容不得少于35', trigger: 'blur' }
        ],
        retainage_time: []
      },
      // 录音
      gridData: [],
      isplaying: false,
      // 下拉数据
      memberidlist: [],
      chatWaySelect: [
        {
          value: '1',
          label: '上门'
        },
        {
          value: '2',
          label: '电话'
        },
        {
          value: '3',
          label: 'QQ'
        },
        {
          value: '4',
          label: 'WX'
        }
      ],
      intentionSelect: [
        {
          id: 'A',
          label: 'A',
          value: 'A'
        },
        {
          id: 'B',
          label: 'B',
          value: 'B'
        },
        {
          id: 'C',
          label: 'C',
          value: 'C'
        }
      ],
      style: [
        {
          id: '新客户',
          label: '新客户',
          value: "新客户"
        },
        {
          id: '老客户',
          label: '老客户',
          value: '老客户'
        }
      ],
      stateSelect: [
        {
          value: '1',
          label: '未签约'
        },
        {
          value: '2',
          label: '已签约'
        }
      ],
      editOldMember: '', // 编辑状态下会员搜索
      allMembers: [],
      timeout: null,
      recordUrl: '',
      recordPlay: false,
      orderNO: '',
      memberIdBlurFlag: null,
      page_count: 20
    }
  },
  created() {
    this.memberid = this.$route.params.id
    this.inituserlist()
    this.initvisitlist()
  },
  methods: {
    callphone() {
      this.visitform.visit_style === '2' ? this.phone = true : this.phone = false
    },
    qianyue() {
      this.visitform.qianyue_status === '2' ? this.qian = true : this.qian = false
    },
    // 新增联系人确定
    dialogFormSure(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.AddUser()
          // this.dialogFormVisible = false
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    // 新增拜访人列表
    handleSave(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          if(parseInt(this.visitform.qianyue_status) === 2) {
            if(!this.editOldMember) {
              this.$message.error('已签约状态下，会员ID不能为空')
              return
            }
          }
          this.Addvisit()
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    // 编辑
    HandelC() {
      this.isEdit = !this.isEdit
    },
    // 编辑保存
    Handlesavelist(obj) {
      this.saveEditUser(obj)
    },
    // 编辑取消
    Handelexit(obj) {
      this.isEdit = !this.isEdit
      this.inituserlist()
      this.editOldMember = ''
    },
    handleSizeChange() {
      this.currentPage = 1
      this.initvisitlist()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.initvisitlist()
    },
    // get会员ID
    initmemberid() {
      fetchMemberOrderID({
        company_name: ''
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.memberidlist = []
          res.data.data.list.forEach((item, index) => {
            item.value = item.company_id
            item.label = item.company_name
            this.memberidlist.push(item)
          })
        }
      })
    },
    // get联系人列表
    inituserlist() {
      fetchListUser({
        company_id: this.memberid
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          const data = res.data.data
          switch (data.is_new) {
            case 1:
              data.is_new_text = '新客户'
              break
            case 2:
              data.is_new_text = '老客户'
              break
            default:
              break
          }
          switch (data.intention) {
            case 1:
              data.intention_text = 'A'
              break
            case 2:
              data.intention_text = 'B'
              break
            case 3:
              data.intention_text = 'C'
              break
            default:
              break
          }
          this.tableData = []
          this.tableDataperson = []
          this.tableData.push(res.data.data)
          this.editOldMember = data.qc
          res.data.data.contacts.forEach((item, index) => {
            this.tableDataperson.push(item)
          })
          if(res.data.data.user_id) {
            this.contactTime(res.data.data.user_id)
          }
        }
      })
    },
    // 渲染拜访人列表
    initvisitlist() {
      fetchListVisit({
        company_id: this.memberid,
        page: this.currentPage,
        page_count: this.page_count
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          if(res.data.data){
            if (res.data.data.list.length <= 0 && this.currentPage > 1) {
              this.currentPage--
              this.initvisitlist()
            } else {
              this.pageData = []
              this.totals = res.data.data.page.total_number
              this.pageSize = res.data.data.page.page_size
              res.data.data.list.forEach((item, index) => {
                item.record_time = new Date(item.created_time*1000).getFullYear() + "-" + (new Date(item.created_time*1000).getMonth() + 1) + "-" + new Date(item.created_time*1000).getDate() + " " + new Date(item.created_time*1000).getHours() + ":" + new Date(item.created_time*1000).getMinutes()
                item.next_contact_time =  new Date(item.visit_next_time*1000).getFullYear() + "-" + (new Date(item.visit_next_time*1000).getMonth() + 1) + "-" + new Date(item.visit_next_time*1000).getDate() + " " + new Date(item.visit_next_time*1000).getHours() + ":" + new Date(item.visit_next_time*1000).getMinutes()
                item.contact_start_time =  new Date(item.visit_start_time*1000).getFullYear() + "-" + (new Date(item.visit_start_time*1000).getMonth() + 1) + "-" + new Date(item.visit_start_time*1000).getDate() + " " + new Date(item.visit_start_time*1000).getHours() + ":" + new Date(item.visit_start_time*1000).getMinutes()
                item.contact_end_time =  new Date(item.visit_end_time*1000).getFullYear() + "-" + (new Date(item.visit_end_time*1000).getMonth() + 1) + "-" + new Date(item.visit_end_time*1000).getDate() + " " + new Date(item.visit_end_time*1000).getHours() + ":" + new Date(item.visit_end_time*1000).getMinutes()
                this.pageData.push(item)
                this.pageData.forEach((item, idx) => {
                  if (this.currentPage <= 1) {
                    // item.index = idx + 1
                    item.index = this.totals - (idx + 1) + 1
                  }else {
                    // item.index = idx + 1 + (this.currentPage-1)*this.page_count
                    item.index = this.totals - (idx + 1 + (this.currentPage-1)*this.page_count) + 1
                  }
                })
              })
            }
          }
        }
      })
    },
    // 新增联系人
    AddUser() {
      fetchAddUser({
        company_id: this.memberid,
        user_name: this.form.user_name,
        user_tel: this.form.user_tel,
        user_wx: this.form.user_wx,
        user_qq: this.form.user_qq
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.$message({
            type: 'success',
            message: '添加成功!',
            duration: 5 * 1000
          })
          location.reload()
        } else {
          this.$message.error(res.data.error_msg)
        }
      }).catch(res => {
        this.$message({
          message: '保存失败，请重新保存',
          type: 'error',
          duration: 5 * 1000
        })
      })
    },
    // 编辑联系人
    saveEditUser(obj) {
      let error = false
      this.tableData[0].contacts.forEach((item, index) => {
        if(item.name) {
          try {
            if(!item.tel && !item.wx && !item.qq) {
              this.$message.error('联系人：' + item.name + '，缺少联系方式，手机，QQ，微信，请至少填写一个')
              error = true
              throw new Error('EndInterative')
            }
            if(item.tel && !isvalidPhone(item.tel)) {
              this.$message.error('联系人：' + item.name + '，手机号码有误，重新输入')
              error = true
              throw new Error('EndInterative')
            }
          } catch (e) {
            if(e.message === 'EndInterative') {
              throw e
            }
          }

        }
      })
      const queryObj = this.handleEditUserArguments(obj)
      if(!queryObj.user_dz) {
        this.$message.error('地址不能为空')
        return
      }
      if(queryObj.users.length <= 0) {
        this.$message.error('至少填写一个联系人')
        return
      }
      if(!error) {
        this.isEdit = !this.isEdit
      }else{
        return
      }
      console.log(queryObj)
      if(!queryObj.company_user_id){
        // 会员ID被清空，要给出提示
        this.$confirm('会员ID不存在 / 被清空，是否提交?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          // 点击确定，提交数据
          fetchEditUser(queryObj).then(res => {
            if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
              this.$message({
                type: 'success',
                message: '添加成功!'
              })
              this.inituserlist()
            } else {
              this.$message.error(res.data.error_msg)
            }
          }).catch(res => {
            this.$message({
              message: '保存失败，请重新保存',
              type: 'error',
              duration: 5 * 1000
            })
          })
        }).catch(() => {
          // 点击取消，不提交
          this.inituserlist()
        })
      }else{
        // 会员ID没被清空
        fetchEditUser(queryObj).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.$message({
              type: 'success',
              message: '添加成功!'
            })
            this.inituserlist()
          } else {
            this.$message.error(res.data.error_msg)
          }
        }).catch(res => {
          this.$message({
            message: '保存失败，请重新保存',
            type: 'error',
            duration: 3 * 1000
          })
        })
      }
    },
    // 添加拜访
    Addvisit(val) {
      const queryObj = this.handleArguments(val)
      fetchAddListVisit(queryObj).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.$message({
            type: 'success',
            message: '添加成功!'
          })
          this.initvisitlist()
          this.visitform.visitTime = ''
          this.visitform.visit_style = ''
          this.visitform.visit_next_time = ''
          this.visitform.qianyue_status = ''
          this.visitform.retainage_time = ''
          this.visitform.desc = ''
        } else {
          this.$message.error(res.data.error_msg)
        }
      }).catch(res => {
        this.$message({
          message: '保存失败，请重新保存',
          type: 'error',
          duration: 5 * 1000
        })
      })
    },
    handleArguments() {
      const queryObj = {
        company_id: this.memberid,
        visit_start_time: '',
        visit_end_time: '',
        visit_style: this.visitform.visit_style,
        visit_next_time: '',
        qianyue_status: this.visitform.qianyue_status,
        retainage_time: '',
        desc: this.visitform.desc,
        call_id: this.callId,
        created_time: this.createTime
      }
      queryObj.visit_start_time = this.visitform.visitTime[0]
      queryObj.visit_end_time = this.visitform.visitTime[1]
      queryObj.visit_next_time = this.visitform.visit_next_time
      if (this.visitform.retainage_time) {
        queryObj.retainage_time = this.visitform.retainage_time
      }

      return queryObj
    },
    handleEditUserArguments(obj) {
      const users = []
      switch (obj.intention_text) {
        case 'A':
          obj.intention = 1
          break
        case 'B':
          obj.intention = 2
          break
        case 'C':
          obj.intention = 3
          break
        default:
          break
      }
      switch (obj.is_new_text) {
        case '新客户':
          obj.is_new = 1
          break
        case '老客户':
          obj.is_new = 2
          break
        default:
          break
      }
      const queryObj = {
        company_id: obj.id,
        user_dz: obj.address,
        user_intention: obj.intention,
        user_style: obj.is_new,
        company_user_id: this.tableData[0].user_id,
        users: []
      }
      this.tableData[0].contacts.forEach((item, index) => {
        if(item.name) {
          if(!item.tel && !item.wx && !item.qq) {
            this.$message.error('联系人：' + item.name + '缺少联系方式，请至少填写一个')
          }
          users.push({
            company_id: obj.id,
            user_id: item.id,
            user_name: item.name,
            user_tel: item.tel,
            user_wx: item.wx,
            user_qq: item.qq
          })
        }
      })
      queryObj.users = users
      return queryObj
    },
    // 打电话
    telphone(id) {
      fetchTelPhone({
        tel: Number(id)
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 1000010) {
          this.callId = res.data.data.callId
          console.log(this.callId);

          this.createTime = res.data.data.createTime
          this.$message({
            message: res.data.error_msg,
            type: 'success',
            duration: 5 * 1000
          })
        }else {
          this.createTime = res.data.data.createTime
            this.$message({
            message: res.data.error_msg,
            type: 'error',
            duration: 5 * 1000
          })
        }
      })
    },
    // 合同时间列表
    contactTime(id) {
      fetchContract({
        user_id: id
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.contractTime[0].allStartTime = res.data.data.allstart
          this.contractTime[0].allEndTime = res.data.data.allend
          this.contractTime[0].theStartTime = res.data.data.start
          this.contractTime[0].theEndTime = res.data.data.end
          this.contractTime[0].balanceTime = res.data.data.retainage_time
        }
      })
    },
    queryOldMemberSearch(queryString, cb) {
      this.memberIdBlurFlag = null
      fetchMemberOrderID({
        company_name: this.editOldMember
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          let allMembers = []
          if (res.data.data.length > 0) {
            allMembers = res.data.data
          }else{
            allMembers = []
          }
          const results = queryString ? allMembers.filter(this.createStateFilter(queryString)) : allMembers

          clearTimeout(this.timeout)
          this.timeout = setTimeout(() => {
            // cb(results)
            cb(allMembers)
          }, 1000 * Math.random())
        }
      })
    },
    createStateFilter(queryString) {
      return (state) => {
        return (state.company_name.toLowerCase().indexOf(queryString.toLowerCase()) > -1);
      };
    },
    handleSelect(item) {
      this.memberIdBlurFlag = item
      this.tableData[0].user_id = item.company_id
    },
    handleBlur() {
      if(!this.memberIdBlurFlag) {
        this.editOldMember = ''
        this.tableData[0].user_id = ''
      }
    },
    handleSoundRecord(obj) {
      this.dialogTableVisible = true
      this.orderNO = obj.id
      this.gridData = []
      fetchSoundRecordList({
        call_sid: obj.call_sid,
        channel: obj.channel
      }).then(res => {
        if(parseInt(res.status) === 200 && res.data.data.length > 0) {
          this.gridData = res.data.data
          console.log(res.data.data)
          this.gridData.forEach((item, index) => {
            if(item.byetype_name === '-') {
              item.bye = '-'
            }else{
              item.bye = item.byetype_name + "(" + item.byetype + ")"
            }
          })
        }
      })
    },
    handleRecordDetail(obj) {
      this.recordUrl = ''
      if (obj.TelCenter_Channel == 'cuct' || obj.TelCenter_Channel == 'holly') {
        fetchSoundRecordDetail({
          call_sid: obj.call_sid,
          channel: obj.channel
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) == 0) {
            this.recordUrl = this.$qzconfig.oss_audio_host + res.data.data.url
            this.recordPlay = true
            this.isplaying = true
          }
        })
      } else if (obj.TelCenter_Channel == 'ytx') {
        console.log(2)
        this.recordPlay = true
        this.recordUrl = obj.recordurl
      }
    },
    handleCloseRecord() {
      this.isplaying = false
      this.recordPlay = false
    },
    closeDialog() {
      this.orderNO = ''
      this.dialogTableVisible = false
    },
  }
}
</script>

<style>
  .report-again .text {
    width: 100%;
  }
  .report-again .none {
    display: none;
  }
  .report-again .red {
    color: red;
    position: absolute;
    top: 90px;
    left: 60px;
  }
  .report-again .reda {
    color: red;
  }
  .report-again .fl {
    float: left;
  }
  .report-again .mr20 {
    margin-right: 20px;
  }
  .report-again .mt20 {
    margin-top: 20px;
  }
  .report-again .mt40 {
    margin-top: 34px;
  }
  .report-again {
    padding: 20px;
  }
  .report-again .public {
    width: 100%;
    padding: 20px;
    background-color: #fff;
    border-top: 3px solid rgb(197, 196, 196);
    box-sizing: border-box;
  }
  .report-again .h80 {
    height: 270px;
  }
  .report-again .pag {
    margin-top: 20px;
    text-align: center;
  }
  .report-again .audio{
    position: absolute;
    width: 300px;
    height: 53px;
    right: 0;
    top: 0;
    left: 0;
    bottom: 0;
    margin: auto;
  }
  .report-again .el-button+.el-button{
    margin-left: 0;
  }
  .report-again .el-date-editor .el-range-separator{
    padding: 0;
  }
</style>
