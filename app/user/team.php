<?php
/**
 * Created by PhpStorm.
 * User: 25791
 * Date: 2020/4/14
 * Time: 10:20
 * Note: team.php
 */

namespace app\user;


use app\lib\api;
use app\lib\enum\enum_err;
use app\lib\model\role_relation;
use app\lib\model\team as model_team;
use app\lib\model\team_apply;
use app\lib\model\user as model_user;
use app\lib\model\team_relation;
use app\lib\pwd;
use Cassandra\Varint;

class team extends api
{
    /**
     * 团队信息
     *
     * @return array
     */
    public function info()
    {
        $uid     = $this->uid;
        $team_id = model_user::new()->fields('team_id')->where(['id', $uid])->get_val();
        if (empty($team_id)) {
            $this->fail(enum_err::NO_TEAM);
        }
        $info              = model_team::new()->where(['id', $team_id])->get_one();
        $info['apply_num'] = $info['crt_id'] == $uid ? team_apply::new()->where([['team_id', $team_id], ['status', 0]])->cnt() : 0;
        return $info;
    }

    /**
     * 创建团队
     *
     * @param string $name
     * @param string $desc
     *
     * @return bool
     */
    public function crt(string $name, string $desc = '')
    {
        $uid = $this->uid;
        if (team_relation::new()->where([['uid', $uid], ['status', 0]])->exist()) {
            $this->fail(enum_err::TEAM_MUST_OUT);
        }
        $this->begin();
        try {
            $mark = pwd::new()->rand_str(4);
            $team = [
                'name'   => $name,
                'desc'   => $desc,
                'crt_id' => $uid,
                'mark'   => $mark
            ];
            model_team::new()->value($team)->add();

            $team_id       = model_team::new()->get_last_insert_id();
            $team_relation = [
                'uid'     => $uid,
                'team_id' => $team_id
            ];
            team_relation::new()->value($team_relation)->add();

            //申请加入某一个团队，其他团队申请将撤销
            team_apply::new()->where([['uid', $uid], ['status', 0]])->value(['status' => 3])->save();

            model_user::new()->value(['team_id' => $team_id])->where([' id', $uid])->save();
            $this->commit();
            return true;
        } catch (\Throwable $e) {
            $this->rollback();
            $this->fail(enum_err::SQL_ERROR, $e->getMessage());
        }
    }

    /**
     * 加入团队
     *
     * @param string $mark
     * @param string $remarks
     *
     * @return bool
     */
    public function join_apply(string $mark, string $remarks = '')
    {
        $uid     = $this->uid;
        $team_id = model_user::new()->where(['id', $uid])->fields('team_id')->get_val();
        if ($team_id != 0) {
            $this->fail(enum_err::TEAM_MUST_OUT);
        }

        $team_info = model_team::new()->where([['mark', $mark], ['status', 0]])->get_one();
        if (empty($team_info)) {
            $this->fail(enum_err::MARK_ERROR);
        }
        $team_id = $team_info['id'];

        //申请加入某一个团队，其他团队申请将撤销
        team_apply::new()->where([['uid', $uid], ['status', 0]])->value(['status' => 3])->save();
        return team_apply::new()->value(['uid' => $uid, 'team_id' => $team_id, 'remarks' => $remarks])->add();
    }

    /**
     * 退出
     *
     * @return bool
     */
    public function out()
    {
        $uid = $this->uid;
        if (model_user::new()->where(['id', $uid])->fields('team_id')->get_val() == 0) {
            $this->fail(enum_err::NO_TEAM);
        }
        if (model_team::new()->where(['crt_id', $uid])->exist()) {
            $this->fail(enum_err::CRATER_OUT);
        }
        $this->begin();
        try {
            model_user::new()->value(['team_id' => 0])->where(['id', $uid])->save();
            team_relation::new()->value(['status' => 1])->where(['uid', $uid])->save();
            $this->commit();
            return true;
        } catch (\Throwable $e) {
            $this->rollback();
            $this->fail(enum_err::SQL_ERROR, $e->getMessage());
        }
    }

    /**
     * 申请列表
     *
     * @param int $page
     * @param int $page_size
     *
     * @return array
     */
    public function apply_list(int $page, int $page_size)
    {
        $uid     = $this->uid;
        $team_id = model_user::new()->where(['id', $uid])->fields('team_id')->get_val();
        if (!$team_id) {
            $this->fail(enum_err::NO_TEAM);
        }
        $crt_id = model_team::new()->where(['id', $team_id])->fields('crt_id')->get_val();
        if ($crt_id != $uid) {
            $this->fail(enum_err::INVALID_PARAMS);
        }
        return team_apply::new()
            ->alias('a')
            ->join('user as b', ['a.uid', 'b.id'], 'LEFT')
            ->fields('a.id', 'a.uid', 'b.acc', 'a.remarks', 'a.create')
            ->where([['a.team_id', $team_id], ['a.status', 0]])
            ->get_page($page, $page_size);
    }

