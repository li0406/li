<template>
  <div class="repair-order-dia">
    <el-dialog :title="repairOrderInfo.title" :visible.sync="showRepairDia" :close-on-click-modal="false" @close="handleClose">
      <el-form ref="memberForm" :model="memberForm" :rules="memberRules" label-width="130px">
        <el-row :gutter="20">
          <el-col :span="8" style="border-right:1px dashed gray;">
            <el-form-item label="发布时间：">
              {{ repairOrderInfo.time_real }}
            </el-form-item>
            <el-form-item label="IP城市：">{{ repairOrderInfo.ip_city }}</el-form-item>
            <el-form-item label="手机号码：">{{ repairOrderInfo.tel }}</el-form-item>
            <el-form-item label="微信号：">
              {{ repairOrderInfo.wx ? repairOrderInfo.wx : '----' }}&nbsp;&nbsp;
              {{ parseInt(repairOrderInfo.is_wx) === 1 ? '微信联系' : '' }}
            </el-form-item>
            <el-form-item label="备用联系方式：">{{ repairOrderInfo.other_contact ? repairOrderInfo.other_contact : '----' }}</el-form-item>
            <el-form-item label="备用联系人：">{{ repairOrderInfo.standby_user ? repairOrderInfo.standby_user : '----' }}</el-form-item>
            <el-form-item label="房屋地址：">{{ repairOrderInfo.dz ? repairOrderInfo.dz : '----' }}</el-form-item>
            <el-form-item label="联系人：">{{ repairOrderInfo.name }}</el-form-item>
            <el-form-item label="所属区域：">{{ repairOrderInfo.ip_city }} {{ repairOrderInfo.area }}</el-form-item>
            <el-form-item label="小区类型：">
              <span>{{ repairOrderInfo.xiaoqu_type }}</span>
            </el-form-item>
            <el-form-item label="小区：">
              {{ repairOrderInfo.xiaoqu }}
            </el-form-item>
            <el-form-item label="坐标：">
              <span v-if="repairOrderInfo.lng && repairOrderInfo.lat">{{ repairOrderInfo.lng }}, {{ repairOrderInfo.lat }}</span>
              <span v-else>----</span>
            </el-form-item>
            <el-form-item label="装修类型：">{{ repairOrderInfo.lx }}，{{ repairOrderInfo.lxs }}</el-form-item>
            <el-form-item label="户型结构：">
              {{ repairOrderInfo.huxing ? repairOrderInfo.huxing : '--' }}，
              {{ repairOrderInfo.shi ? repairOrderInfo.shi : '0' }}室，
              {{ repairOrderInfo.mianji ? repairOrderInfo.mianji : '0' }}m²
            </el-form-item>
            <el-form-item label="风格：">{{ repairOrderInfo.fengge }}</el-form-item>
            <el-form-item label="预算：">{{ repairOrderInfo.yusuan }}，{{ repairOrderInfo.fangshi }}</el-form-item>
            <el-form-item label="拿房时间：">{{ repairOrderInfo.nf_time }}</el-form-item>
            <el-form-item label="钥匙：">{{ repairOrderInfo.keys }}</el-form-item>
            <el-form-item label="量房/到店：">{{ repairOrderInfo.lftime }}</el-form-item>
            <el-form-item label="开工时间：">{{ repairOrderInfo.start ? repairOrderInfo.start : '' }}</el-form-item>
            <el-form-item label="特殊需求：">{{ repairOrderInfo.text }}</el-form-item>
            <el-form-item label="回访记录：">{{ repairOrderInfo.huifan ? repairOrderInfo.huifan : '----' }}</el-form-item>
            <el-form-item label="待定时间：">{{ repairOrderInfo.visitime }}</el-form-item>
            <el-form-item label="编辑人：">{{ repairOrderInfo.customer }}</el-form-item>
            <el-form-item label="分配上限：">{{ repairOrderInfo.allot_num }}家</el-form-item>
            <el-form-item label="当前状态：">{{ repairOrderInfo.type_fw ? repairOrderInfo.type_fw : '----' }}</el-form-item>
            <el-form-item label="审核人：">{{ repairOrderInfo.chk_customer }}</el-form-item>
          </el-col>
          <el-col :span="8" style="border-right:1px dashed gray;">
            <el-row style="margin-bottom: 20px;">
              <el-col :span="8">
                <div style="line-height: 2"><span><b>分单人：</b></span>{{ repairOrderInfo.fen_customer ? repairOrderInfo.fen_customer : '----' }}</div>
              </el-col>
              <el-col :span="8">
                <div style="line-height: 2"><span><b>分配状态：</b></span>{{ parseInt(repairOrderInfo.fen_status) === 1 ? '分单' : '赠单' }}</div>
              </el-col>
              <el-col :span="8" style="text-align: right;">
                <el-button type="primary" @click="handleToMapAssit">地图辅助</el-button>
              </el-col>
            </el-row>
            <el-form-item label="已分配装修公司：">
              <div class="has_assign"><span v-for="item in fen_company" :key="item.id">{{ item.jc }}</span></div>
            </el-form-item>
            <div style="margin-bottom: 10px;"><el-button type="primary" icon="el-icon-search" @click="distanceCheck">距离查询</el-button></div>
            <div style="margin-bottom: 20px;" v-loading="fcm_loading">
              <div v-for="item in fen_company_more" :key="item.id">
                <p class="red">【{{ item.area }}】</p>
                <el-row>
                  <el-col :span="12" v-for="citem in item.company_list" :key="item.company_id"><div class="green nowarp">
                    <span v-if="parseInt(citem.is_change) === 1 ? true : false">
                      <el-checkbox :label="citem.company_id" v-model="citem.is_choose" disabled></el-checkbox>
                    </span>
                    <span v-else>
                      <el-checkbox :label="citem.company_id" v-model="citem.is_choose" @change="calcUpLimit"></el-checkbox>
                    </span>

                    {{ citem.jc }}
                    [
                      {{ citem.fen }},
                      {{ citem.qiang }},
                      {{ citem.zen }}
                    ]
                    <span v-if="parseInt(citem.is_new) === 1">新</span>
                    <span>{{ citem.cooperation_type }}</span>
                    <span class="blue" v-if="distanceShow">{{ citem.distance }}</span>
                  </div></el-col>
                </el-row>
              </div>
            </div>
            <div>
              <p><b>{{ repairOrderInfo.ip_city }}上次分配公司：</b></p>
              <div v-if="past_fen_company && past_fen_company.length>0"><span  v-for="item in past_fen_company" :key="item.id"> {{ item.jc }} </span></div>
              <div v-else>----</div>
            </div>
            <div>
              <p><b>最近30天过期会员：</b></p>
              <div v-if="past_company && past_company.length>0"><span  v-for="item in past_company" :key="item.id"> {{ item.jc }} </span></div>
              <div v-else>----</div>
            </div>
            <div style="text-align: right;">
              <el-button type="primary" @click="handleSaveComs">分配</el-button>
            </div>
          </el-col>
          <el-col :span="8">
            <el-col :span="12">
              <el-form-item label="半包价：">{{ repairOrderInfo.banbao ? repairOrderInfo.banbao : '----' }}</el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="全包价：">{{ repairOrderInfo.quanbao ? repairOrderInfo.quanbao : '----' }}</el-form-item>
            </el-col>
            <el-form-item label="对接注意事项：">{{ repairOrderInfo.description ? repairOrderInfo.description : '----' }}</el-form-item>
            <div style="margin-bottom: 20px;" v-loading="nbc_loading">
              <p><b>相邻城市装修公司：</b></p>
              <div v-for="item in nearBy_company" :key="item.id" style="margin-bottom: 20px;">
                <p class="red">【{{ item.cname }}】</p>
                <div>
                  <el-row>
                    <el-col :span="12" v-for="citem in item.company_list" :key="item.company_id" style="margin-bottom: 10px;">
                      <div class="green nowarp">
                        <span v-if="parseInt(citem.is_change) === 1 ? true : false">
                          <el-checkbox :label="citem.company_id" v-model="citem.is_choose" disabled></el-checkbox>
                        </span>
                        <span v-else>
                          <el-checkbox :label="citem.company_id" v-model="citem.is_choose" @change="calcUpLimit"></el-checkbox>
                        </span>
                    {{ citem.jc }}
                    [
                    {{ citem.fen }},
                    {{ citem.qiang }},
                    {{ citem.zen }}
                    ]
                    <span v-if="parseInt(citem.is_new) === 1">新</span>
                    <span class="blue">{{ citem.distance }}</span>
                  </div>
                    </el-col>
                  </el-row>
                  <div style="color: #606266;">
                    <p>{{ item.cname }}上次分配公司：</p>
                    <span v-for="litem in item.last_fen_company" :key="item.id">{{ litem.jc }}</span>
                  </div>
                </div>
              </div>
            </div>
          </el-col>
        </el-row>
      </el-form>
    </el-dialog>
  </div>
