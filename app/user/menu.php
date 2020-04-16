<?php
/**
 * Created by PhpStorm.
 * User: 25791
 * Date: 2020/4/15
 * Time: 11:30
 * Note: menu.php
 */

namespace app\user;


use app\lib\api;
use app\lib\model\menu_relation;
use app\lib\model\role_relation;
use app\lib\model\menu as model_menu;
use app\lib\model\team as model_team;
use app\user\service\menu as service_menu;

class menu extends api
{
    /**
     * 项目
     *
     * @param int $proj_id
     *
     * @return array
     */
    public function list(int $proj_id)
    {
        $uid   = $this->uid;
        $menus = service_menu::new()->menus($uid, $proj_id);
        $ids   = array_column($menus, 'id');
        $menus = array_combine($ids, $menus);
        foreach ($menus as $id => &$menu) {
            $menu['child'] = [];
            if ($menu['pid'] != 0) {
                $menus[$menu['pid']]['child'][] = $menu;
                unset($menus[$id]);
            }
        }
        return array_values($menus);
    }

    /**
     * 菜单编辑/新增
     *
     * @param int    $proj_id
     * @param string $name
     * @param int    $pid
     * @param string $url
     * @param int    $id
     *
     * @return bool
     */
    public function edit(int $proj_id, string $name, int $pid = 0, string $url = '', int $id = 0)
    {
        $menu = [
            'name'    => $name,
            'url'     => $url,
            'pid'     => $pid,
            'proj_id' => $proj_id
        ];
        if ($id == 0) {
            return model_menu::new()->value($menu)->add();
        } else {
            return model_menu::new()->value($menu)->where(['id', $id])->save();
        }
    }

    /**
     * 删除
     *
     * @param $id
     *
     * @return bool
     */
    public function del($id)
    {
        return model_menu::new()->value(['status' => 1])->where(['id', $id])->save();
    }
}