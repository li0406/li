<?php
/**
 * index模块的路由（前台）
 */

/**
 * V1版本接口
 */
Route::group('', function () {
    Route::group('v1', function () {
        Route::group('report', function () {
            Route::rule('add_user', 'index/v1.Visit/add_contacts', 'POST')->append(['operate'=>'add']);
            Route::rule('edit_user', 'index/v1.Visit/add_contacts', 'POST')->append(['operate'=>'edit']);
            Route::rule('list_user', 'index/v1.Visit/list_contacts', 'GET');
            Route::rule('add_visit', 'index/v1.Visit/add_visit_record', 'POST');
            Route::rule('company_import', 'index/v1.Visit/company_import', 'POST');
            Route::rule('list_visit', 'index/v1.Visit/list_visit_record', 'GET');
            Route::rule('add_first_visit', 'index/v1.Visit/add_first_visit', 'POST');//获取公司列表
            Route::rule('get_contract', 'index/v1.Visit/get_contract', 'GET');//获取会员公司合同
        });

        Route::group('voip', function () {
            Route::rule('call', 'index/v1.Voip/voipCall', 'POST');//拨打电话
            Route::rule('info', 'index/v1.Voip/getVoipRecordList', 'GET');//电话记录
            Route::rule('order_record', 'index/v1.Voip/getOrderRecordList', 'GET');//订单电话记录
        });

        /*************** 销售权限 ***************/
        Route::group('saler', function () {
            Route::rule('signlist', 'index/v1.Saler/signlist', 'GET');      //会员签单统计 -- 签单统计列表
            Route::rule('signinfo', 'index/v1.Saler/signinfo', 'GET');      //会员签单统计 -- 签单统计/签单订单 订单信息
            Route::rule('citys', 'index/v1.Saler/citys', 'GET');
            Route::rule('cityinfo', 'index/v1.Saler/cityinfo', 'GET');
            Route::rule('savecitys', 'index/v1.Saler/savecitys', 'POST');
            Route::rule('clearcity', 'index/v1.Saler/clearcity', 'POST');
            Route::rule('list_sale', 'index/v1.Saler/user_list_sale', 'GET');//获取销售人员列表
        });

        /*************** 公司会员 ***************/
        Route::group('company', function () {
            Route::rule('info', 'index/v1.Company/company_info', 'GET');//获取公司信息
            Route::rule('list', 'index/v1.Company/company_list', 'GET');//获取公司列表
            Route::rule('old_list', 'index/v1.Company/company_old_list', 'GET');//获取旧公司列表
	        Route::rule('list_company', 'index/v1.Company/company_list_all', 'GET');//获取客户维护列表
	        Route::rule('is_repeat', 'index/v1.Company/company_is_repeat', 'GET');//验证会员重复

            Route::rule('no_vip_citys', 'index/v1.Company/no_vip_citys', 'GET'); //获取无会员城市列表
            Route::rule('no_vip_city_orders', 'index/v1.Company/no_vip_city_orders', 'GET'); //获取无会员城市订单列表

            Route::rule('expire_remind', 'index/v1.Company/company_expire', 'GET');//验证会员重复
            Route::rule('expire_remind_pc', 'index/v1.Company/expire_remind_pc', 'GET');//验证会员重复

            Route::rule('allot_orders', 'index/v1.Company/getCompanyAllotOrders', 'GET');// 获取装修公司分单数
            Route::rule('vip_history_list', 'index/v1.Company/getCompanyVipHistoryList', 'GET');// 获取装修公司历史合同时间

            Route::rule('check_user', 'index/v1.Company/check_user', 'GET');//验证会员重复
            Route::rule('register', 'index/v1.Company/registerCompany', 'POST');//注册装修公司接口
        });

        // 工作汇报相关接口分组
        Route::group('reportwork', function () {
            Route::get('list', 'index/v1.ReportWork/rwList');
            Route::post('add', 'index/v1.ReportWork/rwAdd');
            Route::post('reply', 'index/v1.ReportWork/rwReply');
            Route::get('detail', 'index/v1.ReportWork/rwDetail');
        });

        /************* 订单统计相关 **************/
        Route::group('orders', function () {
            Route::get('fen_companys', 'index/v1.Orders/fen_companys');
            Route::get('fen_order_info', 'index/v1.Orders/fen_order_info');
            Route::get('read_orders', 'index/v1.Orders/read_orders');

            Route::rule('qd', 'index/v1.Orders/qdOrders', 'GET');   //签单订单 —— 订单列表
            Route::rule('qdup', 'index/v1.Orders/qdup', 'POST');    //签单订单 —— 签单审核
            Route::rule('qdcacel', 'index/v1.Orders/qdup', 'POST'); //签单订单 —— 取消签单

            Route::rule('consultation$', 'index/v1.Orders/consultation', 'GET');   //签单订单 —— 订单列表
            Route::rule('consultation/show$', 'index/v1.Orders/consultation_detail', 'GET'); //签单订单 —— 签单详情
            Route::rule('consultation/check$', 'index/v1.Orders/consultation_check', 'POST'); //签单订单 —— 签单审核


            Route::get('orderreview', 'index/v1.Orders/orderReview');       //统计管理-城市分单统计
            Route::get('orderreviewouttofile', 'index/v1.Orders/orderReviewWritertoFile');       //统计管理-城市分单统计导出excel

            Route::get('qiandan', 'index/v1.Orders/orderQiandan');       //签单审核
            Route::get('qiandan_no_pass', 'index/v1.Orders/noPassQiandanOrder');//签单审核不通过列表
            Route::get('qiandan_detail', 'index/v1.Orders/orderQiandanDetail');//签单审核信息
        });

        // 订单相关接口分组
        Route::group('order', function () {
            Route::get('list', 'index/v1.Order/orderList'); // 全部订单
            Route::get('detail', 'index/v1.Order/orderDetail'); // 订单详情
            Route::get('options', 'index/v1.Order/orderOptions'); // 订单列表筛选项
            Route::get('remedy', 'index/v1.Order/remedyOrder');//补单列表页
            Route::get('info', 'index/v1.Order/orderInfo');//订单基本信息
            Route::get('company_info', 'index/v1.Order/orderCompanyInfo');//订单装修公司信息(已分配/过期公司)
            Route::get('company_list', 'index/v1.Order/orderCompanyList');//当前/相邻 城市公司列表
            Route::post('allot', 'index/v1.Order/RemedyFenCompany');//补单分配公司
            Route::post('unremedy', 'index/v1.Order/unRemedyOption');//不补操作
            Route::get('auditorder', 'index/v1.Order/auditOrder');//质检订单

            Route::get('signlist', 'index/v1.Order/getRoundOrderSignList');//轮单签单订单列表
            Route::get('signinfo', 'index/v1.Order/getRoundOrderSignInfo');//轮单签单订单信息
        });

        
        /************* 统计管理 **************/
        Route::group('statistics', function () {
            Route::rule('company_orders', 'index/v1.Statistics/user_statistics', 'GET');//会员分单/签单统计
            Route::rule('unread_orders', 'index/v1.Statistics/unread_statistics', 'GET');//未读订单统计

            Route::rule('consult/count$', 'index/v1.Statistics/consultation_count', 'GET');   //城市签单汇总统计
            Route::get('order/city', 'index/v1.Statistics/getCityOrderStat');//城市分单统计
            Route::get('cityvipcount', 'index/v1.Statistics/cityVipCount'); //城市会员哦统计
        });

        /************* 后台用户 **************/
        Route::group('user', function () {
            Route::rule('info', 'index/v1.Adminuser/user_info', 'GET');//后台用户信息
            Route::rule('job', 'index/v1.Adminuser/user_job', 'GET');//后台用户工作列表页
        });

        // 小程序菜单配置
        Route::group('appletmenu', function () {
            Route::get('list', 'index/v1.AppletMenu/menuList'); // 小程序菜单列表
            Route::get('detail', 'index/v1.AppletMenu/menuDetail'); // 小程序菜单详情
            Route::post('save', 'index/v1.AppletMenu/menuSave'); // 小程序菜单编辑
            Route::post('enabled', 'index/v1.AppletMenu/menuEnabled'); // 小程序菜单启用/停用
            Route::get('sysmenu', 'index/v1.AppletMenu/sysmenu'); // 获取平台菜单
            Route::get('menus', 'index/v1.AppletMenu/menus'); // 权限菜单（小程序使用）
        });

        // 营业执照
        Route::group('businesslicence', function () {
            Route::get('list', 'index/v1.BusinessLicence/blList'); // 营业执照审核列表
            Route::get('uplist', 'index/v1.BusinessLicence/uploadList'); // 营业执照工商查询上传
            Route::post('upfile', 'index/v1.BusinessLicence/uploadFile'); // 营业执照图片上传
            Route::post('upclean', 'index/v1.BusinessLicence/uploadClean'); // 营业执照上传清空
            Route::post('examfirst', 'index/v1.BusinessLicence/examFirst'); // 营业执照审核（初审）
            Route::post('examfinal', 'index/v1.BusinessLicence/examFinal'); // 营业执照审核确认（终审）
            Route::post('examreset', 'index/v1.BusinessLicence/examReset'); // 营业执照重审、撤回
            Route::get('citymanager', 'index/v1.BusinessLicence/citymanager'); // 城市负责人

            Route::get('statistics', 'index/v1.BusinessLicence/statistics'); // 营业执照统计
        });

        // 基础信息设置相关接口分组
        Route::group('basicinfo', function () {
            Route::get('company_list', 'index/v1.Basicinfo/companyList'); // 会员公司设置列表
            Route::get('company_detail', 'index/v1.Basicinfo/companyDetail'); //  会员公司设置详情
            Route::post('company_edit', 'index/v1.Basicinfo/companyEdit'); // 会员公司设置编辑
            Route::post('company_pnp_on', 'index/v1.Basicinfo/companyPnpOn'); // 会员公司设置-axb模式批量开启
            Route::post('company_pnp_off', 'index/v1.Basicinfo/companyPnpOff'); // 会员公司设置-axb模式批量关闭

            Route::get('salecity_info', 'index/v1.Basicinfo/salecityInfo'); // 稽核操作分配订单->城市设置（获取页面数据）
            Route::post('salecity_edit', 'index/v1.Basicinfo/salecityEdit'); // 稽核操作分配订单->城市设置（保存）

            //公司标识
            Route::rule('company_tag_list', 'index/v1.CompanyTag/companyList', 'GET');//列表页
            Route::rule('company_tag', 'index/v1.CompanyTag/companyFixationTag', 'GET');//装修公司固定标签
            Route::rule('company_tag_add', 'index/v1.CompanyTag/addCompanyTag', 'POST');//装修公司添加标签


            // 地图
            Route::rule('order_map', 'index/v1.Basicinfo/order_map', 'GET');    // 显示地图所需信息
            Route::rule('mark', 'index/v1.Basicinfo/mark', 'POST');    // 添加/编辑地图上的一个标记点
            Route::rule('delmark', 'index/v1.Basicinfo/delMark', 'POST');    // 删除地图上的一个标记点

        });

		// 微信通知发送记录
        Route::group('wxlog', function () {
            Route::get('ordersend', 'index/v1.Wxlog/ordersend'); // 微信通知发送记录
        });

        // 销售报备：报价管理
        Route::group('quote', function () {
            Route::rule('get_city_quote', 'index/v1.Quote/get_city_quote', 'get');          //城市报价列表
            Route::rule('get_other_quote', 'index/v1.Quote/get_other_quote', 'get');        //三维家ERP报价列表
            Route::rule('add_city_quote', 'index/v1.Quote/add_city_quote', 'post');         //城市报价保存/修改
            Route::rule('add_other_quote', 'index/v1.Quote/add_other_quote', 'post');       //三维家ERP报价保存/修改
            Route::rule('find_city_quote', 'index/v1.Quote/find_city_quote', 'get');        //城市报价详情
            Route::rule('find_other_quote', 'index/v1.Quote/find_other_quote', 'get');      //三维家ERP报价详情
            Route::rule('del_city_quote', 'index/v1.Quote/del_city_quote', 'post');         //删除城市报价
            Route::rule('del_other_quote', 'index/v1.Quote/del_other_quote', 'post');       //删除三维家ERP报价
            Route::rule('get_quote_log', 'index/v1.Quote/get_quote_log', 'get');            //【城市报价|三维家ERP】报价记录
            Route::rule('read_city_quote', 'index/v1.Quote/read_city_quote', 'post');       //城市报价导入
            Route::rule('history_city_quote', 'index/v1.Quote/history_city_quote', 'get');       //历史城市报价
        });

        // 新销售报备
        Route::group('sale_report', function () {
            Route::get('list', 'index/v1.Report/reportList');                   //报备列表
            Route::rule('baobei', 'index/v1.Report/index', 'get');              //销售报备列表
            Route::rule('save', 'index/v1.Report/save', 'post')->middleware(['EditReportStatus']);                //销售报备保存/修改
            Route::rule('del', 'index/v1.Report/del', 'post');                  //销售报备删除
            Route::rule('set_status', 'index/v1.Report/set_status', 'post')->middleware(['EditReportStatus']);    //修改报备记录状态
            Route::rule('show', 'index/v1.Report/show_report', 'get');          //报备详情
            Route::rule('log', 'index/v1.Report/show_log', 'get');              //报备记录审核日志
            Route::get('test_company', 'index/v1.Report/test_company');         //网店代码检测
            Route::get('find_delay_company', 'index/v1.Report/findDelayCompanyInfo');     //获取延期装修公司信息
            Route::get('options', 'index/v1.Report/getEditOptions');         //获取编辑筛选项
            Route::post('recall', 'index/v1.Report/setRecall')->append(["status" => 7])->middleware(['EditReportStatus']); //驳回

            Route::rule('kf_voucher', 'index/v1.Report/kf_voucher', 'post');         //需要客服上传凭证
            Route::rule('kf_voucher_upload', 'index/v1.Report/kf_voucher_upload', 'post');//客服上传凭证
            Route::rule('check_contract', 'index/v1.Report/checkContract', 'post');//验证

            Route::rule('unpasslog', 'index/v1.Report/getUnpassLogList', 'get'); // 报备不通过记录列表

            // 小报备部分
            Route::rule('payment/list', 'index/v1.ReportPayment/getList', 'get'); // 小报备列表
            Route::rule('payment/detail', 'index/v1.ReportPayment/getDetail', 'get'); // 小报备详情
            Route::rule('payment/editinfo', 'index/v1.ReportPayment/getEditInfo', 'get'); // 获取小报备编辑信息
            Route::rule('payment/save', 'index/v1.ReportPayment/saveInfo', 'post'); // 小报备添加、编辑
            Route::rule('payment/delete', 'index/v1.ReportPayment/deleteInfo', 'post'); // 小报备删除
            Route::rule('payment/submit', 'index/v1.ReportPayment/submitInfo', 'post'); // 小报备提交
            Route::rule('payment/recall', 'index/v1.ReportPayment/submitRecall', 'post'); // 小报备提交撤回
            Route::rule('payment/rollback', 'index/v1.ReportPayment/examRollback', 'post'); // 小报备回滚
            Route::rule('payment/related', 'index/v1.ReportPayment/setRelated', 'post'); // 小报备关联大报备
            Route::rule('payment/compare', 'index/v1.ReportPayment/getRelatedCompare', 'get'); // 大小报备关联比较
            Route::rule('payment/other_person_list', 'index/v1.ReportPayment/getSalerOtherPerson', 'get'); // 其它业绩人
            Route::rule('payment/options', 'index/v1.ReportPayment/getOptions', 'get'); // 小报备一些筛选项列表

            Route::rule('payment/examlist', 'index/v1.ReportPayment/getExamList', 'get'); // 小报备审核列表
            Route::rule('payment/examkflist', 'index/v1.ReportPayment/getKfExamList', 'get'); // 小报备客服审核列表
            Route::rule('payment/exampass', 'index/v1.ReportPayment/setExamPass', 'post'); // 小报备审核通过
            Route::rule('payment/examfail', 'index/v1.ReportPayment/setExamFail', 'post'); // 小报备审核不通过

            Route::rule('payment/report_select', 'index/v1.Report/getSelectList', 'get'); // 大报备选择列表
            Route::rule('company/report_list', 'index/v1.Report/getCompanyReportList', 'get'); // 会员公司大报备列表
            Route::rule('related/report_payment_list', 'index/v1.ReportPayment/getRelatedReportList', 'get'); // 大报备关联小报备列表
            Route::rule('orderback/report_payment_list', 'index/v1.ReportPayment/getOrderBackReportList', 'get'); // 订单关联小报备列表
            Route::rule('payment/log', 'index/v1.ReportPayment/checkLog', 'get');//小报备审核记录

            // Route::rule('modify_history', 'index/v1.Report/modifyHistory', 'get');
        });

        // 消息中心
        Route::group('msgsystem', function(){
            Route::get('getremind', 'index/v1.MsgSystem/getRemind'); // 获取未读消息
            Route::get('getlist', 'index/v1.MsgSystem/getList'); // 通知中心消息列表
            Route::get('getinfo', 'index/v1.MsgSystem/getInfo'); // 通知中心消息详情
            Route::get('get_type_list', 'index/v1.MsgSystem/getTypeList'); // 通知中心消息类型列表
            Route::post('setread', 'index/v1.MsgSystem/setRead'); // 通知中心-设置为已读
            Route::post('delete', 'index/v1.MsgSystem/deleteInfo'); // 通知中心-删除消息
        });

        // 回访单
        Route::group('visit', function(){
            Route::get('list', 'index/v1.CompanyVisit/getList'); // 获取分页列表
            Route::get('export', 'index/v1.CompanyVisit/exportList'); // 获取导出列表
            Route::get('options', 'index/v1.CompanyVisit/getOptions'); // 获取列表选项
            Route::get('add_options', 'index/v1.CompanyVisit/getAddOptions'); // 获取新增数据选项
            Route::post('add', 'index/v1.CompanyVisit/addInfo'); // 新增回访订单
            Route::post('delete', 'index/v1.CompanyVisit/deleteInfo'); // 撤回（删除）回访订单
            Route::get('detail', 'index/v1.CompanyVisit/getDetail'); // 回访订单详情
            Route::post('push', 'index/v1.CompanyVisit/pushInfo'); // 回访订单推送
            Route::post('new_push', 'index/v1.CompanyVisit/pushNewInfo'); // 新回访订单推送
        });


        // 获取联通云总机 通话录音
        Route::get('telcenter/recordurl', 'index/v1.Telcenter/cuctcallRecordUrl');

        // 小程序登录
        Route::post('applet/bind', 'index/v1.Wechat/appletBind');

        //工地直播
        Route::group('worksite', function(){
            Route::get("step_list", "index/v1.WorkSiteLive/stepList"); // 工地直播施工阶段
            Route::get("live_list", "index/v1.WorkSiteLive/liveList"); // 工地直播列表
            Route::get("live_detail", "index/v1.WorkSiteLive/liveDetail"); // 工地直播详情
            Route::get("info_list", "index/v1.WorkSiteLive/infoList"); // 施工信息列表
            Route::post("info_delete", "index/v1.WorkSiteLive/infoDelete"); // 施工信息删除
            Route::get("info_detail", "index/v1.WorkSiteLive/infoDetail"); // 施工信息详情
            Route::post("info_edit", "index/v1.WorkSiteLive/infoEdit"); // 施工信息编辑
            Route::get("live_qrcode", "index/v1.WorkSiteLive/getQrcode"); // 二维码
        });

        /********************团队管理****************************/
        Route::group('team', function () {
            Route::rule("getteamlist", "index/v1.Team/getTeamList","GET");//团队列表
            Route::rule("teamup", "index/v1.Team/getTeamUp","GET");//团队信息
            Route::rule("teamup", "index/v1.Team/setTeamUp","POST");//新增/编辑团队
            Route::rule("gettopteam", "index/v1.Team/getTopTeamList","GET");//下拉团队列表
            Route::rule("getteamtree", "index/v1.Team/getTeamTree","GET");//团队树结构
            Route::rule("getteammemberlist", "index/v1.Team/getTeamMember","GET");//团队树结构
            Route::rule("teammemberup", "index/v1.Team/setTeamMemberUp","POST");//添加/编辑团队成员
            Route::rule("getteammember", "index/v1.Team/getTeamMemberInfo","GET");//添加/编辑团队成员
            Route::rule("removeteammember", "index/v1.Team/removeTeamMember","POST");//添加/编辑团队成员
            Route::rule("teamstatusup", "index/v1.Team/teamStatusUp","POST");//添加/编辑团队成员
            Route::rule("select", "index/v1.Team/getSelectList","GET");//添加/编辑团队成员

            Route::rule('getperformancechart', 'index/v1.Team/getPerformanceChart', 'GET');//今日业绩图标
            Route::rule('getteamchart', 'index/v1.Team/getTeamChart', 'GET');//团队业绩统计
            Route::rule('getotherchart', 'index/v1.Team/getOtherChart', 'GET');//其他统计
            Route::rule('getmemberchart', 'index/v1.Team/getMemberChart', 'GET');//会员统计
            Route::rule('getteamindicatorschart', 'index/v1.Team/getTeamIndicatorsChart', 'GET');//团队业绩统计
            Route::rule('get_daily_achievement', 'index/v1.Team/getTeamDailyAchievement', 'GET');//单日团队业绩统计
        });
    
        // 装企咨询
        Route::group('consult', function () {
            Route::rule("options", "index/v1.CompanyConsult/getOptions", "GET"); // 装企咨询选项
            Route::rule("list", "index/v1.CompanyConsult/getConsultList", "GET"); // 装企咨询列表
            Route::rule("detail", "index/v1.CompanyConsult/getConsultDetail", "GET"); // 装企咨询详情
            Route::rule("deal", "index/v1.CompanyConsult/setConsultDeal", "POST"); // 装企咨询处理
            Route::rule("record/add", "index/v1.CompanyConsult/addConsultRrecord", "POST"); // 装企咨询新增跟进记录
            Route::rule("record/list", "index/v1.CompanyConsult/getConsultRrecordList", "GET"); // 装企咨询跟进历史记录
        });

        // 装企咨询
        Route::group('company', function () {
            // 会员资金管理
            Route::rule("account_list", "index/v1.CompanyAccount/getAccountList", "GET"); // 会员资金管理-列表
            Route::rule("account_log_list", "index/v1.CompanyAccount/getAccountLogList", "GET"); // 会员资金管理-收支明细
            Route::rule("account_log_total", "index/v1.CompanyAccount/getAccountLogTotal", "GET"); // 会员资金管理-收支明细统计
            
            // 装修公司轮单签单记录
            Route::rule("roundorder/sign_list", "index/v1.RoundOrder/getCompanySignList", "GET"); // 会员资金管理-签单记录
            Route::rule("roundorder/sign_total", "index/v1.RoundOrder/getCompanySignTotal", "GET"); // 会员资金管理-签单记录统计
        });

        // 轮单订单相关接口
        Route::group('roundorder', function () {
            Route::get('apply_list', 'index/v1.RoundOrder/getRoundApplyList'); // 补轮申请列表
        });

        //发票报备
        Route::group('invoice', function(){ // 发票相关
            Route::post('save', 'index/v1.Invoice/saveInfo'); //保存/编辑
            Route::post('submit', 'index/v1.Invoice/submitInfo'); // 提交
            Route::post('delete', 'index/v1.Invoice/deleteInfo'); // 删除
            Route::post('recall', 'index/v1.Invoice/recallInfo'); // 撤回
            Route::post('verify', 'index/v1.Invoice/verify'); // 审核
            Route::get('view', 'index/v1.Invoice/getInfo'); // 查看发票
            Route::get('list', 'index/v1.Invoice/getList'); // 发票列表
            Route::get('options', 'index/v1.Invoice/getOptions'); // 发票列表搜索选项
            Route::get('getPaymentInvoiceList', 'index/v1.Invoice/getPaymentInvoiceList'); // 小报备多次开票关联发票列表
        });

    })->middleware(['AdminAuth','RequestLog']);

    /************************业绩统计****************************/
    Route::group('v1', function () {
        Route::group('indicators', function () {
            Route::rule('upteamindicators', 'index/v1.Indicators/upTeamIndicators', 'POST');//团队指标上传
            Route::rule('indicatorsup', 'index/v1.Indicators/setIndicatorsUp', 'POST');//团队指标编辑
            Route::rule('upteammemberindicators', 'index/v1.Indicators/upTeamMemberIndicators', 'POST');//个人指标上传
            Route::rule('getteamindicators', 'index/v1.Indicators/getTeamIndicators', 'GET');//团队业绩指标列表
            Route::rule('getteammemberindicators', 'index/v1.Indicators/getTeamMemberIndicators', 'GET');//个人业绩指标列表

            // 小报备业绩明细统计
            Route::rule('payment/sum$', 'index/v1.Statistics/getPaymentSum', 'GET');//业绩明细统计合计
            Route::rule('payment/detailed$', 'index/v1.Statistics/getPaymentDetailed', 'GET');//业绩明细统计
            Route::rule('payment/detailed/export$', 'index/v1.Statistics/getPaymentDetailedExport', 'GET');//业绩明细统计导出
            Route::rule('payment/saler$', 'index/v1.Statistics/getStatisticPaymentSaler', 'GET');//个人业绩统计
            Route::rule('payment/saler/export$', 'index/v1.Statistics/getStatisticPaymentSalerExport', 'GET');//个人业绩统计

        });
    })->middleware(['AdminAuth', 'RequestLog']);

    /*************** 公共接口 ***************/
    Route::group('', function () {
        Route::get('citys', 'index/Common/citys');//获取所有城市
        Route::get('getadmincitys', 'index/Common/getAdminCitys');//获取所有城市
        Route::get('privcitys', 'index/Common/getPrivCitys');//获取权限城市

        Route::get('areas', 'index/Common/areas');//获取城市管辖地区
        Route::get('auth/menus', 'index/Manager/getMyMenu');//获取用户可见菜单
        Route::post('loginout', 'index/Manager/loginOut');//退出登录
        Route::post('finduser', 'index/Common/finduser');//搜索用户
        Route::post('findadmin', 'index/Common/findadmin');//搜索管理人员

        Route::get('novipcitys', 'index/Common/novipCitys');//获取所有无会员城市
        Route::get('yusuan', 'index/Common/getYuSuan');//获取预算
        Route::get('fangshi', 'index/Common/getFangshi');//获取预算

        /********************清除缓存****************************/
        Route::rule('clearcache', 'index/Manager/clearCache', 'GET'); //清除菜单缓存
        Route::post('uploads/upimg', 'index/Upload/uploadImg'); // 上传图片


    })->middleware(['AdminAuth','RequestLog']);

    // 无需登录
    Route::group('', function () {
        // 用户登录，token下发
        Route::post('account', 'index/Manager/accountToken');

        // 联通云总机 通话录音 播放
        Route::get('telcenter/recordurlcamouflage', 'index/v1.Telcenter/callRecordUrlcamouflage');

        /*************** 示例 ***************/
        Route::post('example', 'index/v1.Index/example'); # 统一接口返回格式示例 @author by lovenLiu

        // 图形验证码
        Route::get('verifyimg', 'index/Index/verifyImg');
    })->middleware(['RequestLog']);

    //miss路由
    Route::miss('index/index');

})->allowCrossDomain(true,[
    'Access-Control-Allow-Origin'  => '*',
    'Access-Control-Allow-Methods' => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
    'Access-Control-Allow-Headers' => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With,token'
]);
