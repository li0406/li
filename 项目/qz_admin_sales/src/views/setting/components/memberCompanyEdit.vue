<template>
  <div class="member-company-edit">
    <el-dialog title="编辑" :visible.sync="showEditDia" :close-on-click-modal="false" width="980px" @close="handleClose">
      <el-form ref="memberForm" :model="memberForm" :rules="memberRules" label-width="110px">
        <el-form-item label="会员ID">{{ memberForm.id }}</el-form-item>
        <el-form-item label="会员简称">{{ memberForm.jc }}</el-form-item>
        <el-form-item label="所属城市">
          <el-select v-model="memberForm.city" placeholder="请选择城市" @change="getAreaList('city')">
            <el-option
              v-for="item in cityOption"
              :key="item.value"
              :label="item.value"
              :value="item.cid"
            />
          </el-select>
          <el-select v-model="memberForm.region" placeholder="请选择">
            <el-option
              v-for="item in areaOption"
              :key="item.value"
              :label="item.value"
              :value="item.area_id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="详细地址">
          <el-input v-model="memberForm.address" placeholder="请输入详细地址" />
        </el-form-item>
        <el-form-item label="坐标">
          <el-col :span="11">
            <el-input v-model="memberForm.location" />
          </el-col>
          <el-col class="line" :span="2"><i class="el-icon-location red ml-10 pointer" @click="getBaiduMapPoint" />
          </el-col>
        </el-form-item>
        <el-form-item label="新房/旧房">
          <el-radio-group v-model="memberForm.houseType">
            <el-radio label="1" name="type">新房</el-radio>
            <el-radio label="2" name="type">旧房</el-radio>
            <el-radio label="3" name="type">新房/旧房</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="半包/全包">
          <el-radio-group v-model="memberForm.decortType">
            <el-radio label="1">半包</el-radio>
            <el-radio label="2">全包</el-radio>
            <el-radio label="3">半包/全包</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="公装/家装">
          <el-radio-group v-model="memberForm.decortKind">
            <el-radio label="2">公装</el-radio>
            <el-radio label="1">家装</el-radio>
            <el-radio label="3">公装/家装</el-radio>
          </el-radio-group>
        </el-form-item>

        <!--        <el-form-item v-if="cooperate_mode === 2" label="轮单单价">-->
        <!--          <el-input v-model="memberForm.order_round_amount"></el-input>-->
        <!--        </el-form-item>-->
        <!--        <el-form-item label="轮单单价">-->
        <!--          <el-table :data="memberForm.roundPrice" border style="width: 100%">-->
        <!--            <el-table-column label="装修类型" width="200" align="center">-->
        <!--              123-->
        <!--            </el-table-column>-->
        <!--            <el-table-column prop="list" label="轮单单价" align="center">-->
        <!--            </el-table-column>-->
        <!--          </el-table>-->
        <!--        </el-form-item>-->

        <el-form-item v-if="cooperate_mode === 2 || cooperate_mode === 4" label="轮单单价">
          <div class="roundPrice">
            <div class="roundPrice-header">
              <div class="header-type left">装修类型</div>
              <div class="header-price">轮单单价</div>
            </div>

            <div class="roundPrice-con">
              <div class="con-item jz">
                <div class="con-type left"><p>家装面积</p></div>
                <div class="con-price">
                  <span>大于</span>
                  <select v-model="memberForm.area">
                    <option :value="null">请选择</option>
                    <option :value="60">60㎡</option>
                    <option :value="50">50㎡</option>
                  </select>
                  <input v-model="memberForm.mjmax_price" type="text" maxlength="8" placeholder="请输入轮单单价" @input="changeCount()">
                </div>
              </div>
              <div class="con-item jz">
                <div class="con-type left"><p>家装面积</p></div>
                <div class="con-price">
                  <span>小于</span>
                  <select v-model="memberForm.area">
                    <option :value="null">请选择</option>
                    <option :value="60">60㎡</option>
                    <option :value="50">50㎡</option>
                  </select>
                  <input v-model="memberForm.mjmin_price" type="text" maxlength="8" placeholder="请输入轮单单价">
                </div>
              </div>
              <div class="con-item">
                <div class="con-type left"><p>公装</p></div>
                <div class="con-price">
                  <input v-model="memberForm.gz_price" type="text" maxlength="8" placeholder="请输入轮单单价">
                </div>
              </div>
              <div class="con-item">
                <div class="con-type left"><p>局改</p></div>
                <div class="con-price">
                  <input v-model="memberForm.jb_price" type="text" maxlength="8" placeholder="请输入轮单单价">
                </div>
              </div>

            </div>
          </div>
        </el-form-item>

        <el-form-item label="对立公司ID">
          <el-col :span="11">
            <el-input v-model="memberForm.competition" placeholder="请输入对立公司ID" />
          </el-col>
          <el-col class="red" :span="1">&nbsp;</el-col>
          <el-col class="red" :span="11">注：填写多个对立公司ID，请用英文“,”做区分</el-col>
        </el-form-item>
        <el-form-item label="接单区域">
          <div class="deliver_area">
            <el-tabs v-model="activeTabName">
              <el-tab-pane
                v-for="item in memberForm.deliver_area"
                :key="item.cid"
                :label="item.cname+'('+item.count+')'"
                :name="item.cid"
              >
                <el-checkbox
                  v-for="areaItem in item.children"
                  :key="areaItem.area_id"
                  v-model="areaItem.checked"
                  :label="areaItem.area_id"
                  @change="updateDeliverData(areaItem)"
                >{{ areaItem.area }}
                </el-checkbox>
              </el-tab-pane>
            </el-tabs>
          </div>
        </el-form-item>
        <el-form-item label="分单面积">
          <el-input v-model="memberForm.fendanMj" placeholder="不限" />
        </el-form-item>
        <el-form-item label="分单特殊需求">
          <el-input v-model="memberForm.fendanDesc" type="textarea" placeholder="请填写分单特殊需求" />
        </el-form-item>
        <el-form-item label="是否接受赠单">
          <el-radio-group v-model="memberForm.isAcceptZd">
            <el-radio label="1">是</el-radio>
            <el-radio label="2">否</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="赠单面积">
          <el-input v-model="memberForm.zengdanMj" placeholder="不限"></el-input>
        </el-form-item>
        <el-form-item label="是否开通虚拟号">
          <el-col :span="6">
            <el-radio-group v-model="memberForm.pnp_on" @change="handRadio">
              <el-radio label="1">是</el-radio>
              <el-radio label="2">否</el-radio>
            </el-radio-group>
          </el-col>
          <el-col class="red" :span="4">虚拟号开通说明：
          </el-col>
          <el-col class="red" :span="14" style="margin-bottom: -176px;">
            1、若开通，则装企所分配的订单的业主手机号将会被隐私保护，请第一时间通知装企为子账号的手机号授权，否则装企的子账号在订单有效期内，无法拨打成功。主账号默认可虚拟号拨打。<br>
            2、装企/装企子账号使用的本机号不是装企绑定的手机号，则无法拨打成功</el-col>
        </el-form-item>
        <el-form-item label="订单保护有效期">
          <el-input-number v-model="memberForm.pnp_days" controls-position="right"  :precision="0" :min="1" :max="10" :disabled="ifxnh"></el-input-number>&nbsp;&nbsp;天
        </el-form-item>
        <el-form-item label="授权手机号上限">
          <el-input-number v-model="memberForm.pnp_tel_num" controls-position="right"  :precision="0" :min="5" :max="10" :disabled="ifxnh"></el-input-number>&nbsp;&nbsp;个
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="onSubmit('memberForm')">保 存</el-button>
          <el-button @click="handleClose">取 消</el-button>
        </el-form-item>
      </el-form>
    </el-dialog>
  </div>
