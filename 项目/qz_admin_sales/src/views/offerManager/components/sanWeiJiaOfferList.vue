<template>
    <div class="sanWeiJia-offer-list">
        <div class="search">
            <div><el-button type="success" @click="handleAdd">添加</el-button></div>
        </div>
        <div class="qian-main">
            <el-table
                    v-loading="loading"
                    :data="tableData"
                    border
            >
                <el-table-column
                        align="center"
                        prop="cooperation_name"
                        label="合作类型"
                />
                <el-table-column
                        align="center"
                        prop="quote_type"
                        label="报价类型"
                />
                <el-table-column
                        align="center"
                        prop="cooperation_price"
                        label="合作报价"
                />
                <el-table-column
                        align="center"
                        label="操作"
                        width="200"
                >
                    <template slot-scope="scope">
                        <span class="edit-btn" @click="handleEdit(scope.row)">编辑</span>
                        <span class="check-btn" @click="handleCheckRecord(scope.row)">记录</span>
                        <span class="del-btn" @click="handleDel(scope.row)">删除</span>
                    </template>
                </el-table-column>
            </el-table>
        </div>
    </div>
</template>

<script>
    import { fetchOtherOfferList, fetchOtherOfferDel } from '@/api/memberReport'
    import { fetchCityList } from '@/api/common'
    import { fortmatDate } from '@/utils/index'
    export default {
        name: "sanWeiJiaOfferList",
        data() {
            return {
                tableData: [],
                loading: false
            }
        },
        created() {
            this.getOtherOfferList()
        },
        methods: {
            getOtherOfferList() {
                this.loading = true
                fetchOtherOfferList().then(res => {
                    if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                        if (res.data.data.list.length <= 0 && this.currentPage > 1) {
                            this.currentPage--
                            this.getOtherOfferList()
                        } else {
                            this.tableData = []
                            res.data.data.list.forEach((item, index) => {
                                this.tableData.push(item)
                            })
                            this.loading = false
                        }
                    } else {
                        this.totals = 0
                        this.loading = false
                    }
                })
            },
            handleAdd() {
                const {href} = this.$router.resolve({
                    name: 'sanWeiJiaOfferAdd',
                    path: "/offerManager/sanWeiJiaOfferAdd",
                })
                window.open(href, '_blank')
            },
            handleEdit(obj) {
                if(!obj || !obj.id) {
                    return
                }
                const {href} = this.$router.resolve({
                    name: 'sanWeiJiaOfferAdd',
                    path: "/offerManager/sanWeiJiaOfferAdd",
                    query: {
                        id: obj.id
                    }
                })
                window.open(href, '_blank')
            },
            handleCheckRecord(obj) {
                if(!obj || !obj.id) {
                    return
                }
                const {href} = this.$router.resolve({
                    name: 'swjOfferRecordList',
                    path: "/offerManager/sanWeiJiaRecordList",
                    query: {
                        id: obj.id
                    }
                })
                window.open(href, '_blank')
            },
            handleDel(obj) {
                this.$confirm('确认删除该报价?', '提示', {
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
                fetchOtherOfferDel({
                    id: obj.id
                }).then(res => {
                    if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                        this.$message({
                            type: 'success',
                            message: '删除成功'
                        })
                        this.getOtherOfferList()
                    }else{
                        this.$message.error(res.data.error_code)
                    }
                }).catch(res => {
                    this.$message.error('网络异常，请稍后再试')
                })
            }
        }
    }
</script>

<style rel="stylesheet/scss" lang="scss">
    .sanWeiJia-offer-list {
        width: 100%;
        padding: 20px;
        box-sizing: border-box;
        .search {
            background: #fff;
            padding: 0px;
            box-sizing: border-box;
            line-height: 36px;
            border-top:0;
        }
        .qian-main {
            margin: 20px -20px 0;
            padding: 0px;
            box-sizing: border-box;
            background-color: #fff;
            border-top:0;
        }
        .el-pagination{
            text-align: center;
            margin-top: 20px;
        }
        .fl {
            float: left;
        }
        .mr20 {
            margin-right: 20px;
        }
        .edit-btn{
            color: #e6a23c;
            cursor: pointer;
        }
        .del-btn{
            color: #f56c6c;
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
    }
</style>
