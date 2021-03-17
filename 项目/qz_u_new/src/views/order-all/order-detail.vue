// 订单详情

<template>
<div>
    <div v-if="ischeckorderpass==3" class="outdiv">
        <div class="order-detail leftdiv">
            <qz-card title="订单详情" divider style="margin-bottom: 80px;">
                <template v-slot:more>
                    <el-button type="danger" plain @click="goBack">返回</el-button>
                </template>
                <qz-card :title="data.qz_area+data.xiaoqu">
                    <template v-slot:more>
                        <el-button class="scene" type="danger" @click="goSite(data.live_id)" v-if="data.live_id && data.live_id !== ''">装修现场</el-button>
                    </template>
                    <el-row>
                        <el-col :span="8">
                            <p>联系人:{{data.name || '- -'}} {{data.sex != ''? '('+data.sex + ')' : ''}}</p>
                            <p>备用联系人:{{data.standby_user || '- -'}}</p>
                            <p>所属区域:{{data.qz_area || '- -'}}</p>

                        </el-col>
                        <el-col :span="8">
                            <p>
                                手机号码:{{data.tel8 || '- -'}}
                                <span class="protect">{{data.protect == 1 ? '(业主号码已保护)' : ''}}</span>
                            </p>
                            <p>备用联系方式:{{data.other_contact || '- -'}}</p>
                            <p>所属小区:{{data.xiaoqu || '- -'}}</p>

                        </el-col>
                        <el-col :span="8">
                            <!-- FIXME: data.time -->
                            <p>微信号:{{data.wx || '- -'}}</p>
                            <p v-if="data.is_wx==1">业主要求:要求微信联系</p>
                            <p v-else>业主要求:--</p>
                            <p>订单编号:{{data.id || '- -'}}</p>

                        </el-col>
                        <el-col>
                            <p>房屋地址：{{data.dz || '- -'}}</p>
                        </el-col>
                        <!--
              <el-col :span="8">

              <p>量房时间:{{data.lftime || '- -'}}</p>

                <p>装修方式:{{data.fangshi || '- -'}}</p>
                <p>发布时间:{{dataTimeFormat || '- -'}}</p>

                <p>房屋户型:{{data.hxname || '- -'}}</p>

              </el-col>
              -->
                    </el-row>
                </qz-card>

                <el-tabs v-model="tabname" @tab-click="clicktab()" class="changecolor">
                    <el-tab-pane label="基础订单信息" name="basics">
                        <qz-card title="装修信息" divider bordered headerBackgroundColor>
                            <el-row>
                                <el-col :span="8">
                                    <p>装修类型：{{data.lx_name}}&nbsp;{{data.lxs_name}}</p>
                                    <p>风格：{{data.fengge || '- -'}}</p>
                                    <p>
                                        <span>是否拿钥匙:</span>
                                        <qz-table-cell :option="{0:'无钥匙',1:'有钥匙',2:'--',3:'其他'}" :select="data.keys"></qz-table-cell>
                                    </p>
                                </el-col>
                                <el-col :span="8">
                                    <p>户型结构：{{data.hxname || '- -'}}&nbsp;{{data.shi || ''}}室</p>
                                    <p>预算:{{data.yusuan || '- -'}}</p>
                                    <p>量房/到店：{{data.lftime}}</p>
                                </el-col>
                                <el-col :span="8">
                                    <p>面积:{{data.mianji || '- -'}}㎡</p>
                                    <p>拿房时间:{{data.nf_time}}</p>
                                    <p>开工时间:{{data.start || '- -'}}</p>
                                </el-col>
                            </el-row>
                            <pre class="entertext">{{data.text}}</pre>
                            <div class="line"></div>
                            <p>
                                说明服务和保障：
                                <br />①联系时要给业主名片并说明是齐装网推荐的，加深业主印象
                                <br />②装企必须将此单更进的状态实时添加【小计】，客服【针对性】推荐及时记录小计的会员促进签单
                            </p>
                        </qz-card>
                        <qz-card title="选择公司" divider bordered headerBackgroundColor>
                            <div class="img-box" v-for="(item,index) in data.companys" :key="index">
                                <img v-if="item.logo" class="item-imgs" :src="item.logo" />
                                <img v-else class="item-imgs" src="../../assets/default-logo.jpg" />
                            </div>
                        </qz-card>
                        <qz-card title="签约公司" divider bordered headerBackgroundColor v-if="data.qiandan_company">
                            <div class="img-box">
                                <img v-if="data.qiandan_company.logo" class="item-imgs" :src="data.qiandan_company.logo" />
                                <img v-else class="item-imgs" src="../../assets/default-logo.jpg" />
                            </div>
                        </qz-card>
                    </el-tab-pane>
                    <el-tab-pane label="跟单小记" name="subtotal">
                        <div class="subtotal-div">
                            <div><span class="red">*</span>&nbsp;沟通阶段：</div>
                            <div class="flex btns">
                                <div :class="['suv-btn','point',{'sel-btn':btnval==btn.val}]" v-for="btn of btns" :key="btn.val" @click="swibtn(btn)">{{btn.name}}</div>
                            </div>
                        </div>
                        <div class="subtotal-div">
                            <div><span class="red">*</span>&nbsp;跟进时间：</div>
                            <div class="block btns">
                                <el-date-picker v-model="dateval" format="yyyy-MM-dd HH:mm:ss" value-format="yyyy-MM-dd HH:mm:ss" type="datetime" :picker-options="pickerOptions" placeholder="请选择跟进时间">
                                </el-date-picker>
                            </div>
                        </div>
                        <div class="subtotal-div">
                            <div><span class="red">*</span>&nbsp;跟进记录：</div>
                            <div class="block btns">
                                <el-input :autosize="{ minRows: 4, maxRows: 4 }" class="textarea-sty" type="textarea" placeholder="请填写跟进记录" v-model="textareaval" maxlength="1000">
                                </el-input>
                            </div>
                        </div>
                        <div class="subtotal-div">
                            <div class="flex">
                                <el-button type="danger" class="save" @click="trackEdit()">保存</el-button>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane label="指派人员" name="assign">
                        <div class="flex spa-bet padlr10">
                            <el-link :underline="false">指派人员</el-link>
                            <el-link type="danger" :underline="false" @click="handleAssign(data.id)">+添加人员</el-link>
                        </div>
                        <div class="empty" v-if="tableData==''">
                            <div>
                                <qz-empty></qz-empty>
                                <p>暂无数据</p>
                            </div>
                        </div>
                        <el-table v-else :data="tableData" style="width: 100%">
                            <el-table-column prop="logo" label="头像">
                                <template slot-scope="scope">
                                    <img class="header-logo" :src="scope.row.logo" alt="">
                                </template>
                            </el-table-column>
                            <el-table-column prop="nick_name" label="姓名">
                            </el-table-column>
                            <el-table-column prop="position_name" label="职位">
                            </el-table-column>
                            <el-table-column prop="role_names" label="角色">
                            </el-table-column>
                            <el-table-column prop="created_date" label="指派时间">
                            </el-table-column>
                            <el-table-column label="操作">
                                <template slot-scope="scope">
                                    <el-link type="danger" :underline="false" @click="unassign(scope.row)">取消指派</el-link>
                                </template>
                            </el-table-column>
                        </el-table>
                    </el-tab-pane>
                    <el-tab-pane label="相关服务" name="service">
                        <div class="flex spa-bet">
                            <div class="service-title">相关服务</div>
                            <div v-if="serbul">
                                <el-link type="danger" :underline="false" @click="editser()">编辑</el-link>
                            </div>
                        </div>
                        <div v-if="serbul">
                            <div class="ser-div borbccc">
                                <div class="mt20">装修特色<span class="col999">(主要说明主做楼盘、售后服务保障等)</span></div>
                                <div class="mt20 col999" v-if="data.fixup_special!=''">{{data.fixup_special}}</div>
                                <div class="mt20 col999" v-else>暂无装修特色</div>
                            </div>
                            <div class="ser-div borbccc">
                                <div class="mt20">装企优惠<span class="col999">(主要说明可使用的活动以及价格说明)</span></div>
                                <div class="mt20 col999" v-if="data.company_discount!=''">{{data.company_discount}}</div>
                                <div class="mt20 col999" v-else>暂无装企优惠</div>
                            </div>
                            <div class="ser-div borbccc">
                                <div class="mt20">业主顾虑点<span class="col999">(简述该业主装修的顾虑点以及其他特殊说明)</span></div>
                                <div class="mt20 col999" v-if="data.yz_worry!=''">{{data.yz_worry}}</div>
                                <div class="mt20 col999" v-else>暂无业主顾虑点</div>
                            </div>
                        </div>
                        <div v-else>
                            <div class="ser-div">
                                <div class="mt20">装修特色<span class="col999">(主要说明主做楼盘、售后服务保障等)</span></div>
                                <div class="block btns">
                                    <el-input :autosize="{ minRows: 4, maxRows: 4 }" class="ser-text" type="textarea" placeholder="请填写装修特色" v-model="fixup_special" maxlength="500">
                                    </el-input>
                                </div>
                            </div>
                            <div class="ser-div">
                                <div class="mt20">装企优惠<span class="col999">(主要说明可使用的活动以及价格说明)</span></div>
                                <div class="block btns">
                                    <el-input :autosize="{ minRows: 4, maxRows: 4 }" class="ser-text" type="textarea" placeholder="请填写装企优惠" v-model="company_discount" maxlength="500">
                                    </el-input>
                                </div>
                            </div>
                            <div class="ser-div">
                                <div class="mt20">业主顾虑点<span class="col999">(简述该业主装修的顾虑点以及其他特殊说明)</span></div>
                                <div class="block btns">
                                    <el-input :autosize="{ minRows: 4, maxRows: 4 }" class="ser-text" type="textarea" placeholder="请填写业主顾虑点" v-model="yz_worry" maxlength="500">
                                    </el-input>
                                </div>
                            </div>
                            <div class="ser-btns">
                                <el-button @click="saveser(0)">取消</el-button>
                                <el-button type="danger" @click="saveser(1)">保存</el-button>
                            </div>
                        </div>
                    </el-tab-pane>
                </el-tabs>

            </qz-card>
        </div>
        <div class="rightdiv">
            <qz-card title="动态">
                <el-tabs v-model="righttabname" class="rig-tab-div" @tab-click="changerighttab()">
                    <el-tab-pane label="跟进记录" name="record" class="rec-oh">
                        <div class="empty" v-if="trackobj.list==''">
                            <div>
                                <qz-empty></qz-empty>
                                <p>暂无数据</p>
                            </div>
                        </div>
                        <div v-else class="col999" v-for="record of trackobj.list" :key="record.id">
                            <div class="flex spa-bet header-msg" v-if="record.employee_id==0">
                                <div class="flex spa-bet">
                                    <div>
                                        <img class="rec-head" :src="record.company_logo" alt="">
                                    </div>
                                    <div>
                                        <div class="col000">{{record.company_name || '- -'}}</div>
                                        <div>{{record.created_date}}</div>
                                    </div>
                                </div>
                                <div>- -</div>
                            </div>
                            <div class="flex spa-bet header-msg" v-else>
                                <div class="flex spa-bet">
                                    <div>
                                        <img class="rec-head" :src="record.employee_logo" alt="">
                                    </div>
                                    <div>
                                        <div class="col000">{{record.employee_name || '- -'}}</div>
                                        <div>{{record.created_date}}</div>
                                    </div>
                                </div>
                                <div>{{record.employee_position_name || '- -'}}</div>
                            </div>
                            <div class="rec-status">编辑了项目状态</div>
                            <div class="stage-div">
                                <div class="flex">
                                    <div>沟通阶段：</div>
                                    <!--
                    <div class="stage-lab1" v-if="record.last_track_step_name!=''">{{record.last_track_step_name}}</div>
                    <div class="lab12-cen col000"
                    v-if="record.last_track_step_name!=''"> <i class="el-icon-arrow-right"></i> </div>
                    -->
                                    <div class="stage-lab2">{{record.track_step_name}}</div>
                                </div>
                                <div>跟进时间：<span class="col000">{{record.track_date}}</span></div>
                                <div>跟进备注：{{record.track_content || '- -'}}</div>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane :label="'平台客服反馈'+visit_unreadnum" name="feedback" class="rec-oh">
                        <div class="empty" v-if="trackobj.visit_list==''">
                            <div>
                                <qz-empty></qz-empty>
                                <p>暂无数据</p>
                            </div>
                        </div>
                        <div v-else v-for="(visit,index) of trackobj.visit_list" :key="index">
                            <div class="flex spa-bet feed-tit">
                                <div>齐装网客服反馈</div>
                                <qz-timestamp-format :timestamp="visit.visit_time.toString()" class="col999"></qz-timestamp-format>
                            </div>
                            <div class="col999">{{visit.visit_content_sales || '- -'}}</div>
                        </div>
                    </el-tab-pane>
                </el-tabs>
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
    <el-dialog top="30vh" title="查单密码关闭提示" :close-on-click-modal="false" :visible.sync="setbul" custom-class="setsty">
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
    <el-dialog top="30vh" title="请设置查单密码" :close-on-click-modal="false" :visible.sync="setpasswordbul" custom-class="resetsty">
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
import ApiOrder from '@/api/order';
import api from '@/api/redosystem';
import QzTableCell from '@/components/table-cell.vue';
import QzEmpty from '@/components/empty.vue';
import dayjs from 'dayjs';
import QzTimestampFormat from '@/components/timestamp-format.vue';
import notsetpassword from './components/notsetpassword.vue'

