// 全部订单

<template>
<div class="order-all">
    <div v-if="ischeckorderpass==3">
        <div style="background-color: white;">
            <qz-card title="全部订单" divider>
                <!--
                <template v-slot:more>
                    <el-button type="danger" plain @click="gotoChangePassword">修改订单密码</el-button>
                </template>
                 -->
                <div class="search" style="margin-bottom:16px;">
                    <div class="search-left">
                        <qz-search-item for="is_read" label="读取状态:" style="margin-right: 16px;">
                            <el-select v-model="searchValue.is_read" placeholder="请选择读取状态">
                                <el-option key="0" label="全部" value></el-option>
                                <el-option key="1" label="已读" :value="1"></el-option>
                                <el-option key="2" label="未读" :value="2"></el-option>
                            </el-select>
                        </qz-search-item>
                        <qz-search-item for="state" label="订单状态:" style="margin-right: 16px;">
                            <el-select v-model="searchValue.state" placeholder="请选择订单状态">
                                <el-option key="0" label="全部" value></el-option>
                                <el-option key="1" label="已量房" :value="1"></el-option>
                                <el-option key="2" label="已到店/见面" :value="2"></el-option>
                                <el-option key="3" label="未量房" :value="3"></el-option>
                                <el-option key="4" label="已签约" :value="4"></el-option>
                            </el-select>
                        </qz-search-item>
                        <qz-search-item label="发布日期:" style="margin: 16px 16px 0 0;">
                            <el-date-picker type="daterange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" v-model="searchValue.startEndTime"></el-date-picker>
                        </qz-search-item>
                        <br />

                        <div v-if="show" class="message">
                            <qz-search-item label="订单:" style="margin: 16px 16px 0 0;">
                                <el-input v-model="searchValue.order_info" placeholder="订单号/小区/电话号"></el-input>
                            </qz-search-item>
                            <qz-search-item for="is_read" label="订单类型:" style="margin: 16px 16px 0 0;">
                                <el-select v-model="searchValue.type_fw" placeholder="订单类型">
                                    <el-option key="0" label="全部" value></el-option>
                                    <el-option key="1" label="分单" :value="1"></el-option>
                                    <el-option key="2" label="赠单" :value="2"></el-option>
                                </el-select>
                            </qz-search-item>
                            <qz-search-item label="业主姓名:" style="margin: 16px 16px 0 0;">
                                <el-input v-model="searchValue.name" maxlength="15" placeholder="请输入业主姓名"></el-input>
                            </qz-search-item>
                            <qz-search-item label="跟进人:" style="margin: 16px 16px 0 0;">
                                <el-input v-model="searchValue.employee_name" maxlength="15" placeholder="请输入跟进人"></el-input>
                            </qz-search-item>
                        </div>
                    </div>
                    <div class="search-right">
                        <div class="unwind" @click="show = !show">
                            <div v-if="!show">
                                展开
                                <i class="el-icon-arrow-down"></i>
                            </div>
                            <div v-if="show">
                                收起
                                <i class="el-icon-arrow-up"></i>
                            </div>
                        </div>
                        <el-button type="danger" @click="getTableData">查询</el-button>
                        <el-button type="danger" plain @click="resetForm">重置</el-button>
                    </div>
                </div>
                <el-table :data="tableData" border stripe style="width: 100%" v-loading="tableLoading">
                    <template v-slot:empty>
                        <div style="margin:32px">
                            <qz-empty></qz-empty>
                        </div>
                    </template>
                    <el-table-column prop="isread" label="读取状态">
                        <template slot-scope="scope">
                            <qz-table-cell :option="{0:'未读',1:'已读'}" :select="scope.row.isread"></qz-table-cell>
                        </template>
                    </el-table-column>
                    <el-table-column prop label="订单编号" width="180px">
                        <template slot-scope="scope">
                            {{scope.row.order}}
                            <i @click="handleCopy(scope.row.order)" class="copy el-icon-document-copy"></i>
                        </template>
                    </el-table-column>

                    <el-table-column prop="ordername" label="业主"></el-table-column>
                    <el-table-column prop="qx" label="所在区域"></el-table-column>
                    <el-table-column prop="xiaoqu" label="小区名称" width="160"></el-table-column>
                    <el-table-column prop="mianji" label="建筑面积(㎡)" width="120px"></el-table-column>
                    <el-table-column prop="jiage" label="装修预算"></el-table-column>
                    <el-table-column prop="type_fw_name" label="订单类型"></el-table-column>
                    <el-table-column prop="status" label="订单状态" width="150px">
                        <template slot-scope="scope">
                            <!-- FIXME: 判断是否是当前公司 -->
                            <!-- ISSUE: 未拆分模块导致页面混乱 -->
                            <span v-if="companyId == scope.row.qiandan_companyid">已签约</span>

                            <el-select v-model="scope.row.status" placeholder="请选择" @change="(value)=>{handleSelectStatusChange(value,scope.row)}" :disabled="(scope.row.qiandan_status===1)||(scope.row.status_lock===1)" v-else>
                                <el-option v-for="item in filterStatus(scope)" :key="item.value" :label="item.label" :value="item.value"></el-option>
                            </el-select>
                        </template>
                    </el-table-column>
                    <el-table-column prop="qiandan_status_name" label="签单审核"></el-table-column>
                    <el-table-column prop="customer" label="客服" width="120px"></el-table-column>
                    <el-table-column prop="designer" label="设计师" width="120px"></el-table-column>
                    <el-table-column prop="ordertime" label="发布日期" :formatter="formatDate" width="100"></el-table-column>
                    <el-table-column fixed="right" label="操作" width="140">
                        <template slot-scope="scope">
                            <el-button @click="gotoOrderDetail(scope,'basics')" type="text">详情</el-button>
                            <el-button @click="gotoOrderDetail(scope,'subtotal')" type="text">跟单</el-button>
                            <!--
                <el-button
                  @click="handleButtonOrderTrackClick(scope)"
                  type="text"
                  v-permission="{action:'gendan'}"
                >跟单</el-button>
                -->
                            <el-button type="text" @click="handleAssign(scope.row.order)" v-permission="{action:'zhipai'}">指派</el-button>

                        </template>
                    </el-table-column>
                </el-table>
                <div class="pagination">
                    <el-pagination style="text-align:right" @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page="pagination.page" :page-sizes="[10, 20, 50, 100]" :page-size="pagination.size" layout="total, sizes, prev, pager, next, jumper" :total="pagination.total"></el-pagination>
                </div>

                <el-dialog title="跟单小计" :visible.sync="dialog.orderTrack" width="820px" @open="handleDialogTrackOpen" @close="handleDialogTrackClose" destroy-on-close>
                    <div class="order-track">
                        <el-row>
                            <el-col :span="12" class="order-box">
                                <div class="order-list" v-for="(item,index) in trackData" :key="item.id">
                                    <div class="header">
                                        <qz-timestamp-format :timestamp="item.track_time.toString()" class="time"></qz-timestamp-format>
                                        <span>{{item.track_step_name}}</span>
                                        <el-button class="edit" type="text" v-if="index === 0" @click="handleButtonEdit(item)">编辑</el-button>
                                        <el-popconfirm title="您确定删除该跟单记录吗？" @onConfirm="handleButtonDelete(item.id)" v-if="index === 0">
                                            <el-button slot="reference" class="del" type="text">删除</el-button>
                                        </el-popconfirm>
                                    </div>
                                    <div class="con">{{item.track_content}}</div>
                                </div>
                                <div class="order-list" v-for="(item) in visitData" :key="item.id">
                                    <div class="header">
                                        <qz-timestamp-format :timestamp="item.visit_time.toString()" class="time"></qz-timestamp-format>
                                        <span>{{item.visit_step_name}}</span>
                                    </div>
                                    <div class="con">{{item.visit_content_sales}}</div>
                                </div>
                                <div class="empty" v-if="trackData.length<1 && visitData.length<1">
                                    <qz-empty></qz-empty>
                                    <p>暂无跟进数据</p>
                                </div>
                            </el-col>
                            <el-col :span="12" class="order-form">
                                <el-form ref="form" :model="form.orderTrack" label-width="80px">
                                    <el-form-item label="跟进时间" prop="time" :rules="[{ required: true, message: '请选择跟进时间', trigger: 'blur' }]">
                                        <el-date-picker v-model="form.orderTrack.time" type="datetime" placeholder="选择跟进时间" style="width:260px;" :picker-options="pickerTime"></el-date-picker>
                                    </el-form-item>
                                    <el-form-item label="跟进阶段" prop="step" :rules="[{ required: true, message: '请选择跟进阶段', trigger: 'blur' }]">
                                        <el-select v-model="form.orderTrack.step" placeholder="请选择跟进阶段" style="width:260px;">
                                            <el-option :key="0" label="请选择" value></el-option>
                                            <el-option :key="1" label="初次跟进" :value="1"></el-option>
                                            <el-option :key="2" label="量房" :value="2"></el-option>
                                            <el-option :key="3" label="方案" :value="3"></el-option>
                                            <el-option :key="4" label="签单" :value="4"></el-option>
                                        </el-select>
                                    </el-form-item>
                                    <el-form-item label="跟进内容" prop="content" :rules="[{ required: true, message: '请输入跟进内容', trigger: 'blur' }]">
                                        <el-input v-model="form.orderTrack.content" type="textarea" :rows="2" placeholder="请输入内容"></el-input>
                                    </el-form-item>
                                    <el-form-item>
                                        <el-button type="danger" @click="onSubmit">提交</el-button>
                                    </el-form-item>
                                </el-form>
                            </el-col>
                        </el-row>
                    </div>
                </el-dialog>

                <el-dialog title="请选择未量房原因(可选1-3个):" :visible.sync="dialog.notMeasureReason" @close="handleDialogReasonClose" destroy-on-close>
                    <el-form ref="formNotMeasureReason" :model="form.notMeasureReason">
                        <el-form-item prop="reason" :rules="[{ required: true, message: '请选择未量房原因', trigger: 'blur' }]">
                            <el-checkbox-group v-model="form.notMeasureReason.reason" :max="3">
                                <el-checkbox-button v-for="item in option.notMeasureReason" :key="item.value" :label="item.value">{{item.label}}</el-checkbox-button>
                            </el-checkbox-group>
                        </el-form-item>
                        <el-form-item prop="remark">
                            <el-input v-model="form.notMeasureReason.remark" type="textarea" placeholder="请输入详细备注"></el-input>
                        </el-form-item>
                        <el-form-item>
                            <div style="float: right">
                                <el-button type="danger" @click="handleFormNotMeasureReasonVerify">提交</el-button>
                                <el-button type="danger" plain @click="handleFormNotMeasureReasonCancel">取消</el-button>
                            </div>
                        </el-form-item>
                    </el-form>
                </el-dialog>

                <el-dialog title="申请签单" :visible.sync="dialog.orderApply" @close="handleDialogApplyClose" destroy-on-close>
                    <el-form ref="formOrderApply" :model="form.orderApply" label-width="80px">
                        <!-- FIXME:添加数字校验 -->
                        <!--   {type:'number',message: '请输入正确数字',trigger: 'blur'}  -->
                        <el-form-item label="签单金额" prop="qiandan_jine" :rules="rule.qiandan_jine">
                            <el-input v-model="form.orderApply.qiandan_jine" maxlength="3" placeholder="请输入签单金额">
                                <template slot="suffix">万元</template>
                            </el-input>
                            <span style="color:#ff5353;font-size:14px;">注:签单金额单位为“万元”，填时注意</span>
                        </el-form-item>
                        <el-form-item label="签单备注" prop="qiandan_info">
                            <el-input type="textarea" v-model="form.orderApply.qiandan_info" placeholder="请输入签单备注"></el-input>
                        </el-form-item>
                    </el-form>
                    <div slot="footer" class="dialog-footer">
                        <el-button type="danger" @click="handleFormOrderApplyOnSubmit">提交</el-button>
                        <el-button type="danger" plain @click="handleFormOrderApplyOnCancel">取消</el-button>
                    </div>
                </el-dialog>

                <el-dialog title="指派人员" :visible.sync="dialogAssign" width="480px">
                    <el-form :model="assignForm" :rules="assignRules" ref="assignForm" label-width="100px" class="assign-ruleForm">
                        <el-form-item label="订单编号：">
                            <span>{{assignForm.order_id}}</span>
                        </el-form-item>
                        <el-form-item label="客服：">
                            <el-select v-model="assignForm.customer" multiple placeholder="请选择">
                                <el-option v-for="item in assignKf" :key="item.id" :label="item.nick_name" :value="item.id"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="设计师：">
                            <el-select v-model="assignForm.designer" multiple placeholder="请选择">
                                <el-option v-for="item in assignSj" :key="item.id" :label="item.nick_name" :value="item.id"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item>
                            <el-button @click="resetAssign('assignForm')">取 消</el-button>
                            <el-button type="primary" @click="submitAssign('assignForm')">确 定</el-button>
                        </el-form-item>
                    </el-form>
                </el-dialog>
            </qz-card>
        </div>
    </div>
    <!-- FIXME: 可以共用一个头，修改订单密码 -->
    <div class="intercept-div">
        <!--未设置密码弹窗-->
        <notsetpassword v-if="ischeckorderpass==2"></notsetpassword>
        <!--有密码弹窗-->
        <qz-card title="全部订单" divider v-if="ischeckorderpass==1">
            <div class="newintercept flex jus-bet">
                <div>
                    <div class="font18 bold">请输入订单查看密码</div>
                    <div class="flex font14 mt15">
                        <div class="col999">可在系统设置-订单设置-关闭查单密码</div>
                        <div class="colD92B2B ml26 point" @click="opensearchdialog(1)">快捷设置</div>
                    </div>
                    <el-form :model="passwordfrom" :rules="passwordrules">
                        <el-form-item prop="password">
                            <div class="flex mt50">
                                <div class="el-icon-lock front-icon"></div>
                                <el-input class="pl52" placeholder="请输入密码" v-model="passwordfrom.password" :show-password="true" clearable></el-input>
                            </div>
                        </el-form-item>
                    </el-form>
                    <div v-if="passwordfrom.password!=''" class="mt25 font14 determine canPoint point" @click="handleButtonCheckOrderClick()">确定</div>
                    <div v-else class="font14 mt25 determine canNotPoint point">确定</div>
                    <div class="font12 col000 mt25 point" @click="opensearchdialog(2)">修改查单密码</div>
                </div>
                <div class="font12 col999">
                    <div class="col000 font14 bold">注：</div>
                    <div class="mt19">此订单密码极为重要，请不要将密码透露给他人（包括齐装网的工作人员），谢谢！</div>
                    <div class="mt19">连续输错3次密码，请查看订单将冻结10分钟，如有问题请联系客服。</div>
                    <div class="mt19">订单是贵公司宝贵的财产，请不要随意泄漏订单信息。</div>
                </div>
            </div>
        </qz-card>
        <!--
      <div>
        <qz-card title="全部订单" divider>
          <template v-slot:more>
            <el-button type="danger" plain @click="gotoChangePassword">修改订单密码</el-button>
          </template>
          <div>
            查看订单密码:
            <el-input
              type="password"
              placeholder="请输入订单密码"
              style="width:160px;margin-right:10px;"
              v-model="password"
            ></el-input>
            <img
              src="@/assets/order/qrcode.png"
              @click="handleQrCodeClick"
              style="vertical-align: middle;"
              v-show="tagName == 'company'"
            />
            <p style="color:#ff5353;">
              <i class="el-icon-warning"></i> 提醒：此订单密码极为重要，请不要将密码透露给他人（包括齐装网的工作人员），谢谢！
            </p>
            <p style="color:#ff5353;">
              <i class="el-icon-warning"></i> 连续输错3次密码，查看订单将冻结10分钟，如有问题请联系客服。
            </p>
            <p style="color:#ff5353;">
              <i class="el-icon-warning"></i> 订单是贵公司宝贵的财产，请不要随意泄漏订单信息。
            </p>
            <el-button type="danger" @click="handleButtonCheckOrderClick">查看订单</el-button>
          </div>
        </qz-card>
      </div>
      -->
    </div>
    <el-dialog title="扫码查看" :visible.sync="dialog.wechat" @close="handleDialogWechatClose" width="480px" destroy-on-close>
        <img :src="url" alt="wechat" />
        <p>请用微信扫描二维码</p>
    </el-dialog>
    <!--手机验证码弹框-->
    <el-dialog top="30vh" title="输入手机验证码" :close-on-click-modal="false" :visible.sync="phonecodebul" custom-class="phonecodesty">
        <div class="subtitle">
            <div>已发送至18702534567</div>
            <div class="point colD92B2B" v-if="getcodebul" @click="getcode()">获取验证码</div>
            <div v-else><span class="col0091FF">{{countdown}}s</span>后重新获取验证码</div>
        </div>
        <div class="inputs">
            <input class="input-div" type="text" maxlength="1" v-model="code1">
            <input class="input-div" type="text" maxlength="1" v-model="code2">
            <input class="input-div" type="text" maxlength="1" v-model="code3">
            <input class="input-div" type="text" maxlength="1" v-model="code4">
        </div>
        <div v-if="(code1!=''&&code2!=''&&code3!=''&&code4!='')" class="nextstep canPoint point" @click="nextstep()">下一步</div>
        <div v-else class="nextstep canNotPoint point">下一步</div>
    </el-dialog>
    <!--关闭设置查单密码弹框-->
    <el-dialog top="30vh" title="查单密码关闭提示" :close-on-click-modal="false" :visible.sync="setbul" width="420px" custom-class="setsty">
        <div class="setsutitle">您确定需要关闭查单密码嘛？关闭后可能订单的数据会存在泄漏的风险</div>
        <div class="setask">如何开启查单密码功能</div>
        <div class="setimg-div"></div>
        <div class="settips">可在个人中心- 设置- 开启查单密码</div>
        <div class="setbtns">
            <div class="setbtnl point" @click="setbtnl()">我在想想</div>
            <div class="setbtnr point" @click="setbtnr()">确认关闭</div>
        </div>
    </el-dialog>
    <!--重新设置查单密码弹框-->
    <el-dialog top="30vh" title="请设置查单密码" :close-on-click-modal="false" :visible.sync="setpasswordbul" width="420px" custom-class="resetsty">
        <div class="setsutitle">8位以上,18位以下,包括字母和数字</div>
        <div class="newpass">
            <el-form :model="newlookpass" :rules="newlookpassform" ref="newpass">
                <el-form-item prop="password">
                    <el-input v-model="newlookpass.password" maxlength="18" placeholder="请输入旧查单密码" :show-password="true"></el-input>
                </el-form-item>
                <el-form-item prop="repeatpassword">
                    <el-input v-model="newlookpass.repeatpassword" maxlength="18" placeholder="请输入新查单密码" :show-password="true"></el-input>
                </el-form-item>
                <el-form-item prop="newrepeatpassword">
                    <el-input v-model="newlookpass.newrepeatpassword" maxlength="18" placeholder="请确认新查单密码" :show-password="true"></el-input>
                </el-form-item>
            </el-form>
        </div>
        <div v-if="(newlookpass.password!=''&&newlookpass.repeatpassword!=''&&newlookpass.newrepeatpassword!='')" class="nextstep canPoint point" @click="resetpassword('newpass')">确定</div>
        <div v-else class="nextstep canNotPoint point">确定</div>
    </el-dialog>
