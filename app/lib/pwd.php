<?php
/**
 * Created by PhpStorm.
 * User: 25791
 * Date: 2020/3/30
 * Time: 9:47
 * Note: pwd.php
 */

namespace app\lib;


class pwd extends base
{
    /**
     * 生成安全密码
     * @param string $pwd   密码
     * @param string $salt  用户id
     *
     * @return string
     */
    public function make_safe_pwd(string $pwd, string $salt)
    {
        return md5(md5($pwd) . md5($salt));
    }

    public function rand_str($len = 6, $type = 'str')
    {
        if ($type == 'str') {
            $arr = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
        } else {
            $arr = array_merge(range(0, 9));
        }
        shuffle($arr);
        $sub_arr = array_slice($arr, 0, $len);
        return implode('', $sub_arr);
    }
}