<template>
<div class="classMsg flex">
    <!-- 左边的tab -->
    <div class="left-tab">
        <div class="flex spa-bet tab-title ali-ite">
            <div class="colAEAEAE font12">消息中心</div>
            <el-dropdown @command="allRead" trigger="click">
                <span class="el-dropdown-link">
                    <div class="el-icon-more point hideFun"></div>
                </span>
                <el-dropdown-menu slot="dropdown">
                    <el-dropdown-item :command="1">全部标记已读</el-dropdown-item>
                    <el-dropdown-item :command="2">全部删除</el-dropdown-item>
                </el-dropdown-menu>
            </el-dropdown>
        </div>
        <div class="tabs">
            <div :class="['flex','tab-item','ali-ite','point',{'colFF5353':isOn==1}]" @click="getMsgList(1)">
                <div class="el-icon-bell mr5"></div>
                <div class="flex">
                    <div class="mr5">通知</div>
                    <div v-if="notice_count!=0">({{notice_count}})</div>
                </div>
            </div>
            <div :class="['flex','tab-item','ali-ite','point',{'colFF5353':isOn==2}]" @click="getMsgList(2)">
                <div class="el-icon-chat-dot-square mr5"></div>
                <div class="flex">
                    <div class="mr5">消息</div>
                    <div v-if="msg_count!=0">({{msg_count}})</div>
                </div>
            </div>
        </div>
    </div>
    <!-- 右边的tab -->
    <div class="right-list">
        <div class="flex spa-bet tab-title ali-ite">
            <div>{{listName}}&nbsp;
                <span class="colAEAEAE font12">
                    (共{{msgTotal.count}}条，其中未读为{{msgTotal.unread}}条)
                </span>
            </div>
            <div :class="['flex','font14',{'tab-btns':msgList&&msgList.length==0}]">
                <div class="tab-btn point" @click="allSignRead()">全部标记为已读</div>
                <div class="tab-btn point" @click="allDelete({actfrom:isOn,alltype:2,msg_ids:msgIdList})">全部删除</div>
                <div class="tab-btn point" @click="signRead({actfrom:isOn,alltype:2,msg_ids:sel_msgIdList})">标记为已读</div>
                <div class="tab-btn point" @click="signDelete({actfrom:isOn,alltype:2,msg_ids:sel_msgIdList})">删除</div>
            </div>
        </div>
        <div class="tab-content" v-if="msgList&&msgList.length>0">
            <div class="flex font13 ali-ite">
                <div :class="[{'el-icon-check':allSel},
          {'on':allSel},'con-check','point']" @click="isAllSel()">
                </div>
                <div>全选</div>
            </div>
            <div class="rolling">
                <div class="point" v-for="item of msgList" :key="item.id" @click="toDetail(item)">
                    <div class="flex font13 cen-item spa-bet">
                        <div class="flex">
                            <div :class="[{'el-icon-check':sel_msgIdList.includes(item.id)},
                {'on':sel_msgIdList.includes(item.id)},'con-check','point']" @click.stop="selMsgItem(item.id)">
                            </div>
                            <div class="cen-title">
                                <div class="font16">{{item.title}}</div>
                                <div class="cen-msg">{{item.content}}</div>
                            </div>
                            <div class="cen-date">{{item.created_date}}</div>
                        </div>
                        <div class="isRead">
                            <div class="read" v-if="item.is_read==1">已读</div>
                            <div class="unread" v-else>未读</div>
                        </div>
                    </div>
                </div>
                <el-pagination class="pageStyle" @current-change="changePage" :current-page="msgPage.page_current" :page-size="msgPage.page_size" layout="total, prev, pager, next, jumper" :total="msgPage.total_number">
                </el-pagination>
            </div>
        </div>
        <div class="noData" v-else>
            <qz-empty></qz-empty>
            <p>暂无数据</p>
        </div>
    </div>
</div>
</template>

<script>
import api from '@/api/message';

