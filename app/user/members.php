<?php
/**
 * Created by PhpStorm.
 * User: 25791
 * Date: 2020/4/16
 * Time: 10:28
 * Note: members.php
 */

namespace app\user;


use app\lib\api;
use app\lib\enum\enum_err;
use app\lib\model\role_relation;
use app\lib\model\user as model_user;
use app\lib\model\team as model_team;

class members extends api
{
    /**
     * 成员列表
     *
     * @param int $proj_id
     * @param int $page
     * @param int $page_size
     *
     * @return array
     */
    public function list(int $proj_id, int $page, int $page_size)
    {
        $uid     = $this->uid;
        $team_id = model_user::new()->fields('team_id')->where(['id', $uid])->get_val();
        if (!$team_id) {
            $this->fail(enum_err::INVALID_PARAMS);
        }
        //获取团队的部分用户
        $members    = model_user::new()->fields('id', 'acc')->where(['team_id', $team_id])->get_page($page, $page_size);
        $crt_id     = model_team::new()->where(['id', $team_id])->fields('crt_id')->get_val();
        $member_ids = [0];
        if (!empty($members['list'])) {
            $member_ids = array_column($members['list'], 'id');
        }
        $member_roles = role_relation::new()->alias('a')
            ->join('role as b', ['a.role_id', 'b.id'], 'LEFT')
            ->where([['a.uid', $member_ids], ['a.status', 0], ['b.proj_id', $proj_id]])
            ->fields('a.uid', 'b.name as role_name')
            ->get(\PDO::FETCH_UNIQUE | \PDO::FETCH_COLUMN);
        foreach ($members['list'] as & $member) {
            if ($member['id'] == $crt_id) {
                $member['role_name'] = '创建者';
                continue;
            }
            $member['role_name'] = $member_roles[$member['id']] ?? '';
        }
        return $members;
    }

    /**
     * 分配角色
     *
     * @param int $proj_id
     * @param int $uid
     * @param int $role_id
     *
     * @return bool
     */
    public function role_ctrl(int $proj_id, int $uid, int $role_id)
    {
        if (model_team::new()->where(['crt_id', $uid])->exist()) {
            $this->fail(enum_err::CRATER_CTRL);
        }
        if ($role_id == 0) {
            return role_relation::new()->where([['uid', $uid], ['proj_id', $proj_id]])->value(['status' => 1])->save();
        }
        if (!role_relation::new()->where([['uid', $uid], ['proj_id', $proj_id]])->exist()) {
            return role_relation::new()->value(['uid' => $uid, 'proj_id' => $proj_id, 'role_id' => $role_id])->add();
        }
        return role_relation::new()->where([['uid', $uid], ['proj_id', $proj_id]])->value(['role_id' => $role_id])->save();
    }
}