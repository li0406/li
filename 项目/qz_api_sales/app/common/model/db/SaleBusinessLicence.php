<?php
// +----------------------------------------------------------------------
// | SaleBusinessLicence 装修公司装修营业执照模型
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\db\Where;
use think\Model;
use think\db\Query;

class SaleBusinessLicence extends Model
{

    protected $autoWriteTimestamp = false;

    /**
     * 列表查询条件
     * @param  [type] $state   [description]
     * @param  [type] $options [description]
     * @return [type]          [description]
     */
	private function getListMap($state, $options){
        $map = new Query();
        $map->where("a.type", "NEQ", 4);
        $map->where("a.state", "IN", $state);

        // 权限城市
        if (isset($options["citys"]) && !empty($options["citys"])) {
            if (is_array($options["citys"])) {
                $map->where("u.cs", "IN", $options["citys"]);
            } else {
                $map->where("u.cs", $options["citys"]);
            }
        }

        // 公司名称/ID
        if (isset($options["company_keyword"]) && !empty($options["company_keyword"])) {
            $map->where(function($query) use ($options) {
                $query->where("u.id", $options["company_keyword"])
                    ->whereOr("u.qc", "LIKE", "%".$options["company_keyword"]."%");
            });
        }

        // 审核时间
        if (isset($options["date_begin"]) && !empty($options["date_begin"])) {
            $map->where("a.audit_time", "EGT", strtotime($options["date_begin"]));
        }

        // 审核时间
        if (isset($options["date_end"]) && !empty($options["date_end"])) {
            $map->where("a.audit_time", "ELT", strtotime($options["date_end"]) + 86399);
        }

        return $map;
    }

    /**
     * 查询列表总数量
     * @param  [type] $state   [description]
     * @param  [type] $options [description]
     * @return [type]          [description]
     */
    public function getListCount($state, $options){
        $map = $this->getListMap($state, $options);

        return static::alias("a")->where($map)
            ->join("user u", "u.id = a.company_id")
            ->join("user_company b", "b.userid = a.company_id and b.fake = 0")
            ->join("quyu q", "q.cid = u.cs")
            ->field("a.*,u.qc,q.cname")
            ->order("a.time desc,a.company_id")
            ->count();
    }

    /**
     * 查询列表
     * @param  [type]  $state   [description]
     * @param  [type]  $options [description]
     * @param  integer $offset  [description]
     * @param  integer $limit   [description]
     * @return [type]           [description]
     */
    public function getList($state, $options, $offset = 0, $limit = 0, $actfrom = "examine"){
        $map = $this->getListMap($state, $options);

        if ($actfrom == "examine") {
            $order = "user_on desc,a.time desc";
        } else {
            $order = "a.audit_time desc,user_on desc";
        }

        return static::alias("a")->where($map)
            ->join("user u", "u.id = a.company_id")
            ->join("user_company b", "b.userid = a.company_id and b.fake = 0")
            ->join("quyu q", "q.cid = u.cs")
            ->join("sale_business_licence l", "l.company_id = a.company_id and l.type = 4 and a.type = 1", "left")
            ->field("a.*,IF(u.`on` = 2, 2, 1) as user_on,u.qc,u.cs,q.cname,l.img1 as gszj_img")
            ->order($order)
            ->limit($offset, $limit)
            ->select();
    }

