<?php
/**
 * Created by PhpStorm.
 * User: 25791
 * Date: 2020/4/13
 * Time: 18:00
 * Note: info.php
 */

namespace app\user;


use app\lib\api;
use app\lib\enum\enum_err;
use app\lib\model\role_relation;
use app\lib\model\user as model_user;
use app\lib\model\proj as model_proj;
use app\lib\model\team as model_team;
use app\lib\model\menu as model_menu;

class proj extends api
{
    /**
     * 项目列表
     *
     * @param int $page
     * @param int $page_size
     *
     * @return array
     */
    public function list(int $page, int $page_size)
    {
        $uid     = $this->uid;
        $team_id = model_user::new()->fields('team_id')->where(['id', $uid])->get_val();
        if (empty($team_id)) {
            $this->fail(enum_err::NO_TEAM);
        }
        $where = [['team_id', $team_id], ['status', 0]];
        //如果不是创建者
        if (!model_team::new()->where([['crt_id', $uid]])->exist()) {
            $proj_ids = role_relation::new()
                ->where([['uid', $uid], ['status', 0]])
                ->fields('proj_id')
                ->get(\PDO::FETCH_COLUMN);
            if (!empty($proj_ids)) {
                $where[] = ['id', 'IN', $proj_ids];
            } else {
                $where[] = [1, 2];
            }
        }
        return model_proj::new()
            ->fields('id', 'name', 'desc')
            ->where($where)
            ->get_page($page, $page_size);
    }

    /**
     * 编辑/新增
     *
     * @param string $name
     * @param string $desc
     * @param int    $proj_id
     *
     * @return bool
     */
    public function edit(string $name, string $desc = '', int $proj_id = 0)
    {
        $uid     = $this->uid;
        $team_id = model_team::new()->where([['crt_id', $uid]])->fields('id')->get_val();
        if (empty($team_id)) {
            $this->fail(enum_err::INVALID_PARAMS);
        }
        $proj = [
            'name'    => $name,
            'desc'    => $desc,
            'team_id' => $team_id
        ];
        if ($proj_id) {
            return model_proj::new()->value($proj)->where(['id', $proj_id])->save();
        } else {
            $this->begin();
            try {
                model_proj::new()->value($proj)->add();
                $proj_id = model_proj::new()->get_last_insert_id();
                model_menu::new()->value(['name' => '菜单管理', 'proj_id' => $proj_id])->add();
                model_menu::new()->value(['name' => '角色管理', 'proj_id' => $proj_id])->add();
                model_menu::new()->value(['name' => '成员管理', 'proj_id' => $proj_id])->add();
                $this->commit();
                return true;
            } catch (\Throwable $e) {
                $this->rollback();
                $this->fail(enum_err::SQL_ERROR, $e->getMessage());
            }
        }
    }

    /**
     * 项目删除
     *
     * @param int $proj_id
     *
     * @return bool
     */
    public function del(int $proj_id)
    {
        $uid     = $this->uid;
        $team_id = model_team::new()->where([['crt_id', $uid]])->fields('id')->get_val();
        if (empty($team_id)) {
            $this->fail(enum_err::INVALID_PARAMS);
        }
        return model_proj::new()->value(['status' => 1])->where(['id', $proj_id])->save();
    }
}