<template>
    <div class="dialog">
        <el-dialog 
            title="订单编号" 
            :visible.sync="dialog.show"
            :close-on-click-modal='false'
            :close-on-press-escape='false'
            width="80%"
           >
           <div slot="title" class='tit'>
               订单编号：{{details.order_id}}
               (上次修改时间 {{moment((orderinfos.lasttime)*1000).format('YYYY-MM-DD HH:mm:ss')}}) |
               实际发单时间 {{orderinfos.date_real}} |
               订单发布完整度 {{orderinfos.wzd}}%
           </div>
          
            <div class="content">
                <div  class="content-left">
                    <p><b>发布时间：</b> <span>{{orderinfos.date_real}}</span></p>
                    <p><b>IP城市：</b> <span>{{orderinfos.ip}}</span></p>
                    <p><b>手机号码：</b> <span>{{orderinfos.tel}}</span></p>
                    <p><b>微信号：</b> <span>{{orderinfos.wx ? orderinfos.wx : '----'}}</span></p>
                    <p><b>备用联系方式：</b> <span>{{orderinfos.other_contact ? orderinfos.other_contact : '----'}}</span></p>
                    <p><b>备用联系人：</b> <span>{{orderinfos.standby_user ? orderinfos.standby_user : '----'}}</span></p>
                    <p><b>房屋地址：</b> <span>{{orderinfos.dz ? orderinfos.dz : '----'}}</span></p>
                    <p><b>联系人：</b> <span>{{orderinfos.name}}</span></p>
                    <p><b>所属区域：</b> <span>{{orderinfos.city_name}}{{orderinfos.area_name}}</span></p>
                    <p><b>小区类型：</b> <span>{{orderinfos.xiaoqu_type_name ? orderinfos.xiaoqu_type_name : '----'}}</span></p>
                    <p><b>小区：</b> <span>{{orderinfos.xiaoqu}}</span></p>
                    <p><b>坐标：</b> <span>{{orderinfos.latlng}}</span></p>
                    <p><b>装修类型：</b> <span>{{orderinfos.lxs_full_name}}</span></p>
                    <p><b>户型结构：</b> <span>{{orderinfos.huxing_full_name}}</span></p>
                    <p><b>风格：</b> <span>{{orderinfos.fengge_name}}</span></p>
                    <p><b>预算：</b> <span>{{orderinfos.yusuan_name}}</span></p>
                    <p><b>拿房时间：</b> <span>{{orderinfos.nf_time}}</span></p>
                    <p><b>钥匙：</b> <span>{{orderinfos.keys_name}}</span></p>
                    <p><b>量房/到店：</b> <span>{{orderinfos.lftime}}</span></p>
                    <p><b>开工时间：</b> <span>{{orderinfos.start}}</span></p>
                    <p><b>特殊需求：</b> <span>{{orderinfos.text}}</span></p>
                    <p><b>待定时间：</b><span>{{orderinfos.visitime ? orderinfos.visitime : '----'}}</span></p>
                    <p><b>编辑人：</b><span>{{orderinfos.customer_name}}</span></p>
                    <p><b>分配上限：</b><span>{{orderinfos.allot_num}}</span></p>
                    <p><b>当前状态：</b><span>{{orderinfos.type_fw_name}}</span></p>
                    <p><b>审核人：</b><span>{{orderinfos.chk_name}}</span></p>
                    <p>
                        <b>量房公司：</b>
                        <span v-for='item in orderinfos.lf_companys' :key='item.company_id'>{{item.company_qc}}</span>
                    </p>
                </div>
                <div  class="content-left center">
                    <p><b>要求回访的装修公司：</b></p>
                    <p class="pl12">
                        <span v-for='item in details.visit_companys' :key='item.company_id'>{{item.company_jc}}</span>
                     </p>
                    <p><b>回访阶段：</b></p>
                    <p class="pl12"><span>{{details.visit_step_name}}</span></p>
                    <p><b>需要回访的内容：</b></p>
                    <p><textarea  rows="10" disabled v-model="details.visit_need"></textarea></p>
                    <p><b>会员反馈详情：</b></p>
                    <p><textarea  rows="10" disabled v-model="details.visit_user_need"></textarea></p>
                </div>
                <div  class="content-left  right">
                    <p><b style="width:70px;">回访时间：</b><span>{{details.visit_date ? details.visit_date : '----'}}</span></p>
                    <p><b style="width:70px;">回访人：</b><span>{{details.visit_username ? details.visit_username : '----'}}</span></p>
                    <p><b style="width:70px;">回访状态：</b><span>{{details.valid_status_name}}</span></p>
                    <p><b style="width:172px;">客服回访反馈的订单状态：</b><span>{{details.visit_status_name}} {{ details.remark_type_name && details.remark_type_name.length>0 ? ' - '+ details.remark_type_name : ''}} </span></p>
                    <p><b>回访反馈：</b></p>
                    <p><textarea  rows="10" v-model="details.visit_content"></textarea></p>
                    <p><b>推送给装修公司：</b></p>
                    <div class="pl12">
                        <el-checkbox-group v-model="companys">
                            <el-checkbox
                                v-for='(item,index) in orderinfos.companys'
                                :key="index"
                                :label="item.company_id"
                                name="gongsi"
                                @change="chooseCompany(item.company_id)"
                                >{{item.company_jc}}</el-checkbox>
                        </el-checkbox-group>
                    </div>
                    <p><el-button  type="primary" class="btn" @click="pushHandle">推送</el-button></p>
                </div>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import { fetchVisitPush, fetchVisitNewPush} from '@/api/orderList'
    import moment from 'moment'
    export default {
        props: {
            dialog: Object,
            details: Object,
            orderinfos: Object
        },
        data() {
            return {
                companys: [],
                order_type: ''
            }
        },
        watch: {
            'dialog.show'(val) {
                if (val) {
                    this.order_type = this.details.order_type
                    this.orderinfos.companys.forEach((item, index) => {
                        if (parseInt(item.push_checked) === 1) {
                            this.companys.push(item.company_id)
                        }
                    })
                } else {
                    this.companys = []
                }
            }
        },
        methods: {
            moment,
            chooseCompany(val) {
              console.log(this.companys)
            },
            // 推送
            pushHandle() {
                const params = this.handleArgs()
                var companyName = document.getElementsByName("gongsi")
                var flag = false  // 标记判断是否选中一个
                for(var i = 0; i < companyName.length; i++) {
                    if(companyName[i].checked) {
                        flag = true
                        this.$confirm('您确定要推送吗？', '提示', {
                            confirmButtonText: '确定',
                            cancelButtonText: '取消',
                            type: 'warning'
                        }).then(() => {
                            if (this.order_type === 1) {
                                fetchVisitNewPush(params).then(res => {
                                    if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                                        this.$message({
                                            message: '推送成功',
                                            type: 'success'
                                        })
                                        this.dialog.show = false
                                    } else {
                                        this.$message.error(res.data.error_msg)
                                    }
                                }).catch(res => {
                                    this.$message.error('网络异常，请稍后再试')
                                })
                            } else {
                                fetchVisitPush(params).then(res => {
                                    if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                                        this.$message({
                                            message: '推送成功',
                                            type: 'success'
                                        })
                                        this.dialog.show = false
                                    } else {
                                        this.$message.error(res.data.error_msg)
                                    }
                                }).catch(res => {
                                    this.$message.error('网络异常，请稍后再试')
                                })
                            }
                        }).catch(() => {
                            this.$message({
                                type: 'info',
                                 message: '已取消推送'
                            })
                        })
                    }
                }
                if (!flag) {
                    alert('请最少选择一家装修公司！')
                    return false
                }
            },
            // 推送参数
            handleArgs() {
                let queryObj = ''
                if (this.order_type === 1) {
                    queryObj = {
                        order_id: this.details.order_id, // 订单ID
                        company_ids: '', // 要推送的装修公司ID（多个用，隔开）
                        visit_content: this.details.visit_content // 反馈内容
                    }
                } else {
                    queryObj = {
                        id: this.details.id, // 回访单ID
                        company_ids: '', // 要推送的装修公司ID（多个用，隔开）
                        visit_content: this.details.visit_content // 反馈内容
                    }
                }
                queryObj.company_ids = this.companys.join(',')
                // if (this.orderinfos.companys.map(el => el.company_id) instanceof Array) {
                //     queryObj.company_ids = this.orderinfos.companys.map(el => el.company_id).join(',')
                // }
                return queryObj
            }
        }
    }