    /**
     * 查询城市会员数据
     * @param  [type] $cs      [description]
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function getSaleCityUserCompanyListCount($cs, $user_keyword) {
        $map = new Query();
        $map->where("a.classid", 3);

        if (!empty($user_keyword)) {
            $map->where(function($query) use ($user_keyword) {
                $query->where("a.id", $user_keyword)
                    ->whereOr("a.qc", "LIKE", "%".$user_keyword."%");
            });
        }

        if (!empty($cs)) {
            if (is_array($cs)) {
                $map->where("a.cs", "IN", $cs);
            } else {
                $map->where("a.cs", $cs);
            }
        }

        $subSql = static::name("user")->alias("a")->where($map)
            ->join("user_company b", "b.userid = a.id")
            ->join("quyu q", "q.cid = a.cs")
            ->field("a.id,a.`on` as user_on,a.qc,a.cs,q.cname")
            ->order("a.on desc,b.fake,a.id")
            ->buildSql();

        return static::table($subSql)->alias("t")
            ->join("sale_business_licence l", "l.company_id = t.id and l.type <> 4", "left")
            ->field("t.*,l.type,l.state,l.img1,l.img2,l.img3,l.img4")
            ->count();
    }

    /**
     * 查询城市会员数据
     * @param  [type] $cs      [description]
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function getSaleCityUserCompanyList($module = "list", $cs, $user_keyword, $offset = 0, $limit = 0) {
        $map = new Query();
        $map->where("a.classid", 3);

        if (!empty($user_keyword)) {
            $map->where("a.id", $user_keyword);
        }

        if (!empty($cs)) {
            if (is_array($cs)) {
                $map->where("a.cs", "IN", $cs);
            } else {
                $map->where("a.cs", $cs);
            }
        }

        $dbQuery = static::name("user")->alias("a")->where($map)
            ->join("user_company b", "b.userid = a.id");
            
        if ($module == "list") {
            $subSql = $dbQuery
                ->join("quyu q", "q.cid = a.cs")
                ->field("a.id,a.`on` as user_on,a.qc,a.cs,q.cname")
                ->order("a.on desc,b.fake,a.id")
                ->limit($offset, $limit)
                ->buildSql();

            $result = static::table($subSql)->alias("t")
                ->join("sale_business_licence l", "l.company_id = t.id and l.type <> 4", "left")
                ->field("t.*,l.type,l.state,l.img1,l.img2,l.img3,l.img4")
                ->select();
        } else {
            $result = $dbQuery->count("a.id");
        }

        return $result;
    }

    /**
     * 营业执照数据统计基础查询对象
     *
     * @param array $map 查询条件
     * @return $this|Query
     */
    private function StatisticsBuildBase($map)
    {
        $whereBase['a.classid'] = 3;
        $whereBase['b.fake'] = 0;

        if (!empty($map['cs'])) {
            $whereBase['a.cs'] = ['in', $map['cs']];
        }

        if (!empty($map['city'])) {
            $whereBase['s.city'] = $map['city'];
        }

        $buildBase = self::name('user')
            ->alias('a')
            ->where(new Where($whereBase))
            ->join('user_company b', 'b.userid = a.id')
            ->join('quyu q', 'q.cid = a.cs')
            ->leftJoin('sales_city_manage s', 's.city = q.cname')
            ->field('a.id,a.qc,a.jc,q.cname,s.dev_manage,s.brand_manage,s.brand_regiment,s.dev_regiment,IF(a.`on` = 2 AND b.fake = 0,1,0) is_member');

        if (!empty($map['company'])) {
            if (ctype_digit((string)$map['company'])) {
                $buildBase = $buildBase->whereRaw('a.id = :id', ['id' => $map['company']]);
            } else {
                $buildBase = $buildBase->whereRaw('a.qc like :likeone OR a.jc like :liketwo', [
                        'likeone' => '%' . $map['company'] . '%',
                        'liketwo' => '%' . $map['company'] . '%'
                    ]
                );
            }
        }

        $buildSql = $buildBase->buildSql();

        // 关联 营业执照/装修资质 信息
        $lastBuildBase = self::table($buildSql)
            ->alias('t1')
            ->leftJoin('sale_business_licence yy', 'yy.company_id = t1.id and yy.type = 1')
            ->leftJoin('sale_business_licence zx', 'zx.company_id = t1.id and zx.type = 3');

        if (!empty($map['ying'])) {
            switch ($map['ying']) {
                case 1:
                    $whereRow = 'yy.type is null';
                    break;
                case 2:
                    $whereRow = 'yy.state in (1,2,3)';
                    break;
                case 4:
                    $whereRow = 'yy.state = 4';
                    break;
                case 5:
                    $whereRow = 'yy.state = 5';
                    break;
            }
        }

        if (!empty($map['zhuang'])) {
            switch ($map['zhuang']) {
                case 1:
                    $whereRowTwo = 'zx.type is null';
                    break;
                case 2:
                    $whereRowTwo = 'zx.state IN (1,2,3)';
                    break;
                case 4:
                    $whereRowTwo = 'zx.state IN (4)';
                    break;
                case 5:
                    $whereRowTwo = 'zx.state IN (5)';
                    break;
            }
        }

        if (!empty($whereRow) && !empty($whereRowTwo)) {
            $lastBuildBase = $lastBuildBase->whereRaw($whereRow . ' AND ' . $whereRowTwo);
        } elseif (!empty($whereRow)) {
            $lastBuildBase = $lastBuildBase->whereRaw($whereRow);
        } elseif (!empty($whereRowTwo)) {
            $lastBuildBase = $lastBuildBase->whereRaw($whereRowTwo);
        }
        return $lastBuildBase;
    }

