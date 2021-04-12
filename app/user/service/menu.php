<?php
/**
 * Created by PhpStorm.
 * User: 25791
 * Date: 2020/4/16
 * Time: 14:48
 * Note: menu.php
 */

namespace app\user\service;


use app\lib\base;
use app\lib\model\menu as model_menu;
use app\lib\model\menu_relation;
use app\lib\model\role_relation;
use app\lib\model\team as model_team;

class menu extends base
{
    public function menus(int $uid, int $proj_id)
    {
        $menus = model_menu::new()->fields('id', 'name', 'url', 'pid', 'crt_id')->where([['proj_id', $proj_id], ['status', 0]])->order(['sort'=>'desc'])->get();
        //如果是创建者，拥有所有权限
        if (model_team::new()->where(['crt_id', $uid])->exist()) {
            return $menus;
        }

        //如果没有所属角色，则没有权限
        $role_id = role_relation::new()
            ->where([['uid', $uid], ['proj_id', $proj_id], ['status', 0]])
            ->fields('role_id')
            ->get_val();
        if (empty($role_id)) {
            return [];
        }

        $menu_ids = menu_relation::new()->where(['role_id', $role_id])->fields('menu_id')->get(\PDO::FETCH_COLUMN);
        foreach ($menus as $k => $menu) {
            if (!in_array($menu['id'], $menu_ids) && $menu['crt_id'] != $uid) {
                unset($menus[$k]);
            }
        }
        return $menus;
    }
}