</template>

<script>
  import { fetchRepairOrderInfo, fetchAssignOrderList, fetchNearByOrderList, fetchSaveOrderCom } from '@/api/orderManage'
  import { parseTime } from "@/utils/index"
  export default {
    name: 'repairOrderDia',
    props: {
      showRepairDia: {
        type: Boolean,
        default: false
      },
      orderId: {
        type: String,
        default: ''
      }
    },
    data() {
      return {
        memberForm: {},
        memberRules: {},
        repairOrderInfo: {
          assignComs: {}
        },
        fen_company: null, // 已分配装修公司
        fen_company_more: null, // 已分配装修公司详细列表
        past_company: null, // 最近30天过期会员
        past_fen_company: null, // 上次分配公司
        nearBy_company: null, // 相邻城市装修公司
        fcm_loading: true,
        nbc_loading: true,
        distanceShow: false
      }
    },
    filters: {
      transferCooperationType(val) {
        switch (val) {
          case 1:
            return '常规'
            break
          case 2:
            return '独家'
            break
          case 3:
            return '垄断'
            break
          default:
            return ''
            break
        }
      }
    },
    watch: {
      'showRepairDia'(val) {
        if(val) {
          this.getOrderInfo()
        }
      }
    },
    methods: {
      calcUpLimit() {
        let limit = 0
        for(let p in this.fen_company_more) {
          this.fen_company_more[p].company_list.forEach(item => {
            if(item.is_choose) {
              limit++
            }
          })
        }
        for(let p in this.nearBy_company) {
          this.nearBy_company[p].company_list.forEach(item => {
            if(item.is_choose) {
              limit++
            }
          })
        }
        if(limit > this.repairOrderInfo.allot_num) {
          this.$message.error('分配会员数不能超过订单分配上限')
        }
      },
      getOrderInfo() {
        fetchRepairOrderInfo({
          order_id: this.orderId
          // order_id: '2019062821737711'
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.repairOrderInfo = res.data.data.info
            this.repairOrderInfo.time_real = this.repairOrderInfo.time_real ? parseTime(this.repairOrderInfo.time_real) : '----'
            this.repairOrderInfo.last_time = this.repairOrderInfo.last_time ? parseTime(this.repairOrderInfo.last_time) : '----'
            this.repairOrderInfo.nf_time = this.repairOrderInfo.nf_time ? parseTime(this.repairOrderInfo.nf_time) : '----'
            //this.repairOrderInfo.start = this.repairOrderInfo.start ? parseTime(this.repairOrderInfo.start) : '----'
            this.repairOrderInfo.visitime = this.repairOrderInfo.visitime ? parseTime(this.repairOrderInfo.visitime) : '----'
            this.repairOrderInfo.title = '修改订单' + this.repairOrderInfo.id + '(上次修改时间 ' + this.repairOrderInfo.last_time + ') | 实际发单时间 '+ this.repairOrderInfo.time_real +' | 订单发布完整度' + this.repairOrderInfo.wzd + '.00%'
            this.getAssignCompany()
            this.getNeayByCompanys()
          }else{
            this.repairOrderInfo = {}
          }
        }).catch(res => {
          this.$message.error('网络异常，请稍后再试')
          this.repairOrderInfo = {}
        })
      },
      getAssignCompany() {
        fetchAssignOrderList({
          order_id: this.orderId
          // order_id: '2019062821737711'
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.fen_company = res.data.data.info.fen_company
            this.past_company = res.data.data.info.past_company
          }
        })
      },
      getNeayByCompanys() {
        this.fcm_loading = true
        this.nbc_loading = true
        fetchNearByOrderList({
          order_id: this.orderId
          // order_id: '2019062821737711'
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.past_fen_company = res.data.data.now_fen_company
            this.fen_company_more = res.data.data.now_company
            this.nearBy_company = res.data.data.other_company
            for(let p in this.fen_company_more) {
              const company_list = this.fen_company_more[p].company_list
              if(company_list && company_list.length>0){
                company_list.forEach(item => {
                  item.is_choose = parseInt(item.is_choose) === 1 ? true : false
                })
              }
            }
            for(let p in this.nearBy_company) {
              const company_list = this.nearBy_company[p].company_list
              if(company_list && company_list.length>0){
                company_list.forEach(item => {
                  item.is_choose = parseInt(item.is_choose) === 1 ? true : false
                })
              }
            }
          }else{
            this.$message.error(res.data.error_msg)
          }
          this.fcm_loading = false
          this.nbc_loading = false
        }).catch(res => {
          this.$message.error('网络异常，请稍后再试')
          this.fcm_loading = false
          this.nbc_loading = false
        })
      },
      handleSaveComs() {
        const coms = []
        const cname = []
        for(let p in this.fen_company_more) {
          const company_list = this.fen_company_more[p].company_list
          if(company_list && company_list.length>0){
            company_list.forEach(item => {
              if(item.is_choose && parseInt(item.is_change) === 2) {
                coms.push(item.id)
                cname.push(item.jc)
              }
            })
          }
        }
        for(let p in this.nearBy_company) {
          const company_list = this.nearBy_company[p].company_list
          if(company_list && company_list.length>0){
            company_list.forEach(item => {
              if(item.is_choose && parseInt(item.is_change) === 2) {
                coms.push(item.id)
                cname.push(item.jc)
              }
            })
          }
        }
        if(coms.length <=0 ) {
          this.$message.error('请选择分配装修公司')
          return
        }
        this.$confirm('确定将订单赠送给“'+cname.join('、')+'”吗?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          fetchSaveOrderCom({
            order_id: this.orderId,
            company: coms
          }).then(res => {
            if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
              this.$message({
                message: '保存成功',
                type: 'success'
              })
              this.emitCloseDiaFn('update')
            }else{
              this.$message.error(res.data.error_msg)
            }
          }).catch(res => {
            this.$message.error('网络异常，请稍后再试')
          })
        }).catch(() => {
          this.$message({
            type: 'info',
            message: '已取消分配'
          })
        })
      },
      handleToMapAssit() {
        window.open('http://168new.qizuang.com/map/mapmarker?id=' + this.orderId)
      },
      handleClose() {
        this.emitCloseDiaFn()
      },
      emitCloseDiaFn(tag) {
        this.$emit('closeDia', tag)
      },
      distanceCheck() {
        this.distanceShow = true
      }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss">
.repair-order-dia{
  .el-dialog{
    width: 80%;
  }
  .el-form-item__label{
    line-height: 25px;
  }
  .el-form-item__content{
    line-height: 25px;
  }
  .nowarp{
    white-space: nowrap;
  }
  .has_assign{
    color: green;
  }
  .has_assign span::after{
    content: '、'
  }
  .has_assign span:last-child::after{
    content: ''
  }
  .el-form-item{
    margin-bottom: 0;
  }
  .blue{
    color: blue;
  }
  .el-checkbox__label{
    display: none;
  }
}
</style>
