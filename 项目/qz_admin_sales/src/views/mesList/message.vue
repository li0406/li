<template>
	<div class="meslist">
		<div class="main">
			<div>
                <el-button v-for="item in typeName" :key="item.id" @click="handleTab(item.id)" :class="{'cur':item.id === typeId}">
                    {{item.name}} ({{item.msg_count}}) </el-button>
                
			</div>
            <div class="msg" v-for="(items, index) in dataList" :key="index" v-show="items.type_id == typeId">
                <div class="quan"  >
                    <div class="msg-cont">
                        <!-- <div class="box">
                            <el-checkbox-group v-model="checkList"  @change="handleCheck(items.id)">
                                <el-checkbox :label="items.id"  name="msg"></el-checkbox>
                            </el-checkbox-group>
                        </div> -->
                        <div class="msg-top">
                            <span class="msg-title">{{items.title}}</span>
                            <span class="msg-date">{{items.created_date}}</span>
                        </div>
                        <div @click="tabdir(items.link_url)" class="msg-down">
                            {{items.content}}
                            <span>>></span>
                        </div>
                        <div class="fgx"></div>
                    </div>
                    <span class="del" @click="handleDel(items.id)">删除</span>	
                </div>
            </div>
			<!-- <el-checkbox  class="yidu" v-model="yiduArr" @change="handelSign()">标记已读</el-checkbox> -->
           <div  class="fenye">
                <el-pagination
                :current-page="currentPage"
                :page-size="page_size"
                layout="total, prev, pager, next, jumper"
                :total="totals"
                @size-change="handleSizeChange"
                @current-change="handleCurrentChange"
            />
           </div>
		</div>
		<!-- <div class="main">
			<div class="mes-item" v-for="(item, index) in dataList" :key="index">
				<div class="mes-time">{{item.date}}</div>
				<div>
					<div v-for="only in item.children" :key="only.id">
						<div class="mes-title">
							{{only.title}}
							<span>{{only.created_time}}</span>
						</div>
						<div @click="tabdir(only.link_url)" class="mes-nav">
							{{only.content}}
							<span>>></span>
						</div>
					</div>
				</div>
			</div>
			<div
				class="mes-more"
				v-if="nomore == 2"
				@click="tapMore"
				v-loading.fullscreen.lock="fullscreenLoading"
			>加载更多 &nbsp;
				<i class="iconfont xiah"></i>
			</div>
			<div v-if="nomore == 3" class="mes-more"></div>
		</div>-->
	</div>
</template>

<script>
import { getTypeList, getList, delList, setRead} from "@/api/home";
import qs from 'qs'
export default {
	name: "Message",
	data() {
		return {
            typeId: 1, //消息类型id
            typeName:[], //类型名称
			count: 0,
			dataList: [], //列表
			page: 1,
			nomore: 2,
			fullscreenLoading: false,
			checkList: [], //选中
			yiduArr:[], // 标记选中
			page_size:0, //每页条数
			currentPage: 1, //当前页
            totals: 0, //总条数
            msg_id:'', //消息id
		};
    },
	created() {
        this.getTypelist();
        this.getlist();
	},
  	mounted() {},
  	methods: {
        //tab切换
        handleTab(type_id){ 
            this.typeId = type_id;
            this.getlist();
        },
        //获取类型
        getTypelist(){
            getTypeList().then(res => {
                if (parseInt(res.status) === 200 &&parseInt(res.data.error_code) === 0) {
                    this.typeName = res.data.data.list;
                }
            })
        },
        //获取列表
		getlist() {
            var query = {
                page:this.currentPage,
                type_id:this.typeId,
            }
			getList(query).then(res => {
				if (parseInt(res.status) === 200 &&parseInt(res.data.error_code) === 0) {
                    console.log('消息列表',res.data)
                    this.dataList = res.data.data.list;
					if(res.data.data.list.length <= 0 && this.currentPage > 1){
						  this.currentPage--;
						  this.getlist();
					}else{
						this.dataList = []
                        this.dataList = res.data.data.list;
                        this.currentPage = res.data.data.page.page_current
                        this.page_size = res.data.data.page.page_size
                        this.totals = res.data.data.page.total_number
					}
				} else {
					this.$message({
						message: res.data.error_msg,
						type: "error"
					});
				}
			});
        },
        //删除
		handleDel(id){
            this.$confirm('确认删除该条消息?', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.handleDelAjax(id)
            }).catch(() => {
                this.$message({
                    type: 'info',
                    message: '已取消删除'
                })
            })
        },
        
        handleDelAjax(id) {
            var param = {'msg_id':id }
            var canshu = qs.stringify(param)
            delList(canshu).then(res => {
                if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                    this.$message({
                        message: '删除成功',
                        type: 'success'
                    })
                    this.getlist()
                }else{
                    this.$message.error(res.data.error_code)
                }
            })
        },
        //选中
		handleCheck(val){
            const id = val
            if (this.checkList.includes(val)) {
                this.$confirm('确认该条信息标记已读？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    setRead({ id: id }).then(res => {
                        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                            this.$message({
                                type: 'success',
                                message: '标记成功!',
                                duration: '500'
                            })
                            this.getlist()
                        }else{
                            this.$message.error(res.data.error_code)
                        }
                    })
                }).catch(() => {
                    this.checkList = []
                    this.$message({
                        type: 'info',
                        duration: '500',
                        message: '已取消标记'
                    })
                })
            } else {
                console.log('没选中');
            }
        },
       
        //标记已读
        handelSign(){
           
        },
        //已读参数
		handleYd(){
			
		},
		tapMore() {
			this.getlist();
			this.fullscreenLoading = true;
			setTimeout(() => {
				this.fullscreenLoading = false;
			}, 2000);
		},
		tabdir(e) {
			if (e != "") {
				this.$router.push({ path: e });
			}
		},
		init() {},
		 // 分页处理
		handleSizeChange() {
			this.currentPage = 1
			this.getlist()
		},
		handleCurrentChange(val) {
			this.currentPage = val
			this.getlist()
        },
  	}
}
</script>

