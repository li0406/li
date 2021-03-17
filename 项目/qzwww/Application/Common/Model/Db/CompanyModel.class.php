<?php
// +----------------------------------------------------------------------
// | CompanyLogicModel  装修公司模型
// +----------------------------------------------------------------------
// | author: mcj
// +----------------------------------------------------------------------
// | Date: 2019/2/20 Time: 15:07
// +----------------------------------------------------------------------
namespace Common\Model\Db;

use Think\Model;

class CompanyModel extends Model
{
	protected $autoCheckFields = false;

    /**
     * 获取当前分站装修公司
     *
     * @param string $cityid 城市ID
     * @param int|boolean $limit 限制条数
     * @return mixed
     */
    public function getTrueCompany($cityid, $limit = false)
    {
        $where['b.is_show'] = 1;
        $where['a.on'] = 2;
        $where['b.fake'] = 0;
        $where['a.cs'] = $cityid;

        // feature-1009    合肥首页/分站排除会员40165
        if ($cityid == 340100) {
            $where['a.id'] = array('neq', '40165');
        }

        $result = M('user')->alias('a')
            ->field("a.user,a.logo,a.jc,a.qc,a.case_count,a.`on` 'real',b.fake,b.comment_count,b.cooperate_mode,a.id,a.id as company_id")
            ->where($where)
            ->join('INNER JOIN qz_user_company as b on b.userid = a.id')
            ->join('LEFT JOIN qz_company_score as c on c.company_id = a.id')
            ->order(['b.cooperation_type' => 'desc','c.score' => 'desc', 'a.register_time' => 'asc']);

        if (empty($limit)) {
            return $result->select();
        } else {
            return $result->limit($limit)->select();
        }
    }

