<template>
    <div class="small-report">
        <div class="search">
            <div class="clearfix">
                <div class="yixiang fl mr20">
                    公司名称：<br>
                    <el-input v-model="companyName" placeholder="请输入"></el-input>
                </div>
                <div class="fl mr20">
                    汇款方：<br>
                    <el-input v-model="remitter" placeholder="请输入"></el-input>
                </div>
                <div class="fl mr20">
                    城市：<br>
                    <el-autocomplete
                    v-model="citySelectStr"
                    class="inline-input mt4"
                    :fetch-suggestions="queryCity"
                    placeholder="请输入"
                    @select="handleSelect"
                    @blur="handleBlur"
                    />
                </div>
                <div class="fl mr20">
                    合作类型：<br>
                    <el-select v-model="cooperation" filterable placeholder="请选择">
                        <el-option
                                v-for="item in cptOptions"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value"
                        >
                        </el-option>
                    </el-select>
                </div>
                <div class="fl mr20">
                    审核状态：<br>
                    <el-select v-model="examineVal" filterable placeholder="请选择">
                        <el-option
                            v-for="item in examineOptions"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value"
                        >
                        </el-option>
                    </el-select>
                </div>
                <div class="fl mr20">
                    是否报备会员：<br>
                    <el-select v-model="isMemberVal" filterable placeholder="请选择">
                        <el-option
                            v-for="item in isMemberOptions"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value"
                        >
                        </el-option>
                    </el-select>
                </div>
                <div class="fl">
                    <br>
                    <el-button type="primary" icon="el-icon-search" @click="handleSearch">查询</el-button>
                </div>
            </div>
            <div><br><el-button type="success" @click="handleAdd">添加</el-button></div>
        </div>
        <div class="qian-main">
            <el-table
                v-loading="loading"
                :data="tableData"
                border
            >
                <el-table-column
                    align="center"
                    prop="tableIndex"
                    width="50"
                    label="序号"
                >
                </el-table-column>
                <el-table-column
                    align="center"
                    prop="company_name"
                    label="公司名称"
                >
                </el-table-column>
                <el-table-column
                    align="center"
                    label="合作类型"
                >
                    <template slot-scope="scope">
                        {{ scope.row.cooperation_type_name }}
                    </template>
                </el-table-column>
                <el-table-column
                    align="center"
                    width="60"
                    label="城市"
                >
                    <template slot-scope="scope">
                        {{ scope.row.city_name ? scope.row.city_name : '----' }}
                    </template>
                </el-table-column>
                <el-table-column
                    align="center"
                    label="倍数/返点"
                >
                    <template slot-scope="scope">
                        {{ (parseFloat(scope.row.viptype) === 0 || !scope.row.viptype) && (scope.row.viptype != '0%')  ? '----' : scope.row.viptype}}
                    </template>
                </el-table-column>
                <el-table-column
                    align="center"
                    label="汇/退款方名称"
                >
                  <template slot-scope="scope">
                    {{!scope.row.payment_uname  ? '----' : scope.row.payment_uname}}
                  </template>
                </el-table-column>
                <el-table-column
                    align="center"
                    prop="payment_date_show"
                    label="汇/退款时间"
                >
                </el-table-column>
                <el-table-column
                    align="center"
                    label="收/退款金额"
                >
                    <template slot-scope="scope">

                        <span class="red" v-if="scope.row.cooperation_type == 11">-{{scope.row.refund_money}}</span>
                        <span v-else>{{  Number(scope.row.payment_total_money) > 0 ?  scope.row.payment_total_money : '----' }}</span>
                    </template>
                </el-table-column>
                <el-table-column
                    align="center"
                    label="收/退款类型"
                >
                  <template slot-scope="scope">
                    <span class="red" v-if="scope.row.cooperation_type == 11">{{scope.row.payment_type_name}}</span>
                    <span v-else>{{scope.row.payment_type_name}}</span>
                  </template>
                </el-table-column>
                <el-table-column
                    align="center"
                    prop="creator_name"
                    label="报备人"
                >
                </el-table-column>
                <el-table-column
                    align="center"
                    label="收/退款账户及方式"
                    width="150"
                >
                    <template slot-scope="scope">
                      <template v-if="scope.row.payee_list.length > 0">
                        <p v-for="(item) in scope.row.payee_list" :key="item.payee_px">{{item.payee_type_name}}</p>
                      </template>
                      <span v-else>----</span>
                    </template>
                </el-table-column>
                <el-table-column
                    align="center"
                    label="状态"
                >
                    <template slot-scope="scope">
                        <span :class="classFilter(scope.row.exam_status)">{{ scope.row.exam_status_name }}</span>
                    </template>
                </el-table-column>
                <el-table-column
                    align="center"
                    prop="updated_date"
                    label="保存时间"
                    width="100"
                >
                </el-table-column>
                <el-table-column
                    align="center"
                    label="是否报备会员"
                >
                    <template slot-scope="scope">
                        {{ scope.row.is_related | isNewFilter }}
                    </template>
                </el-table-column>
                <el-table-column
                    align="center"
                    label="操作"
                    width="200"
                >
                    <template slot-scope="scope">
                        <span
                            class="supplement-btn"
                            v-if="parseInt(scope.row.exam_status) === 3 &&
                            parseInt(scope.row.is_related) === 1 &&
                            parseInt(scope.row.cooperation_type) !== 8 &&
                            parseInt(scope.row.cooperation_type) !== 9 &&
                            parseInt(scope.row.cooperation_type) !== 11 &&
                            parseInt(scope.row.cooperation_type) !== 13"
                            @click="handleBaobei(scope.row)"
                        >
                            报备会员
                        </span>
                        <span
                            class="supplement-btn"
                            v-if="parseInt(scope.row.exam_status) === 3 &&
                            parseInt(scope.row.is_related) === 2 &&
                            parseInt(scope.row.cooperation_type) !== 8 &&
                            parseInt(scope.row.cooperation_type) !== 9 &&
                            parseInt(scope.row.cooperation_type) !== 11 &&
                            parseInt(scope.row.cooperation_type) !== 13"
                            @click="handleBaobeiAgain(scope.row)"
                        >
                            重新报备会员
                        </span>
                        <span
                            class="edit-btn"
                            v-if="parseInt(scope.row.exam_status) === 1 || parseInt(scope.row.exam_status) === 4 || parseInt(scope.row.exam_status) === 6"
                            @click="handleEdit(scope.row)"
                        >
                            编辑
                        </span>
                        <span
                            class="del-btn"
                            v-if="parseInt(scope.row.exam_status) === 1 || parseInt(scope.row.exam_status) === 4 || parseInt(scope.row.exam_status) === 6"
                            @click="handleDel(scope.row)"
                        >
                            删除
                        </span>
                        <span
                            class="widthdraw-btn"
                            v-if="parseInt(scope.row.exam_status) === 1 || parseInt(scope.row.exam_status) === 4 || parseInt(scope.row.exam_status) === 6"
                            @click="handleSub(scope.row)"
                        >
                            提交
                        </span>
                        <span
                            class="check-btn"
                            @click="handleCheck(scope.row)"
                        >
                            查看
                        </span>
                        <span
                            class="del-btn"
                            v-if="parseInt(scope.row.exam_status) === 2"
                            @click="handleWithdraw(scope.row)"
                        >
                            撤回
                        </span>
                        <div
                            class="report-btn"
                            v-if="scope.row.cooperation_type != 11 && (scope.row.exam_status==3) || (scope.row.exam_status==5) || (scope.row.exam_status==30)"
                            @click="invoiceReporting(scope.row)"
                        >
                            发票报备
                        </div>
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
        <el-dialog title="提交确认" :visible.sync="submitDialogVisible" :close-on-click-modal="modal" width="30%" center>
            <el-form>
                <el-form-item label="公司名称：">{{detail.company_name}}</el-form-item>
                <el-form-item label="会员类型：">{{detail.cooperation_type_name}}</el-form-item>
                <el-form-item :label="detail.cooperation_type == 11?'退款城市：':'收款城市：'">{{detail.city_name}}</el-form-item>
                <el-form-item label="返点比例：" v-if="detail.cooperation_type == 6 || detail.cooperation_type == 8 || detail.cooperation_type == 10">{{detail.back_ratio}}</el-form-item>
                <el-form-item label="会员倍数：" v-else-if="detail.cooperation_type == 1 || detail.cooperation_type == 2 || detail.cooperation_type == 3 || detail.cooperation_type == 7">{{detail.viptype}}</el-form-item>
                <el-form-item :label="detail.cooperation_type == 14?'会员倍数：':detail.cooperation_type == 15?'返点比例：':''">标杆会员（保产值）</el-form-item>
                <el-form-item :label="detail.cooperation_type == 11?'退款类型：':'收款类型：'">{{detail.payment_type_name}}</el-form-item>
                <el-form-item label="会员费：" v-if="detail.cooperation_type == 1 || detail.cooperation_type == 2 || detail.cooperation_type == 3 || detail.cooperation_type == 7">{{detail.round_order_money}}</el-form-item>
                <el-form-item label="轮单费：" v-if="detail.cooperation_type == 6">{{detail.round_order_money}}</el-form-item>
                <el-form-item label="平台使用费：" v-if="detail.cooperation_type == 1 || detail.cooperation_type == 2 || detail.cooperation_type == 3 || detail.cooperation_type == 6 || detail.cooperation_type == 7">{{detail.platform_money}}</el-form-item>
                <el-form-item label="平台使用费有效期：" v-if="detail.platform_money || detail.cooperation_type == 13">{{detail.platform_money_start_date}} 至 {{detail.platform_money_end_date}}</el-form-item>
                <el-form-item v-if="detail.other_money" label="其他金额：">{{ detail.other_money }}</el-form-item>
                <el-form-item v-if="detail.other_money" label="其他金额类型：">{{ detail.other_money_remark }}</el-form-item>
                <el-form-item label="收款金额：" v-if="detail.cooperation_type != 10 && detail.cooperation_type != 11 && detail.cooperation_type != 13">{{detail.payment_total_money}}</el-form-item>
                <el-form-item label="退款金额：" v-if="detail.cooperation_type == 11">{{detail.refund_money}}</el-form-item>
                <el-form-item label="保证金抵扣轮单费：" v-if="detail.cooperation_type == 10">
                  <span :class="detail.deposit_to_round_money > detail.time_deposit_money ? 'red' : 'green'">{{detail.deposit_to_round_money}}</span>
                  ({{detail.time_deposit_money}})
                </el-form-item>
                <el-form-item label="保证金抵扣金额：" v-if="detail.cooperation_type == 13">
                  <span :class="detail.deposit_to_platform_money > detail.time_deposit_money ? 'red' : 'green'">{{detail.deposit_to_platform_money}}</span>
                  ({{detail.time_deposit_money}})
                </el-form-item>
                <el-form-item label="保证金抵扣：" v-if="detail.cooperation_type == 8">{{detail.deposit_money}}</el-form-item>
                <el-form-item label="轮单费抵扣：" v-if="detail.cooperation_type == 8">{{detail.round_order_money}}</el-form-item>
                <el-form-item label="报备人：">{{detail.creator_name}}</el-form-item>
                <el-form-item label="收款方名称："  v-if="detail.cooperation_type != 10 && detail.cooperation_type != 11 && detail.cooperation_type != 13"><span v-for="item in detail.payee_list" :key="item.payee_px">{{item.payee_type_name}} 、</span></el-form-item>
                <el-form-item :label="detail.cooperation_type == 11?'退款时间：':'汇款时间：'">{{detail.payment_date_show}}</el-form-item>
                <el-form-item label="汇款方名称：" v-if="detail.cooperation_type != 10 && detail.cooperation_type != 11 && detail.cooperation_type != 13">{{detail.payment_uname}}</el-form-item>
                <el-form-item :label="detail.cooperation_type == 11?'退款凭证：':'汇款凭证：'"  v-if="img_list.length > 0" class="flex">
                    <div class="hk-img" v-for='item in img_list' :key="item">
                        <img :src="item" alt="" @click="preview(item)">
                    </div>
                </el-form-item>
                <el-form-item label="销售备注：" v-if="detail.remark != ''" class="no-break-all">{{detail.remark}}</el-form-item>
            </el-form>
            <!-- <div class="text-blue">确认要提交吗？（该操作将直接提交到{{detail.cooperation_type == 11?'李亚北':'蒋总'}}处审核）</div> -->
            <span slot="footer" class="dialog-footer">
                <el-button type="default" @click="submitDialogVisible = false">取消</el-button>
                <el-button type="primary" @click="handleSubmitBtn" style="margin-right: 30px;">确定</el-button>
            </span>
        </el-dialog>
        <el-dialog title="操作确认" :visible.sync="bbDialogVisible" width="30%" center>
            <span>确认要报备会员吗？（需要上会员的时候点击此处）</span>
            <span slot="footer" class="dialog-footer">
                <el-button type="primary" @click="toReport" style="margin-right: 30px;" v-if="type != 12">创建大报备</el-button>
                <el-button type="primary" @click="relaReport">关联大报备</el-button>
            </span>
        </el-dialog>
        <el-dialog title="关联大报备" :visible.sync="reportDialogVisible" :close-on-click-modal="modal"  width="70%" center>
            <div class="lsearch">
                <div class="clearfix">
                    <div class="yixiang fl mr20">
                        公司名称：<br>
                        <el-input v-model="company_name" placeholder="请输入"></el-input>
                    </div>
                    <div class="fl mr20">
                        负责人：<br>
                        <el-input v-model="saler_name" placeholder="请输入"></el-input>
                    </div>
                    <div class="fl mr20">
                        城市：<br>
                        <el-autocomplete
                        v-model="citySelectStr1"
                        class="inline-input mt4"
                        :fetch-suggestions="queryCity"
                        placeholder="请输入"
                        @select="handleSelect1"
                        />
                    </div>
                    <div class="fl">
                        <br>
                        <el-button type="primary" icon="el-icon-search" @click="handleSearch1">查询</el-button>
                    </div>
                </div>
            </div>
            <div class="s-table">
                <el-table
                    v-loading="loading"
                    :data="stableData"
                    height="400"
                    border
                >
                    <el-table-column
                        align="center"
                        prop="company_name"
                        label="公司名称"
                    >
                    </el-table-column>
                    <el-table-column
                        align="center"
                        prop="city_name"
                        label="城市"
                    >
                    </el-table-column>
                    <el-table-column
                        align="center"
                        prop="saler_name"
                        label="负责人"
                    >
                    </el-table-column>
                    <el-table-column
                        align="center"
                        prop="cooperation_type_name"
                        label="合作类型"
                    >
                    </el-table-column>
                    <el-table-column
                        align="center"
                        prop="start_end"
                        label="本次会员时间"
                    >
                    </el-table-column>
                    <el-table-column
                        align="center"
                        prop="report_payment_number"
                        label="小报备"
                    >
                    </el-table-column>
                    <el-table-column
                        align="center"
                        label="操作"
                        width="200"
                    >
                        <template slot-scope="scope">
                            <span class="check-btn" @click="handleCompare(scope.row)">关联</span>
                        </template>
                    </el-table-column>
                </el-table>
                <el-pagination
                    :current-page="scurrentPage"
                    :page-size="20"
                    layout="total, prev, pager, next, jumper"
                    :total="stotals"
                    @size-change="handleSizeChange1"
                    @current-change="handleCurrentChange1"
                />
            </div>
        </el-dialog>
        <!--图片预览-->
        <el-dialog
            :visible.sync="dialogVisiblePreview"
            :close-on-click-modal="false"
            width="60%"
            title="预览"
            center
        >
            <div class="img" v-for="item in previewTemp" :key="item">
                <img :src="item" alt="" style="max-width: 100%; max-height: 100%; display: block; margin: 0 auto;">
            </div>
        </el-dialog>
        <!--查看小报备弹窗-->
        <el-dialog
        title="预警信息"
        :visible.sync="centerDialogVisible"
        width="20%"
        top="30vh"
        center>
        <div class="text-center colff0000">该小报备已有申请记录！</div>
        <span slot="footer" class="dialog-footer">
            <el-button @click="toReportList()">查看发票</el-button>
            <el-button type="primary" @click="toReportAdd()">继续报备</el-button>
        </span>
        </el-dialog>
    </div>
