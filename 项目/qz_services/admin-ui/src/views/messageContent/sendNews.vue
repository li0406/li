<template>
  <div class="send-news">
	  	<div class="search">
			<div class="addmsg">
				<el-button type="primary"  @click="handleAddMsg" class="btn">新增消息内容</el-button>
			</div>
			
			<div class="tab">
				<span @click="handleClick('ios')" :class="{'cur':tabShow === 'ios'}">统计列表-iOS</span>
				<span @click="handleClick('android')" :class="{'cur':tabShow === 'android'}" >统计列表-Android</span>
			</div>
	  	</div>
		<!--  -->
		<div class="box">
			<div>
				<iosList :systemType='system' />
			</div>
		</div>
  </div>
</template>

<script>
import iosList from './components/iosList'
export default {
	name: 'sendNews',
	components: {
	  iosList
    },
	data() {
		return {
            tabShow:'ios', //tab栏切换
            system:'1',
        }
	},
	created() {
	
	},
	methods: {
		//tab切换
		handleClick(tag) {
            if(tag == 'ios'){
                this.system = '1'
            }else{
                this.system = '2'
            }
            this.tabShow = tag
        },
       
        // 跳转新增页
        handleAddMsg(){           
            this.$router.push({
                path: 'createMsg'
            })
        },
      },
}
</script>

<style rel="stylesheet/scss" lang="scss">
  .send-news {
    width: 100%;
    padding: 20px;
    box-sizing: border-box;
    .category{
      margin-bottom: 20px;
    }
    .el-date-editor{
      .el-range-separator{
        padding: 0;
      }
    }
    .search {
      background: #fff;
      border-top: 3px solid #999;
      box-sizing: border-box;
    }
    .el-pagination{
      text-align: center;
      margin-top: 20px;
    }
    .el-dialog__header{
      border-bottom: 1px dashed #CCC;
    }
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
	  padding-top: 15px;
    }
	
	.avatar-uploader .el-upload {
		border: 1px dashed #d9d9d9;
		border-radius: 6px;
		cursor: pointer;
		position: relative;
		overflow: hidden;
	}
	.avatar-uploader .el-upload:hover {
		border-color: #409EFF;
	}
	.avatar-uploader-icon {
		font-size: 28px;
		color: #8c939d;
		width: 50px;
		height: 50px;
		line-height: 50px;
		text-align: center;
	}
	.avatar {
		width: 178px;
		height: 178px;
		display: block;
	}
	.el-upload__tip{
		margin-top: 0px;
	}
	.addmsg{
		width:100%;
		height: 50px;
		line-height: 50px;
		padding-top: 15px;
	}
	.btn{
		float: right;
		margin: 5px 20px 0 0;
	}
	// tab栏
	.box{
		width:100%;
	}
	.tab{
		margin-top: 20px;
		font-size: 16px;
		color:#333;
		cursor: pointer;
		padding-left: 20px;
	}
	.tab span{
		padding: 10px 15px;
	}
	.tab span.cur{
		color:#409EFF;
		border-bottom: 3px solid #409EFF; 
		// margin-left: 20px;
	}
  }
</style>
