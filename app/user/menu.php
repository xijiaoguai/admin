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
use app\lib\enum\enum_err;
use app\lib\model\menu as model_menu;
use app\lib\model\proj as model_proj;
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
                $sort_ids = array_column($menus[$menu['pid']]['child'], 'sort');
                array_multisort($sort_ids, SORT_DESC, $menus[$menu['pid']]['child']);
            }
        }
        $res['menus']     = array_values($menus);
        $res['proj_name'] = model_proj::new()->where(['id', $proj_id])->fields('name')->get_val();
        return $res;
    }

    public function info(int $menu_id)
    {
        return model_menu::new()->where(['id', $menu_id])->get_one();
    }

    /**
     * 菜单编辑/新增
     *
     * @param int    $proj_id
     * @param string $name
     * @param int    $pid
     * @param string $url
     * @param int    $id
     * @param float  $weight
     *
     * @return bool
     */
    public function edit(int $proj_id, string $name, int $pid = 0, string $url = '', int $id = 0, float $weight = 0)
    {
        $menu = [
            'name'    => $name,
            'url'     => $url,
            'pid'     => $pid,
            'sort'    => $weight,
            'proj_id' => $proj_id,
            'crt_id'  => $this->uid
        ];
        if ($id == 0) {
            return model_menu::new()->value($menu)->add();
        }
        if ($pid == $id) {
            $this->fail(enum_err::INVALID_PARAMS, "不能自己做自己的上级菜单");
        }
        if ($pid) {
            $pid_info = model_menu::new()->where(['id', $pid])->get_one();
            if (in_array($pid_info['url'], ['menu.html', 'role.html', 'member.html'])) {
                $this->fail(enum_err::INVALID_PARAMS, "系统菜单不能编辑");
            }
        }
        if ($id) {
            $id_info = model_menu::new()->where(['id', $id])->get_one();
            if (in_array($id_info['url'], ['menu.html', 'role.html', 'member.html'])) {
                $this->fail(enum_err::INVALID_PARAMS, "系统菜单不能编辑");
            }
        }
        return model_menu::new()->value($menu)->where(['id', $id])->save();
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
        $id_info = model_menu::new()->where(['id', $id])->get_one();
        if (in_array($id_info['url'], ['menu.html', 'role.html', 'member.html'])) {
            $this->fail(enum_err::INVALID_PARAMS, "系统菜单不能删除");
        }
        return model_menu::new()->value(['status' => 1])->where([['id', $id], ['or', 'pid', $id]])->save();
    }
}