    /**
     * 获取当前分站非真会员装修公司
     *
     * @param string $cityid 城市ID
     * @param bool $limit  限制条数
     * @param array $order 排序条件
     * @param array $only  选定的会员类型
     * @return mixed
     */
    public function getFakeCompany($cityid, $limit = false, $order = ['sort' => 'asc', 'a.register_time' => 'asc'], $only = [])
    {
        $where['_complex'] = [
            'a.on' => ['NEQ', 2],
            'b.fake' => ['NEQ', 0],
            '_logic' => 'OR'
        ];

        if (!empty($only)) {
            $where['_complex']  = $only;
        }

        // feature-1009    合肥首页/分站排除会员40165
        if ($cityid == 340100) {
            $where['a.id'] = array('not in', array('40165'));
        }


        $where['a.logo'] = [
            ['NEQ',''],
            ['EXP','IS NOT NULL'],
            ['NEQ', C('QINIU_SCHEME').'://'.C('QINIU_DOMAIN').'/Public/default/images/default_logo.png'],
            ['NEQ','/Public/default/images/default_logo.png'],
            'AND'
        ];
        $where['a.qc'] = [
            ['NEQ',''],
            ['EXP','IS NOT NULL'],
            'AND'
        ];
        $where['a.cs'] = $cityid;
        $where['b.is_show'] = 1;
        $vip_old = M('user_vip')->order('end_time desc')->buildSql();
        $user_vip = M('user_vip')->table($vip_old)->alias('vip')->group('company_id')->buildSql();
        $result = M('user')->alias('a')
            ->field("a.user,a.logo,a.jc,a.qc,a.case_count,a.`on` 'real',a.register_time,b.fake,b.comment_count,b.cooperate_mode,a.id,a.id as company_id,a.id,UNIX_TIMESTAMP(v.end_time) as end_time,q.bm,
            CASE WHEN  a.`on` = -1 THEN 1
            WHEN a.`on` = 2 AND b.fake = 1 THEN 2
            WHEN a.`on` = 0 AND b.fake = 0 THEN 3
            else 99 end sort")
            ->where($where)
            ->join('INNER JOIN qz_user_company as b on b.userid = a.id')
            ->join('INNER JOIN qz_quyu as q on q.cid = a.cs')
            ->join('LEFT JOIN qz_company_score as c on c.company_id = a.id')
            ->join('LEFT JOIN '.$user_vip.' as v on v.company_id = a.id')
            ->order($order);

        if (empty($limit)) {
            return $result->select();
        } else {
            return $result->limit($limit)->select();
        }
    }
    
    
    //根据装修公司id获取装修公司信息
    public function getCompanyInfoByComId($comid)
    {
        $where['user.id'] = $comid;
        return M('user')->alias('user')
            ->where($where)
            ->field('user.user,user.logo,user.jc,user.qc,user.case_count,company.comment_score,is_show')
            ->join('INNER JOIN qz_user_company as company on company.userid = user.id')
            ->find();
    }


    /**
     *
     * 获取非会员排行榜
     * @param string $cs   城市信息
     * @param int $limit 列表个数
     * @return array
     */
    public function getNotVipCompanyActivityList($cs = '',$limit=10)
    {
        $map = [];
        $map['user.classid'] = array('EQ',3);
        if(!empty($cs)){
            $map['user.cs'] = array('EQ',$cs);
        }
        $map['company.is_show'] = array('EQ',1);
        $list = M('user')->alias('user')
            ->where($map)
            ->field('user.id company_id,user.on,user.cs,user.jc,user.qc,user.register_time,q.bm,company.fake,company.op_info_last_time')
            ->join('INNER JOIN qz_user_company as company on company.userid = user.id')
            ->join('INNER JOIN qz_quyu q on q.cid = user.cs')
            ->order('company.op_info_last_time desc,user.register_time DESC')
            ->limit($limit)
            ->select();
        return $list ? $list : [];

    }


    /**
     *
     * 获取会员活跃排行榜
     * @param string $cs   城市信息
     * @param int $limit 列表个数
     * @return array
     */
    public function getCompanyActivityRank($cs='',$limit = 10)
    {
        $map = [];
        $map['user.classid'] = array('EQ',3);
        if(!empty($cs)){
            $map['user.cs'] = array('EQ',$cs);
        }
        $map['user.jc'] = array('NEQ',"");
        $map['log.action'] = array('IN',array("Companyinfo/edit_info","Companyinfo/edit_detail_info","Team/teamup","Cases/caseup","Cases/threedup","Oneselfevent/eventup","Companyinfo/edit_company_img"));

        $list = M('user')->alias('user')
            ->where($map)
            ->field('user.id company_id,user.on,user.cs,user.jc,user.qc,user.register_time,max(log.time) maxtime,q.bm,company.fake,company.op_info_last_time')
            ->join('qz_log_user as log on log.userid = user.id')
            ->join('INNER JOIN qz_user_company as company on company.userid = user.id')
            ->join('INNER JOIN qz_quyu q on q.cid = user.cs')
            ->group("user.id")
            ->order('maxtime desc,company.op_info_last_time desc,user.register_time DESC')
            ->limit($limit)
            ->select();
        return $list ? $list : [];

    }

    public function getRecommendLiangfangList($where, $limit = 4, $field = 'u.id,u.jc,u.qc,u.logo,q.bm,q.cname,c.fake,u.case_count,c.comment_count,c.team_count')
    {
        return M('order_company_review')->alias('r')
            ->field($field)
            ->join('qz_user u on u.id = r.comid')
            ->join('qz_user_company c on u.id = c.userid')
            ->join('qz_quyu q on q.cid = u.cs')
            ->where($where)
            ->group('r.comid')
            ->order('r.time desc')
            ->limit($limit)
            ->select();
    }

    public function getRecommendList($where, $limit = 4, $field = 'u.id,u.jc,u.qc,u.logo,q.bm,q.cname,c.fake,u.case_count,c.comment_count,c.team_count')
    {
        return M('user_company')->alias('c')
            ->join('qz_user u on u.id = c.userid')
            ->join('qz_quyu q on q.cid = u.cs')
            ->where($where)
            ->field($field)
            ->limit($limit)
            ->order('u.case_count desc')
            ->select();
    }

    public function getUserCompanyRank($comid)
    {
        //查询前两天的数据
        $where = [
            'comid' => ['in', $comid],
             'day' => ['between', [date('Y-m-d', strtotime('-5day')), date('Y-m-d')]]
        ];
        $buildSql = M('user_company_rank')
            ->where($where)
            ->order('day desc')
            ->buildSql();
        return M()->table($buildSql)->alias('t')
                  ->join("join qz_user_company b on b.userid = t.comid and b.is_show = 1")
                  ->group('t.comid')->select();
    }
    public function getRankCompany($cs,$row,$no_ids)
    {
        $map = [
            'u.on' => ['EQ',2],
            'c.fake'=>['EQ',0],
            'c.is_show'=>['EQ',1],
            'u.classid'=>['EQ',3],
            'r.day'=>['between',[date('Y-m-d',strtotime('-1 day')),date('Y-m-d')]],
        ];
        if(!empty($no_ids))
        {
            $map['u.id'] = ['NOT IN',$no_ids];
        }
        if (!empty($cs)) {
            $map['u.cs'] = ['EQ', $cs];
        }
        $sql = M('user')->alias('u')
            ->join('JOIN qz_user_company c ON c.userid = u.id')
            ->join('JOIN qz_user_company_rank r ON r.comid = u.id')
            ->where($map)
            ->field('u.id')
            ->order('r.id desc,r.casesnum DESC')
            ->buildSql();
        return M()->table($sql)->alias('t')->group('t.id')->limit($row)->select();
    }

    /**
     * 获取当前分站装修公司
     *
     * @param string $city_id 城市ID
     * @param int|boolean $limit 限制条数
     * @return mixed
     */
    public function getNewestCompany($city_id = '', $limit = 4)
    {
        $where['b.is_show'] = 1;
        $where['a.on'] = 2;
        $where['b.fake'] = 0;
        if(!empty($city_id)){
            $where['a.cs'] = $city_id;
        }

        $result = M('user')->alias('a')
            ->field("a.user,a.logo,a.jc,a.qc,a.case_count,a.`on` 'real',b.fake,b.comment_count,a.id,a.id as company_id,q.bm")
            ->where($where)
            ->join('INNER JOIN qz_user_company as b on b.userid = a.id')
            ->join('INNER JOIN qz_quyu as q on q.cid = a.cs')
            ->order(['b.cooperation_type' => 'desc', 'a.register_time' => 'desc']);

        return $result->limit($limit)->select();
    }

    /**
     * 获取分站口碑列表
     * @param $cs
     * @param int $num
     * @return mixed
     */
    public function getSubKoubeiRank($cs, $num = 5)
    {
        !empty($cs) && $map['a.cs'] = array('eq', $cs);
        $rankWhere = [
            'c.`day`' => ['eq', date('Y-m-d', strtotime('-1 day'))]
        ];

        $where = [
            'a.classid' => ['in', [3, 6]],
            'a.`cs`' => ['in', $cs],
            //排除不显示的公司
            'b.`is_show`' => ['eq', 1],
        ];
        $buildSql = M('user')->alias('a')
            ->field('a.id,a.qc,a.jc,a.register_time,a.logo,a.activte_score,a.case_count,a.cs,a.on,b.fake')
            ->where($where)
            ->join('INNER JOIN qz_user_company b on a.id = b.userid and b.fake = 0')
            ->buildSql();

        $buildSql = M()->table($buildSql)->alias("t")
            ->where($rankWhere)
            ->field('t.*,`day`,comid,liangfang,qiandan,haoping,wanzhengdu,ping,casesnum,loginnum,answernum,designnum')
            ->join('qz_user_company_rank c on c.comid = t.id')
            ->order('c.day desc')
            ->buildSql();

        $buildSql = M()->table($buildSql)->alias("r")
            ->group("comid")
            ->buildSql();

        $field = 'a.*,q.bm,((a.haoping / a.ping) *10) as comment_score,
        (1 * a.liangfang + 5 * a.qiandan + 0.1 * a.haoping + 0.1 * a.wanzhengdu) AS `rank`,
        case when a.on = 2 and a.fake=0 then 1 when a.on <> 2 and a.fake=0  then 2 else 3 end paixu';
        return M()->table($buildSql)->alias('a')
            ->join("qz_quyu as q on a.cs = q.cid ")
            ->field($field)
            ->order('paixu,rank DESC,a.register_time')
            ->limit("0,".$num)
            ->select();
    }

    /**
     * 获取分站口碑列表
     * @param $cs
     * @param int $num
     * @param bool $is_true
     * @return mixed
     */
    public function getCompanyByKoubei($cs = '', $num = 3 , $is_true = true)
    {
        $rankWhere = [
            'c.`day`' => ['eq', date('Y-m-d', strtotime('-1 day'))]
        ];

        $where = [
            'a.classid' => ['in', [3, 6]],
            //排除不显示的公司
            'b.`is_show`' => ['eq', 1],
        ];
        if(!empty($cs)){
            $where['a.cs'] = ['eq',$cs];
        }

        if ($is_true) {
            $where['a.on'] = ['eq', 2];
            $where['b.fake'] = ['eq', 0];
        } else {
            $where['a.on'] = ['neq', 2];
            $where['b.fake'] = ['eq', 0];
        }

        $buildSql = M('user')->alias('a')
            ->field('a.id,a.qc,a.jc,a.register_time,a.logo,a.activte_score,a.case_count,a.cs,a.qx,a.on,b.fake')
            ->where($where)
            ->join('INNER JOIN qz_user_company b on a.id = b.userid and b.fake = 0')
            ->buildSql();

        $buildSql = M()->table($buildSql)->alias("t")
            ->where($rankWhere)
            ->field('t.*,`day`,comid,liangfang,qiandan,haoping,wanzhengdu,ping,casesnum,loginnum,answernum,designnum')
            ->join('qz_user_company_rank c on c.comid = t.id')
            ->order('c.day desc')
            ->buildSql();

        $buildSql = M()->table($buildSql)->alias("r")
            ->group("comid")
            ->buildSql();

        $field = 'a.*,q.bm,q.cname,area.qz_area,(1.5 * a.liangfang + 10 * a.wanzhengdu + 0.5 * a.loginnum + 0.5 * a.answernum) hot,
        (1 * a.liangfang + 5 * a.qiandan + 0.1 * a.haoping + 10 * a.wanzhengdu) AS `rank`';
        return M()->table($buildSql)->alias('a')
            ->join("qz_quyu as q on a.cs = q.cid ")
            ->join("left join qz_area area on area.qz_areaid = a.qx")
            ->field($field)
            ->order('rank DESC,hot desc,a.register_time')
            ->limit("0", $num)
            ->select();
    }

    /**
     * 获取分站口碑列表
     * @param $cs
     * @param int $num
     * @param bool $is_true
     * @param array $passId
     * @return mixed
     */
    public function getCompanyByCase($cs = '', $num = 3, $is_true = true, $passId = [])
    {
        $rankWhere = [
            'c.`day`' => ['eq', date('Y-m-d', strtotime('-1 day'))]
        ];

        $where = [
            'a.classid' => ['in', [3, 6]],
            //排除不显示的公司
            'b.`is_show`' => ['eq', 1],
        ];
        if (!empty($cs)) {
            $where['a.cs'] = ['eq', $cs];
        }

        if ($is_true) {
            $where['a.on'] = ['eq', 2];
            $where['b.fake'] = ['eq', 0];
        } else {
            $where['a.on'] = ['neq', 2];
            $where['b.fake'] = ['eq', 0];
        }

        if (count($passId) > 0) {
            $where['a.id'] = ['not in', $passId];
        }

        $buildSql = M('user')->alias('a')
            ->field('a.id,a.qc,a.jc,a.register_time,a.logo,a.activte_score,a.case_count,a.cs,a.qx,a.on,b.fake')
            ->where($where)
            ->join('INNER JOIN qz_user_company b on a.id = b.userid and b.fake = 0')
            ->buildSql();

        $buildSql = M()->table($buildSql)->alias("t")
            ->where($rankWhere)
            ->field('t.*,`day`,comid,liangfang,qiandan,haoping,wanzhengdu,ping,casesnum,loginnum,answernum,designnum')
            ->join('qz_user_company_rank c on c.comid = t.id')
            ->order('c.day desc')
            ->buildSql();

        $buildSql = M()->table($buildSql)->alias("r")
            ->group("comid")
            ->buildSql();

        $field = 'a.*,q.bm,q.cname,area.qz_area';
        return M()->table($buildSql)->alias('a')
            ->join("qz_quyu as q on a.cs = q.cid ")
            ->join("left join qz_area area on area.qz_areaid = a.qx")
            ->field($field)
            ->order('a.casesnum DESC,a.register_time')
            ->limit("0", $num)
            ->select();
    }
}