<style scoped>
.meslist {
	width: 60%;
	padding: 20px;
	font-size: 14px;
	color: #606266;
	-webkit-box-sizing: border-box;
	box-sizing: border-box;
}
.meslist .main {
	background: #fff;
	padding: 20px;
	border-top: 3px solid #999;
	-webkit-box-sizing: border-box;
	box-sizing: border-box;
	position: relative;
    overflow: hidden;
}
.mes-item {
	line-height: 32px;
	border-bottom: 1px solid #d7d7d7;
	padding: 12px 0;
}
.mes-item .mes-title {
	color: #009999;
	font-weight: bold;
	display: flex;
	align-items: center;
	justify-content: space-between;
}
.mes-item .mes-title span {
	color: #a1afc7;
	text-align: right;
	font-weight: normal;
}
.mes-time {
	color: #333333;
	font-weight: bold;
}
.mes-nav {
	display: block;
	width: 92%;
	overflow: hidden;
	cursor: pointer;
}
.mes-nav span {
	color: #009999;
}
.mes-nav:hover {
	color: #6a798e;
}
.mes-more {
	display: flex;
	width: 100%;
	height: 56px;
	font-size: 16px;
	color: #009999;
	font-weight: bold;
	align-items: center;
	justify-content: center;
	cursor: pointer;
}
@font-face {
	font-family: "iconfont";
	src: url("//at.alicdn.com/t/font_844055_pjf0dfa6x1.eot");
	src: url("//at.alicdn.com/t/font_844055_pjf0dfa6x1.eot?#iefix")
		format("embedded-opentype"),
	url("//at.alicdn.com/t/font_844055_pjf0dfa6x1.woff2") format("woff2"),
	url("//at.alicdn.com/t/font_844055_pjf0dfa6x1.woff") format("woff"),
	url("//at.alicdn.com/t/font_844055_pjf0dfa6x1.ttf") format("truetype"),
	url("//at.alicdn.com/t/font_844055_pjf0dfa6x1.svg#iconfont") format("svg");
}
.mes-more .iconfont {
	font-family: "iconfont" !important;
	font-size: 14px;
	font-style: normal;
	-webkit-font-smoothing: antialiased;
	-webkit-text-stroke-width: 0.2px;
	-moz-osx-font-smoothing: grayscale;
}
.mes-more .xiah {
	display: inline-block;
	width: 20px;
	height: 20px;
	background: url("data:image/png;base64,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")
	no-repeat center;
	background-size: 100% 100%;
}
.msg{
	width:100%;
	height: auto;
	padding: 20px 15px;
	box-sizing: border-box;
}
.box{
	width:2%;
	float: left;
	margin-top: 20px;
	margin-left: -25px;
}

.msg-cont{
	width:90%;
	height: auto;
	float: left;
	padding:0 25px;
	box-sizing: border-box;
}
.msg-cont .msg-top{
	padding-bottom: 10px;
}
.msg-cont .msg-top .msg-date{
	float: right;
}
.msg-down{
	line-height: 20px;
	padding-bottom: 15px;
    cursor: pointer;
}
.msg-down:hover{
    color:red;
    cursor: pointer;
}
.del{
	width:8%;
	text-align: center;
    float: left;
    margin-top: 25px;
    color: #27b2ea;
	cursor: pointer;
}

.fgx{
	width:110%;
	height: 1px;
	background: #e6e8e6;
	margin-bottom: 15px;
}
.yidu{
    width: 100%;
    padding: 10px 15px;
}
.fenye{
    padding: 10px 15px;
    float: left;
    margin-left: 20%;
}
.box >>> .el-checkbox__label{
    display: none !important;
    padding-left: 10px;
    line-height: 19px;
    font-size: 14px;
    color:red
}
.cur{
    color: #409EFF;
    border-color: #c6e2ff;
    background-color: #ecf5ff;
    display: inline-block;
}
</style>