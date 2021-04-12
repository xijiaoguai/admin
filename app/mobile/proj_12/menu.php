<?php
/**
 * Created by PhpStorm.
 * User: 25791
 * Date: 2020/11/25
 * Time: 14:06
 * Note: menu.php
 */

namespace app\mobile\proj_12;


use app\lib\api;
use app\lib\base;
use app\lib\model\menu as model_menu;
use app\lib\model\menu_relation;
use app\lib\model\proj as model_proj;
use app\lib\model\role_relation;
use app\lib\model\team as model_team;
use app\lib\model\user;
use app\lib\token;
use app\user\service\menu as service_menu;

class menu extends api
{
    public $proj_id  = 12;
    public $menu_pid = 90;

    public function list()
    {
        $uid      = $this->uid;
        $proj_id  = $this->proj_id;
        $menu_pid = $this->menu_pid;
        $menus    = model_menu::new()->fields('id', 'name', 'url', 'pid', 'crt_id')->where([['pid', $menu_pid], ['status', 0]])->get();
        //如果是创建者，拥有所有权限
        if (model_team::new()->where(['crt_id', $uid])->exist()) {
            return $menus;
        }
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

    public function auth_info(string $token, int $menu_id)
    {
        $res     = [
            'result'   => 0,
            'user_acc' => ''
        ];
        $proj_id = $this->proj_id;
        $data    = token::new()->parse($token, 'uid');
        if ($data['status'] !== 0) {
            $res['result'] = 1;
            return $res;
        }
        $uid    = (int)$data['data']['uid'];
        $result = $this->auth($uid, $menu_id, $proj_id);
        if ($result !== 0) {
            $res['result'] = $result;
            return $res;
        }
        $res['user_acc'] = user::new()->where(['id', $uid])->fields('acc')->get_val();
        return $res;
    }

    public function auth(int $uid, int $menu_id, int $proj_id)
    {
        if (model_team::new()->where(['crt_id', $uid])->exist()) {
            return 0;
        }
        $role_id = role_relation::new()->where([['uid', $uid], ['proj_id', $proj_id], ['status', 0]])->fields('role_id')->get_val();
        if (!$role_id) {
            return 2;
        }
        if (!menu_relation::new()->where([['menu_id', $menu_id], ['role_id', $role_id]])->exist()) {
            return 3;
        }
        return 0;
    }
}