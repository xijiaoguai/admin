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
use app\lib\model\user as model_user;
use app\lib\model\proj as model_proj;
use app\lib\model\team as model_team;

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
        return model_proj::new()
            ->fields('id', 'name', 'desc')
            ->where([['team_id', $team_id], ['status', 0]])
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
            return model_proj::new()->value($proj)->add();
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