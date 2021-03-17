<?php
/**
 * v2美图,分类
 */

namespace Home\Model\Logic;


use Common\Enums\ApiConfig;
use Home\Model\Db\MeituV2CategoryModel;
use Home\Model\Db\MeituV2Model;
use Think\Db;
use Think\Exception;

class MeituV2LogicModel
{
    protected $meituV2CategoryLogic;

    protected $meituV2Model;

    protected $meituV2ImageLogic;

    protected $user;

    protected $perCount = 20;

    /**
     * 发布状态
     * @var array
     */
    protected $state = [
        1 => '未发布',
        2 => '预发布',
        3 => '已发布',
    ];

    public function __construct()
    {
        $this->meituV2CategoryLogic = new MeituV2CategoryLogicModel();
        $this->meituV2Model = new MeituV2Model();
        $this->meituV2ImageLogic = new MeituV2ImageLogicModel();
        $this->user = session('uc_userinfo');
    }

    private function encdoe($value)
    {
        return htmlspecialchars(trim($value));
    }

    private function decode($value)
    {
        return htmlspecialchars_decode($value);
    }

    /**
     * 清洗表单的数据
     * @param array $params
     * @return array
     */
    private function transformAddData(array $params)
    {
        $data = [
            'title' => $this->encdoe($params['title']),
            'type' => (int)$params['type'],
            'state' => (int)$params['state'],
            'is_recommend' => (int)$params['is_recommend'] ?: 2,
            'is_house_custom' => (int)$params['is_house_custom'] ?: -1,
            'keyword' => $this->encdoe($params['keyword']),
            'description' => $this->encdoe($params['description']),
            'publish_time' => $params['publish_time'],
            'operator' => $this->user['id'],
            'updated_at' => time(),
        ];

        //家装
        if ($params['type'] == 1) {
            $data['pub_category'] = (int)$params['pub_category'];
        } else {
            $data['home_space_category'] = (int)$params['space_category'];
            $data['home_part_category'] = (int)$params['part_category'];
            $data['home_style_category'] = (int)$params['style_category'];
            $data['home_layout_category'] = (int)$params['layout_category'];
        }

        //更新
        if ($params['id']) {
            $historyState = (int)$this->value($params['id'], 'state');
            // 已发布的不能修改状态
            if ($historyState === 3) {
                $data['state'] = 3;
                //直接发布的 去掉发布时间
                unset($data['publish_time']);
            }
        } else {
            $data['creator'] = $this->user['id'];
            $data['created_at'] = time();
            $data['pv'] = mt_rand(1000, 1500);

            if ($data['state'] == 3) {
                $data['publish_time'] = time();
            }
        }

        //是否是直接发布
        if ($data['state'] == 1 && $data['publish_time']) {
            $data['state'] = 2;
        } elseif ($data['state'] == 1) {
            //所有未发布的 改成预发布
            $data['state'] = 2;
            $data['publish_time'] = strtotime('+1 day');
        }

        return $data;
    }

    /**
     * 清洗列表数据
     * @param array $data
     * @return array
     */
    private function transformListData(array $data)
    {
        $formattedData = [];
        if (empty($data)) {
            return $formattedData;
        }
        $formattedData = $data;
        $formattedData['publish_at'] = $data['publish_time'] ? date('Y-m-d H:i:s', $data['publish_time']) : '';
        $formattedData['publish_time'] = $data['publish_time'] ? date('Y-m-d', $data['publish_time']) : '';
        $formattedData['created_at'] = $data['created_at'] ? date('Y-m-d H:i:s', $data['created_at']) : '';
        $formattedData['updated_at'] = $data['updated_at'] ? date('Y-m-d H:i:s', $data['updated_at']) : '';
        $formattedData['state_name'] = $this->state[$data['state']];
        $formattedData['image_src'] = $data['image_src'];
        $formattedData['image_src_total'] = changeImgUrl($data['image_src']);
        $formattedData['image_title'] = $this->decode($data['image_title']);
        $formattedData['title'] = $this->decode($data['title']);
        $formattedData['keyword'] = $this->decode($data['keyword']);
        $formattedData['description'] = $this->decode($data['description']);
        return $formattedData;
    }

    /**
     * 获取值
     * @param $id
     * @param $field
     * @return \Think\Model
     */
    public function value($id, $field)
    {
        $map['id'] = $id;
        $value = $this->meituV2Model->where($map)->getField($field);
        return $value;
    }

    /**
     * 是否有某个值
     * @param $map
     * @return bool
     */
    public function hasValue($map)
    {
        $ret = $this->meituV2Model->where($map)->getField('id');
        return !!$ret;
    }

