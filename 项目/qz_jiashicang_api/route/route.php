<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

Route::group('', function () {

    // 需要登录
    Route::group('v1', function () {

        // 销售驾驶舱部分
        Route::group('sales', function () {

            //城市报表数据
            Route::group('city', function () {
                Route::get('getsignranking', 'index/v1.City/getSignRanking'); //签单排行榜
                Route::get('getsignrate', 'index/v1.City/getSignRate');//签单率
                Route::get('getsigndistance', 'index/v1.City/getSignDistance'); //签单距离占比
                Route::get('getsignmoneytotal','index/v1.City/getSignMoneyTotal');//总签单金额
            });

            //销售驾驶舱-会员数据分析
            Route::group('user', function () {
                Route::get('getuseramount', 'index/v1.User/getUserAmount'); //总会员数
                Route::get('getuseramountv1', 'index/v1.User/getUserAmountV1'); //1.0会员数
                Route::get('getuseramountv2', 'index/v1.User/getUserAmountV2'); //2.0会员数
                Route::get('getuserconsumptiontotal','index/v1.User/getUserConsumptionTotal');//会员消耗总金额
                Route::get('getuserconsumptionv1','index/v1.User/getUserConsumptionV1');//1.0会员消耗金额
                Route::get('getuserconsumptionv2', 'index/v1.User/getUserConsumptionV2');//2.0会员消耗金额
                Route::get('getuserexceptgap','index/v1.User/getUserExceptGap');//会员数差距


                Route::get('membershipoverview','index/v1.User/getMembershipOverview');//会员概览
                Route::get('membershipchange','index/v1.User/getMembershipChange');//会员数量变化趋势
                Route::get('membershiprenewal','index/v1.User/getMembershipRenewal');//会员续费率

                Route::get('getinputandoutputv1','index/v1.User/getInputAndOutputV1');//1.0装企投入产出比
                Route::get('getinputandoutputv2','index/v1.User/getInputAndOutputV2');//2.0装企投入产出占比
                Route::get('getusercustomerprice','index/v1.User/getUserCustomerPrice');//会员客单价
                Route::get('getusercustomerpricev1','index/v1.User/getUserCustomerPriceV1');//1.0会员客单价
                Route::get('getusercustomerpricev2','index/v1.User/getUserCustomerPriceV2');//2.0会员客单价

            });

            //订单数据
            Route::group('order', function () {
                Route::rule('allcount', 'index/v1.Order/getAllOrderCount',"GET"); //城市订单统计-总单量
                Route::rule('liangfang', 'index/v1.Order/getOrderLiangFangRate',"GET"); //城市订单统计-量房率
                Route::rule('transformingdata', 'index/v1.Order/getCityTransformingData',"GET"); //城市报表-订单转化
                Route::rule('orderarearate', 'index/v1.Order/getOrderAreaRate',"GET"); //城市订单统计-订单面积占比
                Route::rule('applyorder', 'index/v1.Order/getApplyOrder',"GET"); //分单数据-申请补轮数量
                Route::rule('applypassorder', 'index/v1.Order/getApplyPassOrder',"GET"); //分单数据-实际已补轮数量
                Route::rule('applypassorderfull', 'index/v1.Order/getApplyPassOrderFull',"GET"); //分单数据-补轮通过率
                Route::rule('orderwaste', 'index/v1.Order/getOrderWaste',"GET"); //分单数据-分单浪费率
                Route::rule('orderfill', 'index/v1.Order/getOrderFill',"GET"); //分单数据-分单满足率
                Route::rule('orderprice', 'index/v1.Order/getOrderPrice',"GET"); //分单数据-分单客单价
                Route::rule('consumptiontotal','index/v1.Order/getConsumptionTotal','GET');//分单数据-总消耗金额
                Route::rule('distributeorder','index/v1.Order/getDistributeOrder','GET');//分单数据-分单平均分配次数
                Route::rule('orderrebut','index/v1.Order/getOrderRebut','GET');//分单数据-订单撤回次数
                Route::rule('orderrebutdetail','index/v1.Order/getOrderRebutDetail','GET');//分单数据-订单撤回详情
                Route::rule('violateapply','index/v1.Order/getViolateApply','GET');//分单数据-违规补轮次数
                Route::rule('violateapplydetail','index/v1.Order/getViolateApplyDetail','GET');//分单数据-违规补轮详情
            });

            // 销售驾驶舱主面板
            Route::group('main', function () {
                // 订单问题反馈图
                Route::get('city/orderlack', 'index/v1.City/getCityOrderLackList'); // 城市缺单TOP20
                Route::get('city/order/priceinsuff', 'index/v1.City/getCityOrderPriceInsuffList'); // 城市订单价格不足TOP20
                Route::get('city/order/fawaste', 'index/v1.City/getCityOrderFawaste'); // 发单浪费率
                Route::get('city/order/fenwaste', 'index/v1.City/getCityOrderFenwaste'); // 分单浪费率

                // 成本效益图
                Route::get('city/important', 'index/v1.City/getCityImportantList'); // 全国城市重点数据
                Route::get('achievement/monthtrend', 'index/v1.ReportPayment/getAchievementMonthtrend'); // 业绩月趋势图
                Route::get('achievement/outputavg', 'index/v1.ReportPayment/getAchievementOutputAvg'); // 人均产出
                Route::get('achievement/gradeavg', 'index/v1.ReportPayment/getAchievementGradeAvg'); // 平均业绩档位


                Route::get('user/signvipanalysis','index/v1.User/getSignVipAnalysis');//签约企业分析
                Route::get('city/citypotential','index/v1.City/getCityPotential');//城市企业潜力分析
                Route::get('city/sfsignanalysis','index/v1.City/getSfSignAnalysis');//各省份已签企业情况
                Route::get('user/renewanalysis','index/v1.User/getRenewAnalysis');//企业续费率
                Route::get('order/liangfangandandsign','index/v1.Order/getLiangFangAndSignAnalysis');//企业量房/签单走势
                Route::get('order/departachievement','index/v1.Order/getDepartAchievement');//销售驾驶舱中心板块
            });

            // 销售公共部分
            Route::get('team/toplist', 'index/v1.Team/getTeamTopList'); // 获取销售顶级团队
        });


        // 市场中心数据分析
        Route::group('market', function () {
            // 市场ROI数据分析
            Route::get('sourceroi/total', 'index/v1.OrderSourceRoi/getRoiTotal'); // ROI数据汇总
            Route::get('sourceroi/detailed', 'index/v1.OrderSourceRoi/getRoiDetailed'); // ROI数据明细
            Route::get('sourceroi/trendline', 'index/v1.OrderSourceRoi/getRoiTrendline'); // ROI趋势折线图
        });

		// 公共数据分析部分
        Route::group('pubdata', function () {
            Route::get('city/order/detailed', 'index/v1.City/getCityOrderDetailed'); // 城市数据明细
            Route::get('city/fendan/detailed', 'index/v1.City/getCityFendanDetailed'); //城市分单数据明细
            Route::get('city/user/detailed', 'index/v1.City/getCityUserDetailed'); //城市会员明细

            Route::get('company/detailed', 'index/v1.User/getCompanyDetailed'); //会员详情明细
            Route::get('roundapply/detailed', 'index/v1.RoundOrder/getRoundApplyDetailed'); //会员申请补轮明细

            // 渠道数据分析
            Route::get('src/options', 'index/v1.OrderSource/getSrcOptions'); // 渠道数据分析-筛选项
            Route::get('src/search', 'index/v1.OrderSource/getSrcSearchList'); // 渠道数据分析-检索来源

            Route::get('src/detailed', 'index/v1.OrderSource/getSrcDetailed'); // 渠道数据统计-列表
            Route::get('source/detailed', 'index/v1.OrderSource/getSourceDetailed'); // 发单位置统计-列表

            Route::get('src/detailed', 'index/v1.OrderSource/getSrcDetailed'); // 渠道数据统计-按渠道/城市
            Route::get('src/date/detailed', 'index/v1.OrderSource/getSrcDateDetailed'); // 渠道数据统计-按日期
            Route::get('source/detailed', 'index/v1.OrderSource/getSourceDetailed'); // 发单位置统计-按发单位置
            Route::get('source/date/detailed', 'index/v1.OrderSource/getSourceDateDetailed'); // 发单位置统计-按日期

            Route::get('order/departtransform','index/v1.Order/getDepartTransFormOrder');//各部门订单转化
            Route::get('order/departwaste','index/v1.Order/getDepartWaste');//各部门订单浪费
            Route::get('order/fadanwastecity','index/v1.Order/getFaDanWasteCity');//获取城市发单浪费率
            Route::get('order/fadantimeanalysis','index/v1.Order/getFaDanTimeAnalysis');//发单时间段分布
            Route::get('order/abnormalanalysis','index/v1.Order/getAbnormalAnalysis');//异常单分析
            Route::get('user/fadancycle','index/v1.User/getFaDanCycleAnalysis');//注册用户发单周期
        });

        // 基础信息配置模块
        Route::group('basic', function () {
            Route::get('sourceaccount/accountall', 'index/v1.OrderSourceAccount/getAccountAll'); // 渠道账户下拉数据
            Route::get('sourceaccount/expend_list', 'index/v1.OrderSourceAccount/getAccountExpendList'); // 渠道账户消耗数据
            Route::post('sourceaccount/expend_save', 'index/v1.OrderSourceAccount/setAccountExpend'); // 渠道账户消耗数据编辑
            Route::post('sourceaccount/expend_delete', 'index/v1.OrderSourceAccount/deleteAccountExpend'); // 渠道账户消耗数据删除
            Route::post('sourceaccount/expend_upload', 'index/v1.OrderSourceAccount/uploadAccountExpend'); // 上传渠道账户消耗数据
        });


        // 基础信息配置模块
        Route::group('template', function () {
            Route::post('uploadcitydata', 'index/v1.Template/uploadCityData');//上传城市数据-执行上传城市数据文件
        });

        // 公共模块
        Route::group('common', function () {
            Route::get('city/all', 'index/v1.City/getCityAll'); // 获取所有已开站城市
            Route::get('city/arealist', 'index/v1.City/getCityAreaList'); // 获取城市管辖区域

            Route::get('company/search', 'index/v1.User/getSearchList'); //会员公司详情检索
            Route::get('menu', 'index/v1.Common/getMenu'); //获取权限菜单
            Route::get('info', 'index/v1.Common/getUserInfo'); //获取权限菜单

            Route::post('clean', 'index/v1.Common/cleanupCache'); // 清除缓存
        });

    })->middleware(['AdminAuth','RequestLog']);


    // 齐装实验室
    Route::group('qzlab', function () {
        Route::get('index', 'index/v1.Index/index');
    });

    //miss路由
    Route::miss(function(){
        return json(sys_response(1000004, "路由不存在", false));
    });
})->allowCrossDomain(true, [
    'Access-Control-Allow-Origin'  => '*',
    'Access-Control-Allow-Methods' => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
    'Access-Control-Allow-Headers' => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With, token'
]);