</template>

<script>
import { fetchMemberCompanyEdit, fetchMemberCompanyDetail } from '@/api/setting'
import { fetchAreaList } from '@/api/common'

export default {
  name: 'MemberCompanyEdit',
  props: {
    'showEditDia': {
      type: Boolean,
      default: false
    },
    'currentMemberId': {
      type: String,
      default: ''
    },
    'cityOption': {
      type: Array,
      default: []
    }
  },
  data() {
    return {
      activeTabName: '',
      cooperate_mode: '',
      areaOption: [{
        area_id: '0', area: '请选择', value: '请选择'
      }],
      memberForm: {
        id: '',
        jc: '',
        address: '',
        city: '',
        region: '',
        location: '',
        houseType: '',
        decortType: '',
        decortKind: '',
        competition: '',
        fendanMj: '',
        fendanDesc: '',
        isAcceptZd: '',
        pnp_on: '',
        pnp_days: 1,
        pnp_tel_num: 1,
        zengdanMj: '',
        deliver_area: null,
        // order_round_amount: '',
        area: '',
        gz_price: null,
        jb_price: null,
        jg_td: '',
        mjmin_price_td: '',
        mjmin_price: null,
        mjmin_mianji: '',
        mjmax_price: null,
        mjmax_mianji: ''
      },
      numReg: /^[0-9]+.?[0-9]*$/,
      memberRules: {
        name: [
          { required: true, message: '请输入活动名称', trigger: 'blur' },
          { min: 3, max: 5, message: '长度在 3 到 5 个字符', trigger: 'blur' }
        ],
        region: [
          { required: true, message: '请选择活动区域', trigger: 'change' }
        ],
        date1: [
          { type: 'date', required: true, message: '请选择日期', trigger: 'change' }
        ],
        date2: [
          { type: 'date', required: true, message: '请选择时间', trigger: 'change' }
        ],
        type: [
          { type: 'array', required: true, message: '请至少选择一个活动性质', trigger: 'change' }
        ],
        resource: [
          { required: true, message: '请选择活动资源', trigger: 'change' }
        ],
        desc: [
          { required: true, message: '请填写活动形式', trigger: 'blur' }
        ]
      }

    }
  },
  watch: {
    'showEditDia'(val) {
      if (val) {
        this.getMemberComDetail()
        this.memberForm.mjmax_price = ''
        this.memberForm.mjmin_price = ''
        this.memberForm.gz_price = ''
        this.memberForm.jb_price = ''
      }
    },
    'memberForm.mjmax_price'(val) {
      if (val !== null && val !== '') {
        if (!this.numReg.test(val)) {
          this.$message({
            message: '请输入正确价格',
            type: 'error'
          })
        }
        this.memberForm.mjmax_price = val.toString().replace(/[^\d\.]/g, '')
      }
    },
    'memberForm.mjmin_price'(val) {
      if (val !== null && val !== '') {
        if (!this.numReg.test(val)) {
          this.$message({
            message: '请输入正确价格',
            type: 'error'
          })
        }
        this.memberForm.mjmin_price = val.toString().replace(/[^\d\.]/g, '')
      }
    },

    'memberForm.gz_price'(val) {
      if (val !== null && val !== '') {
        if (!this.numReg.test(val)) {
          this.$message({
            message: '请输入正确价格',
            type: 'error'
          })
        }
        this.memberForm.gz_price = val.toString().replace(/[^\d\.]/g, '')
      }
    },

    'memberForm.jb_price'(val) {
      if (val !== null && val !== '') {
        if (!this.numReg.test(val)) {
          this.$message({
            message: '请输入正确价格',
            type: 'error'
          })
        }
        this.memberForm.jb_price = val.toString().replace(/[^\d\.]/g, '')
      }
    }
  },
    computed: {
      ifxnh: function() {
        return this.memberForm.pnp_on == 1 ? false : true
      }
    },
  methods: {
      handRadio(e) {
        if(e == 2){
          // this.memberForm.pnp_days = 1
          // this.memberForm.pnp_tel_num = 5
        }
      },
    getAreaList(qx) {
      fetchAreaList({
        cid: this.memberForm.city
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.areaOption = [{
            area_id: '0', area: '请选择', value: '请选择'
          }]
          this.areaOption = this.areaOption.concat(res.data.data[0])
          this.areaOption.forEach(item => {
            item.area_id = String(item.area_id)
          })
          qx === 'city' ? this.memberForm.region = '0' : (!qx ? this.memberForm.region = '0' : this.memberForm.region = String(qx))
        }
      })
    },
    getMemberComDetail() {
      fetchMemberCompanyDetail({
        id: this.currentMemberId
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          const data = res.data.data
          this.memberForm.id = data.detail.id
          this.memberForm.jc = data.detail.jc
          this.memberForm.address = data.detail.dz
          this.memberForm.city = data.detail.cs
          this.memberForm.region = data.detail.qx
          this.memberForm.location = data.detail.latlng
          this.memberForm.houseType = String(data.detail.lxs)
          this.memberForm.decortType = String(data.detail.contract)
          this.memberForm.decortKind = String(data.detail.lx)
          this.memberForm.competition = data.detail.other_id
          this.memberForm.fendanMj = data.detail.fen_mianji
          this.memberForm.fendanDesc = data.detail.fen_special_needs
          this.memberForm.isAcceptZd = String(data.detail.get_gift_order)
          this.memberForm.pnp_on = String(data.detail.pnp_on)
          this.memberForm.pnp_days = data.detail.pnp_days
          this.memberForm.pnp_tel_num = data.detail.pnp_tel_num
          this.memberForm.zengdanMj = data.detail.mianji
          this.memberForm.deliver_area = data.deliver_area
          this.activeTabName = data.deliver_area[0].cid
          // this.memberForm.order_round_amount = data.detail.order_round_amount
          this.cooperate_mode = data.detail.cooperate_mode
          this.memberForm.roundPrice = data.detail.round_order_amount
          this.memberForm.deliver_area.forEach(item => {
            if (item.children && item.children.length > 0) {
              item.children.forEach(citem => {
                citem.checked = parseInt(citem.checked) === 1
              })
            }
          })
          this.getAreaList(data.detail.qx)
          if (this.cooperate_mode === 2 || this.cooperate_mode === 4) {
            if (data.detail.round_order_amount !== null) {
              this.memberForm.gz_price = data.detail.round_order_amount.gz_price
              this.memberForm.jb_price = data.detail.round_order_amount.jg_price
              this.memberForm.jg_td = data.detail.round_order_amount.jg_price
              this.memberForm.mjmin_price = data.detail.round_order_amount.mjmin_price
              this.memberForm.mjmin_price_td = data.detail.round_order_amount.mjmin_price
              // 面积一样，获取一个
              // this.memberForm.mjmin_mianji = data.detail.round_order_amount.mjmin_mianji
              this.memberForm.mjmax_price = data.detail.round_order_amount.mjmax_price
              this.memberForm.area = data.detail.round_order_amount.mjmax_mianji || data.detail.round_order_amount.mjmin_mianji
              console.log(this.memberForm.area)
            }
          }
        }
      })
    },
    onSubmit(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.handleSaveEdit()
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    getBaiduMapPoint() {
      window.open('http://api.map.baidu.com/lbsapi/getpoint/index.html')
    },
    updateDeliverData(obj) {
      this.memberForm.deliver_area.forEach(item => {
        let count = 0
        item.children.forEach(citem => {
          if (citem.checked) {
            count++
          }
        })
        item.count = count
      })
    },
    handleArgs() {
      const deliver_area = []
      this.memberForm.deliver_area.forEach(item => {
        item.children.forEach(citem => {
          if (citem.checked) {
            deliver_area.push(citem.area_id)
          }
        })
      })
      const queryObj = {
        id: this.memberForm.id,
        cs: this.memberForm.city,
        qx: this.memberForm.region,
        dz: this.memberForm.address,
        latlng: this.memberForm.location,
        lxs: this.memberForm.houseType,
        contract: this.memberForm.decortType,
        lx: this.memberForm.decortKind,
        other_id: this.memberForm.competition,
        fen_mianji: this.memberForm.fendanMj,
        fen_special_needs: this.memberForm.fendanDesc,
        get_gift_order: this.memberForm.isAcceptZd,
        pnp_on: this.memberForm.pnp_on,
        pnp_days: this.memberForm.pnp_days,
        pnp_tel_num: this.memberForm.pnp_tel_num,
        mianji: this.memberForm.zengdanMj,
        deliver_area: deliver_area.join(','),
        gz_price: this.memberForm.gz_price,
        jb_price: this.memberForm.jb_price,
        mjmin_price: this.memberForm.mjmin_price,
        mjmax_price: this.memberForm.mjmax_price,
        mjmin_mianji: this.memberForm.area,
        mjmax_mianji: this.memberForm.area
      }
      return queryObj
    },
    handleSaveEdit() {
      let queryObj = {}
      const reg = /^[+]{0,1}(\d+)$|^[+]{0,1}(\d+\.\d+)$/
      queryObj = this.handleArgs()
      if (!parseInt(queryObj.cs) || !parseInt(queryObj.qx)) {
        this.$message.error('请选择城市和区县')
        return
      }
      if (this.cooperate_mode === 2 || this.cooperate_mode === 4) {
        if ((this.memberForm.mjmax_price === null || this.memberForm.mjmax_price === '') && (this.memberForm.mjmin_price === null || this.memberForm.mjmin_price === '') && (this.memberForm.gz_price === null || this.memberForm.gz_price === '') && (this.memberForm.jb_price === null || this.memberForm.jb_price === '')) {
          this.$message.error('请至少填写一个轮单单价')
          return
        }
        if ((this.memberForm.mjmax_price !== null && this.memberForm.mjmax_price !== '') || (this.memberForm.mjmin_price !== null && this.memberForm.mjmin_price !== '')) {
          if (this.memberForm.area === null || this.memberForm.area === 0) {
            this.$message.error('请选择轮单面积')
            return
          }
        }
      }
      fetchMemberCompanyEdit(queryObj).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.$message({
            message: '编辑成功',
            type: 'success'
          })
          this.emitCloseDiaFn('updata')
        } else {
          this.$message.error(res.data.error_msg)
        }
      })
    },
    changeCount() {
      if (this.memberForm.jg_td === null) {
        this.memberForm.jb_price = this.memberForm.mjmax_price / 2 || ''
      } else {
        this.memberForm.jb_price = this.memberForm.jb_price
      }

      if (this.memberForm.mjmin_price_td === null) {
        this.memberForm.mjmin_price = this.memberForm.mjmax_price / 2 || ''
      } else {
        this.memberForm.mjmax_price = this.memberForm.mjmax_price
      }
    },

    handleClose() {
      this.emitCloseDiaFn()
    },
    emitCloseDiaFn(tag) {
      this.$emit('closeDia', tag)
    }
  }

}
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
  .member-company-edit {
    .text-right {
      text-align: right;
    }

    .ml-10 {
      margin-left: 10px;
    }

    .red {
      color: red;
    }

    .el-checkbox {
      margin-left: 0;
      margin-right: 30px;
    }
    .roundPrice{
      border: 1px solid rgba(223, 223, 223, 1);
      .left{
        border-right: 1px solid rgba(223, 223, 223, 1);
      }
      input{
        &:focus{
          outline: 1px solid #429eff;
        }
      }
      .roundPrice-header{
        display: flex;
        background-color: rgba(242, 242, 242, 1);
        text-align: center;
        .header-type{
          width: 200px;
          padding: 10px 0;
        }
        .header-price{
          width: calc(100% - 200px);
          padding: 10px 0;
        }
      }
      .roundPrice-con{
        .con-item{
          display: flex;
          border-top: 1px solid rgba(223, 223, 223, 1);
          input{
            width: 500px;
            height: 32px;
            line-height: 32px;
            border: 1px solid rgba(204, 204, 204, 1);
            border-radius: 3px;
            padding: 0 10px;
            outline: none;
            transition: all .3s;
            &:focus{
              border: 1px solid rgba(64, 158, 255, 1);
            }
          }
          input::-webkit-input-placeholder{
            color:rgb(153, 153, 153);
          }
          input::-moz-placeholder{   /* Mozilla Firefox 19+ */
            color:rgb(153, 153, 153);
          }
          input:-moz-placeholder{    /* Mozilla Firefox 4 to 18 */
            color:rgb(153, 153, 153);
          }
          input:-ms-input-placeholder{  /* Internet Explorer 10-11 */
            color:rgb(153, 153, 153);
          }
          &.jz{
            input{
              width: 360px;
              height: 32px;
              line-height: 32px;
              border: 1px solid rgba(204, 204, 204, 1);
              border-radius: 3px;
              padding: 0 10px;
              transition: all .3s;
              &:focus{
                border: 1px solid rgba(64, 158, 255, 1);
              }
            }
          }
          p{
            margin: 0;
          }
          .con-type{
            width: 200px;
            text-align: center;
            padding: 10px 0px;
          }
          .con-price{
            width: calc(100% - 200px);
            box-sizing: border-box;
            padding: 10px 20px;
            span{
              font-size: 14px;
              color: #333333;
            }
            select{
              width: 79px;
              height: 32px;
              line-height: 32px;
              border-radius: 2px;
              margin: 0 10px;
              outline: none;
            }
          }
        }
      }
    }

  }

</style>
