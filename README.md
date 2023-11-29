##  授权 

## 安装 

~~~
composer require thefunpower/license_checker
~~~

依赖
~~~
"phpseclib/phpseclib": "^2.0",
~~~

## 使用

~~~
$lic = new \license;
//生成证书
//pr($lic->create());
$data = [
    'domain'=>'主域名',
    //过期时间
    'last_time'=>'2025-06-01',
    //永久有效
    //'is_fover'=>false,
];
license_create($data);
~~~
 


### 开源协议 

[Apache License 2.0](LICENSE)