    /**
     * 获取公装列表
     * @param array $params
     * @return array
     */
    public function getPubList(array $params)
    {
        $count = $this->meituV2Model->getPubCount($params);
        $ret = [
            'page' => '',
            'data' => [],
        ];
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $page = new \Page($count, $this->perCount);
            $show = $page->show();
            $data = $this->meituV2Model->getPubList($params, $page->firstRow, $page->listRows);
            $formattedData = [];
            if (!empty($data)) {
                foreach ($data as $value) {
                    $formattedData[] = $this->transformListData($value);
                }
            }
            $ret['page'] = $show;
            $ret['data'] = $formattedData;
        }
        return $ret;
    }

    /**
     * 获取家装列表
     * @param array $params
     * @return array
     */
    public function getHomeList(array $params)
    {
        $count = $this->meituV2Model->getHomeCount($params);
        $ret = [
            'page' => '',
            'data' => [],
        ];
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $page = new \Page($count, $this->perCount);
            $show = $page->show();
            $data = $this->meituV2Model->getHomeList($params, $page->firstRow, $page->listRows);
            $formattedData = [];
            if (!empty($data)) {
                foreach ($data as $value) {
                    $formattedData[] = $this->transformListData($value);
                }
            }
            $ret['page'] = $show;
            $ret['data'] = $formattedData;
        }
        return $ret;
    }

    /**
     * 添加美图
     * @param $params
     * @throws Exception
     */
    public function add($params)
    {
        try {
            D()->startTrans();
            $data = $this->transformAddData($params);
            //保存美图
            $insertMeitu = (int)$this->meituV2Model->data($data)->add();

            if (empty($insertMeitu)) {
                throw new Exception('保存失败', ApiConfig::PARAMETER_ILLEGAL);
            }

            //保存图片
            $savedImage = $this->meituV2ImageLogic->save($insertMeitu, $params['image_src'], $params['image_title'], $params['image_width'], $params['image_height']);
            if (empty($savedImage)) {
                throw new Exception('保存图片失败', ApiConfig::PARAMETER_ILLEGAL);
            }
            D()->commit();
        } catch (Exception $e) {
            $insertMeitu = 0;
            D()->rollback();
            throw $e;
        }

        return $insertMeitu;
    }

    /**
     * 更新美图
     * @param $params
     * @throws Exception
     */
    public function update($params)
    {
        try {
            D()->startTrans();
            $data = $this->transformAddData($params);
            $id = $params['id'];

            //更新美图
            $insertMeitu = $this->meituV2Model->where("id=$id")->save($data);
            if (empty($insertMeitu)) {
                throw new Exception('保存失败', ApiConfig::PARAMETER_ILLEGAL);
            }

            //更新图片
            $savedImage = $this->meituV2ImageLogic->update($id, $params['image_src'], $params['image_title'], $params['image_weight'], $params['image_height']);
            if (empty($savedImage)) {
                throw new Exception('保存图片失败', ApiConfig::PARAMETER_ILLEGAL);
            }
            D()->commit();
        } catch (Exception $e) {
            D()->rollback();
            throw $e;
        }
    }

    /**
     * 批量更新数据
     * @param $ids
     * @throws Exception
     */
    public function multiPublish($ids)
    {
        try {
            //更新id
            $currentTimestamp = time();
            $data = [
                'state' => 3,
                'publish_time' => $currentTimestamp,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
                'operator' => $this->user['id']
            ];
            $ret = $this->meituV2Model->multiPublish($ids, $data);
            if (empty($ret)) {
                throw new Exception('保存失败', ApiConfig::PARAMETER_ILLEGAL);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }


    /**
     * 获取美图详情
     * @param $id
     * @return array|mixed
     */
    public function getMeituDetail($id, $type)
    {

        if ($type == 1) {
            $data = $this->meituV2Model->getPubDetail($id);
        } else {
            $data = $this->meituV2Model->getHomeDetail($id);
        }
        if (!empty($data)) {
            $data = $this->transformListData($data);
        }
        return $data;
    }

    /**
     * 删除美图
     * @param $id
     * @throws Exception
     */
    public function delMeitu($id)
    {
        try {
            D()->startTrans();
            //1.删除美图
            $delMeitu = $this->meituV2Model->del($id);
            if (empty($delMeitu)) {
                throw new Exception('删除美图失败', ApiConfig::DELETE_FALE);
            }

            //3.删除图片
            $delMeituImage = $this->meituV2ImageLogic->drop($id);
            if (empty($delMeituImage)) {
                throw new Exception('删除美图失败', ApiConfig::DELETE_FALE);
            }
            D()->commit();
        } catch (Exception $e) {
            D()->rollback();
            throw $e;
        }

    }

    /**
     * 根据type获取相关美图数据
     * @param $type 1表示公装， 2表示家装，3表示3D效果图
     */
    public function getMeituListByType($type = 1)
    {
        if ($type = 1 || $type == 2) {

        } elseif ($type == 3) {

        } else {
            return [];
        }

    }


    /**
     * 获取美图首页管理家装列表
     * @param array $params
     * @return array
     */
    public function getMeiluBoxList(array $params)
    {
        $params['state'] = 3;
        $count = $this->meituV2Model->getMeiluBoxListCount($params, intval($params['choosestate']));
        $type = intval($params['type']) ? intval($params['type']) : 2;
        $ret = [
            'page' => '',
            'data' => [],
        ];
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $page = new \Page($count, 15);
            $show = $page->show();
            $data = $this->meituV2Model->getMeiluBoxList($params, $page->firstRow, $page->listRows, $type, intval($params['choosestate']));
            $formattedData = [];
            if (!empty($data)) {
                foreach ($data as $value) {
                    $formattedData[] = $this->transformListData($value);
                }
            }
            $ret['page'] = $show;
            $ret['data'] = $formattedData;
        }
        return $ret;
    }


    /**
     * 公装弹窗 获取公装列表
     * @param array $params
     * @return array
     */
    public function getPubMeiluBoxList(array $params)
    {
        $params['state'] = 3;
        $count = $this->meituV2Model->getPubBoxListCount($params, intval($params['choosestate']));
        $type = intval($params['type']) ? intval($params['type']) : 1;
        $ret = [
            'page' => '',
            'data' => [],
        ];
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $page = new \Page($count, 15);
            $show = $page->show();
            $data = $this->meituV2Model->getPubMeiluBoxList($params, $page->firstRow, $page->listRows, $type, intval($params['choosestate']));
            $formattedData = [];
            if (!empty($data)) {
                foreach ($data as $value) {
                    $formattedData[] = $this->transformListData($value);
                }
            }
            $ret['page'] = $show;
            $ret['data'] = $formattedData;
        }
        return $ret;
    }

    /**
     * 重新存储数据
     * @param $ids
     * @throws Exception
     */
    public function restore($ids)
    {
        try {
            M()->startTrans();
            //1.获取数据
            $data = $this->meituV2Model->getMulti($ids);
            if (empty($data)) {
                throw new Exception('数据不存在', ApiConfig::PARAMETER_ILLEGAL);
            }
            foreach ($data as $k => $v) {

                $oldId = $v['id'];
                //数据是否存在
                $map['id'] = ['eq', $oldId];
                if (!$this->hasValue($map)) {
                    throw new Exception('数据不存在', ApiConfig::PARAMETER_ILLEGAL);
                }
                //2.添加数据

                $currentTimestamp = time();
                $v['state'] = 3;
                $v['publish_time'] = $currentTimestamp;
                $v['created_at'] = $currentTimestamp;
                $v['updated_at'] = $currentTimestamp;
                $v['operator'] = $this->user['id'];

                unset($v['id']);
                $newId = $this->meituV2Model->add($v);
                if (empty($newId)) {
                    throw new Exception('添加数据失败', ApiConfig::PARAMETER_ILLEGAL);
                }
                //3.修改图片关联id
                $updateImageRet = $this->meituV2ImageLogic->restore($newId, $oldId);
                if (empty($updateImageRet)) {
                    throw new Exception('更新图片数据失败', ApiConfig::PARAMETER_ILLEGAL);
                }
                //4.删除旧数据
                $delRet = $this->meituV2Model->del($oldId);
                if (empty($delRet)) {
                    throw new Exception('删除数据失败', ApiConfig::PARAMETER_ILLEGAL);
                }
            }
            M()->commit();
        } catch (Exception $e) {
            M()->rollback();
            throw $e;
        }
    }

    // 获取所有美图编辑人员
    public function getMeituUsers()
    {
        $model = new MeituV2Model();
        return $model->getMeituUsers();

    }

    // 获取满足条件的美图总数量
    public function getMarketContentMeituCount($condition)
    {
        $model = new MeituV2Model();
        $condition = $this->checkMapKeyword($condition);

        return $model->getMarketContentMeituCount($condition);

    }

    // 获取美图业绩分析数据列表
    public function getMarketContentMeituList($condition, $pageIndex = 1, $pageCount = 20)
    {
        $model = new MeituV2Model();
        $condition = $this->checkMapKeyword($condition);

        return $model->getMarketContentMeituList($condition, $pageIndex, $pageCount);
    }

    // 获取折线图数据
    public function getMarketContentMeituByDay($condition)
    {
        $model = new MeituV2Model();
        $condition = $this->checkMapKeyword($condition);

        return $model->getMarketContentMeituByDay($condition);
    }

    public function getMeituOrdersNumByTime($condition)
    {
        $model = new MeituV2Model();
        $condition = $this->checkMapKeyword($condition);

        return $model->getMeituOrdersNumByTime($condition);
    }


    /**
     * 美图业绩分析  标题/ID查询的keyword验证
     * @param $condition
     * @return mixed
     */
    private function checkMapKeyword($condition)
    {
        if (!empty($condition['keywords'])) {
            $condition["_complex"] = array(
                'id' => array('eq', intval($condition['keywords'])),
                'title' => array('LIKE', '%' . $condition['keywords'] . '%'),
                '_logic' => 'OR'
            );
            unset($condition['keywords']);
        }

        return $condition;
    }


}