    /**
     * 申请处理
     *
     * @param int $apply_id
     * @param int $agree
     *
     * @return bool
     */
    public function apply_handle(int $apply_id, int $agree)
    {
        $uid        = $this->uid;
        $apply_info = team_apply::new()->where([['id', $apply_id], ['status', 0]])->get_one();
        if (empty($apply_info)) {
            $this->fail(enum_err::INVALID_PARAMS);
        }
        $team_id   = $apply_info['team_id'];
        $apply_uid = $apply_info['uid'];
        //必须创建者操作
        $crt_id = model_team::new()->where(['id', $apply_info['team_id']])->fields('crt_id')->get_val();
        if ($crt_id != $uid) {
            $this->fail(enum_err::INVALID_PARAMS);
        }
        if ($agree != 1) {
            return team_apply::new()->where([['id', $apply_id], ['status', 0]])->value(['status' => 2])->save();
        }
        $this->begin();
        try {
            team_apply::new()->where([['id', $apply_id], ['status', 0]])->value(['status' => 1])->save();
            model_user::new()->where(['id', $apply_uid])->value(['team_id' => $team_id])->save();
            if (team_relation::new()->where([['uid', $apply_uid]])->exist()) {
                team_relation::new()->where(['uid', $apply_uid])->value(['team_id' => $team_id, 'status' => 0])->save();
            } else {
                team_relation::new()->value(['uid' => $apply_uid, 'team_id' => $team_id])->add();
            }
            $this->commit();
            return true;
        } catch (\Throwable $e) {
            $this->rollback();
            $this->fail(enum_err::SQL_ERROR, $e->getMessage());
        }
    }

    /**
     * 团队成员
     *
     * @param int $page
     * @param int $page_size
     *
     * @return array
     */
    public function members(int $page, int $page_size)
    {
        $uid     = $this->uid;
        $team_id = model_team::new()->where([['crt_id', $uid], ['status', 0]])->fields('id')->get_val();
        if (empty($team_id)) {
            $this->fail(enum_err::INVALID_PARAMS);
        }
        $members        = team_relation::new()->alias('a')
            ->join('user as b', ['a.uid', 'b.id'], 'LEFT')
            ->where([['a.team_id', $team_id], ['a.status', 0], ['uid', '<>', $uid]])
            ->fields('b.id', 'b.acc', 'b.create_time')
            ->get_page($page, $page_size);
        $uids           = array_column($members['list'], 'id');
        $role_relations = [];
        if ($uids) {
            $role_relations = role_relation::new()
                ->alias('a')
                ->join('role as b', ['a.role_id', 'b.id'], 'LEFT')
                ->join('proj as c', ['a.proj_id', 'c.id'], 'LEFT')
                ->where([['a.uid', 'IN', $uids], ['a.status', 0]])
                ->fields('a.uid', 'c.name as proj_name', 'b.name as role_name')
                ->get();
        }
        foreach ($members['list'] as &$member) {
            $add = [];
            foreach ($role_relations as $role_relation) {
                if ($role_relation['uid'] == $member['id']) {
                    $add[] = ['proj_name' => $role_relation['proj_name'], 'role_name' => $role_relation['role_name']];
                }
            }
            $member['role_info'] = $add;
        }
        return $members;
    }

    /**
     * 踢出用户
     *
     * @param int $uid
     *
     * @return bool
     */
    public function kick(int $uid)
    {
        $now_uid = $this->uid;
        if ($now_uid == $uid) {
            $this->fail(enum_err::INVALID_PARAMS);
        }
        //创建者才能操作
        $team_id = model_team::new()->where([['crt_id', $now_uid], ['status', 0]])->fields('id')->get_val();
        if (empty($team_id)) {
            $this->fail(enum_err::INVALID_PARAMS);
        }
        //是否操作的本团队用户
        if (!team_relation::new()->where([['uid', $uid], ['team_id', $team_id], ['status', 0]])->exist()) {
            $this->fail(enum_err::INVALID_PARAMS);
        }
        $this->begin();
        try {
            model_user::new()->where(['id', $uid])->value(['team_id' => 0])->save();
            team_relation::new()->where(['uid', $uid])->value(['status' => 1])->save();
            role_relation::new()->where(['uid', $uid])->value(['status' => 1])->save();
            $this->commit();
            return true;
        } catch (\Throwable $e) {
            $this->rollback();
            $this->fail(enum_err::SQL_ERROR, $e->getMessage());
        }
    }
}