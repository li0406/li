<?php


namespace Common\Model\Logic;



use Common\Model\Db\UserModel;

class UserLogicModel
{

    public function getPackageInfo($company_id)
    {
        $model = new UserModel();
        return $model->getPackageInfo($company_id);
    }

    /**
     * 兼容老用户信息
     * @param string $check_field 查询的字段
     * @param array $userList
     * @return array|mixed
     */
    public function compatibilityOldUserInfo($check_field = '', $userList = [])
    {
        //验证数组 取不同数据
        if(count($userList) == count($userList, 1)){
            $user_id = [$userList[$check_field]];
        }else{
            $user_id = array_column($userList,$check_field);
        }
        if (count($user_id) == 0) {
            return [];
        }
        $userDb = new UserModel();
        $user = $userDb->getUserInfoById($user_id);
        $user = array_column($user, null, 'id');
        if (count($userList) > 0) {
            if (count($userList) == count($userList, 1)) {
                if (isset($user[$userList[$check_field]])) {
                    $userList['name'] = $user[$userList[$check_field]]['name'];
                    $userList['logo'] = changeImgUrl($user[$userList[$check_field]]['logo'], 1);
                }
                //设置默认值
                $userList['logo'] = $userList['logo']?:'https://' . C('QINIU_DOMAIN') . '/Public/default/images/default_logo.png';
            } else {
                foreach ($userList as $k => $v) {
                    //将c端用户头像做个拼接
                    $userList[$k]['logo'] = changeImgUrl($userList[$k]['logo'], 2);
                    if (empty($v['logo']) || empty($v['username'])) {
                        if (isset($user[$v[$check_field]])) {
                            $userList[$k]['username'] = $user[$v[$check_field]]['name'];
                            $userList[$k]['logo'] = changeImgUrl($user[$v[$check_field]]['logo'], 1);
                        }
                    }
                    //设置默认值
                    $userList[$k]['logo'] = $userList[$k]['logo']?:'https://' . C('QINIU_DOMAIN') . '/Public/default/images/default_logo.png';
                }
            }
            return $userList;
        }
        return $user;
    }
}