</template>

<script>
import {
    fetchSmallReportList,
    fetchSmallReportSubmit,
    fetchSmallReportRecall,
    fetchSmallReportDelete,
    fetchSmallReportDetail,
    fetchBigReportList,
    fetchSmallReportRelated,
    fetchReportCompare,
    fetchSmallReportEdit } from '@/api/smallReport'
import { fetchCityList } from '@/api/common'
export default {
    name: "smallList",
    data() {
        return {
            id: '',
            company_id: '',
            companyName: '',
            remitter: '',
            citySelectStr: '',
            citySelectVal: '',
            citySelectVal1: '',
            citys: [],
            cityBlurFlag: null,
            comBlurFlag: null,
            cooperation: '0',
            cptOptions: [],
            examineVal: '0',
            examineOptions: [
                {
                    value: '0',
                    label: '请选择'
                }, {
                    value: '1',
                    label: '待提交'
                }, {
                    value: '2',
                    label: '待审核'
                }, {
                    value: '3',
                    label: '审核通过'
                }, {
                    value: '4',
                    label: '审核不通过'
                }, {
                    value: '5',
                    label: '审核通过/待客服上传'
                }, {
                    value: '6',
                    label: '客服审核不通过'
                }, {
                    value: '30',
                    label: '审核通过/客服完成上传'
                }
            ],
            isMemberVal: '0',
            isMemberOptions: [
                {
                    value: '0',
                    label: '请选择'
                },{
                    value: '1',
                    label: '否'
                },{
                    value: '2',
                    label: '是'
                }
            ],
            tableData: [],
            currentPage: 1,
            totals: 0,
            pageSize: 20,
            loading: false,
            detail: {},
            img_list: [],
            previewTemp: [],
            dialogVisiblePreview: false,
            modal: false,
            submitDialogVisible: false,
            bbDialogVisible: false,
            type: '',
            viptype: '',
            payment_money: '',
            payment_date: '',
            back_ratio: '',
            reportDialogVisible: false,
            citySelectStr1: '',
            trueName: '',
            company_name: '',
            deposit_to_round_money: '',
            saler_name: '',
            stableData: [{
                sName: '苏州蓝狐装饰'
            }],
            scurrentPage: 1,
            stotals: 0,
            centerDialogVisible: false
        }
    },
    filters: {
        isNewFilter(val) {
            switch (val) {
                case 0:
                    return '----'
                    break
                case 1:
                    return '否'
                    break
                case 2:
                    return '是'
                    break
                default:
                    return '----'
            }
        }
    },
    watch: {
        citySelectStr(value) {
            if (!value) {
                this.citySelectVal = ''
                this.cityBlurFlag = null
            }
        },
        citySelectStr1(value) {
            if (!value) {
                this.citySelectVal1 = ''
                this.trueName = ''
                this.cityBlurFlag = null
            }
        },
    },
    mounted() {
      this.loadAllCity()
    },
    created() {
        this.getType()
        this.getSmallReportList()
    },
    methods: {
      toReportList(){
            this.$router.push({
                path:'/invoiceReport/reportList',
                query:{
                    payment_id:this.payment_id
                }
            })
      },
      toReportAdd(){
          this.$router.push({
              path:'/invoiceReport/reportAddorEdit'
          })
      },
      invoiceReporting(item){
        if(item.has_invoice==1){
            this.centerDialogVisible=true
            this.payment_id=item.id
        }else{
            this.$router.push({
                path:'/invoiceReport/reportAddorEdit',
                query:{
                    reportItem:item
                }
            })
        }
      },
        getType() {
        fetchSmallReportEdit().then(res => {
          const list = res.data.data.cooperation_type_list
          const arr = list.map((it) => {
            return {
              value: it.id + '',
              label: it.name
            }
          })
          const typeList = [{ value: '0', label: '请选择' }, ...arr]
          this.cptOptions = typeList
        })
      },
        loadAllCity() {
            fetchCityList().then(res => {
                if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                    const data = res.data.data[0]
                    data.forEach((item, index) => {
                        this.citys.push(item)
                    })
                } else {
                    this.citys = []
                }
            })
        },
        handleSelect(item) {
            this.cityBlurFlag = item
            this.citySelectVal = item.cid
            this.citySelectStr = item.value
        },
        handleSelect1(item) {
            this.cityBlurFlag = item
            this.citySelectVal1 = item.cid
            this.citySelectStr1 = item.value
            this.trueName = item.true_cname
        },
        handleBlur() {
            if (!this.cityBlurFlag) {
                this.citySelectStr = ''
                this.citySelectVal = ''
            }
        },
        queryCity(queryString, cb) {
            const citys = this.citys
            const results = queryString ? citys.filter(this.createFilter(queryString)) : citys
            this.cityBlurFlag = null
            cb(results)
        },
        createFilter(queryString) {
            return (city) => {
                return (city.value.toLowerCase().indexOf(queryString.toLowerCase()) > -1)
            }
        },
        handleSearch() {
            this.currentPage = 1
            this.getSmallReportList()
        },
        handleSearch1() {
            this.scurrentPage = 1
            this.getBigReportList()
        },
        handleAdd() {
            this.$router.push({
                path: "/smallReport/add"
            })
        },
        handleEdit(obj) {
            if(!obj || !obj.id) {
                return
            }
            const queryObj = {
                id: obj.id,
                type: obj.cooperation_type
            }
            this.$router.push({
                path: "/smallReport/add",
                query: queryObj
            })
        },
        handleBaobei(obj) {
            this.bbDialogVisible = true
            this.id = obj.id
            this.type = obj.cooperation_type
            this.viptype = obj.viptype
            this.payment_money = obj.payment_total_money
            this.payment_date = obj.payment_date
            this.back_ratio = obj.back_ratio
            this.citySelectVal1 = obj.city_id
            this.citySelectStr1 = obj.city_name
            this.trueName = obj.city_name
            this.company_name = obj.company_name
            this.deposit_to_round_money = obj.deposit_to_round_money
        },
        handleBaobeiAgain(obj) {
            this.citySelectVal1 = obj.cid
            this.citySelectStr1 = obj.city_name
            this.trueName = obj.city_name
            this.type = obj.cooperation_type
            this.company_name = obj.company_name
            this.id = obj.id
            this.$confirm('该收款已关联报备会员，确认要重新关联吗？', '操作确认', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.reportDialogVisible = true
                this.getBigReportList()
            }).catch(() => {
                this.$message({
                    type: 'info',
                    message: '已取消'
                })
            })
        },
        handleSub(obj) {
            this.submitDialogVisible = true
            this.id = obj.id
            this.getSmallReportDetail()
        },
        handleSubmitBtn() {
            fetchSmallReportSubmit({id:this.id}).then(res => {
                if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                    this.$message({
                        type: 'success',
                        message: '提交成功'
                    })
                    this.submitDialogVisible = false
                    this.getSmallReportList()
                } else {
                    this.$message({
                        type: 'warning',
                        message: res.data.error_msg
                    })
                }
            })
        },
        handleWithdraw(obj) {
            this.id = obj.id
            this.$confirm('确认撤回吗?（该操作不可挽回）', '操作确认', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.handleRecallAjax(obj)
            }).catch(() => {
                this.$message({
                    type: 'info',
                    message: '已取消'
                })
            })
        },
        handleDel(obj) {
            this.id = obj.id
            this.$confirm('确认删除该公司信息?（该操作不可挽回）', '操作确认', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.handleDelAjax(obj)
            }).catch(() => {
                this.$message({
                    type: 'info',
                    message: '已取消删除'
                })
            })
        },
        handleDelAjax(obj) {
            fetchSmallReportDelete({
                id: obj.id
            }).then(res => {
                if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                    this.$message({
                        type: 'success',
                        message: '删除成功'
                    })
                    this.getSmallReportList()
                }else{
                    this.$message.error(res.data.error_code)
                }
            }).catch(res => {
                this.$message.error('网络异常，请稍后再试')
            })
        },
        handleRecallAjax(obj) {
            fetchSmallReportRecall({
                id: obj.id
            }).then(res => {
                if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                    this.$message({
                        type: 'success',
                        message: '撤回成功'
                    })
                    this.getSmallReportList()
                }else{
                    this.$message.error(res.data.error_code)
                }
            }).catch(res => {
                this.$message.error('网络异常，请稍后再试')
            })
        },
        handleCompare(obj) {
            fetchReportCompare({
                id: this.id,
                report_id: obj.id,
                report_cooperation_type: obj.cooperation_type
            }).then(res => {
                if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                    if (res.data.data.all_compare == 1) {
                        this.$confirm('确认要关联该报备吗？', '操作确认', {
                            confirmButtonText: '确定',
                            cancelButtonText: '取消',
                            type: 'warning'
                        }).then(() => {
                            this.handelRelated(obj)
                        }).catch(() => {
                            this.$message({
                                type: 'info',
                                message: '已取消'
                            })
                        })
                    } else if (res.data.data.all_compare == 2) {
                        this.$confirm(res.data.data.msg_info, '操作确认', {
                            confirmButtonText: '确定',
                            cancelButtonText: '取消',
                            type: 'warning'
                        }).then(() => {
                            this.handelRelated(obj)
                        }).catch(() => {
                            this.$message({
                                type: 'info',
                                message: '已取消'
                            })
                        })
                    }
                }
            })
        },
        handelRelated(obj) {
            fetchSmallReportRelated({
                id: this.id,
                report_id: obj.id,
                report_cooperation_type: obj.cooperation_type
            }).then(res => {
               if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                    this.$message({
                        type: 'success',
                        message: '关联成功'
                    })
                    this.reportDialogVisible = false
                    this.getSmallReportList()
                }else{
                    this.$message.error(res.data.error_msg)
                }
            }).catch(res => {
                this.$message.error('网络异常，请稍后再试')
            })
        },
        toReport() {
            let type = ''
            if (this.type === 10) {
                type = 6
            }else{
                type = this.type
            }
            const queryObj = {
                type: type,
                viptype: this.viptype,
                payment_money: this.payment_money,
                payment_date: this.payment_date,
                back_ratio: this.back_ratio,
                report_payment_id: this.id,
                company_name: this.company_name,
                deposit_to_round_money: this.deposit_to_round_money,
                citySelectStr: this.citySelectStr1,
                citySelectVal: this.citySelectVal1
            }
            localStorage.setItem('smallReport', JSON.stringify(queryObj))
            this.$router.push({
                path: "/memberReport/add"
            })
        },
        relaReport() {
            this.bbDialogVisible = false
            this.reportDialogVisible = true
            this.getBigReportList()
        },
        handleCheck(obj) {
            const {href} = this.$router.resolve({
                name: 'reportDetail',
                path: "/smallReport/detail",
                query: {
                    id: obj.id,
                    type: obj.cooperation_type
                }
            })
            window.open(href, '_blank')
        },
        handleArguments() {
            const queryObj = {
                company_name: this.companyName, // 公司名称
                payment_uname: this.remitter, // 汇款方
                city_id: this.citySelectVal, // 城市
                cooperation_type: this.cooperation, // 合作类型
                exam_status: this.examineVal,   // 审核状态
                is_related: this.isMemberVal,  // 是否报备会员
                page: this.currentPage, // 分页 页码  默认1
            }
            return queryObj
        },
        getSmallReportList() {
            const queryObj = this.handleArguments()
            this.loading = true
            let tableIndex = 1
            fetchSmallReportList(queryObj).then(res => {
                if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                    if (res.data.data.list.length <= 0 && this.currentPage > 1) {
                        this.currentPage--
                        this.fetchSmallReportList()
                    } else {
                        this.tableData = []
                        this.totals = res.data.data.page.total_number
                        this.pageSize = res.data.data.page.page_size
                        res.data.data.list.forEach((item, index) => {
                            item.tableIndex = (this.currentPage - 1) * 20 + tableIndex
                            if(item.cooperation_type == 6 || item.cooperation_type == 8 || item.cooperation_type == 10) {
                                item.viptype = item.back_ratio
                            }else if(item.cooperation_type == 4 || item.cooperation_type == 9){
                                item.viptype = ''
                            }else if(item.cooperation_type == 14 || item.cooperation_type == 15){
                                item.viptype = '标杆会员（保产值）'
                            }
                            this.tableData.push(item)
                            tableIndex++
                        })
                        this.loading = false
                    }
                } else {
                    // this.tableData = []
                    this.totals = 0
                    this.loading = false
                }
            })
        },
        getSmallReportDetail() {
            fetchSmallReportDetail({id: this.id}).then(res => {
                if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                    this.detail = res.data.data.detail
                    this.detail.time_deposit_money = res.data.data.company_account.deposit_money
                    this.img_list = []
                    res.data.data.detail.auth_imgs.forEach((item, index) => {
                        this.img_list[index] =  item.img_full
                    })
                    this.detail = res.data.data.detail
                }
            })
        },
        getBigReportList() {
            fetchBigReportList({
                company_name: this.company_name,
                city_id: this.citySelectVal1,
                city_name: this.trueName,
                saler_name: this.saler_name,
                page: this.scurrentPage,
                cooperation_type: this.type
            }).then(res => {
                if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                    if (res.data.data.list.length <= 0 && this.currentPage > 1) {
                        this.currentPage--
                        this.fetchSmallReportList()
                    } else {
                        this.stableData = []
                        this.stotals = res.data.data.page.total_number
                        this.pageSize = res.data.data.page.page_size
                        res.data.data.list.forEach((item, index) => {
                            item.start_end = item.current_contract_start_date + '~' + item.current_contract_end_date
                            this.stableData.push(item)
                        })
                        this.loading = false
                    }
                } else {
                    this.stotals = 0
                    this.loading = false
                }
            })
        },
        handleSizeChange() {
            this.currentPage = 1
            this.getSmallReportList()
        },
        handleCurrentChange(val) {
            this.currentPage = val
            this.getSmallReportList()
        },
        handleSizeChange1() {
            this.scurrentPage = 1
            this.getBigReportList()
        },
        handleCurrentChange1(val) {
            this.scurrentPage = val
            this.getBigReportList()
        },
        preview(item) {
            this.previewTemp = []
            this.previewTemp.push(item)
            this.dialogVisiblePreview = true
        },
        classFilter(val) {
            switch (val) {
                case 1:
                    return 'c3'
                    break
                case 2:
                    return 'c2'
                    break
                case 3:
                    return 'c5'
                    break
                case 4:
                    return 'c1'
                    break
                case 5:
                    return 'c5'
                    break
                case 6:
                    return 'c1'
                    break
                case 7:
                    return 'c1'
                    break
                case 8:
                    return 'c5'
                    break
                case 9:
                    return 'c1'
                    break
                case 10:
                    return 'c5'
                    break
                case 11:
                    return 'c6'
                    break
                default:
                    return ''
            }
        }
    }
}
</script>
<style rel="stylesheet/scss" lang="scss">
    .small-report {
        width: 100%;
        padding: 20px;
        box-sizing: border-box;
        .el-date-editor{
            .el-range-separator{
                padding: 0;
            }
        }
        .search {
            background: #fff;
            padding: 20px;
            border-top: 3px solid #999;
            box-sizing: border-box;
        }
        .qian-main {
            margin-top: 20px;
            padding: 20px;
            border-top: 3px solid #999;
            box-sizing: border-box;
            background-color: #fff;
        }
        .el-pagination{
            text-align: center;
            margin-top: 20px;
        }
        // .el-dialog__header{
        //     border-bottom: 1px dashed #CCC;
        // }
        .dia_table{
            width: 100%;
        }
        .dia_table td{
            line-height: 2.5;
        }
        .fix_letter_spance{
            letter-spacing: 3px;
        }
        .fl {
            float: left;
        }
        .mr20 {
            margin-right: 20px;
        }
        .search{
            line-height: 36px;
        }
        .el-select{
            margin-top: 0px;
        }
        .c1{
            color: #FF0000;
        }
        .c2{
            color: #FF33CC;
        }
        .c3{
            color: #FF6600;
        }
        .c4{
            color: #008000;
        }
        .c5{
            color: #339999;
        }
        .c6{
            color: #409eff;
        }
        .edit-btn{
            color: #e6a23c;
            cursor: pointer;
        }
        .supplement-btn{
            color: #008000;
            cursor: pointer;
        }
        .del-btn{
            color: #f56c6c;
            cursor: pointer;
        }
        .report-btn{
            color:#6600CC;
            cursor: pointer;
        }
        .check-btn{
            color: #409eff;
            cursor: pointer;
        }
        .widthdraw-btn{
            color: #67c23a;
            cursor: pointer;
        }
        .el-form-item {
            margin-bottom: 0;
        }
        .flex {
            display: flex;
            .el-form-item__label {
                // width: 160px;
                text-align: left;
                flex: 1;
            }
            .el-form-item__content {
                flex: 6;
            }
        }
        .no-break-all{
            word-wrap: break-word;
            word-break: break-all;
        }
        .hk-img {
            width: 100px;
            height: 100px;
            background: #eee;
            margin: 10px;
            float: left;
            img {
                display: block;
                width: 100%;
                height: 100%;
            }
        }
        .text-blue {
            color: #409eff;
        }
        .s-table {
            margin-top: 20px;
        }
    }
</style>
<style scoped>
.text-center{
    text-align:center;

}
.colff0000{
    color: #ff0000;
}
</style>
