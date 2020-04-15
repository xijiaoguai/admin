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

namespace app\lib;

use app\lib\enum\enum_err;
use core\lib\stc\factory;
use core\lib\std\pool;
use ext\core;
use ext\errno;

/**
 * Class api
 *
 * 所有对外 API 暴露类请继承这个
 *
 * @package app\lib
 */
class api extends base
{
    public $tz = '*';

    public $uid;

    public $check_token = true;
    //过滤保留字段（不过滤）
    const ESCAPE_EXCLUDE = ['token'];

    public function __construct()
    {
        parent::__construct();

        //默认操作成功，具体状态码在业务中修改
        errno::set(200, 1, 'OK!');

        //入参过滤
        $this->escape(factory::build(pool::class), core::get_data());

        //解析token
        $this->check_token();
    }

    /**
     * 解析token
     *
     * @throws \Exception
     */
    public function check_token()
    {
        if ($this->check_token) {
            $token = core::get_data('token');
            if (empty($token)) {
                $this->fail(enum_err::TOKEN_ERROR);
            }
            $data = token::new()->parse($token, 'uid');
            if ($data['status'] !== 0) {
                $this->fail(enum_err::TOKEN_ERROR);
            }
            $this->uid = (int)$data['data']['uid'];
        }
    }

    /**
     * 过滤输入
     *
     * @param \core\lib\std\pool $unit_pool
     * @param array              $input
     */
    private function escape(pool $unit_pool, array $input): void
    {
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $this->escape($unit_pool, $value);
                continue;
            }

            if (in_array($key, self::ESCAPE_EXCLUDE, true)) {
                continue;
            }

            if (is_object($value)) {
                continue;
            }

            $unit_pool->data[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }
}