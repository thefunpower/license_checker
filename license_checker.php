<?php 
/*
    Copyright (c) 2021-2031, All rights reserved.
    This is NOT a freeware, use is subject to license terms 
    Connect Email: sunkangchina@163.com 
    Code Vesion: v1.0.x 
*/ 
class license
{
    public $rsa;
    public function __construct()
    {
        $this->rsa = new phpseclib\Crypt\RSA;
    }
    public function create()
    {
        return $this->rsa->createKey();
    }
    public function encode($data, $public_key)
    {
        $this->rsa->loadKey($public_key);
        $r = $this->rsa->encrypt($data);
        return base64_encode($r);
    }
    public function decode($data, $private_key)
    {
        $data = base64_decode($data);
        $this->rsa->loadKey($private_key);
        return $this->rsa->decrypt($data);
    }
}
/**
 * 根据公钥生成文件
 *
 * @param array $data
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return void
 */
function license_create($data, $file = null)
{
    if (!$data['title'] || !$data['company']) {
        return false;
    }
    $data['last_time'] = date('Y-m-d 00:00:00', strtotime($data['last_time']));
    $d          = json_encode($data);
    $public_key = file_get_contents(PATH . '/data/public_key.txt');
    $obj        = new license;
    $data       = $obj->encode($d, $public_key);
    if (!$file) {
        $file = PATH . '/data/license.crt';
        file_put_contents($file, $data);
    }
    return $data;
} 

/**
 * 获取授权信息
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return void
 */
function license_data($keep = false, $file = '')
{

    $time =  30;
    static $data;
    if ($keep && $data) {
        return $data;
    }
    $obj            = new license;
    if (!$file) {
        $file = PATH . '/data/license.crt';
    }
    $data           = file_get_contents($file);
    $private_key    = file_get_contents(PATH . '/data/private_key.txt');
    try {
        $data       = json_decode($obj->decode($data, $private_key), true);
        license_data_parse($data);
        return $data;
    } catch (Exception $e) {
    }
}
 

function license_data_parse(&$data)
{
    $less = ceil((strtotime($data['last_time']) - time()) / 86400);
    if ($less == 0) {
        $less = 0;
    }
    $data['last_real_time'] = $less . '天';
    if ($data['is_fover']) {
        $data['txt'] = "永久授权";
        $data['flag'] = 'ok';
        $data['last_real_time'] = "无限";
    } else if ($data['last_time'] >= date('Y-m-d') && date('Y-m-d', time() + $time * 86400) > $data['last_time']) {
        $data['txt'] = "即将过期,剩余" . $less . '天。';
        $data['flag'] = 'near';
    } else if ($data['last_time'] >= date('Y-m-d')) {
        $data['txt'] = "正常,剩余" . $less . '天。';
        $data['flag'] = 'normal';
    } else if ($data['last_time'] < date('Y-m-d')) {
        $data['flag'] = 'passed';
        $data['txt'] = "已过期";
    }
}
/**
 * 检测授权内容是否正确
 *
 * @param string $data
 * @param array $has
 * @return void
 */
function license_check($data, $has = [])
{
    $obj          = new license;
    $private_key  = file_get_contents(PATH . '/data/private_key.txt');
    try {
        $data   = json_decode($obj->decode($data, $private_key), true);
        if ($has) {
            foreach ($has as $k => $v) {
                if (!$data[$v]) {
                    return false;
                }
            }
        }
        return true;
    } catch (Exception $e) {
        return false;
    }
}
/**
 * 授权是否过期
 *
 * @return bool
 */
function license_is_expire()
{
    $data = license_data();
    if ($data['flag'] == 'passed') {
        return true;
    }
    return false;
}
/**
 * 授权是否接近过期
 *
 * @return void
 */
function license_near_expire()
{
    $data = license_data();
    if ($data['flag'] == 'near') {
        return true;
    }
    return false;
} 

 