export default {
    data() {
        return {
            unReadIdList: [], //  未读id列表
            readTopic: '选中的文件已标记为已读!',
            delTopic: '选中的文件已删除!',
            isOn: 1, //  选中状态 默认通知
            listName: '通知',
            allSel: false, // 全选

            //  消息中心-消息列表
            //  入参
            page: 1,
            //  返回参数
            msgList: [], // 消息列表
            msgPage: { // 分页信息
                total_number: 0, // 总条数
                page_total_number: 0, //  总页数
                page_size: 0, // 每页显示条数
                page_current: 1 // 当前页
            },
            msgTotal: {
                count: 0, // 消息总数量
                unread: 0 // 未读消息数量
            },
            //  消息中心-未读消息数量
            //  返回数据
            count: '', // 未读总数
            msg_count: '', // 未读消息数
            notice_count: '', //  未读通知数
            public_count: '', //  未读公告数

            sel_msgIdList: [] //  选中得数据列表
        }
    },
    components: {
        QzEmpty: () => import('@/components/empty.vue'), // 没列表数据
    },
    created() {
        this.getMsgList(1)
    },
    methods: {
        //  全部标记已读
        allSignRead() {
            const msgIdList = []
            this.msgList.forEach((element) => {
                msgIdList.push(element.id)
            });
            const params = {
                actfrom: this.isOn,
                alltype: 2,
                msg_ids: msgIdList
            }
            this.readTopic = '文件已全部标记为已读!'
            this.read(params, 0)
        },
        //  全部删除
        allDelete() {
            this.$confirm('确定要删除当前页所有消息通知吗？', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                const msgIdList = []
                this.msgList.forEach((element) => {
                    msgIdList.push(element.id)
                });
                const params = {
                    actfrom: this.isOn,
                    alltype: 2,
                    msg_ids: msgIdList
                }
                this.delTopic = '文件已全部删除!'
                this.deleteMsg(params)
            })
        },
        //  勾选标记已读
        signRead() {
            if (this.sel_msgIdList && this.sel_msgIdList.length === 0) {
                this.$message({
                    message: '请勾选需要操作的信息',
                    type: 'warning'
                });
                return
            }
            const params = {
                actfrom: this.isOn,
                alltype: 2,
                msg_ids: this.sel_msgIdList
            }
            this.readTopic = '选中的文件已标记为已读!'
            this.read(params, 0)
        },
        //  勾选删除
        signDelete() {
            if (this.sel_msgIdList && this.sel_msgIdList.length === 0) {
                this.$message({
                    message: '请勾选需要操作的信息',
                    type: 'warning'
                });
                return
            }
            this.$confirm('确定要删除该条消息通知吗？', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                const params = {
                    actfrom: this.isOn,
                    alltype: 2,
                    msg_ids: this.sel_msgIdList
                }
                this.delTopic = '选中的文件已删除!'
                this.deleteMsg(params)
            })
        },
        //  跳转
        toDetail(item) {
            console.log(item, 66)
            const params = {
                actfrom: this.isOn,
                alltype: 2,
                msg_ids: [item.id]
            }
            this.read(params, 1, item)
        },
        //  全选样式跟数据
        selStyData() {
            this.sel_msgIdList = []
            if (this.allSel) {
                this.msgList.forEach((element) => {
                    this.sel_msgIdList.push(element.id)
                });
            }
        },
        //  全选
        isAllSel() {
            this.allSel = !this.allSel
            this.selStyData()
        },
        //  选择需要操作的每一项
        selMsgItem(id) {
            if (this.sel_msgIdList.includes(id)) {
                this.sel_msgIdList.forEach((element, i) => {
                    if (element === id) {
                        this.sel_msgIdList.splice(i, 1)
                        this.allSel = false
                    }
                });
            } else {
                this.sel_msgIdList.push(id)
                if (this.sel_msgIdList.length === this.msgList.length) {
                    this.allSel = true
                    this.selStyData()
                }
            }
        },
        //  换页
        changePage(val) {
            this.page = val
            this.getMsgList(this.isOn)
        },
        //  点击tab
        allRead(type) { // 1、全部标记为已读 2、全部删除
            const params = {
                actfrom: 99, //  消息类型（1：通知；2：消息；99：全部）
                alltype: 1, //  操作范围（1：全部；2：部分）
                msg_ids: [] //  消息ID（数组或逗号隔开字符串）（标记类型为部分时必填）
            }
            if (type === 1) { //  全部标记为已读
                if (this.count === 0) {
                    this.$message({
                        message: '暂无未读文件',
                        type: 'warning'
                    });
                    return
                }
                this.readTopic = '文件已全部标记为已读!'
                this.read(params, 0)
            } else { //  全部删除
                this.$confirm('是否需要删除全部消息中心的文件?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.delTopic = '文件已全部删除!'
                    this.deleteMsg(params)
                })
            }
        },
        //  消息中心-消息列表
        getMsgList(actfrom) {
            this.isOn = actfrom // 样式&& // 参数
            if (actfrom === 1) {
                this.listName = '通知'
            } else {
                this.listName = '消息'
            }
            const params = {
                actfrom, //  消息类型（1：通知；2：消息）
                limit: 20,
                page: this.page
            }
            api.list(params).then((res) => {
                if (res.data.error_code === 0) {
                    this.sel_msgIdList = [] // 清空
                    this.msgList = [] // 清空
                    this.unReadIdList = [] // 清空
                    this.msgList = res.data.data.list
                    res.data.data.list.forEach((item) => {
                        if (item.is_read === 2) {
                            this.unReadIdList.push(item.id)
                        }
                    })
                    this.msgPage = res.data.data.page
                    this.msgTotal = res.data.data.total
                    this.unreadnum()
                } else {
                    this.$message.error(res.data.error_msg);
                }
            }).catch((err) => {
                this.$message.error(err);
            })
        },
        //  消息中心-未读消息数量
        unreadnum() {
            const params = {}
            api.unreadnum(params).then((res) => {
                if (res.data.error_code === 0) {
                    this.count = res.data.data.count //  未读总数
                    this.msg_count = res.data.data.msg_count //  未读消息数
                    this.notice_count = res.data.data.notice_count // 未读通知数
                    this.public_count = res.data.data.public_count // 未读公告数
                } else {
                    this.$message.error(res.data.error_msg);
                }
            }).catch((err) => {
                this.$message.error(err);
            })
        },
        //  消息中心-消息删除
        deleteMsg(params) {
            api.deleteMsg(params).then((res) => {
                if (res.data.error_code === 0) {
                    this.$message({
                        type: 'success',
                        message: this.delTopic
                    });
                    this.getMsgList(1)
                }
                if (res.data.error_code === 1000001) {
                    this.$message({
                        message: res.data.error_msg,
                        type: 'warning'
                    });
                }
            }).catch((err) => {
                this.$message.error(err);
            })
        },
        //  消息中心-标记已读
        read(params, bul, item) {
            api.read(params).then((res) => {
                if (bul) {
                    this.$router.push({
                        path: item.link
                    })
                } else {
                    if (res.data.error_code === 0) {
                        this.$message({
                            type: 'success',
                            message: this.readTopic
                        });
                        this.getMsgList(1)
                    }
                    if (res.data.error_code === 1000001) {
                        this.$message({
                            message: res.data.error_msg,
                            type: 'warning'
                        });
                    }
                }
            }).catch((err) => {
                this.$message.error(err);
            })
        }
    }
}
</script>

