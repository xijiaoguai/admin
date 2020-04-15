<?php
/**
 * Git Get
 *
 * Copyright 2019-2020 leo <2579186091@qq.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace app\lib\enum;


use ext\errno;

class enum_err
{
    const SUCCESS = '200-OK';

    const TOKEN_ERROR    = '1001-token错误，请重新登录';
    const SQL_ERROR      = '1002-sql错误';
    const INVALID_PARAMS = '1003-参数错误';

    const USER_NOT_EXIST = '2001-该用户不存在';
    const PWD_ERROR      = '2002-密码错误';
    const USER_EXISTS    = '2003-用户已存在';
    const TEAM_NOT_EXIST = '2004-团队不存在';
    const TEAM_MUST_OUT  = '2005-创建或加入团队之前请先退出上一个';
    const NO_TEAM        = '2006-尚未加入团队';
    const MARK_ERROR     = '2007-该团队不存在';
    const CRATER_OUT     = '2008-创建者不能推出团队';

    public static function get_code($msg)
    {
        $arr = explode('-', $msg);
        return [
            'code' => $arr[0],
            'msg'  => $arr[1]
        ];
    }
}