    /**
     * 营业执照数据统计记录数量
     *
     * @param array $map 查询条件
     * @return float|string|Query
     */
    public function getLicenceStatisticsCount($map)
    {
        return $this->StatisticsBuildBase($map)->count('t1.id');
    }

    /**
     * 营业执照数据统计记录列表
     *
     * @param array $map 查询条件
     * @param int $page 页码
     * @param int $pageSize 每页数量
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getLicenceStatisticsList($map, $page = 1, $pageSize = 20)
    {
        if ($page == 0) {
            $offset = 0;
        } else {
            $offset = $pageSize * ($page - 1);
        }
        return $this->StatisticsBuildBase($map)->field([
            't1.id',
            't1.qc',
            't1.jc',
            't1.cname',
            't1.is_member',
            'u.name dev_manage',
            'u1.name brand_manage',
            'u2.name brand_regiment',
            'u3.name dev_regiment',
            'yy.type yytype',
            'yy.state yystate',
            'zx.type zxtype',
            'zx.state zxstate',
        ])->leftJoin('adminuser u',' u.id = t1.dev_manage')
            ->leftJoin('adminuser u1','u1.id = t1.brand_manage')
            ->leftJoin('adminuser u2','u2.id = t1.brand_regiment')
            ->leftJoin('adminuser u3','u3.id = t1.dev_regiment')
            ->limit($offset, $pageSize)
            ->order('is_member desc,yy.audit_time desc,zx.audit_time desc')
            ->select();

    }

    /**
     * 根据联合主键查询
     * @param  [type] $company_id [description]
     * @param  [type] $type       [description]
     * @return [type]             [description]
     */
    public function findByUnique($company_id, $type){
        return static::where("company_id", $company_id)->where("type", $type)->find();
    }

    /**
     * 添加一个记录
     * @param  [type] $company_id [description]
     * @param  [type] $type       [description]
     * @return [type]             [description]
     */
    public function addRecord($company_id, $type, $img, $img_host = "qiniu", $state = 1){
        $record = [
            "company_id" => $company_id,
            "type" => $type,
            "img1" => $img,
            "img_host" => $img_host,
            "state" => $state,
            "time" => time()
        ];

        return $this->insert($record);
    }

    /**
     * 修改记录
     * @param  [type] $data  [description]
     * @return [type]        [description]
     */
    public function editRecord($company_id, $type, $data){
        $where = array(
            "company_id" => $company_id,
            "type" => $type
        );
        return $this->where($where)->update($data);
    }

    /**
     * 删除一个记录
     * @param  [type] $company_id [description]
     * @param  [type] $type       [description]
     * @return [type]             [description]
     */
    public function delRecord($company_id, $type){
        $where = array(
            "company_id" => $company_id,
            "type" => $type
        );
        return static::where($where)->delete();
    }
}
