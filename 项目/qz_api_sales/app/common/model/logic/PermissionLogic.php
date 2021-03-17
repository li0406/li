<?php
// +----------------------------------------------------------------------
// | RbacSystemMenu
// +----------------------------------------------------------------------
namespace app\common\model\logic;

use think\facade\Cache;
use app\common\model\db\RbacNodeRole;
use app\common\model\db\RbacSystemMenu;
use app\common\enum\CacheKeyCodeEnum;

class PermissionLogic
{
    /**
     * 系统权限版本
     */
    const VERSION = 7;

    /**
     * 获取用户可见菜单
     *
     * @param $role_id
     * @return array|mixed
     */
    public function getMyMenu($role_id)
    {
        //递归操作性能占据高，需要缓存
        $cache_key = sprintf(CacheKeyCodeEnum::RBACROLE_MENU, $role_id);
        if(Cache::has($cache_key)){
            return json_decode(Cache::get($cache_key));
        }

        //获取用户菜单权限
        $menu = [];
        if ($role_id == 1) {
            //管理员加载全部菜单
            $auth_menu = $this->getAdminMenu();
        } else {
            $auth_menu = $this->getRoleMenu($role_id);
        }

        if ($auth_menu) {
            //整理查询结果集 准备递归
            foreach ($auth_menu as $value) {
                $menu[] = [
                    'id' => $value->getAttr('nodeid'),
                    'name' => $value->getAttr('name'),
                    'parent_id' => $value->getAttr('parentid'),
                    'link' => $value->getAttr('link'),
                    'linktype' => $value->getAttr('link_type'),
                    'icon' => $value->getAttr('icon'),
                ];
            }
        }

        $tree = arrange_category($menu);
        
        //根据前端需求整理成期望结构
        $auth_menu_result = [];
        $i = 0;
        foreach ($tree as $key => $v) {
            if (!empty($v['children'])) {
                $auth_menu_result[$i] = [
                    'key' => $key + 1,
                    'path' => '/' . trim($v['link'], '/'),
                    'component' => 'Layout',
                    'redirect' => $v['children'][0]['link'],
                    'name' => $v['link'],
                    'meta' => [
                        'title' => $v['name'],
                        'icon' => $v['icon'],
                    ],
                    'children' => [],
                ];
                $j = 0;
                foreach ($v['children'] as $vv) {
                    $component = trim($v['link'], '/') . '/' . $vv['link'];
                    $auth_menu_result[$i]['children'][$j] = [
                        'path' => trim($vv['link'], '/'),
                        'component' => $vv['linktype'] == 1 ? '' : $component,
                        'linktype' => intval($vv['linktype']),
                        'linkurl' => $vv['linktype'] == 1 ? $vv['link'] : '',
                        'name' => $vv['name'],
                        'meta' => [
                            'title' => $vv['name'],
                            'icon' => $vv['icon'],
                        ]
                    ];
                    $j++;
                }
                $i++;
            }
        }
        Cache::set($cache_key, json_encode($auth_menu_result));
        return $auth_menu_result;
    }

    /**
     * 管理员获取所有菜单
     *
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getAdminMenu()
    {
        return RbacSystemMenu::field('id,name,enabled,px,link,link_type,parentid,nodeid,icon')
            ->where('enabled', '=', 1)
            ->where('version', '=', self::VERSION)
            ->order(['px' => 'desc', 'id' => 'desc'])
            ->select();
    }

    /**
     * 根据角色获取菜单
     *
     * @param $role_id
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getRoleMenu($role_id)
    {
        $where = function ($query) use ($role_id) {
            $query->where('qz_rbac_node_role.role_id', '=', $role_id);
            $query->where('menu.enabled', '=', 1);
            $query->where('menu.version', '=', self::VERSION);
        };
        $with_join = [
            'menu' => ['name','nodeid','enabled', 'px', 'link','link_type','parentid','version','icon']
        ];
        return RbacNodeRole::field('node_id,role_id')
            ->withJoin($with_join)
            ->where($where)
            ->order(['menu.px' => 'desc', 'menu.id' => 'desc'])
            ->select();
    }

    public function clearCache($role_id)
    {
        $cache_key = sprintf(CacheKeyCodeEnum::RBACROLE_MENU, $role_id);
        return Cache::rm($cache_key);
    }

    /**
     * 根据角色和权限名称获取具体菜单
     *
     * @param $role_id
     * @return mixed
     */
    public function getMenuByRoleidAndName($role_id, $path_name)
    {
        $where = function ($query) use ($role_id, $path_name) {
            $query->where('qz_rbac_node_role.role_id', '=', $role_id);
            $query->where('menu.enabled', '=', 1);
            $query->where('menu.version', '=', self::VERSION);
            $query->where('menu.name', '=', $path_name);
        };
        $with_join = [
            'menu' => ['name', 'nodeid', 'enabled', 'px', 'link', 'link_type', 'parentid', 'version', 'icon']
        ];
        return RbacNodeRole::field('node_id,role_id')
            ->withJoin($with_join)
            ->where($where)
            ->order(['menu.px' => 'desc', 'menu.id' => 'desc'])
            ->find();
    }
}