</div>
</template>

<script>
import QzCard from '@/components/card.vue';
import QzSearchItem from '@/components/search-item.vue';
import QzEmpty from '@/components/empty.vue';
import QzTableCell from '@/components/table-cell.vue';
import ApiOrder from '@/api/order';
import ApiPublic from '@/api/public';
import api from '@/api/redosystem';
import dayjs from 'dayjs';
import QzTimestampFormat from '@/components/timestamp-format.vue';
import notsetpassword from './components/notsetpassword.vue'

export default {
    name: 'OrderAll',
    components: {
        QzCard,
        QzSearchItem,
        QzEmpty,
        QzTableCell,
        QzTimestampFormat,
        notsetpassword
    },
    async created() {
        this.checkorderpassstatus()
        if (this.$route.query.order_info) {
            this.searchValue.order_info = this.$route.query.order_info
        }
        this.searchValue.is_read = this.$route.query.is_read ? parseInt(this.$route.query.is_read, 10) : '';
        this.searchValue.state = this.$route.query.state ? parseInt(this.$route.query.state, 10) : '';
        const res = await ApiPublic.getBasicinfo();
        await this.getemployeedropdowlist();
        this.companyId = res.data.data.user_id;
        this.tagName = JSON.parse(localStorage.getItem('tagName'));
    },
    methods: {
        //  订单管理-获取是否有订单密码
        checkorderpassstatus() {
            const params = {}
            api.checkorderpassstatus(params).then((res) => {
                if (res.data.error_code === 0) {
                    this.ischeckorderpass = res.data.data
                    if (res.data.data !== 2) {
                        this.getTableData();

                    }
                } else {
                    this.$message.error(res.data.error_msg)
                }
            }).catch((err) => {
                this.$message.error(err)
            })
        },
        //  重置密码
        resetpassword(formName) {
            this.$refs[formName].validate((valid) => {
                if (valid) {
                    this.editPwd(this.newlookpass.repeatpassword, this.newlookpass.newrepeatpassword, this.newlookpass.password)
                } else {
                    // return false;
                }
            })
        },
        //  获取验证码按钮
        getcode() {
            this.getcodebul = false
            this.starttiming()
        },
        //  开始计时
        starttiming() {
            this.timer = window.setInterval(() => {
                this.countdown -= 1
                if (this.countdown === 0) {
                    this.getcodebul = true
                    this.countdown = 60
                    clearInterval(this.timer)
                }
            }, 1000)
        },
        //  打开验证码弹窗
        opensearchdialog(flag) {
            // this.flag=flag
            if (flag === 1) {
                this.$router.push({
                    path: '/set-menu'
                })
            } else {
                this.setpasswordbul = true; // 打开设置查单密码弹窗
                // this.getcodebul = false
                // this.phonecodebul = true;
                // if (this.countdown === 60) {
                //     this.starttiming()
                // }
            }
        },
        //  订单管理-修改订单密码
        editPwd(newpass, comfirmpass, oldpass) {
            const params = {
                pass: newpass, // 订单新密码
                comfirm_pass: comfirmpass, //  确认订单密码
                old_pass: oldpass //   旧密码
            }
            ApiOrder.editPwd(params).then((res) => {
                if (res.data.error_code === 0) {
                    this.setpasswordbul = false
                    this.$message.success('订单密码设置成功');
                } else {
                    this.$message.error(res.data.error_msg)
                }
            }).catch((err) => {
                this.$message.error(err)
            })
        },
        //  验证码弹窗下一步
        nextstep() {
            // if(this.flag===1){//  点击快捷设置
            //   this.setbul=true;// 打开关闭查单密码弹窗
            // }else{//  点击忘记查单密码
            this.setpasswordbul = true; // 打开设置查单密码弹窗
            // }
            this.phonecodebul = false; //  关闭验证手机验证码弹窗
            this.resetdata() // 重置数据
        },
        //  重置部分数据
        resetdata() {
            this.countdown = 60
            clearInterval(this.timer)
            this.code1 = ''
            this.code2 = ''
            this.code3 = ''
            this.code4 = ''
        },
        setbtnl() {
            this.setbul = false;
        },
        setbtnr() {
            this.setbul = false;
        },
        async handleQrCodeClick() {
            const res = await ApiOrder.showQrCode();
            if (res.data.error_code === 0) {
                this.url = res.data.data.url;
                this.dialog.wechat = true;
                this.timer = window.setInterval(async () => {
                    const res2 = await ApiOrder.checkOrderWechat();
                    // 成功
                    if (res2.data.error_code === 0) {
                        this.dialog.wechat = false;
                        this.ischeckorderpass = 3
                        this.checkStatus = true;
                        await this.getTableData();
                        clearInterval(this.timer);
                    }
                    // 二维码已过期
                    if (res2.data.error_code === 4000600) {
                        this.$message.info('二维码过期');
                        this.url = '';
                        this.dialog.wechat = false;
                        clearInterval(this.timer);
                    }

                    // 该用户微信未绑定
                    if (res2.data.error_code === 4000007) {
                        this.$message.info(res2.data.error_msg);
                        this.url = '';
                        this.dialog.wechat = false;
                        clearInterval(this.timer);
                    }
                }, 1000);
            } else {
                this.$message.error(res.data.error_msg);
            }
        },
        async handleButtonCheckOrderClick() {
            const res = await ApiOrder.checkPwd({
                pass: this.passwordfrom.password
            });
            if (res.data.error_code === 0) {
                this.checkStatus = true;
                this.ischeckorderpass = 3
                await this.getTableData();
            } else {
                this.$message.error(res.data.error_msg);
            }
        },
        handleButtonOrderTrackClick(scope) {
            this.orderId = scope.row.order;
            this.dialog.orderTrack = true;
        },
        async handleDialogApplyClose() {
            await this.getTableData();
        },
        async handleDialogReasonClose() {
            await this.getTableData();
        },
        async handleFormNotMeasureReasonCancel() {
            this.dialog.notMeasureReason = false;
            await this.getTableData();
        },
        async handleFormNotMeasureReasonVerify() {
            this.$refs.formNotMeasureReason.validate(async (valid) => {
                if (valid) {
                    await ApiOrder.changeState({
                        id: this.orderId,
                        state: 3,
                        reason: this.form.notMeasureReason.reason,
                        remark: this.form.notMeasureReason.remark,
                    });
                    this.$refs.formNotMeasureReason.resetFields();
                    this.dialog.notMeasureReason = false;
                    this.$message.success('提交成功');
                }
            });
        },
        async handleFormOrderApplyOnSubmit() {
            this.$refs.formOrderApply.validate(async (valid) => {
                if (valid) {
                    await ApiOrder.changeState({
                        id: this.orderId,
                        state: 4,
                        qiandan_jine: this.form.orderApply.qiandan_jine,
                        qiandan_info: this.form.orderApply.qiandan_info,
                    });
                    this.$refs.formOrderApply.resetFields();
                    this.dialog.orderApply = false;
                    this.$message.success('提交成功');
                    await this.getTableData();
                }
            });
        },
        handleFormOrderApplyOnCancel() {
            this.$refs.formOrderApply.resetFields();
            this.dialog.orderApply = false;
        },
        handleSizeChange(size) {
            this.pagination.size = size;
            this.getTableData();
        },
        handleCurrentChange(currentPage) {
            this.pagination.page = currentPage;
            this.getTableData();
        },
        async handleSelectStatusChange(value, row) {
            if (row.isread === 0) {
                this.$message.error('请查看订单详情');
                // eslint-disable-next-line no-param-reassign
                row.status = '请选择';
                return;
            }
            this.orderId = row.order;
            if (value === 1 || value === 2) {
                const res = await ApiOrder.changeState({
                    id: this.orderId,
                    state: value,
                });
                if (res.data.error_code === 0) {
                    this.$message.success('订单状态切换成功');
                } else {
                    this.$message.success('订单状态切换失败');
                }
            }
            if (value === 4) {
                this.dialog.orderApply = true;
            }
            if (value === 3) {
                this.dialog.notMeasureReason = true;
            }
        },
        async getTableData() {
            this.tableLoading = true;
            const res1 = await ApiOrder.listv1();
            if (res1.data.error_code === 4000100) {
                this.ischeckorderpass = 1
                this.checkStatus = false;
            } else {
                this.tableLoading = false;
                this.ischeckorderpass = 3
                this.checkStatus = true;
                const {
                    startEndTime
                } = this.searchValue;

                if (startEndTime !== null && startEndTime?.length === 2) {
                    this.search.start_time = dayjs(this.searchValue.startEndTime[0]).format('YYYY-MM-DD');
                    this.search.end_time = dayjs(this.searchValue.startEndTime[1]).format('YYYY-MM-DD');
                } else {
                    this.search.start_time = '';
                    this.search.end_time = '';
                }

                this.search.is_read = this.searchValue.is_read;
                this.search.state = this.searchValue.state;
                this.search.order_info = this.searchValue.order_info;
                this.search.name = this.searchValue.name;
                this.search.employee_name = this.searchValue.employee_name;
                this.search.type_fw = this.searchValue.type_fw; // 添加订单类型筛选参数

                const res2 = await ApiOrder.listv1({
                    ...this.search,
                    page: this.pagination.page,
                    limit: this.pagination.size,
                });

                const {
                    data: {
                        data
                    },
                    data: {
                        data: {
                            page
                        },
                    },
                } = res2;

                this.tableData = data.list;
                this.pagination.page = page.page_current;
                this.pagination.total = page.total_number;
            }
        },
        async getemployeedropdowlist() {
            const res = await ApiOrder.getemployeedropdowlist({
                position: 1
            });
            if (res.data.error_code === 0) {
                this.assignKf = res.data.data;
            } else {
                this.$message.error(res.data.error_message);
            }

            const res1 = await ApiOrder.getemployeedropdowlist({
                position: 2
            });
            if (res1.data.error_code === 0) {
                this.assignSj = res1.data.data;
            } else {
                this.$message.error(res.data.error_message);
            }
        },
        gotoOrderDetail(scope, tabname) {
            this.$router.push({
                path: 'order-detail',
                query: {
                    id: scope.row.order,
                    tabname
                }
            })
        },
        gotoChangePassword() {
            this.$router.push('order-password-update');
        },
        formatDate(row) {
            return dayjs.unix(row.ordertime).format('YYYY-MM-DD');
        },
        handleDialogWechatClose() {
            this.dialog.wechat = false;
            this.url = '';
            clearInterval(this.timer);
        },
        filterStatus(scope) {
            const option = [{
                    value: 0,
                    label: '请选择'
                },
                {
                    value: 1,
                    label: '已量房'
                },
                {
                    value: 2,
                    label: '已到店/见面'
                },
                {
                    value: 3,
                    label: '未量房'
                },
            ];
            if (!scope.row.qiandan_companyid) {
                option.push({
                    value: 4,
                    label: '已签约'
                });
            }

            return option;
        },
        handleDialogTrackClose() {
            this.form.orderTrack.order_id = null;
            this.form.orderTrack.track_id = null;
            this.$refs.form.resetFields();
        },
        async handleDialogTrackOpen() {
            await this.getList();
        },
        handleButtonEdit(item) {
            // FIXME: 编辑跟单小计
            this.form.orderTrack.track_id = item.id;
            this.form.orderTrack.time = dayjs.unix(item.track_time);
            this.form.orderTrack.step = item.track_step;
            this.form.orderTrack.content = item.track_content;
            this.form.orderTrack.order_id = item.order_id;
        },
        async handleButtonDelete(id) {
            // FIXME: 删除跟单小计
            const res = await ApiOrder.trackDel({
                track_id: id
            });
            if (res.data.error_code === 0) {
                this.$message.success('跟单信息删除成功');
                await this.getList();
            } else {
                this.$message.error(res.data.error_message);
            }
        },

        async onSubmit() {
            this.$refs.form.validate(async (valid) => {
                if (valid) {
                    const res = await ApiOrder.trackEdit({
                        track_id: this.form.orderTrack.track_id,
                        time: dayjs(this.form.orderTrack.time).format('YYYY-MM-DD HH:mm:ss'),
                        step: this.form.orderTrack.step,
                        content: this.form.orderTrack.content,
                        order_id: this.orderId,
                    });
                    if (res.data.error_code === 0) {
                        this.$message.success('提交成功');
                        this.form.orderTrack.track_id = '';
                        this.$refs.form.resetFields();
                        await this.getList();
                    } else {
                        this.$message.success('提交失败');
                    }
                }
            });
        },
        async getList() {
            const res = await ApiOrder.trackList({
                id: this.orderId
            });
            this.trackData = res.data.data.list;
            this.visitData = res.data.data.visit_list;
        },
        // 点击复制
        handleCopy(row) {
            this.copyData = row;
            this.copy(this.copyData);
        },
        copy(data) {
            const url = data;
            const oInput = document.createElement('input');
            oInput.value = url;
            document.body.appendChild(oInput);
            oInput.select(); // 选择对象;
            document.execCommand('Copy'); // 执行浏览器复制命令
            this.$message({
                message: '复制成功',
                type: 'success',
            });
            oInput.remove();
        },
        resetForm() {
            this.searchValue = {
                is_read: '',
                state: '',
                order_info: '',
                startEndTime: [],
                type_fw: '',
                name: '',
                employee_name: '',
            };
        },
        handleAssign(e) {
            this.dialogAssign = true;
            this.assignForm = {
                order_id: e,
                customer: [],
                designer: [],
            };
        },
        submitAssign(formName) {
            // eslint-disable-next-line consistent-return
            this.$refs[formName].validate((valid) => {
                if (valid) {
                    if (this.assignForm.customer.length < 1 && this.assignForm.designer.length < 1) {
                        this.$message({
                            message: '请选择客服或设计师',
                            type: 'error',
                        });
                        return false;
                    }
                    ApiOrder.designated(this.assignForm).then((res) => {
                        if (parseInt(res.status, 10) === 200 && parseInt(res.data.error_code, 10) === 0) {
                            this.$message({
                                message: '添加成功',
                                type: 'success',
                            });
                            this.dialogAssign = false;
                            this.getTableData();
                        } else {
                            this.$message({
                                message: res.data.error_msg,
                                type: 'error',
                            });
                        }
                    });
                } else {
                    // console.log('error submit!!');
                    return false;
                }
            });
        },
        resetAssign(formName) {
            this.$refs[formName].resetFields();
            this.dialogAssign = false;
        },
    },
    data() {
        const validateKf = (rule, value, callback) => {
            if (this.assignForm.designer.length < 1 && this.assignForm.customer.length < 1) {
                callback(new Error('请选择客服'));
            } else {
                callback();
            }
            callback();
        };
        const validateSj = (rule, value, callback) => {
            if (this.assignForm.customer.length < 1 && this.assignForm.designer.length < 1) {
                callback(new Error('请选择客服'));
            } else {
                callback();
            }
        };
        return {
            ischeckorderpass: '', //  1.已开启功能 2.显示选择页面 3.已关闭功能
            newlookpass: {
                password: '',
                repeatpassword: '',
                newrepeatpassword: '',
            },
            newlookpassform: {
                password: [{
                    required: true,
                    message: '请输入旧查单密码',
                    trigger: 'blur'
                }, {
                    min: 8,
                    max: 18,
                    message: '请输入8-18位密码',
                    trigger: 'blur'
                }, ],
                repeatpassword: [{
                    required: true,
                    message: '请输入新查单密码',
                    trigger: 'blur'
                }, {
                    min: 8,
                    max: 18,
                    message: '请输入8-18位密码',
                    trigger: 'blur'
                }, ],
                newrepeatpassword: [{
                    required: true,
                    message: '请确认新查单密码',
                    trigger: 'blur'
                }, {
                    min: 8,
                    max: 18,
                    message: '请输入8-18位密码',
                    trigger: 'blur'
                }, ],
            },
            flag: 0, // 0默认 1点击快捷设置 2点击忘记查单密码
            onOff: false, //  查看密码开关
            phonecodebul: false, // 手机验证码弹框
            setbul: false, // 设置查单密码关闭弹框
            setpasswordbul: false, //  设置查单密码弹窗
            code1: '', // 验证码第一位数字
            code2: '', // 验证码第二位数字
            code3: '', // 验证码第三位数字
            code4: '', // 验证码第四位数字
            getcodebul: true, // 获取验证码按钮
            timer: null, // 定时器
            countdown: 60, // 倒计时
            pickerTime: {
                disabledDate(time) {
                    return time.getTime() > Date.now();
                },
            },
            companyId: '', // 公司id
            url: '', // 查看订单二维码
            checkStatus: false, // 添加密码验证状态
            orderId: '',
            password: '',
            passwordfrom: {
                password: '',
            },
            passwordrules: {
                password: [{
                    required: true,
                    message: '请输入查单密码',
                    trigger: 'blur'
                }, {
                    min: 8,
                    max: 18,
                    message: '请输入8-18位密码',
                    trigger: 'blur'
                }, ]
            },
            rule: {
                qiandan_jine: [{
                        required: true,
                        message: '请输入签单金额',
                        trigger: 'blur'
                    },
                    {
                        pattern: /(?:^[1-9]([0-9]+)?(?:\.[0-9]{1,2})?$)|(?:^(?:0){1}$)|(?:^[0-9]\.[0-9](?:[0-9])?$)/,
                        message: '最多只能输入2位小数',
                        trigger: 'blur',
                    },
                ],
            },
            trackData: [],
            visitData: [],
            form: {
                orderTrack: {
                    track_id: '', // 跟进id (有就是编辑)
                    time: '', // 跟进时间
                    step: '', // 跟进阶段
                    content: '', // 跟进内容
                    order_id: '', // 订单编号
                },
                orderApply: {
                    qiandan_jine: '',
                    qiandan_info: '',
                },
                notMeasureReason: {
                    reason: [],
                    remark: '',
                },
            },
            dialog: {
                wechat: false, //  查看订单二维码
                orderTrack: false, // 跟单
                orderApply: false, // 申请签单
                notMeasureReason: false, // 未量房原因
            },
            searchValue: {
                is_read: '',
                state: '',
                order_info: '',
                startEndTime: [],
                type_fw: '', //  订单类型（1：有效单；2：赠送单；3：补轮单）
                name: '',
                employee_name: '',
            },
            search: {
                is_read: '',
                state: '',
                order_info: '',
                start_time: '',
                end_time: '',
                type_fw: '', //  订单类型（1：有效单；2：赠送单；3：补轮单）
                name: '',
                employee_name: '',
            },
            pagination: {
                page: 1,
                size: 10,
                total: 0,
            },
            option: {
                status: [{
                        value: 0,
                        label: '请选择'
                    },
                    {
                        value: 1,
                        label: '已量房'
                    },
                    {
                        value: 2,
                        label: '已到店/见面'
                    },
                    {
                        value: 3,
                        label: '未量房'
                    },
                    {
                        value: 4,
                        label: '已签约'
                    },
                ],
                notMeasureReason: [{
                        value: 1,
                        label: '业主无法联系'
                    },
                    {
                        value: 2,
                        label: '业主无装修需求'
                    },
                    {
                        value: 3,
                        label: '业主已经签约'
                    },
                    {
                        value: 4,
                        label: '业主无法量房'
                    },
                    {
                        value: 5,
                        label: '业主仅咨询了解'
                    },
                    {
                        value: 6,
                        label: '业主有户型图'
                    },
                ],
            },
            tableData: [],
            tableLoading: false,
            copyData: '',
            show: false,
            // 指派人员弹框
            assignKf: [],
            assignSj: [],
            dialogAssign: false,
            assignForm: {
                order_id: '',
                customer: [],
                designer: [],
            },
            assignRules: {
                customer: [{
                    validator: validateKf,
                    trigger: 'blur'
                }],
                designer: [{
                    validator: validateSj,
                    trigger: 'blur'
                }],
            },
            tagName: '',
        };
    },
};
</script>