<style scoped>
.classMsg {
    height: 100%;
    padding: 0 0 50px 0;
}

.flex {
    display: flex;
}

.spa-aro {
    justify-content: space-around;
}

.jus-con {
    justify-content: center;
}

.spa-bet {
    justify-content: space-between;
}

.ali-ite {
    align-items: center;
}

.point {
    cursor: pointer;
}

.left-tab {
    flex-grow: 1;
    height: 100%;
    margin: 0 10px 0 0;
    background-color: #fff;
}

.right-list {
    flex-grow: 5;
    height: 100%;
    background-color: #fff;
}

.font12 {
    font-size: 12px;
}

.font13 {
    font-size: 13px;
}

.colAEAEAE {
    color: #AEAEAE;
}

.tab-title {
    height: 50px;
    margin: 0 0 15px 0;
    padding: 20px 25px 0 25px;
}

.font14 {
    font-size: 14px;
}

.tab-btn {
    margin: 0 10px 0 0;
    padding: 7px 21px;
    border: 1px solid rgba(204, 204, 204, 1);
}

.tab-btn:hover {
    color: #F56C6C;
    background-color: #FEF0F0;
    border: 1px solid #F56C6C;
}

.tabs {
    padding: 15px;
}

.tab-content {
    padding: 15px 0 15px 15px;
}

.tab-item {
    height: 60px;
    margin: 0 0 10px 0;
    padding: 0 10px;
}

.tab-item:hover {
    background-color: #f9f9f9;
}

.mr5 {
    margin: 0 5px 0 0;
}

.colFF5353 {
    color: #FF5353;
    background-color: #f9f9f9;
}

.con-check {
    width: 16px;
    height: 16px;
    margin: 0 10px 0 0;
    line-height: 16px;
    text-align: center;
    border: 1px solid #999;
}

.on {
    color: #fff;
    background-color: #E94747;
    border: 1px solid #fff;
}

.cen-title {
    margin: 0 0 0 30px;
}

.cen-msg {
    margin: 20px 0 0 0;
}

.cen-date {
    position: absolute;
    left: 600px;
}

.cen-item {
    position: relative;
    padding: 20px 20px 20px 0;
    border-bottom: 1px solid rgba(234, 234, 234, 1);
}

.cen-item:hover {
    background-color: #f9f9f9;
}

.read {
    color: rgba(17, 16, 16, 0.501960784313725);
}

.unread {
    color: #E94747;
}

.idRead {
    margin: 0 15px 0 0;
}

.noData {
    margin: 240px 0;
    color: #999;
    text-align: center;
}

.tab-btns {
    visibility: hidden;
}

.rolling {
    height: 700px;
    margin: 20px 0 0 0;
    overflow-y: auto;
    word-wrap: break-word;
    word-break: break-all;
    scrollbar-width: none;
}

.rolling::-webkit-scrollbar {
    width: 0;
}

.rolling::-moz-scrollbar {
    width: 0;
}

.rolling::-ms-scrollbar {
    width: 0;
}

.rolling::-o-scrollbar {
    width: 0;
}

.pageStyle {
    margin: 20px;
    text-align: right;
    text-align: center;
}

.font16 {
    font-size: 16px;
}

.hideFun {
    width: 24px;
    height: 24px;
    line-height: 24px;
    text-align: center;
    border-radius: 50%;
}

.hideFun:hover {
    background-color: #f9f9f9;
}
</style>
