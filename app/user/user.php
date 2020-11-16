<?php
/**
 * Created by PhpStorm.
 * User: 25791
 * Date: 2020/4/13
 * Time: 17:26
 * Note: user.php
 */

namespace app\user;


use app\lib\api;
use app\lib\enum\enum_err;
use app\lib\model\menu_relation;
use app\lib\model\role_relation;
use app\lib\model\team;
use app\lib\model\team as model_team;
use app\lib\model\team_apply;
use app\lib\pwd;
use app\lib\token;
use app\lib\model\user as model_user;

class user extends api
{
    public $check_token = false;

    /**
     * 登录
     *
     * @param string $acc
     * @param string $pwd
     *
     * @return mixed
     * @throws \Exception
     */
    public function login(string $acc, string $pwd)
    {
        $acc_info = model_user::new()->where(['acc', $acc])->get_one();
        if (empty($acc_info)) {
            $this->fail(enum_err::USER_NOT_EXIST);
        }
        $make_pwd = pwd::new()->make_safe_pwd($pwd, $acc_info['entry']);
        if ($make_pwd != $acc_info['pwd']) {
            $this->fail(enum_err::PWD_ERROR);
        }
        $res['token']      = token::new()->make(['uid' => $acc_info['id']], 'uid');
        $res['have_team']  = $acc_info['team_id'] ? 1 : 0;
        $res['is_creator'] = (int)team::new()->where(['crt_id', $acc_info['id']])->exist();
        $res['apply_num']  = $res['is_creator'] ? team_apply::new()->where([['team_id', $acc_info['team_id']], ['status', 0]])->cnt() : 0;
        $res['on_apply']   = (int)team_apply::new()->where([['uid', $acc_info['id']], ['status', 0]])->exist();
        return $res;
    }

    /**
     * 注册
     *
     * @param string $acc
     * @param string $pwd
     *
     * @return mixed
     * @throws \Exception
     */
    public function register(string $acc, string $pwd)
    {
        $model_user = model_user::new();
        $acc_info   = $model_user->where(['acc', $acc])->get_one();
        if (!empty($acc_info)) {
            $this->fail(enum_err::USER_EXISTS);
        }
        $entry    = pwd::new()->rand_str();
        $make_pwd = pwd::new()->make_safe_pwd($pwd, $entry);
        $value    = [
            'acc'   => $acc,
            'pwd'   => $make_pwd,
            'entry' => $entry,
        ];
        $model_user->value($value)->add();
        $uid          = $model_user->get_last_insert_id();
        $res['token'] = token::new()->make(['uid' => $uid], 'uid');
        return $res;
    }

    public function auth_info(string $token)
    {
        $res     = [
            'result'   => 0,
            'uid'      => 0,
            'menu_id'  => 0,
            'proj_id'  => 0,
            'user_acc' => ''
        ];
        $arr     = explode("*", $token);
        $token   = $arr[0] ?? '';
        $menu_id = $arr[1] ?? '';
        $proj_id = $arr[2] ?? '';
        $data    = token::new()->parse($token, 'uid');
        if ($data['status'] !== 0) {
            $res['result'] = 1;
            return $res;
        }
        $uid    = (int)$data['data']['uid'];
        $res['uid'] = $uid;
        $result = $this->auth($uid, $menu_id, $proj_id);
        if ($result !== 0) {
            $res['result'] = $result;
            return $res;
        }
        $res['user_acc'] = \app\lib\model\user::new()->where(['uid',$uid])->fields('acc')->get_val();
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