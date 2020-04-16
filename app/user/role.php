<?php
/**
 * Created by PhpStorm.
 * User: 25791
 * Date: 2020/4/15
 * Time: 16:05
 * Note: role.php
 */

namespace app\user;


use app\lib\api;
use app\lib\enum\enum_err;
use app\lib\model\menu_relation;
use app\lib\model\role as model_role;
use app\lib\model\role_relation;
use app\user\service\menu as service_menu;

class role extends api
{
    /**
     * 角色列表
     *
     * @param int $proj_id
     * @param int $page
     * @param int $page_size
     *
     * @return array
     */
    public function list(int $proj_id, int $page, int $page_size)
    {
        return model_role::new()->where([['proj_id', $proj_id], ['status', 0]])->get_page($page, $page_size);
    }

    /**
     * 角色编辑/新增
     *
     * @param int    $proj_id
     * @param string $name
     * @param int    $role_id
     *
     * @return bool
     */
    public function edit(int $proj_id, string $name, int $role_id = 0)
    {
        if ($role_id == 0) {
            return model_role::new()->value(['name' => $name, 'proj_id' => $proj_id])->add();
        } else {
            return model_role::new()->where(['id', $role_id])->value(['name' => $name])->save();
        }
    }

    /**
     * 删除角色
     *
     * @param int $role_id
     *
     * @return bool
     */
    public function del(int $role_id)
    {
        $this->begin();
        try {
            model_role::new()->where(['id', $role_id])->value(['status' => 1])->save();
            role_relation::new()->where(['role_id', $role_id])->value(['status' => 1])->save();
            $this->commit();
            return true;
        } catch (\Throwable $e) {
            $this->rollback();
            $this->fail(enum_err::SQL_ERROR, $e->getMessage());
        }
    }

    /**
     * 角色菜单
     *
     * @param int $role_id
     * @param int $proj_id
     *
     * @return array
     */
    public function menus(int $role_id, int $proj_id)
    {
        $uid           = $this->uid;
        $menus         = service_menu::new()->menus($uid, $proj_id);
        $role_menu_ids = menu_relation::new()->fields('menu_id')->where(['role_id', $role_id])->get(\PDO::FETCH_COLUMN);
        $ids           = array_column($menus, 'id');
        $menus         = array_combine($ids, $menus);
        foreach ($menus as $id => &$menu) {
            $menu['check'] = 0;
            $menu['child'] = [];
            if (in_array($menu['id'], $role_menu_ids)) {
                $menu['check'] = 1;
            }
            if ($menu['pid'] != 0) {
                $menus[$menu['pid']]['child'][] = $menu;
                unset($menus[$id]);
            }
        }
        return array_values($menus);
    }

    public function menu_ctrl(int $role_id, array $menu_ids)
    {
        $this->begin();
        try {
            menu_relation::new()->where(['role_id', $role_id])->del();
            foreach ($menu_ids as $menu_id) {
                menu_relation::new()->value(['menu_id' => $menu_id, 'role_id' => $role_id])->add();
            }
            $this->commit();
            return true;
        } catch (\Throwable $e) {
            $this->rollback();
            $this->fail(enum_err::SQL_ERROR, $e->getMessage());
        }
    }
}