export default {
    created() {
        this.checkorderpassstatus()
        this.adaptId = this.$route.query.id || this.$route.query.order_id
    },
    components: {
        QzCard,
        QzTableCell,
        QzEmpty,
        QzTimestampFormat,
        notsetpassword
    },
    methods: {
        //  订单管理-获取是否有订单密码
        checkorderpassstatus() {
            const params = {}
            api.checkorderpassstatus(params).then((res) => {
                if (res.data.error_code === 0) {
                    this.ischeckorderpass = res.data.data
                    if (res.data.data !== 2) {
                        this.getOrderDetail()
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
                        await this.getOrderDetail();
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
        gotoChangePassword() {
            this.$router.push('order-password-update');
        },
        //  获取订单详情
        getOrderDetail() {
            const params = {
                order_id: this.adaptId
            }
            ApiOrder.detail(params).then((res) => {
                    if (res.data.error_code === 0) {
                        if (this.$route.query.tabname) {
                            this.tabname = this.$route.query.tabname;
                        } else {
                            this.tabname = 'basics'
                        }
                        if (this.$route.query.righttabname) {
                            this.righttabname = this.$route.query.righttabname;
                        } else {
                            this.righttabname = 'record'
                        }
                        this.data = res.data.data;
                        if (res.data.error_code === 4000100) {
                            this.ischeckorderpass = 1
                            this.checkStatus = false;
                        } else {
                            this.ischeckorderpass = 3
                            this.checkStatus = true;
                            this.getemployeedropdowlist()
                            this.trackList()
                            this.designatedList()
                        }
                    } else {
                        //  失败
                        console.log(res.data.error_msg)
                        // this.$message.error(res.data.error_msg);
                    }
                })
                .catch((err) => {
                    console.log(err.data.error_msg)
                    // this.$message.error(err);
                });
        },
        saveser(bul) {
            if (bul) {
                this.changeService()
            } else {
                this.serbul = true
            }
        },
        editser() {
            this.serbul = false
            this.fixup_special = this.data.fixup_special
            this.company_discount = this.data.company_discount
            this.yz_worry = this.data.yz_worry
        },
        //  相关服务
        changeService() {
            const params = {
                order_id: this.data.id, //  订单号
                fixup_special: this.fixup_special, //  装修特色
                company_discount: this.company_discount, //  装企优惠
                yz_worry: this.yz_worry //  业主顾虑点
            }
            ApiOrder.changeService(params).then((res) => {
                    if (res.data.error_code === 0) {
                        // 0成功 其余失败
                        this.getorderdetail()
                        this.$message({
                            message: '保存成功',
                            type: 'success'
                        });
                    } else {
                        //  失败
                        this.$message.error(res.data.error_msg);
                    }
                })
                .catch((err) => {
                    this.$message.error(err);
                });
        },
        getorderdetail() {
            const params = {
                order_id: this.$route.query.id
            }
            ApiOrder.detail(params).then((res) => {
                    if (res.data.error_code === 0) {
                        // 0成功 其余失败
                        this.data = res.data.data;
                        this.serbul = true
                    } else {
                        //  失败
                        this.$message.error(res.data.error_msg);
                    }
                })
                .catch((err) => {
                    this.$message.error(err);
                });
        },
        unassign(staffmsg) {
            this.$confirm('您确定取消该员工跟进该订单的权限吗?', '取消指派', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                confirmButtonClass: 'conbtn',
                type: 'warning'
            }).then(() => {
                const params = {
                    order_id: staffmsg.order_id,
                    employee_id: staffmsg.employee_id
                }
                ApiOrder.designatedCancel(params).then((res) => {
                        if (res.data.error_code === 0) {
                            // 0成功 其余失败
                            this.designatedList()
                            this.$message({
                                type: 'success',
                                message: '取消成功!'
                            });
                        } else {
                            //  失败
                            this.$message.error(res.data.error_msg);
                        }
                    })
                    .catch((err) => {
                        this.$message.error(err);
                    });
            }).catch(() => {

            });
        },
        handleAssign(e) {
            this.dialogAssign = true;
            this.assignForm = {
                order_id: e,
                customer: [],
                designer: [],
            };
        },
        changerighttab() {
            if (this.righttabname === 'feedback') {
                if (this.trackobj.visit_unreadnum !== 0) {
                    this.visitRead()
                } else {
                    this.visit_unreadnum = ''
                }
            }
        },
        //  订单管理-订单跟进情况
        trackList() {
            const params = {
                id: this.data.id
            }
            ApiOrder.trackList(params).then((res) => {
                    if (res.data.error_code === 0) {
                        // 0成功 其余失败
                        this.trackobj = res.data.data
                        // this.trackobj={
                        //   list:[],
                        //   visit_list:[],
                        //   visit_unreadnum:1
                        // }
                        if (this.trackobj.visit_unreadnum !== 0) {
                            this.visit_unreadnum = `(${this.trackobj.visit_unreadnum})`
                        } else {
                            this.visit_unreadnum = ''
                        }
                    } else {
                        //  失败
                        this.$message.error(res.data.error_msg);
                    }
                })
                .catch((err) => {
                    this.$message.error(err);
                });
        },
        //  订单管理-订单跟进-标记回访已读
        visitRead() {
            const params = {
                order_id: this.data.id
            }
            ApiOrder.visitRead(params).then((res) => {
                    if (res.data.error_code === 0) {
                        // 0成功 其余失败
                        this.trackList()
                    } else {
                        //  失败
                        this.$message.error(res.data.error_msg);
                    }
                })
                .catch((err) => {
                    this.$message.error(err);
                });
        },
        swibtn(btn) {
            this.btnval = btn.val
        },
        goBack() {
            this.$router.back();
        },

        goSite(id) {
            this.$router.push(`/site-detail/${id}`);
        },
        clicktab() {
            // console.log(this.tabname)
        },
        designatedList() {
            const params = {
                order_id: this.data.id
            }
            ApiOrder.designatedList(params).then((res) => {
                    if (res.data.error_code === 0) {
                        // 0成功 其余失败
                        this.tableData = res.data.data.list
                    } else {
                        //  失败
                        this.$message.error(res.data.error_msg);
                    }
                })
                .catch((err) => {
                    this.$message.error(err);
                });
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
                            this.designatedList();
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
        //  保存跟单
        trackEdit() {
            if (this.btnval === '') {
                this.$message({
                    message: '请选择沟通阶段',
                    type: 'error',
                });
                return
            }
            if (this.dateval === '') {
                this.$message({
                    message: '请选择跟进时间',
                    type: 'error',
                });
                return
            }
            if (this.textareaval === '') {
                this.$message({
                    message: '请填写跟进记录',
                    type: 'error',
                });
                return
            }
            const params = {
                id: '',
                order_id: this.data.id,
                step: this.btnval, //  沟通阶段
                time: this.dateval, // 跟进时间
                content: this.textareaval, // 跟进记录
            }
            ApiOrder.trackEdit(params).then((res) => {
                    if (res.data.error_code === 0) {
                        // 0成功 其余失败
                        this.$message({
                            message: '保存成功',
                            type: 'success',
                        });
                        this.trackList();
                        this.btnval = ''
                        this.dateval = ''
                        this.textareaval = ''
                    } else {
                        //  失败
                        this.$message.error(res.data.error_msg);
                    }
                })
                .catch((err) => {
                    this.$message.error(err);
                });
        },
        resetAssign(formName) {
            this.$refs[formName].resetFields();
            this.dialogAssign = false;
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
            password: '',
            url: '', // 查看订单二维码
            dialog: {
                wechat: false, //  查看订单二维码
                orderTrack: false, // 跟单
                orderApply: false, // 申请签单
                notMeasureReason: false, // 未量房原因
            },
            checkStatus: false, // 添加密码验证状态
            adaptId: '', // 转接id
            tabname: 'basics',
            righttabname: 'record',
            // 跟进入参
            btnval: '', //  沟通阶段
            dateval: '', // 跟进时间
            textareaval: '', // 跟进记录
            // 相关服务入参
            fixup_special: '', // 装修特色
            company_discount: '', //  装企优惠
            yz_worry: '', //  业主顾虑点
            //  指派人员列表
            tableData: [],
            pickerOptions: {
                disabledDate(time) {
                    return time.getTime() > Date.now();
                },
            },
            btns: [{
                    name: '初次',
                    val: 1
                },
                {
                    name: '量房',
                    val: 2
                },
                {
                    name: '方案',
                    val: 3
                },
                {
                    name: '签约',
                    val: 4
                },
            ],
            visit_unreadnum: '',
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
            serbul: true,
            trackobj: {},
            data: {
                id: '', // 订单编号
                xiaoqu: '', // 小区
                name: '', // 联系人
                sex: '', // 性别
                tel8: '', // 联系电话
                wx: '', // 微信号
                is_wx: '', // 是否要求微信联系
                time: '', // 发布时间
                yusuan: '', // 预算
                mianji: '', // 房屋面积
                hxname: '', // 房屋户型
                keys: '', // 是否拿钥匙 1.是 2.否 3.其他
                fangshi: '', // 装修方式
                start: '', // 开工时间
                lftime: '', // 量房时间
                text: '', // 装修要求
                companys: [], // 选择公司
                qiandan_company: {}, // 签约公司
                protect: '', // 号码是否保护
            },
        };
    },
    computed: {
        dataTimeFormat() {
            return dayjs.unix(this.data.time).format('YYYY-MM-DD HH:mm:ss');
        },
    },
};
</script>

<style lang="less">
.order-detail {
    .qz-card .content {
        overflow: hidden;
    }

    .img-box {
        float: left;
        padding-right: 20px;
        overflow: hidden;

        .item-imgs {
            display: block;
            float: left;
            width: 180px;
            height: 90px;
        }
    }

    .line {
        width: 100%;
        height: 1px;
        margin: 10px 0;
        border: 1px dashed rgba(227, 227, 227, 0.6);
    }

    .qz-card .divider {
        margin: 0;
    }

    .protect {
        color: #3bb19c;
    }
}

.outdiv {
    display: flex;
    overflow: hidden;
}

.leftdiv {
    width: 75%;
    overflow-y: scroll;
}

.rightdiv {
    width: 25%;
    margin: 0 10px;
}

.empty {
    display: flex;
    align-items: center;
    justify-content: center;
    padding-top: 200px;

    img {
        width: 300px;
    }

    p {
        margin: 20px 0 0 0;
        color: #999;
        text-align: center;
    }
}
</style><style scoped>
@import './css/newintercept.css';

.subtotal-div {
    margin: 0 auto;
    padding: 24px 0 0 0;
}

.flex {
    display: flex;
}

.btns {
    margin: 10px 0 0 0;
}

.point {
    cursor: pointer;
}

.suv-btn {
    width: 70px;
    height: 40px;
    margin: 0 10px 0 0;
    line-height: 40px;
    text-align: center;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.sel-btn {
    color: #fff;
    background-color: #F56C6C;
    border: none;
}

.textarea-sty {
    width: 320px;
}

.save {
    width: 320px;
}

.spa-bet {
    justify-content: space-between;
}

.padlr10 {
    padding: 0 10px;
}

.header-logo {
    width: 50px;
    height: 50px;
    border-radius: 50%;
}

.rec-oh {
    height: 720px;
    overflow-y: auto;
    word-wrap: break-word;
    word-break: break-all;
    scrollbar-width: none;
}

.rec-oh::-webkit-scrollbar {
    width: 0;
}

.rec-oh::-moz-scrollbar {
    width: 0;
}

.rec-oh::-ms-scrollbar {
    width: 0;
}

.rec-oh::-o-scrollbar {
    width: 0;
}

.service-title {
    margin: 10px 0 0 0;
    font-size: 18px;
}

.col999 {
    color: #999;
}

.ser-div {
    width: 100%;
    padding: 0 0 12px 0;
}

.borbccc {
    border-bottom: 1px solid #ccc;
}

.mt20 {
    margin-top: 20px;
}

.ser-btns {
    margin: 20px 0 0 0;
    text-align: right;
}

.rig-tab-div {
    margin-top: -30px;
}

.red {
    color: red;
}

.feed-tit {
    margin: 20px 0 5px 0;
}

.rec-head {
    width: 50px;
    height: 50px;
    margin: 0 10px 0 0;
    border-radius: 50%;
}

.stage-lab1 {
    position: relative;
    height: 22px;
    margin: 0 0 0 10px;
    padding: 2px 10px;
    color: #C8C8C8;
    line-height: 22px;
    text-align: center;
    text-decoration: line-through;
    background-color: #eee;
    border-radius: 2px;
}

.lab12-cen {
    margin: 0 15px;
}

.stage-lab2 {
    height: 22px;
    padding: 2px 10px;
    color: #28BB6B;
    line-height: 22px;
    text-align: center;
    background-color: #D8FFEC;
    border-radius: 2px;
}

.stage-div {
    padding: 0 0 0 15px;
    border-left: 1px solid #CACACA;
}

.stage-div>div {
    margin: 0 0 10px 0;
}

.rec-status {
    margin: 10px 0;
}

.col000 {
    color: #000;
}

.header-msg {
    margin: 20px 0 10px 0;
}

.assign-ruleForm .el-select {
    width: 300px;
}

.scene {
    margin: 0 -30px 0 0;
}
</style><style>
.el-tabs__item.is-active {
    color: #F56C6C;
    font-weight: bold;
}

.el-tabs__active-bar {
    background-color: #F56C6C;
}

.el-tabs__item:hover {
    color: #F56C6C;
}

.conbtn {
    color: #fff;
    background-color: #F56C6C;
}

.conbtn:hover {
    color: #fff;
    background-color: #F56C6C;
    border: 1px solid #F56C6C;
}
.entertext{
    white-space: pre-wrap;
    word-wrap: break-word;
}
</style>