<style lang="less" scoped>
@import './css/newintercept.css';

.order-track {
    .order-box {
        height: 308px;
        overflow: auto;
    }

    .order-list {
        width: 360px;
        border: 1px solid rgba(227, 227, 227, 1);

        .header {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
            height: 40px;
            color: #333;
            font-size: 12px;
            background: #f7fcff;

            .time {
                margin: 0 20px;
            }

            .edit {
                position: absolute;
                top: 0;
                right: 80px;
            }

            .del {
                position: absolute;
                top: 0;
                right: 30px;
            }
        }

        .con {
            box-sizing: border-box;
            width: 100%;
            padding: 18px;
            line-height: 24px;
        }
    }

    .empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 300px;

        p {
            margin-top: 30px;
        }
    }

    .el-form-item {
        margin-bottom: 44px;
    }

    .el-form {
        padding-left: 20px;
    }
}

.tag {
    display: inline-block;
    width: 120px;
    height: 40px;
    border: 1px solid black;
}
</style><style lang="less">
.order-all {
    height: 100%;
    background-color: #fff;

    .pagination {
        margin-top: 10px;
        margin-bottom: 120px;
    }

    .el-checkbox-button {
        margin-right: 10px;
    }

    .el-checkbox-button__inner {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100px;
        height: 30px;
        padding: 0;
        font-size: 12px;
        background: rgba(250, 250, 250, 1);
        border: 0;
        border-radius: 2px;
        box-shadow: none;
    }

    .el-checkbox-button.is-checked .el-checkbox-button__inner {
        color: #ff5353;
        background-color: #fff7f7;
        border: 0;
        box-shadow: none;
    }

    .el-checkbox-button:first-child .el-checkbox-button__inner {
        border: none;
    }

    .el-checkbox-button__inner:hover {
        color: #c0c4cc;
    }

    .el-dialog {
        width: 26%;
    }

    .money {
        width: 40%;
    }

    .holder {
        float: right;
    }

    .note {
        margin-bottom: 30px;
        padding-left: 80px;
        color: rgba(255, 83, 83, 1);
        font-weight: 400;
        font-size: 12px;
    }

    .copy {
        margin-left: 10px;
        cursor: pointer;
    }

    .search {
        .search-left {
            float: left;
            width: 80%;
            margin-bottom: 20px;
        }

        .search-right {
            float: right;
            width: 20%;
            margin-top: 16px;
            margin-bottom: 20px;
            text-align: right;

            .unwind {
                display: inline-block;
                margin-right: 30px;
                cursor: pointer;
            }
        }
    }

    .fade-enter-active {
        transition: all 0.5s linear;
    }

    .fade-leave-active {
        transition: all 0.5s linear;
    }

    .fade-enter {
        transform: translateY(-80%);
        opacity: 0;
    }

    .fade-leave-to {
        transform: translateY(-50%);
        opacity: 0;
    }

    .assign-ruleForm .el-select {
        width: 300px;
    }

    .flex {
        display: flex;
    }

    .jus-bet {
        justify-content: space-between;
    }

    .setorder-div {
        width: 100%;
        border: 1px solid #E5E5E5;
    }

    .font18 {
        font-size: 18px;
    }

    .fw500 {
        font-weight: 500;
    }

    .col333 {
        color: #333;
    }

    .bold {
        font-weight: bold;
    }

    .font14 {
        font-size: 14px;
    }

    .col999 {
        color: #999;
    }

    .mt20 {
        margin-top: 20px;
    }

    .mt10 {
        margin-top: 10px;
    }

    .content-text {
        height: 168px;
        padding: 24px 0 0 16px;
    }

    .changpassword-div {
        bottom: 0;
        left: 0;
        height: 51px;
        padding: 0 66px 0 16px;
        line-height: 51px;
        border-top: 1px solid #E5E5E5;
    }

    .btn-icon {
        color: #979797;
        font-size: 20px;
        line-height: 51px;
    }

    .elswi {
        margin: 60px 66px 0 0;
    }

    .el-switch__core {
        height: 30px;
        border-radius: 20px;
    }

    .el-switch__core::after {
        width: 26px;
        height: 26px;
    }

    .el-switch.is-checked .el-switch__core::after {
        margin-left: -26px;
    }

    .point {
        cursor: pointer;
    }

    /* 手机验证码弹窗 */
    .phonecodesty {
        width: 420px;
        height: 320px;
        background: #FFF;
        border-radius: 8px;
    }

    .el-dialog__title {
        color: #333;
        font-weight: bold;
        font-size: 18px;
    }

    .col0091FF {
        color: #0091FF;
    }

    .subtitle {
        display: flex;
        justify-content: space-between;
        color: #999;
        font-size: 14px;
    }

    .inputs {
        display: flex;
        justify-content: space-around;
        margin-top: 40px;
    }

    .input-div {
        width: 50px;
        height: 50px;
        font-size: 20px;
        text-align: center;
        border: 1px solid #999;
        border-radius: 2px;
    }

    .nextstep {
        width: 380px;
        height: 40px;
        margin-top: 40px;
        color: #fff;
        line-height: 40px;
        text-align: center;
        border-radius: 2px;
    }

    .canNotPoint {
        background-color: #E46A6A;
    }

    .canPoint {
        background-color: #D92B2B;
    }

    /* 设置查单密码弹框 */
    .setsty {
        width: 420px;
        height: 460px;
        background: #FFF;
        border-radius: 8px;
    }

    .setsutitle {
        margin-top: -25px;
        color: #999;
        font-size: 12px;
    }

    .setask {
        margin-top: 15px;
        color: #000;
        font-size: 14px;
    }

    .settips {
        margin-top: 8px;
        color: #6D7278;
        font-size: 14px;
    }

    .setbtns {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
    }

    .setbtnl {
        width: 160px;
        height: 40px;
        color: #7D7D7D;
        line-height: 40px;
        text-align: center;
        border: 1px solid #DADADA;
        border-radius: 2px;
    }

    .setbtnr {
        width: 160px;
        height: 40px;
        color: #fff;
        line-height: 40px;
        text-align: center;
        background: #D92B2B;
        border-radius: 2px;
    }

    .colD92B2B {
        color: #D92B2B;
    }

    /* 设置新密码 */
    .newpass {
        margin-top: 40px;
    }

    .resetsty {
        width: 420px;
        background: #FFF;
        border-radius: 8px;
    }
}
</style>