</script>

<style scoped >
    el-dialog{
        border:1px solid #ccc;
    }
    .el-checkbox{
        margin-left: 0;
        margin-right: 30px;
    }
    .content{
        width:100%;       
        height:auto;
        margin-top: -10px;
        display: flex;
    }
    .tit{
        border-bottom:1px dashed #ccc;
        padding-bottom:17px;
    }
    .content .content-left{
        flex: 1
    }
    .content .content-left p{
        display: flex
    }
    .content .content-left b{
        width:140px;
        text-align:right;
        display: inline-block;
    }
    .content .content-left span{
        width:45%;
    }
    .content .center{
        border-left:1px dashed #ccc;
        border-right:1px dashed #ccc;
        padding:0 10px;
    }
    .content .center b{
        text-align:left;
    }
    .content .center p textarea{
        width: 90%;
        margin: 0 auto;
        padding:10px;
    }
    .content .right{
        padding:0 10px;
    }
    .content .right b{
         text-align:left;
    }
    .content .right p textarea{
        width: 90%;
        margin: 0 auto;
        padding:10px;
    }

    .pl12{
        padding-left:12px;
    }
    .content .btn{
        margin:20px auto;
    }
    .el-dialog__body{
        border-top:1px dashed #ccc;
    }
    .w70{
        width:70px;
    }
    .pushCheck{
        margin-right:20px;
    }

</style>
