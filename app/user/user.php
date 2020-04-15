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
use app\lib\model\team;
use app\lib\model\team_apply;
use app\lib\model\team_relation;
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
        $res['on_apply'] = (int)team_apply::new()->where([['uid', $acc_info['id']], ['status', 0]])->exist();
        return $res;
    }

    /**
     * 注册
     *
     * @param string $acc
     * @param string $pwd
     *
     * @return bool
     */
    public function register(string $acc, string $pwd)
    {
        $acc_info = model_user::new()->where(['acc', $acc])->get_one();
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
        return model_user::new()->value($value)->add();
    }
}