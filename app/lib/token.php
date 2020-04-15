<?php
/**
 * Created by PhpStorm.
 * User: Reck
 * Date: 2020/1/13
 * Time: 15:39
 * Note: token.php
 */

namespace app\lib;

use ext\crypt;
use ext\keygen;

class token extends base
{
    //token 寿命
    const LIFE = 86400 * 7;

    /** @var \ext\crypt $crypt */
    public $crypt;

    /**
     * token constructor.
     */
    public function __construct()
    {
        parent::__construct();
        //todo 需要修改 kengen
        $this->crypt = crypt::new()->set_keygen(keygen::class);
    }

    /**
     * @param array  $data
     * @param string $key
     *
     * @return string
     * @throws \Exception
     */
    public function make(array $data, string $key): string
    {
        $token = $this->crypt->sign(json_encode($data, JSON_FORMAT));

        //合成 token key
        $token_key = 'tk:' . (isset($data['tk_key']) ? $data['tk_key'] : 'main') . ':' . $data[$key];

        //延长token周期
        $this->redis->set($token_key, hash('md5', $token), self::LIFE);

        return $token;
    }

    /**
     * @param string $token
     * @param string $key
     *
     * @return array
     * @throws \Exception
     */
    public function parse(string $token, string $key): array
    {
        //get token data
        $token_data = $this->get_token_data($token, $key);

        //Failed to get data
        if (0 !== $token_data['status']) {
            return [
                'status' => &$token_data['status'],
                'data'   => []
            ];
        }

        //获取token hash
        $token_hash = $this->redis->get($token_data['key']);

        //token错误或已过期
        if (false === $token_hash) {
            return [
                'status' => 3,
                'data'   => []
            ];
        }

        //被挤下去了
        if ($token_hash !== hash('md5', $token)) {
            return [
                'status' => 4,
                'data'   => []
            ];
        }

        //延长token周期
        $this->redis->expire($token_data['key'], self::LIFE);

        return [
            'status' => 0,
            'data'   => &$token_data['data']
        ];
    }

    /**
     * 删除一个 token
     *
     * @param string $token
     * @param string $key
     *
     * @return int
     * @throws \Exception
     */
    public function remove(string $token, string $key): int
    {
        //get token data
        $token_data = $this->get_token_data($token, $key);

        //Failed to get data
        if (0 !== $token_data['status']) {
            return 0;
        }

        return $this->redis->del($token_data['key']);
    }

    /**
     * 获取 token 相关数据
     *
     * @param string $token
     * @param string $key
     *
     * @return array
     * @throws \Exception
     */
    private function get_token_data(string $token, string $key): array
    {
        //解密
        $json = $this->crypt->verify($token);

        //token无法解密
        if ('' === $json) {
            return [
                'key'    => '',
                'data'   => [],
                'status' => 1
            ];
        }

        $data = json_decode($json, true);

        //token中丢失user_id
        if (!is_array($data) || !isset($data[$key])) {
            return [
                'key'    => '',
                'data'   => [],
                'status' => 2
            ];
        }

        //合成 token key
        $key = 'tk:' . (isset($data['tk_key']) ? $data['tk_key'] : 'main') . ':' . $data[$key];

        return [
            'key'    => &$key,
            'data'   => &$data,
            'status' => 0
        ];
    }
}